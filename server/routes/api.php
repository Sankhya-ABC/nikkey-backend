<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UfController;
use App\Http\Controllers\CidadeController;
use App\Http\Controllers\BairroController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\RelatorioProdutividadeController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DashboardCommonController;
use App\Http\Controllers\RelatorioCommonController;
use App\Http\Controllers\VisitaController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::get('/clientes/{id}', [ClienteController::class, 'show']);
    Route::post('/clientes', [ClienteController::class, 'store']);
    Route::put('/clientes/{id}', [ClienteController::class, 'update']);
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);

    Route::get('/departamentos/select', [DepartamentoController::class, 'listSelect']);
    Route::get('/departamentos', [DepartamentoController::class, 'index']);
    Route::get('/departamentos/{id}', [DepartamentoController::class, 'show']);
    Route::post('/departamentos', [DepartamentoController::class, 'store']);
    Route::put('/departamentos/{id}', [DepartamentoController::class, 'update']);
    Route::patch('/departamentos/{id}', [DepartamentoController::class, 'update']);
    Route::delete('/departamentos/{id}', [DepartamentoController::class, 'destroy']);

    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
    Route::patch('/usuarios/status/{id}', [UsuarioController::class, 'updateStatus']);
    Route::patch('/usuarios/{id}', [UsuarioController::class, 'update']);
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);

    Route::get('/ordens-servico', [OrdemServicoController::class, 'index']);
    Route::get('/ordens-servico/{id}', [OrdemServicoController::class, 'show']);

    Route::get('/relatorios-produtividade', [RelatorioProdutividadeController::class, 'index']);

    Route::get('/ufs', [UfController::class, 'index']);
    Route::get('/ufs/{id}', [UfController::class, 'show']);
    Route::get('/cidades', [CidadeController::class, 'index']);
    Route::get('/cidades/{id}', [CidadeController::class, 'show']);
    Route::get('/bairros', [BairroController::class, 'index']);
    Route::get('/bairros/{id}', [BairroController::class, 'show']);

    Route::get('/dashboard/admin/dados-basicos', [DashboardAdminController::class, 'getBasicData']);
    Route::get('/dashboard/admin/grafico-ordens-servico', [DashboardAdminController::class, 'getOrdensServico']);
    Route::get('/dashboard/admin/grafico-atendimentos-tecnico', [DashboardAdminController::class, 'getAtendimentosTecnico']);
    Route::get('/dashboard/admin/grafico-consumo-produtos', [DashboardAdminController::class, 'getConsumoProdutos']);
    Route::get('/dashboard/admin/proximas-visitas', [DashboardAdminController::class, 'getProximasVisitas']);

    Route::get('/dashboard/common/ultima-visita', [DashboardCommonController::class, 'getUltimaVisita']);
    Route::get('/dashboard/common/download-certificado', [DashboardCommonController::class, 'getDownloadCertificado']);
    Route::get('/dashboard/common/foco-pragas-encontradas', [DashboardCommonController::class, 'getFocoPragasEncontradas']);
    Route::get('/dashboard/common/roedores-capturados', [DashboardCommonController::class, 'getRoedoresCapturados']);
    Route::get('/dashboard/common/consumo-produtos', [DashboardCommonController::class, 'getConsumoProdutos']);
    Route::get('/dashboard/common/proximas-visitas', [DashboardCommonController::class, 'getProximasVisitas']);

    Route::get('/relatorios/common/consumo-produtos', [RelatorioCommonController::class, 'getConsumoProdutos']);
    Route::get('/relatorios/common/consumo-insumos', [RelatorioCommonController::class, 'getConsumoInsumos']);
    Route::get('/relatorios/common/foco-pragas-encontradas', [RelatorioCommonController::class, 'getFocoPragasEncontradas']);
    Route::get('/relatorios/common/inseticidas-pragas', [RelatorioCommonController::class, 'getInseticidasXPragas']);
    Route::get('/relatorios/common/armadilhas-feromonio', [RelatorioCommonController::class, 'getArmadilhasFeromonio']);
    Route::get('/relatorios/common/armadilhas-luminosas', [RelatorioCommonController::class, 'getArmadilhasLuminosas']);
    Route::get('/relatorios/common/roedores-mortos', [RelatorioCommonController::class, 'getRoedoresMortos']);
    Route::get('/relatorios/common/placa-cola-armadilha-mecanica', [RelatorioCommonController::class, 'getPlacaColaArmadilhaMecanica']);
    Route::get('/relatorios/common/iscas-roidas', [RelatorioCommonController::class, 'getIscasRoidas']);
    Route::get('/relatorios/common/rodenticidas-roedores', [RelatorioCommonController::class, 'getRodenticidasXRoedores']);
    Route::get('/relatorios/common/nao-conformidades', [RelatorioCommonController::class, 'getNaoConformidades']);

    Route::get('/pragas/grupo-pragas', [RelatorioCommonController::class, 'getGrupoPragas']);
    Route::get('/pragas', [RelatorioCommonController::class, 'getPragasPorGrupo']);

    Route::get('/visitas', [VisitaController::class, 'getVisitas']);
});
