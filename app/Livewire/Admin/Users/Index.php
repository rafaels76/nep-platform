<?php

namespace App\Livewire\Admin\Users;

use App\Models\Company;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?string $selectedUserId = null;
    public ?string $selectedCompanyId = null;
    public ?string $selectedRole = null;

    public array $availableRoles = [];

    public function openRoleModal(string $userId): void
    {
        $this->selectedUserId = $userId;
        $this->selectedCompanyId = null;
        $this->selectedRole = null;
        $this->availableRoles = [];
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function updatedSelectedCompanyId(?string $value): void
    {
        $this->selectedRole = null;

        if (! $value) {
            $this->availableRoles = [];
            return;
        }

        $this->availableRoles = Role::where('team_id', $value)
            ->pluck('name')
            ->toArray();
    }

    public function assignRole(): void
    {
        $this->validate([
            'selectedCompanyId' => ['required', 'exists:companies,id'],
            'selectedRole' => ['required', 'string'],
        ]);

        $registrar = app(PermissionRegistrar::class);
        $originalTeamId = $registrar->getPermissionsTeamId();

        $user = User::findOrFail($this->selectedUserId);
        $company = Company::findOrFail($this->selectedCompanyId);

        $registrar->setPermissionsTeamId($company->id);
        $user->unsetRelation('roles');
        $user->syncRoles([$this->selectedRole]);

        $user->companies()->syncWithoutDetaching([
            $company->id => ['role' => $this->selectedRole],
        ]);

        $registrar->setPermissionsTeamId($originalTeamId);

        $this->showModal = false;
        session()->flash('status', 'Rol asignado correctamente.');
    }

    public function removeAccess(string $userId, string $companyId): void
    {
        $registrar = app(PermissionRegistrar::class);
        $originalTeamId = $registrar->getPermissionsTeamId();

        $user = User::findOrFail($userId);

        $registrar->setPermissionsTeamId($companyId);
        $user->unsetRelation('roles');
        $user->syncRoles([]);

        $user->companies()->detach($companyId);

        $registrar->setPermissionsTeamId($originalTeamId);

        session()->flash('status', 'Acceso removido correctamente.');
    }

    public function render()
    {
        $users = User::with('companies')->paginate(10);
        $companies = Company::orderBy('name')->get();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'companies' => $companies,
        ]);
    }
}