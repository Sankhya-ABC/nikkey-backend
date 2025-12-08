<?php

namespace App\Services\Sankhya;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SankhyaLoadRecordsService
{
    private Client $client;
    private string $gateway;
    private int $defaultPageSize = 200;

    public function __construct()
    {
        $this->client = new Client(['verify' => false, 'timeout' => 120]);
        $this->gateway = env('SNK_GATEWAY');
    }

    /**
     * Retorna um gerador de páginas iniciando de $startOffset.
     *
     * Cada yield retorna um array com:
     *  - records: array (lista de registros)
     *  - offset: int (offset da página atual)
     *  - pageSize: int
     *  - hasMore: bool
     */
    public function fetchPaginatedFrom(
        string $token,
        string $rootEntity,
        array $fields,
        array $criteria = [],
        ?int $pageSize = null,
        int $startOffset = 0
    ): \Generator {
        $pageSize = $pageSize ?? $this->defaultPageSize;
        $offset = $startOffset;

        do {
            $body = [
                'requestBody' => [
                    'dataSet' => [
                        'rootEntity' => $rootEntity,
                        'ignoreCalculatedFields' => 'true',
                        'useFileBasedPagination' => 'true',
                        'includePresentationFields' => 'N',
                        'tryJoinedFields' => 'true',
                        'offsetPage' => (string) $offset,
                        'pageSize' => (string) $pageSize,
                        'criteria' => $this->buildCriteria($criteria),
                        'entity' => $this->buildEntities($fields)
                    ]
                ]
            ];

            $response = $this->client->post(
                rtrim($this->gateway, '/') . '/mge/service.sbr?serviceName=CRUDServiceProvider.loadRecords&outputType=json',
                [
                    'headers' => [
                        'Authorization' => "Bearer {$token}",
                        'Accept' => 'application/json'
                    ],
                    'json' => $body,
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            $entities = $data['responseBody']['entities'] ?? [];
            $records = $entities['entity'] ?? [];
            $hasMore = isset($entities['hasMoreResult']) && $entities['hasMoreResult'] === 'true';

            yield [
                'records' => $records,
                'offset' => $offset,
                'pageSize' => $pageSize,
                'hasMore' => $hasMore,
            ];

            $offset++;

        } while ($hasMore);
    }

    private function buildCriteria(array $criteria): array
    {
        if (empty($criteria)) {
            return [];
        }

        $expressions = [];
        $parameters = [];

        foreach ($criteria as $c) {
            $operator = $c['operator'] ?? '=';
            $expressions[] = "{$c['field']} {$operator} ?";
            $parameters[] = [
                '$' => $c['value'],
                'type' => $c['type'] ?? 'S'
            ];
        }

        return [
            'expression' => ['$' => implode(' AND ', $expressions)],
            'parameter'  => $parameters
        ];
    }

    private function buildEntities(array $fields): array
    {
        return array_map(
            fn($path, $list) => ['path' => $path, 'fieldset' => ['list' => implode(',', $list)]],
            array_keys($fields),
            $fields
        );
    }

    public function fetchAll(
        string $token,
        string $rootEntity,
        array $fields,
        array $criteria = [],
        ?int $pageSize = null
    ): array {
        $result = [];

        foreach ($this->fetchPaginatedFrom(
            token: $token,
            rootEntity: $rootEntity,
            fields: $fields,
            criteria: $criteria,
            pageSize: $pageSize,
            startOffset: 0
        ) as $page) {
            foreach ($page['records'] as $record) {
                $result[] = $record;
            }
        }

        return $result;
    }
}
