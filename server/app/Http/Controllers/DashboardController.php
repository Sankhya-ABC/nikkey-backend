<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TipoDashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller {
    public function getBasicData(){
        return response()->json([
            'message' => 'Endpoint getBasicData',
        ]);
    }

    public function getOrdensServico(){
        return response()->json([
            'message' => 'Endpoint getOrdensServico',
        ]);
    }

    public function getAtendimentosTecnico(){
        return response()->json([
            'message' => 'Endpoint getAtendimentosTecnico',
        ]);
    }

    public function getConsumoProdutos(){
        return response()->json([
            'message' => 'Endpoint getConsumoProdutos',
        ]);
    }

    public function getProximasVisitas(){
        return response()->json([
            'message' => 'Endpoint getProximasVisitas',
        ]);
    }
}
