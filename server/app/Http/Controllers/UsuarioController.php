<?php

namespace App\Http\Controllers;

use App\Models\User;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['departamento', 'tipoUsuario'])
            ->get()
            ->map(function ($u) {
                return [
                   'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'cliente_id' => $user->cliente_id,
                    'cliente' => $user->cliente->nome_fantasia ?? null,
                    'tipo_usuario_id' => $user->tipo_usuario_id,
                    'tipo_usuario' => $user->tipoUsuario->descricao ?? null,
                    'departamento_id' => $user->departamento_id,
                    'departamento' => $user->departamento->nome ?? null
                ];
            });

        return response()->json($usuarios);
    }

    public function show($id)
    {
        $u = User::with(['departamento', 'tipoUsuario'])
            ->findOrFail($id);

        $usuario = [
                   'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'cliente_id' => $user->cliente_id,
                    'cliente' => $user->cliente->nome_fantasia ?? null,
                    'tipo_usuario_id' => $user->tipo_usuario_id,
                    'tipo_usuario' => $user->tipoUsuario->descricao ?? null,
                    'departamento_id' => $user->departamento_id,
                    'departamento' => $user->departamento->nome ?? null
        ];

        return response()->json($usuario);
    }
}
