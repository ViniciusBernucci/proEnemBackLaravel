<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redireciona o usuário para a tela de autenticação do Google.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Processa o callback do Google após autenticação.
     * Cria ou atualiza o usuário e retorna um token Sanctum.
     * Redireciona para o frontend com o token na query string.
     */
    public function handleGoogleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            Log::error('[GOOGLE_OAUTH] Falha ao obter usuário do Google', ['error' => $e->getMessage()]);
            $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:4200'));
            return redirect("{$frontendUrl}/login?error=google_auth_failed");
        }

        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name'               => $socialUser->getName(),
                'google_id'          => $socialUser->getId(),
                'password'           => Hash::make(Str::random(24)),
                'email_verified_at'  => now(),
            ]
        );

        $token = $user->createToken('google-oauth')->plainTextToken;

        Log::info('[GOOGLE_OAUTH] Login social realizado', ['user_id' => $user->id]);

        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:4200'));

        $userPayload = urlencode(json_encode([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ]));

        return redirect("{$frontendUrl}/auth/callback?token={$token}&user={$userPayload}");
    }
}
