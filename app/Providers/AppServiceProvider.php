<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('gestionar-usuarios', fn ($user) => $user->hasRole('admin')
        );

        Gate::define('gestionar-catalogos', fn ($user) => $user->hasRole('admin')
        );

        Gate::define('consultar-inventario', fn ($user) => $user->hasRole('auxiliar_farmacia')
            || $user->hasRole('regente_farmacia')
            || $user->hasRole('auditor')
        );

        Gate::define('dispensar-medicamentos', fn ($user) => $user->hasRole('auxiliar_farmacia')
            || $user->hasRole('regente_farmacia')
        );

        Gate::define('crear-traslados', fn ($user) => $user->hasRole('auxiliar_farmacia')
            || $user->hasRole('regente_farmacia')
        );

        Gate::define('recibir-traslados', fn ($user) => $user->hasRole('auxiliar_farmacia')
            || $user->hasRole('regente_farmacia')
        );

        Gate::define('aprobar-traslados', fn ($user) => $user->hasRole('regente_farmacia')
        );

        Gate::define('autorizar-control-especial', fn ($user) => $user->hasRole('regente_farmacia')
        );

        Gate::define('ajustar-inventario', fn ($user) => $user->hasRole('regente_farmacia')
        );

        Gate::define('crear-prescripciones', fn ($user) => $user->hasRole('medico')
        );

        Gate::define('consultar-pacientes', fn ($user) => $user->hasRole('medico')
            || $user->hasRole('auditor')
        );

        Gate::define('consultar-kardex', fn ($user) => $user->hasRole('auxiliar_farmacia')
            || $user->hasRole('regente_farmacia')
            || $user->hasRole('auditor')
        );
    }
}
