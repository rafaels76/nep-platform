<?php

namespace App\Livewire\Admin\Users;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.app')]
class Create extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $type = 'company_user';

    public ?string $selectedCompanyId = null;
    public ?string $selectedRole = null;
    public array $availableRoles = [];

    public function updatedType(): void
    {
        $this->selectedCompanyId = null;
        $this->selectedRole = null;
        $this->availableRoles = [];
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

    public function save()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'type' => ['required', 'in:ecosystem_admin,company_user,specialist'],
        ];

        if ($this->type !== 'ecosystem_admin') {
            $rules['selectedCompanyId'] = ['required', 'exists:companies,id'];
            $rules['selectedRole'] = ['required', 'string'];
        }

        $this->validate($rules);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'type' => $this->type,
            'email_verified_at' => now(),
        ]);

        $registrar = app(PermissionRegistrar::class);

        if ($this->type === 'ecosystem_admin') {
            $registrar->setPermissionsTeamId(0);
            $user->assignRole('admin');
        } else {
            $company = Company::findOrFail($this->selectedCompanyId);

            $registrar->setPermissionsTeamId($company->id);
            $user->assignRole($this->selectedRole);

            $user->companies()->syncWithoutDetaching([
                $company->id => ['role' => $this->selectedRole],
            ]);
        }

        session()->flash('status', 'Usuario creado correctamente.');

        return redirect()->route('admin.usuarios.index');
    }

    public function render()
    {
        $companies = Company::orderBy('name')->get();

        return view('livewire.admin.users.create', ['companies' => $companies]);
    }
}