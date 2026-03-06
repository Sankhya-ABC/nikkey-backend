<?php

namespace App\Services\Sankhya;

use GuzzleHttp\Client;
use App\Services\Sankhya\AuthSankhya;
use Illuminate\Support\Facades\Cache;

class SankhyaDbExplorerSPService
{
    private Client $client;
    private string $gateway;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
            'timeout' => 60,
            'connect_timeout' => 10,
            'http_errors' => false,
        ]);

        $this->gateway = env('SNK_GATEWAY');
    }

    public function mapDbExplorerResult(array $response): array
    {
        $metadata = $response['responseBody']['fieldsMetadata'] ?? [];
        $rows = $response['responseBody']['rows'] ?? [];

        if (empty($rows)) {
            return [];
        }

        $fields = array_map(
            fn ($field) => $field['name'],
            $metadata
        );

        return array_map(function ($row) use ($fields) {
            return array_combine($fields, $row);
        }, $rows);
    }

  public function fetchAll(string $sql): array
    {
        $token = (new AuthSankhya())->login();

        if (!$token) {
            throw new \Exception('Falha ao autenticar no Sankhya');
        }

        $body = [
            'serviceName' => 'DbExplorerSP.executeQuery',
            'requestBody' => [
                'sql' => $sql
            ]
        ];

        $response = $this->client->post(
            rtrim($this->gateway, '/') . '/mge/service.sbr?serviceName=DbExplorerSP.executeQuery&outputType=json',
            [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ],
                'json' => $body
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}