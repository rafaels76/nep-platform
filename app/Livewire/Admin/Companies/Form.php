<?php

namespace App\Livewire\Admin\Companies;

use App\Models\Company;
use App\Services\CompanyRoleProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?Company $company = null;

    public string $name = '';
    public string $legal_name = '';
    public string $tax_id = '';
    public string $status = 'active';

    public function mount(?Company $company = null): void
    {
        if ($company?->exists) {
            $this->company = $company;
            $this->name = $company->name;
            $this->legal_name = $company->legal_name ?? '';
            $this->tax_id = $company->tax_id ?? '';
            $this->status = $company->status;
        }
    }

    public function save(CompanyRoleProvisioner $provisioner)
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('companies', 'tax_id')
                    ->ignore($this->company?->id)
                    ->whereNull('deleted_at'),
            ],
            'status' => ['required', Rule::in(array_keys(Company::STATUSES))],
        ]);

        $data['legal_name'] = $data['legal_name'] ?: null;

        if ($this->company?->exists) {
            DB::transaction(function () use ($data, $provisioner) {
                $this->company->update($data);
                // Repara empresas antiguas que no tuvieran sus roles (no toca los existentes)
                $provisioner->provision($this->company);
            });

            $message = 'Empresa actualizada correctamente.';
        } else {
            DB::transaction(function () use ($data, $provisioner) {
                $company = Company::create($data);
                $provisioner->provision($company);
            });

            $message = 'Empresa creada con sus roles: empresa, freelancer y freelancer-senior.';
        }

        session()->flash('status', $message);

        return redirect()->route('admin.empresas.index');
    }

    public function render()
    {
        return view('livewire.admin.companies.form');
    }
}
