<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Services\CompanyRoleProvisioner;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $provisioner = app(CompanyRoleProvisioner::class);

        foreach (CompanyRoleProvisioner::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Administrador: único rol global (team_id = 0)
        $registrar->setPermissionsTeamId(0);
        $admin = Role::firstOrCreate(['name' => 'admin', 'team_id' => 0]);
        $admin->syncPermissions(CompanyRoleProvisioner::PERMISSIONS);

        // Roles por empresa, resincronizando permisos con la definición actual
        Company::all()->each(
            fn (Company $company) => $provisioner->provision($company, resync: true)
        );
    }
}