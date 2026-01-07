<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page    = (int) $request->query('page', 1);
        $search  = trim($request->query('search'));

        $query = User::with(['departamento', 'tipoUsuario']);

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

        $usuariosFormatados = $usuarios->getCollection()->map(function (User $u) {
            return $this->toVO($u);
        });

        $usuarios->setCollection($usuariosFormatados);

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
        $usuario = User::with(['departamento', 'tipoUsuario'])
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
        ]);

        $user = new User();
        $user->name = $request->nome;
        $user->email = $request->email;
        $tipoUsuarioId = is_numeric($request->perfil)
            ? $request->perfil
            : TipoUsuario::where('descricao', $request->perfil)->value('id');

        if (!$tipoUsuarioId) {
            return response()->json([
                'message' => 'Perfil inválido'
            ], 422);
        }

        $user->tipo_usuario_id = $tipoUsuarioId;
        $user->telefone = $request->telefone;
        $user->departamento_id = $request->departamento_id ?? null;
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
        ]);

        $user->name = $request->nome;
        $user->email = $request->email;
        $tipoUsuarioId = is_numeric($request->perfil)
            ? $request->perfil
            : TipoUsuario::where('descricao', $request->perfil)->value('id');

        if (!$tipoUsuarioId) {
            return response()->json([
                'message' => 'Perfil inválido'
            ], 422);
        }
        $user->tipo_usuario_id = $tipoUsuarioId;
        $user->telefone = $request->telefone;
        $user->departamento_id = $request->departamento_id ?? null;
        $user->ativo = $request->ativo ?? $user->ativo;

        if (!empty($request->senha)) {
            $user->password = Hash::make($request->senha);
        }

        $user->save();

        return response()->json($this->toVO($user));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Usuário removido com sucesso']);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'ativo' => 'required|boolean',
        ]);

        $user = User::findOrFail($id);
        $user->ativo = $request->ativo;
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
            'idCliente' => $u->cliente_id,
            'nome' => $u->name,
            'email' => $u->email,
            'departamento' => $u->departamento->nome ?? '',
            'perfil' => $u->tipoUsuario->descricao ?? '',
            'telefone' => $u->telefone ?? '',
            'senha' => '',
            'confirmarSenha' => '',
            'ativo' => (bool) $u->ativo,
            'dataCadastro' => $u->created_at
        ];
    }
}
