<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $user = Auth::user();

        $user->tokens()->delete(); 

        $token = $user->createToken('api_token')->plainTextToken;

        $expirationMinutes = config('sanctum.expiration');

        $user = User::with('tipoUsuario', 'departamento', 'cliente')->find(Auth::id());

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'cliente_id' => $user->cliente_id,
                    'cliente' => $user->cliente->nome_fantasia ?? null,
                    'tipo_usuario_id' => $user->tipo_usuario_id,
                    'tipo_usuario' => $user->tipoUsuario->descricao ?? null,
                    'departamento_id' => $user->departamento_id,
                    'departamento' => $user->departamento->nome ?? null
            ],
            'token' => $token,
            'expires_in' => $expirationMinutes 
        ]);
    }

    public function me(Request $request)
    {
    $user = $request->user()->load('tipoUsuario', 'departamento', 'cliente');

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'cliente_id' => $user->cliente_id,
        'cliente' => $user->cliente->nome_fantasia ?? null,
        'tipo_usuario_id' => $user->tipo_usuario_id,
        'tipo_usuario' => $user->tipoUsuario->descricao ?? null,
        'departamento_id' => $user->departamento_id,
        'departamento' => $user->departamento->nome ?? null
    ]); 
   }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
