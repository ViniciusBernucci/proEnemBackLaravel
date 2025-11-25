<?php

namespace App\Http\Controllers\Users;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function __construct(){}

    public function register(Request $request){

        $usuario = $request->input();

        //Esse registro vem da hotmart -> Vide documentacao de fluxo de usuário

        //TO_DO -> Estudar API da Hotmart (Ou outro checkout) 
        // para implementar o Registro de usuário

    }

    public function login(Request $request){

        //O que precisa para efetuar um login?
        //1 - Receber os dados do request

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string']
        ]);

        //2 - Fazer a consulta no banco para verificar se possui o usuario
        $user = User::where('email', $data['email'])->first();

        //3 - Verificar se a senha está correta
        //4 - Se algo estiver errado retornar para o front o erro
        if(!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Gera um token Sanctum para o usuário
        // O nome do token pode ser qualquer string identificadora
        // O segundo parâmetro são os "abilities" (escopos/opções de permissão)

        $tokenResult = $user->createToken('admin-panel', ['admin']);
        $plainText   = $tokenResult->plainTextToken;

        Log::info('Entrou em register', ['REMEMBER' => $data['remember']]);

        if ($data['remember'] == false) {

            $expiration = config('sanctum.expiration') ?? 15;
            $expiresAt = Carbon::now()->addMinutes($expiration);

            Log::info('EXPIRATION MINUTES:', ['EXPIRATION' => $expiration]);
        } else {
            $expiration = config('sanctum.expiration') ?? 90;
            $expiresAt = Carbon::now()->addDays($expiration);

            Log::info('EXPIRATION DAYS:', ['EXPIRATION' => $expiration]);
        }

        try {
            $model = null;

            if (
                isset($tokenResult->accessToken) &&
                $tokenResult->accessToken instanceof \Laravel\Sanctum\PersonalAccessToken
            ) {
                $model = $tokenResult->accessToken;
            }

            if (empty($model) && !empty($plainText)) {
                $model = \Laravel\Sanctum\PersonalAccessToken::findToken($plainText);
            }

            if (empty($model)) {
                $model = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)
                    ->where('name', 'admin-panel')
                    ->latest()
                    ->first();
            }

            if ($model) {
                $model->expires_at = $expiresAt;
                $model->save();
            }
        } catch (\Throwable $e) {
            Log::warning(
                'Falha ao definir expires_at no personal_access_tokens (admin): ' . $e->getMessage()
            );
        }

        // Retorna o token puro para o front-end (mostrar só na criação)
        // O front deve usar esse token no header Authorization: Bearer {token}
        return response()->json([
            'access_token' => $plainText, // Token puro
            'token_type'   => 'Bearer',
            'expires_at'   => isset($expiresAt) ? $expiresAt->toDateTimeString() : null,
            'user'         => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }


    public function logout(Request $request)
    {
        // Obtém o usuário autenticado via Sanctum
        $user = $request->user();

        if ($user) {
            // Revoga apenas o token de acesso atual do Sanctum
            //$user->currentAccessToken()?->delete();

            //revoga todos os tokens do usuário
            //$user->tokens()?->delete();
        }

        // Não há mais lógica de refresh token/cookie customizado
        return response()->json(['ok' => true], 200);
    }
}
