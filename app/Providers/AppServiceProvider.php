<?php

namespace App\Providers;

use App\Contracts\InventoryAiProvider;
use App\Services\InventoryAI\MockInventoryAiProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            InventoryAiProvider::class,
            function ($app) {
                $provider = config('inventory_ai.provider', 'mock');

                if ($provider === 'mock') {
                    return $app->make(MockInventoryAiProvider::class);
                }

                throw new \RuntimeException(
                    "Proveedor de IA no implementado: {$provider}"
                );
            }
        );
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

        Gate::define('despachar-traslados', fn ($user) => $user->hasRole('auxiliar_farmacia')
            || $user->hasRole('regente_farmacia')
        );

        Gate::define('anular-traslados', fn ($user) => $user->hasRole('auxiliar_farmacia')
            || $user->hasRole('regente_farmacia')
        );
    }
}
