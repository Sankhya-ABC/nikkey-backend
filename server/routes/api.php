<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UfController;
use App\Http\Controllers\CidadeController;
use App\Http\Controllers\BairroController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Rotas públicas e protegidas da API
|--------------------------------------------------------------------------
*/

// 🔓 AUTH
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */
    Route::get('/clientes', [ClienteController::class, 'index']);      // LISTAR
    Route::get('/clientes/{id}', [ClienteController::class, 'show']);   // DETALHE
    Route::post('/clientes', [ClienteController::class, 'store']);      // CRIAR
    Route::put('/clientes/{id}', [ClienteController::class, 'update']); // ATUALIZAR
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']); // EXCLUIR

    /*
    |--------------------------------------------------------------------------
    | USUÁRIOS
    |--------------------------------------------------------------------------
    */
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | LOCALIZAÇÃO
    |--------------------------------------------------------------------------
    */
    Route::get('/ufs', [UfController::class, 'index']);
    Route::get('/ufs/{id}', [UfController::class, 'show']);

    Route::get('/cidades', [CidadeController::class, 'index']);
    Route::get('/cidades/{id}', [CidadeController::class, 'show']);

    Route::get('/bairros', [BairroController::class, 'index']);
    Route::get('/bairros/{id}', [BairroController::class, 'show']);
});
