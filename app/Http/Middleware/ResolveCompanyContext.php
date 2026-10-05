<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class ResolveCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // NUEVO: cortar la sesión si el usuario fue deshabilitado
        if ($user && $user->isDisabled()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Tu cuenta ha sido deshabilitada.');
        }

        if ($user) {
            $activeCompanyId = session('active_company_id');

            if (! $activeCompanyId) {
                $firstCompany = $user->companies()->first();

                if ($firstCompany) {
                    session(['active_company_id' => $firstCompany->id]);
                    $activeCompanyId = $firstCompany->id;
                }
            }

            app(PermissionRegistrar::class)->setPermissionsTeamId($activeCompanyId);
        }

        return $next($request);
    }
}