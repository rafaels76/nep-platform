<?php

namespace App\Livewire\Cases;

use App\Enums\CaseStatus;
use App\Models\OperationalCase;
use App\Models\User;
use App\Orchestration\OrchestrationEngine;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.app')]
class Show extends Component
{
    public string $caseId;

    public function mount(string $caseId): void
    {
        abort_unless(auth()->user()->can('casos.ver'), 403);

        $this->caseId = $caseId;
        $this->findCase(); // 404 si no existe o no es visible para este usuario
    }

    public function assign(?string $specialistId, OrchestrationEngine $engine): void
    {
        abort_unless($this->canAssign(), 403);

        if (! $specialistId) {
            return;
        }

        $engine->assign($this->findCase(), User::findOrFail($specialistId));

        session()->flash('status', 'Caso asignado correctamente.');
    }

    public function transition(string $to, OrchestrationEngine $engine): void
    {
        abort_unless(auth()->user()->can('casos.gestionar'), 403);

        $engine->transition($this->findCase(), CaseStatus::from($to));

        session()->flash('status', 'Estado actualizado correctamente.');
    }

    protected function findCase(): OperationalCase
    {
        $company = auth()->user()->activeCompany();

        abort_unless($company, 404);

        return OperationalCase::with(['company', 'creator', 'assignee'])
            ->where('company_id', $company->id)
            ->when(
                auth()->user()->type === 'specialist',
                fn ($q) => $q->where('assigned_to', auth()->id())
            )
            ->findOrFail($this->caseId);
    }

    protected function canAssign(): bool
    {
        return auth()->user()->type !== 'specialist'
            && auth()->user()->can('casos.gestionar');
    }

    /**
     * Historial cronológico del caso a partir del log de actividad.
     */
    protected function timeline(OperationalCase $case): Collection
    {
        $activities = Activity::query()
            ->where('subject_type', OperationalCase::class)
            ->where('subject_id', $case->id)
            ->with('causer')
            ->orderBy('id')
            ->get();

        $hasOrchestration = $activities->contains('log_name', 'orchestration');

        // Nombres de los usuarios que aparecen en cambios de asignación
        $userIds = $activities
            ->flatMap(function ($activity) {
                $props = $activity->properties->toArray();

                return [data_get($props, 'attributes.assigned_to'), data_get($props, 'old.assigned_to')];
            })
            ->filter()
            ->unique()
            ->values();

        $names = User::whereIn('id', $userIds)->pluck('name', 'id');

        $entries = collect();

        foreach ($activities as $activity) {
            $props = $activity->properties->toArray();
            $base = [
                'at' => $activity->created_at,
                'who' => $activity->causer?->name ?? 'Sistema',
            ];

            if ($activity->log_name === 'orchestration') {
                $entries->push($base + [
                    'title' => 'Caso creado',
                    'detail' => 'Canal: '.($props['channel'] ?? 'web')
                        .' · Decisión del Motor: '.str_replace('_', ' ', $props['decision'] ?? 'sin dato'),
                ]);
                continue;
            }

            if ($activity->event === 'created') {
                if (! $hasOrchestration) {
                    $entries->push($base + ['title' => 'Caso creado', 'detail' => null]);
                }
                continue;
            }

            if ($activity->event === 'updated') {
                $old = $props['old'] ?? [];

                foreach ($props['attributes'] ?? [] as $field => $value) {
                    $entries->push($base + $this->describeChange($field, $old[$field] ?? null, $value, $names));
                }
                continue;
            }

            if ($activity->event === 'deleted') {
                $entries->push($base + ['title' => 'Caso eliminado', 'detail' => null]);
            }
        }

        return $entries;
    }

    protected function describeChange(string $field, mixed $old, mixed $new, Collection $names): array
    {
        return match ($field) {
            'status' => [
                'title' => 'Cambio de estado',
                'detail' => $this->statusLabel($old).' → '.$this->statusLabel($new),
            ],
            'assigned_to' => [
                'title' => $new ? 'Caso asignado' : 'Asignación retirada',
                'detail' => $new
                    ? ($old ? ($names->get($old) ?? "Usuario #{$old}").' → ' : '').($names->get($new) ?? "Usuario #{$new}")
                    : null,
            ],
            'title' => ['title' => 'Título modificado', 'detail' => "{$old} → {$new}"],
            'type' => ['title' => 'Tipo modificado', 'detail' => "{$old} → {$new}"],
            default => ['title' => "Campo «{$field}» modificado", 'detail' => null],
        };
    }

    protected function statusLabel(mixed $value): string
    {
        return CaseStatus::tryFrom((string) $value)?->label() ?? ($value ?: '—');
    }

    public function render()
    {
        $case = $this->findCase();

        $specialists = $this->canAssign()
            ? $case->company->users()
                ->where('users.type', 'specialist')
                ->where('users.status', 'active')
                ->orderBy('users.name')
                ->get()
            : collect();

        return view('livewire.cases.show', [
            'case' => $case,
            'timeline' => $this->timeline($case),
            'specialists' => $specialists,
            'canAssign' => $this->canAssign(),
        ]);
    }
}