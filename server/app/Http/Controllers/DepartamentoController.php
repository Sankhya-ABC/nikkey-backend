<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $search  = trim($request->query('search'));

        $query = Departamento::query();

        if (!empty($search)) {
            $query->where('nome', 'LIKE', "%{$search}%");
        }

        $departamentos = $query->paginate($perPage);

        return response()->json([
            'data' => $departamentos->items(),
            'meta' => [
                'current_page' => $departamentos->currentPage(),
                'per_page'     => $departamentos->perPage(),
                'total'        => $departamentos->total(),
                'last_page'    => $departamentos->lastPage(),
            ]
        ]);
    }

    public function listSelect()
    {
        $departamentos = Departamento::orderBy('descricao')
            ->get(['id', 'descricao']);

        return response()->json($departamentos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255'
        ]);

        $departamento = Departamento::create([
            'nome' => trim($request->nome)
        ]);

        return response()->json([
            'id' => $departamento->id
        ], 201);
    }

    public function show(int $id)
    {
        $departamento = Departamento::with('users')->findOrFail($id);

        return response()->json($departamento);
    }

    public function update(Request $request, int $id)
    {
        $departamento = Departamento::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255'
        ]);

        $departamento->update([
            'nome' => trim($request->nome)
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(int $id)
    {
        $departamento = Departamento::findOrFail($id);

        if ($departamento->users()->exists()) {
            return response()->json([
                'message' => 'Departamento possui usuários vinculados'
            ], 422);
        }

        $departamento->delete();

        return response()->json(['success' => true]);
    }
}
