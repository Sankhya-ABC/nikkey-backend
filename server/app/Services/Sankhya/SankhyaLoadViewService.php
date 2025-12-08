<?php

namespace App\Services\Sankhya;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SankhyaLoadViewService
{
    private Client $client;
    private string $gateway;
    private int $defaultPageSize = 500;

    public function __construct()
    {
        $this->client = new Client(['verify' => false, 'timeout' => 120]);
        $this->gateway = env('SNK_GATEWAY');
    }

    public function fetchPaginated(
        string $token,
        string $viewName,
        array $fields,
        array $criteria = [],
        ?int $pageSize = null
    ): \Generator {
        $pageSize = $pageSize ?? $this->defaultPageSize;

        $start = 0;
        $end = $pageSize;

        do {
            $body = [
                "serviceName" => "CRUDServiceProvider.loadView",
                "requestBody" => [
                    "query" => [
                        "viewName" => $viewName,
                        "startRow" => $start,
                        "endRow" => $end,
                        "fields" => ["field" => ["$" => implode(',', $fields)]],
                        "criteria" => $this->buildCriteria($criteria)
                    ]
                ]
            ];

            $response = $this->client->post(
                rtrim($this->gateway, '/') . '/mge/service.sbr?serviceName=CRUDServiceProvider.loadView&outputType=json',
                [
                    'headers' => [
                        'Authorization' => "Bearer {$token}",
                        'Accept' => 'application/json'
                    ],
                    'json' => $body,
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            $records = $data['responseBody']['records']['record'] ?? [];

            // Normaliza quando o Sankhya retorna 1 registro em formato diferente
            if (isset($records['NUMOS']) || isset($records['$'])) {
                $records = [$records];
            }

            $count = count($records);
            $hasMore = $count === $pageSize;

            yield [
                'records' => $records,
                'startRow' => $start,
                'endRow' => $end,
                'hasMore' => $hasMore
            ];

            $start = $end;
            $end += $pageSize;

        } while ($hasMore);
    }

    private function buildCriteria(array $criteria): array
    {
        if (empty($criteria)) {
            return [];
        }

        $exp = [];
        $params = [];

        foreach ($criteria as $c) {
            $op = $c['operator'] ?? '=';
            $exp[] = "{$c['field']} {$op} ?";
            $params[] = [
                '$' => $c['value'],
                'type' => $c['type'] ?? 'S'
            ];
        }

        return [
            'expression' => ['$' => implode(' AND ', $exp)],
            'parameter'  => $params
        ];
    }
}
