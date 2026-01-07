<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use Illuminate\Http\Request;
use App\Policies\VisibilityPolicy;
use Illuminate\Support\Facades\DB;

class RelatorioProdutividadeController extends Controller
{
    public function index(Request $request)
    {
        $dataInicio = $request->query('dataInicio');
        $dataFim    = $request->query('dataFim');
        $idCliente  = $request->query('idCliente'); 

        if (!$dataInicio || !$dataFim) {
            return response()->json([
                'message' => 'Os parâmetros dataInicio e dataFim são obrigatórios.'
            ], 422);
        }

        $user = $request->user();

        $query = OrdemServico::with('tecnico')
            ->whereDate('hrini', '>=', $dataInicio)
            ->whereDate('hrini', '<=', $dataFim);

        $query = VisibilityPolicy::apply($user, $query, 'cliente_id', $idCliente);

        $relatorio = $query
            ->get()
            ->groupBy(fn($os) => $os->tecnico?->id)
            ->map(function ($osGroup, $tecnicoId) {
                $tecnico = $osGroup->first()?->tecnico;
                $visitasAgendadas = $osGroup->count();
                $osRealizadas = $osGroup->where('statusos', 'REALIZADA')->count();
                $osNaoRealizadas = $osGroup->where('statusos', 'NAO_REALIZADA')->count();
                $visitasPendentes = $visitasAgendadas - $osRealizadas;

                $horasTrabalhadas = $osGroup
                    ->sum(function ($os) {
                        if ($os->hrini && $os->hrfin) {
                            return $os->hrfin->diffInMinutes($os->hrini);
                        }
                        return 0;
                    });

                $horasTrabalhadasFormat = sprintf(
                    '%02d:%02d',
                    intdiv($horasTrabalhadas, 60),
                    $horasTrabalhadas % 60
                );

                return [
                    'id' => $tecnicoId,
                    'tecnico' => [
                        'id' => $tecnicoId,
                        'nome' => $tecnico?->nome ?? ''
                    ],
                    'horasTrabalhadas' => $horasTrabalhadasFormat,
                    'visitasAgendadas' => $visitasAgendadas,
                    'osRealizadas' => $osRealizadas,
                    'osNaoRealizadas' => $osNaoRealizadas,
                    'visitasPendentes' => $visitasPendentes,
                ];
            })
            ->values(); 

        return response()->json([
            'data' => $relatorio
        ]);
    }
}
