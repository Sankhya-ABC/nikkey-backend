<?php

namespace App\Http\Controllers;

use App\Models\Bairro;

class BairroController extends Controller
{
    public function index()
    {
        return Bairro::orderBy('nome')->get();
    }

    public function show(int $id)
    {
        return Bairro::findOrFail($id);
    }
}
