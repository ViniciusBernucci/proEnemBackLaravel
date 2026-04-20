<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AuthController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Cronogramas\CronogramaController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\Disciplinas\DisciplinaController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;

// ── Autenticação (rotas públicas) ─────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Google OAuth
    Route::get('/google/redirect', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
});

// ── Rotas públicas ────────────────────────────────────────────────────────────
Route::get('/disciplinas', [DisciplinaController::class, 'index']);

// ── Rotas protegidas (requer token Sanctum) ───────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Cronogramas
    Route::get('/cronogramas', [CronogramaController::class, 'index']);
    Route::post('/cronogramas', [CronogramaController::class, 'store']);
    Route::get('/cronogramas/ativo', [CronogramaController::class, 'ativo']); // Deve vir ANTES de /{cronograma} para evitar conflito com ID
    Route::get('/cronogramas/{cronograma}', [CronogramaController::class, 'show']);
    Route::get('/cronogramas/{cronograma}/pdf', [CronogramaController::class, 'exportPdf']);
    Route::patch('/cronogramas/{cronograma}/ativar', [CronogramaController::class, 'ativar']);
    Route::delete('/cronogramas/{cronograma}', [CronogramaController::class, 'destroy']);

    // Tracker
    Route::get('/tracker/tasks', [TrackerController::class, 'index']);
    Route::patch('/tracker/tasks/{id}/toggle', [TrackerController::class, 'toggle']);
});
