<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;

class DashboardController extends Controller {
    // utils
    private function autenticarSankhya(): string
    {
        $token = (new AuthSankhya())->login();

        if (!$token) {
            abort(500, 'Falha ao autenticar no Sankhya');
        }

        return $token;
    }

    private function parsePeriodo(Request $request): array
    {
        return [
            'inicio' => $request->query('dataInicio')
                ? Carbon::parse($request->query('dataInicio'))->startOfDay()
                : null,

            'fim' => $request->query('dataFim')
                ? Carbon::parse($request->query('dataFim'))->endOfDay()
                : null
        ];
    }

    private function filtrarPorPeriodo(array $records, ?Carbon $inicio, ?Carbon $fim): array
    {
        if (!$inicio && !$fim) {
            return $records;
        }

        return array_values(array_filter($records, function ($item) use ($inicio, $fim) {
            if (empty($item['dataPrevista'])) {
                return false;
            }

            $data = Carbon::createFromFormat('d/m/Y H:i:s', $item['dataPrevista']);

            if ($inicio && $data->lt($inicio)) {
                return false;
            }

            if ($fim && $data->gt($fim)) {
                return false;
            }

            return true;
        }));
    }

    // mappers
    private function mapGetBasicData(array $item): array
    {
        return [
            'clienteId' => $item['f0']['$'] ?? null,
            'tecnicoId' => $item['f1']['$'] ?? null,
            'dataPrevista' => $item['f2']['$'] ?? null,
        ];
    }

    private function mapGetOrdensServico(array $item): array
    {
        return [
            'dataPrevista' => $item['f0']['$'] ?? null,
        ];
    }

    // endpoints requests
    public function getBasicData(Request $request)
    {
        // authentication
        $token = $this->autenticarSankhya();
        $periodo = $this->parsePeriodo($request);

        // service
        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_VGFOSE',
            fields: [
                '' => ['CODPARC', 'CODTEC', 'DHPREVISTA']
            ]
        );

        // mapping
        $records = array_map(
            fn ($item) => $this->mapGetBasicData($item),
            $records
        );

        // filter by date
        $records = $this->filtrarPorPeriodo(
            $records,
            $periodo['inicio'],
            $periodo['fim']
        );

        // response
        return response()->json([
            'qtdOrdemServico' => count($records),
            'qtdCliente' => count(array_unique(array_column($records, 'clienteId'))),
            'qtdTecnico' => count(array_unique(array_column($records, 'tecnicoId')))
        ]);
    }

    public function getOrdensServico(Request $request)
    {
        // authentication
        $token = $this->autenticarSankhya();
        $periodo = $this->parsePeriodo($request);

        // service
        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_VGFOSE',
            fields: [
                '' => ['DHPREVISTA']
            ]
        );

        // mapping
        $records = array_map(
            fn ($item) => $this->mapGetOrdensServico($item),
            $records
        );

        // filter by date
        $records = $this->filtrarPorPeriodo(
            $records,
            $periodo['inicio'],
            $periodo['fim']
        );

        // group
        $grouped = [];
        foreach ($records as $item) {
            $dataKey = Carbon::createFromFormat(
                'd/m/Y H:i:s',
                $item['dataPrevista']
            )->format('Y-m-d');

            $grouped[$dataKey] = ($grouped[$dataKey] ?? 0) + 1;
        }
        ksort($grouped);

        // response
        return response()->json(
            array_map(
                fn ($data, $qntOS) => ['data' => $data, 'qntOS' => $qntOS],
                array_keys($grouped),
                $grouped
            )
        );
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
