<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Registra / Cria um novo usuário no sistema (Registro API).
     */
    public function register(Request $request)
    {
        try {
            // 1. Validar e sanitizar a entrada
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            // 2. Persistência via Eloquent ORM
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 3. Emissão imediata de Token (Auto-login)
            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('[USERS] Novo usuário registrado', ['user_id' => $user->id]);

            return response()->json([
                'message'      => 'Cliente registrado com sucesso',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'  => 'Erro de validação',
                'fields' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('[USERS] Falha crítica ao registrar', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }
}
