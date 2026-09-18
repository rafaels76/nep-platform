<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if (! $user) {
                return null;
            }

            $registrar = app(PermissionRegistrar::class);
            $originalTeamId = $registrar->getPermissionsTeamId();

            $registrar->setPermissionsTeamId(0);
            $isAdmin = $user->hasRole('admin');
            $registrar->setPermissionsTeamId($originalTeamId); // restaura el contexto de empresa activa

            return $isAdmin ? true : null;
        });
    }
}
