<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class SankhyaSyncController extends Controller
{
    public function syncAll()
    {
        set_time_limit(0);

        Artisan::call('sankhya:sync-all');

        return response()->json([
            'message' => 'Sincronização com a Sankhya concluída com sucesso.',
            'output' => Artisan::output(),
        ]);
    }
}
