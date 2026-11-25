<?php

use App\Http\Controllers\Admin\AprobacionController;
use App\Http\Controllers\Admin\EmpleadoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\RegistroController;
use Illuminate\Support\Facades\Route;

// Público: cualquiera registra sus horas sin cuenta.
Route::get('/', [RegistroController::class, 'create'])->name('registro');
Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Cada rol aterriza en su pantalla principal.
    Route::get('/panel', fn () => redirect()->route(
        auth()->user()->esAdmin() ? 'admin.aprobaciones' : 'consulta'
    ))->name('panel');

    Route::get('/consulta', [ConsultaController::class, 'index'])
        ->middleware('rol:admin,consultor')->name('consulta');

    Route::prefix('admin')->name('admin.')->middleware('rol:admin')->group(function () {
        Route::get('/aprobaciones', [AprobacionController::class, 'index'])->name('aprobaciones');
        Route::post('/aprobaciones/{horaExtra}/aprobar', [AprobacionController::class, 'aprobar'])->name('aprobaciones.aprobar');
        Route::post('/aprobaciones/{horaExtra}/rechazar', [AprobacionController::class, 'rechazar'])->name('aprobaciones.rechazar');

        Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados');
        Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update');
    });
});
