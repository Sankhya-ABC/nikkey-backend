<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use Illuminate\Http\Request;
use App\Policies\VisibilityPolicy;

class OrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $perPage    = (int) $request->query('per_page', 15);
        $page       = (int) $request->query('page', 1);
        $search     = trim($request->query('search', ''));
        $dataInicio = $request->query('dataInicio');
        $dataFim    = $request->query('dataFim');
        $idCliente  = $request->query('idCliente'); // agora é cliente

        if (!$dataInicio || !$dataFim) {
            return response()->json([
                'message' => 'Os parâmetros dataInicio e dataFim são obrigatórios.'
            ], 422);
        }

        $user = $request->user();

        // Base query com datas
        $query = OrdemServico::with('tecnico')
            ->whereDate('hrini', '>=', $dataInicio)
            ->whereDate('hrini', '<=', $dataFim);

        // Aplica a policy para filtrar clientes
        $query = VisibilityPolicy::apply($user, $query, 'cliente_id', $idCliente);

        // Busca por número da OS ou nome do técnico
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

        $ordensFormatadas = $ordens->getCollection()->map(fn($os) => $this->toVO($os));
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
            'numOS'      => $os->numos,
            'status'     => strtoupper($os->statusos ?? ''),
            'data'       => $os->hrini?->toDateString() ?? null,
            'horaInicio' => $os->hrini?->format('H:i') ?? null,
            'horaFim'    => $os->hrfin?->format('H:i') ?? null,
            'tecnico'    => [
                'id'   => $os->tecnico?->id ?? "",
                'nome' => $os->tecnico?->nome ?? "",
            ],
        ];
    }
}
