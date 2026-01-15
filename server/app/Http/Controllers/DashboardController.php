<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;

class DashboardController extends Controller {
    // mappers
    private function mapGetBasicData(array $item): array
    {
        return [
            'codigo' => $item['f0']['$'] ?? null,
            'nome' => trim($item['f1']['$'] ?? ''),
            'razao_social' => trim($item['f2']['$'] ?? ''),
            'cpf_cnpj' => $item['f3']['$'] ?? null,
            'ativo' => ($item['f4']['$'] ?? 'N') === 'S',
        ];
    }

    private function mapGetOrdensServico(array $item): array
    {
        return [
            'clienteId' => $item['f0']['$'] ?? null,
            'tecnicoId' => $item['f1']['$'] ?? null,
            'dataPrevista' => $item['f2']['$'] ?? null,
        ];
    }

    // endpoints requests
    public function getBasicData()
    {
        $token = (new AuthSankhya())->login();

        if (!$token) {
            return response()->json([
                'message' => 'Falha ao autenticar no Sankhya'
            ], 500);
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'Parceiro',
            fields: [
                '' => [
                    'CODPARC',
                    'NOMEPARC',
                    'RAZAOSOCIAL',
                    'CGC_CPF',
                    'ATIVO'
                ]
            ],
            criteria: [
                ['field' => 'CLIENTE', 'value' => 'S', 'type' => 'S']
            ]
        );

        $records = array_map(
            fn ($item) => $this->mapGetBasicData($item),
            $records
        );

        return response()->json([
            'data' => $records
        ]);
    }

    public function getOrdensServico(Request $request)
    {
        // app auth
        $token = (new AuthSankhya())->login();

        if (!$token) {
            return response()->json([
                'message' => 'Falha ao autenticar no Sankhya'
            ], 500);
        }

        // request
        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_VGFOSE',
            fields: [
                '' => [
                    'CODPARC',
                    'CODTEC',
                    'DHPREVISTA'
                ]
            ]
        );

        $records = array_map(
            fn ($item) => $this->mapGetOrdensServico($item),
            $records
        );

        // handle dates from queryParamRequest
        $dataInicio = $request->query('dataInicio');
        $dataFim = $request->query('dataFim');
        $dataInicio = $dataInicio
            ? Carbon::parse($dataInicio)->startOfDay()
            : null;
        $dataFim = $dataFim
            ? Carbon::parse($dataFim)->endOfDay()
            : null;

        // filter by date
        if ($dataInicio || $dataFim) {
            $records = array_values(array_filter($records, function ($item) use ($dataInicio, $dataFim) {
                if (empty($item['dataPrevista'])) {
                    return false;
                }

                $dataPrevista = Carbon::createFromFormat(
                    'd/m/Y H:i:s',
                    $item['dataPrevista']
                );

                if ($dataInicio && $dataPrevista->lt($dataInicio)) {
                    return false;
                }

                if ($dataFim && $dataPrevista->gt($dataFim)) {
                    return false;
                }

                return true;
            }));
        }

        // group data
        $qtdOrdemServico = count($records);

        $clientesUnicos = [];
        $tecnicosUnicos = [];

        foreach ($records as $item) {
            if (!empty($item['clienteId'])) {
                $clientesUnicos[$item['clienteId']] = true;
            }

            if (!empty($item['tecnicoId'])) {
                $tecnicosUnicos[$item['tecnicoId']] = true;
            }
        }

        $qtdCliente = count($clientesUnicos);
        $qtdTecnico = count($tecnicosUnicos);

        return response()->json([
            'qtdOrdemServico' => $qtdOrdemServico,
            'qtdCliente' => $qtdCliente,
            'qtdTecnico' => $qtdTecnico
        ]);
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
