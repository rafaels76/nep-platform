<?php

namespace App\Services;

use App\Models\Company;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CompanyRoleProvisioner
{
    public const PERMISSIONS = [
        'dashboard.ver',
        'casos.ver',
        'casos.crear',
        'casos.gestionar',
        'empresas.ver',
        'empresas.gestionar',
        'especialistas.ver',
        'especialistas.gestionar',
    ];

    public const ROLES = [
        'empresa' => [
            'dashboard.ver', 'casos.ver', 'casos.crear', 'casos.gestionar',
            'empresas.ver', 'especialistas.ver',
        ],
        'freelancer' => [
            'dashboard.ver', 'casos.ver', 'casos.gestionar', 'empresas.ver',
        ],
        'freelancer-senior' => [
            'dashboard.ver', 'casos.ver', 'casos.gestionar', 'empresas.ver', 'especialistas.ver',
        ],
    ];

    /**
     * Garantiza que la empresa tenga sus roles.
     * Por defecto solo asigna permisos a los roles recién creados;
     * con $resync = true también sobrescribe los permisos de los existentes.
     */
    public function provision(Company $company, bool $resync = false): void
    {
        $registrar = app(PermissionRegistrar::class);
        $originalTeamId = $registrar->getPermissionsTeamId();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $registrar->setPermissionsTeamId($company->id);

        foreach (self::ROLES as $name => $permissions) {
            $role = Role::firstOrCreate(['name' => $name, 'team_id' => $company->id]);

            if ($role->wasRecentlyCreated || $resync) {
                $role->syncPermissions($permissions);
            }
        }

        $registrar->setPermissionsTeamId($originalTeamId);
        $registrar->forgetCachedPermissions();
    }
}