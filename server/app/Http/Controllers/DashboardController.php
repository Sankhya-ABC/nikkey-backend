<?php

namespace App\Http\Controllers;

use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;

class DashboardController extends Controller {
    // mappers
    private function mapParceiro(array $item): array
    {
        return [
            'codigo' => $item['f0']['$'] ?? null,
            'nome' => trim($item['f1']['$'] ?? ''),
            'razao_social' => trim($item['f2']['$'] ?? ''),
            'cpf_cnpj' => $item['f3']['$'] ?? null,
            'ativo' => ($item['f4']['$'] ?? 'N') === 'S',
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
            fn ($item) => $this->mapParceiro($item),
            $records
        );

        return response()->json([
            'data' => $records
        ]);
    }

    public function getOrdensServico(){
        return response()->json([
            'message' => 'Endpoint getOrdensServico',
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
