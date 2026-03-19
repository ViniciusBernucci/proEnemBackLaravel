<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetController extends Controller
{
    /**
     * Passo 1: Geração de Token (Forgot Password)
     * O Front-end envia apenas o E-mail.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // O broker localiza o usuário, gera o token, salva em 'password_reset_tokens' 
        // e dispara a ResetPasswordNotification (e-mail) nativa.
        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            // Em APIs REST, __() tentará traduzir a string "passwords.sent"
            return response()->json(['message' => __($status)], 200);
        }

        // Caso o e-mail não exista no banco
        return response()->json(['error' => __($status)], 400);
    }

    /**
     * Passo 2: Redefinição (Reset Password)
     * O Front-end intercepta o link do E-mail, coleta as senhas 
     * e dispara este endpoint fechando o fluxo.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        /* O Broker valida Token e E-mail juntos na tabela. 
           Se baterem e o token não expirou, ele injeta os dados na closure abaixo. */
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json(['error' => __($status)], 400);
    }
}
