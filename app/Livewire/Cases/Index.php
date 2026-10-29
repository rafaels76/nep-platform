<?php

namespace App\Livewire\Cases;

use App\Enums\CaseStatus;
use App\Models\OperationalCase;
use App\Models\User;
use App\Orchestration\Interaction;
use App\Orchestration\OrchestrationEngine;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $title = '';
    public string $description = '';
    public bool $showForm = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('casos.ver'), 403);
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

        $this->reset(['title', 'description', 'showForm']);
        session()->flash('status', 'Caso creado correctamente.');
    }

    public function assign(string $caseId, ?string $specialistId, OrchestrationEngine $engine): void
    {
        abort_unless($this->canAssign(), 403);

        if (! $specialistId) {
            return;
        }

        $case = $this->findCase($caseId);
        $engine->assign($case, User::findOrFail($specialistId));

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
        $query = OperationalCase::where('company_id', auth()->user()->activeCompany()->id);

        if (auth()->user()->type === 'specialist') {
            $query->where('assigned_to', auth()->id());
        }

        return $query->findOrFail($caseId);
    }

    protected function canAssign(): bool
    {
        return auth()->user()->type !== 'specialist'
            && auth()->user()->can('casos.gestionar');
    }

    public function render()
    {
        $company = auth()->user()->activeCompany();

        $cases = OperationalCase::with(['creator', 'assignee'])
            ->where('company_id', $company?->id)
            ->when(auth()->user()->type === 'specialist',
                fn ($q) => $q->where('assigned_to', auth()->id()))
            ->latest()
            ->paginate(10);

        $specialists = $company
            ? $company->users()
                ->where('users.type', 'specialist')
                ->where('users.status', 'active')
                ->orderBy('users.name')
                ->get()
            : collect();

        return view('livewire.cases.index', [
            'company' => $company,
            'cases' => $cases,
            'specialists' => $specialists,
            'canAssign' => $this->canAssign(),
        ]);
    }
}