<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    // ->withRouting(
    //     web: __DIR__.'/../routes/web.php',
    //     api: __DIR__ . '/../routes/api.php',
    //     commands: __DIR__.'/../routes/console.php',
    //     health: '/up',
    //     then: function () {
    //         Route::middleware('api')
    //             ->prefix('api/admin')
    //             ->name('admin.')
    //             ->group(base_path('routes/admin.php'));
    //     }
    // )
    // ->create();
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // 'role' => \App\Http\Middleware\RoleMiddleware::class, // ❌ Hapus ini
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class, // ✅ Pakai ini
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();