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
            'idCliente' => $item['f0']['$'] ?? null,
            'idTecnico' => $item['f1']['$'] ?? null,
            'dataPrevista' => $item['f2']['$'] ?? null,
        ];
    }

    private function mapGetOrdensServico(array $item): array
    {
        return [
            'dataPrevista' => $item['f0']['$'] ?? null,
        ];
    }

    private function mapGetAtendimentosTecnico(array $item): array
    {
        return [
            'idTecnico' => $item['f0']['$'] ?? null,
            'nomeTecnico' => trim($item['f1']['$'] ?? ''),
            'dataPrevista' => $item['f2']['$'] ?? null,
        ];
    }

    private function mapGetConsumoProdutosOSAndDate(array $item): array
    {
        return [
            'numOS' => $item['f0']['$'] ?? null,
            'dataPrevista' => trim($item['f1']['$'] ?? ''),
        ];
    }
    
    private function mapGetConsumoProdutosOSAndCodProdAndQtd(array $item): array
    {
        return [
            'numOS' => $item['f0']['$'] ?? null,
            'codProduto' => $item['f1']['$'] ?? null,
            'qnt' => (float) ($item['f2']['$'] ?? 0),
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
            'qtdCliente' => count(array_unique(array_column($records, 'idCliente'))),
            'qtdTecnico' => count(array_unique(array_column($records, 'idTecnico')))
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

    public function getAtendimentosTecnico(Request $request)
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
                '' => ['CODTEC', 'NOMETEC', 'DHPREVISTA']
            ]
        );

        // mapping
        $records = array_map(
            fn ($item) => $this->mapGetAtendimentosTecnico($item),
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
            if (empty($item['idTecnico'])) {
                continue;
            }

            $key = $item['idTecnico'];

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'idTecnico' => $item['idTecnico'],
                    'nomeTecnico' => $item['nomeTecnico'],
                    'qtdOrdemServico' => 0
                ];
            }

            $grouped[$key]['qtdOrdemServico']++;
        }
        usort($grouped, fn ($a, $b) => $b['qtdOrdemServico'] <=> $a['qtdOrdemServico']);

        // response
        return response()->json(array_values($grouped));
    }

    public function getConsumoProdutos(Request $request)
    {
        // authentication
        $token = $this->autenticarSankhya();
        $periodo = $this->parsePeriodo($request);

        // service
        $service = new SankhyaLoadRecordsService();

        $OSAndDate = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_VGFOSE',
            fields: [
                '' => ['NUMOS', 'DHPREVISTA']
            ]
        );

        // mapping
        $OSAndDate = array_map(
            fn ($item) => $this->mapGetConsumoProdutosOSAndDate($item),
            $OSAndDate
        );

        // filter by date
        $OSAndDate = $this->filtrarPorPeriodo(
            $OSAndDate,
            $periodo['inicio'],
            $periodo['fim']
        );

        // get numOS
        $numerosOS = array_values(
            array_unique(
                array_column($OSAndDate, 'numOS')
            )
        );

        $OSAndCodProdAndQtd = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_VGFOSESERPRGPRD',
            fields: [
                '' => ['NUMOS', 'CODPROD', 'QTDNEG']
            ]
        );

        // mapping
        $OSAndCodProdAndQtd = array_map(
            fn ($item) => $this->mapGetConsumoProdutosOSAndCodProdAndQtd($item),
            $OSAndCodProdAndQtd
        );

        // group
        $consumoPorProduto = [];
        foreach ($OSAndCodProdAndQtd as $item) {
            $codProduto = $item['codProduto'];

            if (!isset($consumoPorProduto[$codProduto])) {
                $consumoPorProduto[$codProduto] = [
                    'codProduto' => $codProduto,
                    'qnt' => 0,
                ];
            }

            $consumoPorProduto[$codProduto]['qnt'] += $item['qnt'];
        }
        $consumoPorProduto = array_values($consumoPorProduto);


        return response()->json(array_values($consumoPorProduto));
    }

    public function getProximasVisitas(){
        return response()->json([
            'message' => 'Endpoint getProximasVisitas',
        ]);
    }
}
