<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
                    'tipo'          => $u->tipoUsuario->descricao ?? '',
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

        return response()->json([
            'id'            => $u->id,
            'nome'          => $u->name,
            'email'         => $u->email,
            'departamento'  => $u->departamento->nome ?? '',
            'tipo'          => $u->tipoUsuario->descricao ?? '',
            'telefone'      => $u->telefone ?? '',
            'ativo'         => (bool) $u->ativo,
            'dataCadastro'  => $u->created_at?->format('Y-m-d H:i:s'),
            'senha'         => '',
            'confirmarSenha'=> '',
        ]);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'senha'             => 'required|string|min:6|confirmed',
            'telefone'          => 'nullable|string|max:20',
            'ativo'             => 'boolean',
            'departamento_id'   => 'nullable|exists:departamentos,id',
            'tipo_usuario_id'   => 'nullable|exists:tipos_usuarios,id',
        ]);

        $usuario = User::create([
            'name'              => $dados['nome'],
            'email'             => $dados['email'],
            'password'          => Hash::make($dados['senha']),
            'telefone'          => $dados['telefone'] ?? null,
            'ativo'             => $dados['ativo'] ?? true,
            'departamento_id'   => $dados['departamento_id'] ?? null,
            'tipo_usuario_id'   => $dados['tipo_usuario_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'id'      => $usuario->id
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $dados = $request->validate([
            'nome'              => 'sometimes|required|string|max:255',
            'email'             => 'sometimes|required|email|unique:users,email,' . $usuario->id,
            'senha'             => 'nullable|string|min:6|confirmed',
            'telefone'          => 'nullable|string|max:20',
            'ativo'             => 'boolean',
            'departamento_id'   => 'nullable|exists:departamentos,id',
            'tipo_usuario_id'   => 'nullable|exists:tipos_usuarios,id',
        ]);

        if (isset($dados['nome'])) {
            $usuario->name = $dados['nome'];
        }

        if (isset($dados['email'])) {
            $usuario->email = $dados['email'];
        }

        if (!empty($dados['senha'])) {
            $usuario->password = Hash::make($dados['senha']);
        }

        if (array_key_exists('telefone', $dados)) {
            $usuario->telefone = $dados['telefone'];
        }

        if (array_key_exists('ativo', $dados)) {
            $usuario->ativo = $dados['ativo'];
        }

        if (array_key_exists('departamento_id', $dados)) {
            $usuario->departamento_id = $dados['departamento_id'];
        }

        if (array_key_exists('tipo_usuario_id', $dados)) {
            $usuario->tipo_usuario_id = $dados['tipo_usuario_id'];
        }

        $usuario->save();

        return response()->json([
            'message' => 'Usuário atualizado com sucesso'
        ]);
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();

        return response()->json([
            'message' => 'Usuário removido com sucesso'
        ]);
    }
}
