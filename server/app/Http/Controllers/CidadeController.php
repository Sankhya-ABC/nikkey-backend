<?php

namespace App\Http\Controllers;

use App\Models\Cidade;

class CidadeController extends Controller
{
    public function index()
    {
        return Cidade::with('uf')
            ->orderBy('nome')
            ->get();
    }

    public function show(int $id)
    {
        return Cidade::with('uf')->findOrFail($id);
    }
}
