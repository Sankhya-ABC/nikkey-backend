<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login
     */
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

        // Remove tokens antigos
        $user->tokens()->delete();

        $token = $user->createToken('api_token')->plainTextToken;
        $expirationMinutes = config('sanctum.expiration');

        $user = User::with(['tipoUsuario', 'departamento'])
            ->findOrFail($user->id);

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'user' => $this->toVO($user),
            'token' => $token,
            'expires_in' => $expirationMinutes
        ]);
    }

    /**
     * Usuário autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user()
            ->load(['tipoUsuario', 'departamento']);

        return response()->json(
            $this->toVO($user)
        );
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    /**
     * Converte User para VO do frontend
     */
    private function toVO(User $u): array
    {
        return [
            'id' => $u->id,
            'nome' => $u->name,
            'email' => $u->email,
            'departamento' => $u->departamento->descricao ?? '',
            'perfil' => $u->tipoUsuario->descricao ?? '',
            'telefone' => $u->telefone ?? '',
            'senha' => '',
            'confirmarSenha' => '',
            'ativo' => (bool) $u->ativo,
            'dataCadastro' => $u->created_at
        ];
    }
}
