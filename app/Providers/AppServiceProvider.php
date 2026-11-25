<?php

namespace App\Providers;

use App\Models\HoraExtra;
use Illuminate\Support\Facades\View;
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
        // Contador de pendientes para el sidebar y la cabecera de Aprobaciones.
        View::composer(['layouts.panel', 'admin.aprobaciones'], function ($view) {
            $view->with('pendientes', auth()->user()?->esAdmin()
                ? HoraExtra::where('estado', HoraExtra::PENDIENTE)->count()
                : 0);
        });
    }
}
