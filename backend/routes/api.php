<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BukuController;
use Illuminate\Support\Facades\Route;

// Satu-satunya endpoint publik
Route::post('/login', [AuthController::class, 'login']);

// Semua endpoint lain wajib token (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('buku', BukuController::class);
});