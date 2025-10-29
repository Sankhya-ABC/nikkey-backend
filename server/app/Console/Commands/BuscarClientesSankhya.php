<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Models\Cliente;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;

class BuscarClientesSankhya extends Command
{
    protected $signature = 'sankhya:buscar-clientes {--incremental}';
    protected $description = 'Busca clientes no Sankhya e atualiza a base local (suporta até grandes volumes).';

    private int $limit = 500;
    private Client $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client(['verify' => false]);
    }

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização de clientes Sankhya...');
        $inicio = microtime(true);

        try {
            $auth = new AuthSankhya();
            $token = $auth->login();

            if (!$token) {
                $this->error('❌ Falha ao autenticar no Sankhya.');
                return 1;
            }

            $offset = 0;
            $totalProcessados = 0;

            do {
                $records = $this->buscarClientesDoSankhya($token, $offset, $this->limit);

                if (empty($records)) {
                    $this->warn("⚠️ Nenhum registro retornado na página $offset. Encerrando.");
                    break;
                }

                $clientes = $this->transformarClientes($records);

                if ($clientes->isNotEmpty()) {
                    Cliente::upsert(
                        $clientes->toArray(),
                        ['codparc_snk'],
                        [
                            'codparc_matriz_snk',
                            'nome_fantasia',
                            'razao_social',
                            'cnpj_cpf',
                            'logradouro',
                            'bairro',
                            'cidade',
                            'estado',
                            'numero',
                            'cep',
                            'telefone',
                            'email',
                            'updated_at',
                        ]
                    );

                    $this->info("✅ Página {$offset} processada ({$clientes->count()} clientes).");
                    $totalProcessados += $clientes->count();
                }

                $offset += $this->limit;

            } while (count($records) === $this->limit);

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("🎯 Total de {$totalProcessados} clientes sincronizados em {$duracao}s.");

            return 0;

        } catch (RequestException $e) {
            $this->error('🌐 Erro de comunicação com o Sankhya: ' . $e->getMessage());
        } catch (\Throwable $e) {
            $this->error('❌ Erro inesperado: ' . $e->getMessage());
        }

        return 1;
    }

    /**
     * Busca uma página de clientes do Sankhya.
     */
    private function buscarClientesDoSankhya(string $token, int $offset, int $limit): array
    {
        $body = [
            "serviceName" => "CRUDServiceProvider.loadView",
            "requestBody" => [
                "query" => [
                    "viewName" => "VW_CLIENTES_PORTAL",
                    "startRow" => $offset,
                    "endRow" => $offset + $limit,
                    "fields" => [
                        "field" => [
                            "$" => "CODPARC, CODPARCMATRIZ, NOMEPARC, RAZAOSOCIAL, CGC_CPF, LOGRADOURO, BAIRROS, CIDADE, UF, NUMEND, CEP, TELEFONE, EMAIL"
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->post(ENV('SNK_GATEWAY') . '/mge/service.sbr?serviceName=CRUDServiceProvider.loadView&outputType=json', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $records = $data['responseBody']['records']['record'] ?? [];
        return isset($records['CODPARC']) ? [$records] : $records;
    }

    /**
     * Transforma o retorno do Sankhya em uma Collection pronta para upsert.
     */
    private function transformarClientes(array $records): Collection
    {
        return collect($records)->map(function ($cli) {
            return [
                'codparc_snk'        => $this->val($cli, 'CODPARC'),
                'codparc_matriz_snk' => $this->val($cli, 'CODPARCMATRIZ'),
                'nome_fantasia'      => $this->val($cli, 'NOMEPARC'),
                'razao_social'       => $this->val($cli, 'RAZAOSOCIAL'),
                'cnpj_cpf'           => $this->val($cli, 'CGC_CPF'),
                'logradouro'         => $this->val($cli, 'LOGRADOURO'),
                'bairro'             => $this->val($cli, 'BAIRROS'),
                'cidade'             => $this->val($cli, 'CIDADE'),
                'estado'             => $this->val($cli, 'UF'),
                'numero'             => $this->val($cli, 'NUMEND'),
                'cep'                => $this->val($cli, 'CEP'),
                'telefone'           => $this->val($cli, 'TELEFONE'),
                'email'              => $this->val($cli, 'EMAIL'),
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        })->filter(fn($c) => !empty($c['codparc_snk']));
    }

    /**
     * Helper para extrair valor seguro de campo Sankhya.
     */
    private function val(array $cli, string $key): ?string
    {
        return isset($cli[$key]['$']) ? trim($cli[$key]['$']) : null;
    }
}
