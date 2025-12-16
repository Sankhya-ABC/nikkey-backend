<?php

namespace App\Http\Controllers;

use App\Models\Uf;

class UfController extends Controller
{
    public function index()
    {
        return Uf::orderBy('sigla')->get();
    }

    public function show(int $id)
    {
        return Uf::findOrFail($id);
    }
}
