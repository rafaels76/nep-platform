<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0 = "equipo global", usado exclusivamente para el rol admin
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

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

        $admin = Role::firstOrCreate(['name' => 'admin', 'team_id' => 0]);
        $admin->syncPermissions($permissions);
    }
}
