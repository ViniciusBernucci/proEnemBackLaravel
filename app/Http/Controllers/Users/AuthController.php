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


        //5 - Se tudo estiver certo redirecionar para a rota do dashboard
        
    }
}
