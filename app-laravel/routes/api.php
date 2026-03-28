<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AuthController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Cronogramas\CronogramaController;


//Route::get('/user', [UserController::class, 'getUser']);

Route:://middleware('auth:sanctum')
  prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

  // Finalidade: Fluxo "Esqueci minha senha".
  //Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail']);

  // Finalidade: Confirmar a redefinição do endpoint /password/email
  //Route::post('password/reset', [PasswordResetController::class, 'reset']);
});

// Rotas protegidas - Cronogramas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cronogramas', [CronogramaController::class, 'store']);
});


