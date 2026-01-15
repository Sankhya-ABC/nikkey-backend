<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use Illuminate\Http\Request;
use App\Policies\VisibilityPolicy;
use App\Models\Individuo;

class OrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $perPage    = (int) $request->query('per_page', 15);
        $page       = (int) $request->query('page', 1);
        $search     = trim($request->query('search', ''));
        $dataInicio = $request->query('dataInicio');
        $dataFim    = $request->query('dataFim');
        $idCliente  = $request->query('idCliente'); 

        if (!$dataInicio || !$dataFim) {
            return response()->json([
                'message' => 'Os parâmetros dataInicio e dataFim são obrigatórios.'
            ], 422);
        }

        $user = $request->user();

        $query = OrdemServico::with(['tecnico', 'cliente'])
            ->whereDate('hrini', '>=', $dataInicio)
            ->whereDate('hrini', '<=', $dataFim);

        $query = VisibilityPolicy::apply($user, $query, 'cliente_id', $idCliente);

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

    public function show(int $id)
    {
        $os = OrdemServico::with([
            'evidenciasPragas.individuo:id,codigo',
            'evidenciasPragas.ambiente:id,setor',
            'evidenciasPragas.praga:id,nome_praga',
        ])->findOrFail($id);

        /**
         * 1️⃣ Pragas encontradas (simples)
         */
        $pragas = $os->evidenciasPragas->map(fn ($e) => [
            'idPraga' => $e->praga_id ?? '',
            'comoEncontrado' => $e->individuo?->codigo ?? '',
            'ondeEncontrado' => $e->ambiente?->setor
                ?? $e->setor
                ?? '',
            'quantidade' => $e->quantidade ?? '',
        ]);

        /**
         * 2️⃣ Identificações únicas (V, M, F)
         */
        $identificacoes = $os->evidenciasPragas
            ->pluck('individuo.codigo')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        /**
         * 3️⃣ Contagem dinâmica por espécie (PRAGA)
         */
        $especies = $os->evidenciasPragas
            ->groupBy('praga_id')
            ->map(function ($evidencias, $pragaId) use ($identificacoes) {

                $praga = $evidencias->first()->praga;

                $quantidades = collect($identificacoes)->map(function ($codigo) use ($evidencias) {
                    return $evidencias
                        ->where('individuo.codigo', $codigo)
                        ->sum('quantidade');
                })->toArray();

                return [
                    'idPraga' => $pragaId,
                    'nome' => $praga?->nome ?? 'Não identificado',
                    'quantidades' => $quantidades,
                ];
            })
            ->values();

        return response()->json([
            'pragas' => $pragas,

            'contagemInsetos' => [
                'tipoContagem' => 'ESPECIE',
                'dados' => [
                    'identificacao' => $identificacoes,
                    'especies' => $especies,
                ]
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

            'cliente' => [
                'id'   => $os->cliente?->id ?? null,
                'nome' => $os->cliente?->nome ?? '',
            ],

            'tecnico' => [
                'id'   => $os->tecnico?->id ?? null,
                'nome' => $os->tecnico?->nome ?? '',
            ],
        ];
    }
}
