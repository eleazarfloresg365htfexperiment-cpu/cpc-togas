<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
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
        // Los listados usan Bootstrap 5 (el mismo de la plantilla).
        Paginator::useBootstrapFive();

        // Direcciones en español: /productos/crear y /productos/{id}/editar.
        Route::resourceVerbs([
            'create' => 'crear',
            'edit' => 'editar',
        ]);
    }
}
