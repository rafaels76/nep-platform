<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
                }
            }
        }

        return $next($request);
    }
}