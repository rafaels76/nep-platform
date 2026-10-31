<?php

namespace App\Livewire;

use App\Enums\CaseStatus;
use App\Models\Company;
use App\Models\OperationalCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        // Sin empresa activa se muestra un aviso en vez de un 403
        abort_unless($user->can('dashboard.ver') || ! $user->activeCompany(), 403);
    }

    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $isSpecialist = $user->type === 'specialist';
        $company = $user->activeCompany();

        $data = [
            'company' => $company,
            'isAdmin' => $isAdmin,
            'isSpecialist' => $isSpecialist,
        ];

        if ($company) {
            $data += $this->companyMetrics($user, $company, $isSpecialist);
        }

        if ($isAdmin) {
            $data['global'] = $this->globalMetrics();
        }

        return view('livewire.dashboard', $data);
    }

    protected function companyMetrics(User $user, Company $company, bool $isSpecialist): array
    {
        $closed = CaseStatus::Cerrado->value;

        $base = OperationalCase::visibleTo($user, $company);
        $open = (clone $base)->where('status', '!=', $closed);

        $byStatus = (clone $base)->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = collect(CaseStatus::cases())->map(fn(CaseStatus $status) => [
            'status' => $status,
            'total' => (int) ($byStatus[$status->value] ?? 0),
        ]);

        $closedRecently = (clone $base)
            ->where('status', $closed)
            ->where('closed_at', '>=', now()->subDays(30))
            ->count();

        $closedCases = (clone $base)
            ->where('status', $closed)
            ->whereNotNull('closed_at')
            ->where('closed_at', '>=', now()->subDays(90))
            ->get(['created_at', 'closed_at']);

        $avgDays = $closedCases->isEmpty()
            ? null
            : $closedCases->avg(fn($case) => abs($case->created_at->diffInHours($case->closed_at))) / 24;

        $recent = $isSpecialist
            ? (clone $open)->with('assignee')->oldest()->take(6)->get()
            : (clone $base)->with('assignee')->orderByDesc('updated_at')->take(6)->get();

        return [
            'statusCounts' => $statusCounts,
            'total' => $statusCounts->sum('total'),
            'openCount' => (clone $open)->count(),
            'unassigned' => $isSpecialist ? null : (clone $open)->whereNull('assigned_to')->count(),
            'closedRecently' => $closedRecently,
            'avgDays' => $avgDays,
            'recent' => $recent,
            'recentTitle' => $isSpecialist ? 'Mis casos abiertos (los más antiguos primero)' : 'Actividad reciente',
            'workload' => $isSpecialist ? collect() : $this->workload($open, $company),
        ];
    }

    /**
     * Casos abiertos por freelancer: incluye a los activos sin carga
     * y a cualquiera que tenga casos abiertos, aunque esté deshabilitado.
     */
    protected function workload(Builder $open, Company $company): Collection
    {
        $loads = (clone $open)
            ->whereNotNull('assigned_to')
            ->toBase()
            ->selectRaw('assigned_to, count(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $activeIds = $company->users()
            ->where('users.type', 'specialist')
            ->where('users.status', 'active')
            ->pluck('users.id');

        return User::query()
            ->whereIn('id', $loads->keys()->merge($activeIds)->unique()->values())
            ->get(['id', 'name', 'status'])
            ->map(fn(User $person) => [
                'id' => $person->id,
                'name' => $person->name,
                'disabled' => $person->isDisabled(),
                'total' => (int) ($loads[$person->id] ?? 0),
            ])
            ->sortBy([['total', 'desc'], ['name', 'asc']])
            ->values();
    }

    protected function globalMetrics(): array
    {
        $closed = CaseStatus::Cerrado->value;

        $companies = Company::query()->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $users = User::query()->toBase()
            ->selectRaw('type, status, count(*) as total')
            ->groupBy('type', 'status')
            ->get();

        $cases = OperationalCase::query()->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeUsers = $users->where('status', 'active');

        return [
            'companies' => [
                'total' => (int) $companies->sum(),
                'active' => (int) ($companies['active'] ?? 0),
                'inactive' => (int) ($companies['inactive'] ?? 0),
                'suspended' => (int) ($companies['suspended'] ?? 0),
            ],
            'users' => [
                'active' => (int) $activeUsers->sum('total'),
                'disabled' => (int) $users->where('status', 'disabled')->sum('total'),
                'admins' => (int) $activeUsers->where('type', 'ecosystem_admin')->sum('total'),
                'companyUsers' => (int) $activeUsers->where('type', 'company_user')->sum('total'),
                'specialists' => (int) $activeUsers->where('type', 'specialist')->sum('total'),
            ],
            'cases' => [
                'total' => (int) $cases->sum(),
                'open' => (int) $cases->except($closed)->sum(),
                'unassigned' => OperationalCase::query()
                    ->where('status', '!=', $closed)
                    ->whereNull('assigned_to')
                    ->count(),
            ],
            'topCompanies' => Company::query()
                ->withCount(['cases as open_cases_count' => fn($q) => $q->where('status', '!=', $closed)])
                ->orderByDesc('open_cases_count')
                ->orderBy('name')
                ->take(5)
                ->get(),
        ];
    }
}
