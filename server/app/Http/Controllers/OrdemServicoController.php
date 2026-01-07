<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use Illuminate\Http\Request;

class OrdemServicoController extends Controller
{
    public function index(Request $request, int $idCliente)
    {
        $perPage    = (int) $request->query('per_page', 15);
        $page       = (int) $request->query('page', 1);
        $search     = trim($request->query('search', ''));
        $dataInicio = $request->query('dataInicio');
        $dataFim    = $request->query('dataFim');

        if (!$dataInicio || !$dataFim) {
            return response()->json([
                'message' => 'Os parâmetros dataInicio e dataFim são obrigatórios.'
            ], 422);
        }

        $query = OrdemServico::with('tecnico')
            ->where('cliente_id', $idCliente)
            ->whereDate('hrini', '>=', $dataInicio)
            ->whereDate('hrini', '<=', $dataFim);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('numos', 'LIKE', "%{$search}%")
                  ->orWhereHas('tecnico', function ($t) use ($search) {
                      $t->where('nome', 'LIKE', "%{$search}%");
                  });
            });
        }

        $ordens = $query->orderByDesc('hrini')
                        ->paginate($perPage, ['*'], 'page', $page);

        $ordensFormatadas = $ordens->getCollection()->map(function ($os) {
            return $this->toVO($os);
        });

        $ordens->setCollection($ordensFormatadas);

        return response()->json([
            'data' => $ordens->items(),
            'meta' => [
                'current_page' => $ordens->currentPage(),
                'per_page'     => $ordens->perPage(),
                'total'        => $ordens->total(),
                'last_page'    => $ordens->lastPage(),
            ]
        ]);
    }

    private function toVO(OrdemServico $os): array
    {
        return [
            'numOs'        => $os->numos,
            'status'    => strtoupper($os->statusos ?? ''),
            'data'      => $os->hrini?->toDateString() ?? null,
            'horaInicio'=> $os->hrini?->format('H:i') ?? null,
            'horaFim'   => $os->hrfin?->format('H:i') ?? null,
            'tecnico'   => [
                'id'   => $os->tecnico?->id ?? "",
                'nome' => $os->tecnico?->nome ?? "",
            ],
        ];
    }
}
