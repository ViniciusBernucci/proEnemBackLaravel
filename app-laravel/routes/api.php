<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AuthController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Cronogramas\CronogramaController;
use App\Http\Controllers\Auth\SocialAuthController;

// ── Autenticação (rotas públicas) ─────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Google OAuth
    Route::get('/google/redirect', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
});

// ── Rotas protegidas (requer token Sanctum) ───────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Cronogramas
    Route::get('/cronogramas', [CronogramaController::class, 'index']);
    Route::post('/cronogramas', [CronogramaController::class, 'store']);
    Route::delete('/cronogramas/{cronograma}', [CronogramaController::class, 'destroy']);
});
