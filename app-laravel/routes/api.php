<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AuthController;


//Route::get('/user', [UserController::class, 'getUser']);

Route:://middleware('auth:sanctum')
  prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

  // Finalidade: Fluxo “Esqueci minha senha”.
  //Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail']);

  // Finalidade: Confirmar a redefinição do endpoint /password/email
  //Route::post('password/reset', [PasswordResetController::class, 'reset']);
});


