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
            $company = $user->activeCompany();

            if (! $company) {
                $company = $user->defaultCompany();
                session(['active_company_id' => $company?->id]);
            }

            app(PermissionRegistrar::class)->setPermissionsTeamId($company?->id);
        }

        return $next($request);
    }
}
