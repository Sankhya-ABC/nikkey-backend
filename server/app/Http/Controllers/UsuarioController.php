<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TipoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page    = (int) $request->query('page', 1);
        $search  = trim($request->query('search'));

        $query = User::with(['departamento', 'tipoUsuario', 'cliente']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhereHas('departamento', function ($d) use ($search) {
                      $d->where('nome', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('tipoUsuario', function ($t) use ($search) {
                      $t->where('descricao', 'LIKE', "%{$search}%");
                  });
            });
        }

        $usuarios = $query->paginate($perPage, ['*'], 'page', $page);

        $usuarios->setCollection(
            $usuarios->getCollection()->map(fn (User $u) => $this->toVO($u))
        );

        return response()->json([
            'data' => $usuarios->items(),
            'meta' => [
                'current_page' => $usuarios->currentPage(),
                'per_page'     => $usuarios->perPage(),
                'total'        => $usuarios->total(),
                'last_page'    => $usuarios->lastPage(),
            ]
        ]);
    }

    public function show($id)
    {
        $usuario = User::with(['departamento', 'tipoUsuario', 'cliente'])
            ->findOrFail($id);

        return response()->json($this->toVO($usuario));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'perfil' => 'required',
            'senha' => 'required|min:6|same:confirmarSenha',
            'departamento.id' => 'nullable|integer|exists:departamentos,id',
            'cliente.id' => 'nullable|integer|exists:clientes,id',
        ]);

        $tipoUsuarioId = is_numeric($request->perfil)
            ? $request->perfil
            : TipoUsuario::where('descricao', $request->perfil)->value('id');

        if (!$tipoUsuarioId) {
            return response()->json(['message' => 'Perfil inválido'], 422);
        }

        $user = new User();
        $user->name = $request->nome;
        $user->email = $request->email;
        $user->tipo_usuario_id = $tipoUsuarioId;
        $user->telefone = $request->telefone;
        $user->departamento_id = $request->departamento['id'] ?? null;
        $user->cliente_id = $request->cliente['id'] ?? null;
        $user->ativo = $request->ativo ?? true;
        $user->password = Hash::make($request->senha);
        $user->save();

        return response()->json($this->toVO($user), 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'perfil' => 'required',
            'senha' => 'nullable|min:6|same:confirmarSenha',
            'departamento.id' => 'nullable|integer|exists:departamentos,id',
            'cliente.id' => 'nullable|integer|exists:clientes,id',
        ]);

        $tipoUsuarioId = is_numeric($request->perfil)
            ? $request->perfil
            : TipoUsuario::where('descricao', $request->perfil)->value('id');

        if (!$tipoUsuarioId) {
            return response()->json(['message' => 'Perfil inválido'], 422);
        }

        $user->name = $request->nome;
        $user->email = $request->email;
        $user->tipo_usuario_id = $tipoUsuarioId;
        $user->telefone = $request->telefone;
        $user->departamento_id = $request->departamento['id'] ?? null;
        $user->cliente_id = $request->cliente['id'] ?? null;
        $user->ativo = $request->ativo ?? $user->ativo;

        if (!empty($request->senha)) {
            $user->password = Hash::make($request->senha);
        }

        $user->save();

        return response()->json($this->toVO($user));
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return response()->json(['message' => 'Usuário removido com sucesso']);
    }

    public function updateStatus($id)
    {
        $user = User::findOrFail($id);
        $user->ativo = !$user->ativo;
        $user->save();

        return response()->json([
            'message' => 'Status do usuário atualizado com sucesso',
            'data' => $this->toVO($user),
        ]);
    }

    private function toVO(User $u): array
    {
        return [
            'id' => $u->id,
            'nome' => $u->name ?? '',
            'email' => $u->email ?? '',

            'departamento' => [
                'id' => $u->departamento->id ?? '',
                'descricao' => $u->departamento->nome ?? '',
            ],

            'perfil' => $u->tipoUsuario->descricao ?? null,

            'cliente' => [
                'id' => $u->cliente->id ?? '',
                'nomeFantasia' => $u->cliente->nome_fantasia ?? '',
                'cnpjCpf' => $u->cliente->cnpj_cpf ?? '',
            ],

            'telefone' => $u->telefone ?? '',
            'senha' => '',
            'confirmarSenha' => '',
            'ativo' => (bool) $u->ativo,
            'dataCadastro' => $u->created_at?->toISOString(),
        ];
    }
}
