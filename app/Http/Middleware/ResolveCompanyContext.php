<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\PermissionRegistrar;

class ResolveCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $activeCompanyId = session('active_company_id');

            if (! $activeCompanyId) {
                $firstCompany = $user->companies()->first();

                if ($firstCompany) {
                    session(['active_company_id' => $firstCompany->id]);
                    $activeCompanyId = $firstCompany->id;
                }
            }

            // Sincroniza el contexto de permisos de Spatie con la empresa activa.
            // Los administradores usan team_id = 0 (contexto global), resuelto aparte en el Gate::before.
            app(PermissionRegistrar::class)->setPermissionsTeamId($activeCompanyId);
        }

        return $next($request);
    }
}