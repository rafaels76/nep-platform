<?php

namespace App\Livewire\Cases;

use App\Enums\CaseStatus;
use App\Models\OperationalCase;
use App\Models\User;
use App\Orchestration\Interaction;
use App\Orchestration\OrchestrationEngine;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $title = '';
    public string $description = '';
    public bool $showForm = false;

    #[Url(as: 'estado')]
    public string $status = '';

    #[Url(as: 'asignado')]
    public string $assigned = '';

    #[Url(as: 'cierre')]
    public string $closedDays = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('casos.ver'), 403);
    }

    public function updatedStatus(): void
    {
        $this->closedDays = '';
        $this->resetPage();
    }

    public function updatedAssigned(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['status', 'assigned', 'closedDays']);
        $this->resetPage();
    }

    public function create(OrchestrationEngine $engine): void
    {
        abort_unless(auth()->user()->can('casos.crear'), 403);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $company = auth()->user()->activeCompany();

        try {
            $engine->handle(new Interaction(
                companyId: $company->id,
                userId: auth()->id(),
                title: $this->title,
                description: $this->description ?: null,
            ));
        } catch (AuthorizationException $e) {
            $this->addError('title', $e->getMessage());
            return;
        }

        // Se limpian los filtros para que el caso nuevo no quede oculto
        $this->reset(['title', 'description', 'showForm', 'status', 'assigned', 'closedDays']);
        $this->resetPage();

        session()->flash('status', 'Caso creado correctamente.');
    }

    public function assign(string $caseId, ?string $specialistId, OrchestrationEngine $engine): void
    {
        abort_unless($this->canAssign(), 403);

        if (! $specialistId) {
            return;
        }

        $engine->assign($this->findCase($caseId), User::findOrFail($specialistId));

        session()->flash('status', 'Caso asignado correctamente.');
    }

    public function transition(string $caseId, string $to, OrchestrationEngine $engine): void
    {
        abort_unless(auth()->user()->can('casos.gestionar'), 403);

        $engine->transition($this->findCase($caseId), CaseStatus::from($to));

        session()->flash('status', 'Estado actualizado correctamente.');
    }

    protected function findCase(string $caseId): OperationalCase
    {
        return OperationalCase::visibleTo(auth()->user())->findOrFail($caseId);
    }

    protected function isSpecialist(): bool
    {
        return auth()->user()->type === 'specialist';
    }

    protected function canAssign(): bool
    {
        return ! $this->isSpecialist() && auth()->user()->can('casos.gestionar');
    }

    /** Los valores de la URL los controla el usuario: solo se aceptan los conocidos. */
    protected function statusFilter(): string
    {
        $valid = array_merge(['abiertos'], array_map(fn (CaseStatus $s) => $s->value, CaseStatus::cases()));

        return in_array($this->status, $valid, true) ? $this->status : '';
    }

    protected function assignedFilter(): string
    {
        if ($this->isSpecialist()) {
            return '';
        }

        return $this->assigned === 'none' || ctype_digit($this->assigned) ? $this->assigned : '';
    }

    protected function closedDaysFilter(): ?int
    {
        return $this->closedDays === '30' ? 30 : null;
    }

    public function render()
    {
        $user = auth()->user();
        $company = $user->activeCompany();

        $status = $this->statusFilter();
        $assigned = $this->assignedFilter();
        $days = $this->closedDaysFilter();

        $cases = OperationalCase::with(['creator', 'assignee'])
            ->visibleTo($user, $company)
            ->when($status === 'abiertos', fn ($q) => $q->where('status', '!=', CaseStatus::Cerrado->value))
            ->when($status !== '' && $status !== 'abiertos', fn ($q) => $q->where('status', $status))
            ->when($assigned === 'none', fn ($q) => $q->whereNull('assigned_to'))
            ->when($assigned !== '' && $assigned !== 'none', fn ($q) => $q->where('assigned_to', (int) $assigned))
            ->when($days, fn ($q) => $q->where('closed_at', '>=', now()->subDays($days)))
            ->latest()
            ->paginate(10);

        $specialists = $company
            ? $company->users()
                ->where('users.type', 'specialist')
                ->where('users.status', 'active')
                ->orderBy('users.name')
                ->get()
            : collect();

        // Para filtrar: freelancers activos y cualquiera con casos en esta empresa (aunque esté deshabilitado)
        $filterAssignees = collect();

        if ($company && ! $this->isSpecialist()) {
            $assignedIds = OperationalCase::where('company_id', $company->id)
                ->whereNotNull('assigned_to')
                ->distinct()
                ->pluck('assigned_to');

            $filterAssignees = User::whereIn('id', $assignedIds->merge($specialists->pluck('id'))->unique()->values())
                ->orderBy('name')
                ->get(['id', 'name', 'status']);
        }

        return view('livewire.cases.index', [
            'company' => $company,
            'cases' => $cases,
            'specialists' => $specialists,
            'canAssign' => $this->canAssign(),
            'isSpecialist' => $this->isSpecialist(),
            'filterAssignees' => $filterAssignees,
            'closedDaysActive' => $days,
            'hasFilters' => $status !== '' || $assigned !== '' || $days !== null,
        ]);
    }
}