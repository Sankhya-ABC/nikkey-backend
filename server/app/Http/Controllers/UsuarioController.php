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
                    'id'            => $u->id,
                    'nome'          => $u->name,
                    'email'         => $u->email,
                    'departamento'  => $u->departamento->nome ?? '',
                    'perfil'        => $u->tipoUsuario->nome ?? '',
                    'telefone'      => $u->telefone ?? '',
                    'ativo'         => (bool) $u->ativo,
                    'dataCadastro'  => $u->created_at?->format('Y-m-d H:i:s'),
                    'senha'         => '',
                    'confirmarSenha'=> '',
                ];
            });

        return response()->json($usuarios);
    }

    public function show($id)
    {
        $u = User::with(['departamento', 'tipoUsuario'])
            ->findOrFail($id);

        $usuario = [
            'id'            => $u->id,
            'nome'          => $u->name,
            'email'         => $u->email,
            'departamento'  => $u->departamento->nome ?? '',
            'perfil'        => $u->tipoUsuario->id ?? '',
            'telefone'      => $u->telefone ?? '',
            'ativo'         => (bool) $u->ativo,
            'dataCadastro'  => $u->created_at?->format('Y-m-d H:i:s'),
            'senha'         => '',
            'confirmarSenha'=> '',
        ];

        return response()->json($usuario);
    }
}
