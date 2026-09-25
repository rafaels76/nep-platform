<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);

        $permissions = [
            'dashboard.ver',
            'casos.ver',
            'casos.crear',
            'casos.gestionar',
            'empresas.ver',
            'empresas.gestionar',
            'especialistas.ver',
            'especialistas.gestionar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Administrador: único rol verdaderamente global, contexto team_id = 0
        $registrar->setPermissionsTeamId(0);
        $admin = Role::firstOrCreate(['name' => 'admin', 'team_id' => 0]);
        $admin->syncPermissions($permissions);

        // Roles de empresa/freelancer: se crean por cada empresa existente,
        // porque Spatie exige que el rol viva en el mismo contexto de equipo
        // en el que se va a asignar.
        Company::all()->each(function (Company $company) use ($registrar) {
            $registrar->setPermissionsTeamId($company->id);

            $empresa = Role::firstOrCreate(['name' => 'empresa', 'team_id' => $company->id]);
            $empresa->syncPermissions([
                'dashboard.ver',
                'casos.ver',
                'casos.crear',
                'casos.gestionar',
                'empresas.ver',
                'especialistas.ver',
            ]);

            $freelancer = Role::firstOrCreate(['name' => 'freelancer', 'team_id' => $company->id]);
            $freelancer->syncPermissions([
                'dashboard.ver',
                'casos.ver',
                'casos.gestionar',
                'empresas.ver',
            ]);

            $freelancerSenior = Role::firstOrCreate(['name' => 'freelancer-senior', 'team_id' => $company->id]);
            $freelancerSenior->syncPermissions([
                'dashboard.ver',
                'casos.ver',
                'casos.gestionar',
                'empresas.ver',
                'especialistas.ver',
            ]);
        });
    }
}
