<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use Illuminate\Http\Request;

class OrdemServicoController extends Controller
{

    public function index(Request $request, int $idCliente)
    {
        $perPage     = (int) $request->query('per_page', 15);
        $page        = (int) $request->query('page', 1);
        $search      = trim($request->query('search'));
        $dataInicio  = $request->query('dataInicio');
        $dataFim     = $request->query('dataFim');

        $query = OrdemServico::with('tecnico')
            ->where('cliente_id', $idCliente);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('numos', 'LIKE', "%{$search}%")
                ->orWhereHas('tecnico', function ($t) use ($search) {
                    $t->where('nome', 'LIKE', "%{$search}%");
                });
            });
        }

        if (!empty($dataInicio)) {
            $query->whereDate('hrini', '>=', $dataInicio);
        }

        if (!empty($dataFim)) {
            $query->whereDate('hrini', '<=', $dataFim);
        }

        $ordens = $query
            ->orderByDesc('hrini')
            ->paginate($perPage, ['*'], 'page', $page);

        $ordensFormatadas = $ordens->getCollection()->map(function ($os) {
            return [
                'id' => $os->id,
                'numOs' => $os->numos,
                'status' => strtoupper($os->statusos),

                'data' => $os->hrini
                    ? $os->hrini->toDateString()
                    : null,

                'horaInicio' => $os->hrini
                    ? $os->hrini->format('H:i')
                    : null,

                'horaFim' => $os->hrfin
                    ? $os->hrfin->format('H:i')
                    : null,

                'tecnico' => [
                    'id' => $os->tecnico?->id ?? "",
                    'nome' => $os->tecnico?->nome ?? ""
                ]
            ];
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
}