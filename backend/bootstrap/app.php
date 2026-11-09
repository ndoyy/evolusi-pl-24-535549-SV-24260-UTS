<?php

use App\Http\Controllers\HealthController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // GET /health: cek basis data dan cache (tanpa middleware web/sesi)
            Route::get('/health', HealthController::class);
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Catat validasi gagal: hanya nama field, tanpa nilai input
        $exceptions->render(function (ValidationException $e, Request $request) {
            Log::warning('Validasi permintaan gagal', [
                'user_id'          => $request->user()?->id,
                'method'           => $request->method(),
                'path'             => $request->path(),
                'field_bermasalah' => array_keys($e->errors()),
            ]);

            return null; // lanjut ke respons 422 bawaan Laravel
        });
    })->create();