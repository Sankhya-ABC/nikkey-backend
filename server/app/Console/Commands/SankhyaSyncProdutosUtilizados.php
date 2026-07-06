<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\ProdutoUtilizado;
use App\Models\OrdemServico;
use App\Models\Servico;
use App\Models\Praga;
use App\Models\Produto;
use App\Models\Metodologia;
use App\Services\SyncLogService;

class SankhyaSyncProdutosUtilizados extends Command
{
    protected $signature = 'sankhya:sync-produtos-utilizados';
    protected $description = 'Busca produtos utilizados no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('produtos-utilizados'))->start();
        try {
            $this->info('Iniciando sincronizacao de produtos utilizados...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_VGFOSESERPRGPRDUTIL',
                fields: ['' => [
                    'PRAGA', 'NUMOS', 'CODSERV', 'ID', 'CODPROD',
                    'QTDNEG', 'LOTE', 'DTFAB', 'DTVAL', 'CALDA',
                    'CONCENTRACAO', 'GRUPOQUIM', 'PRINCIPIOATV', 'SINTOMAS',
                    'ANTIDOTO', 'CODREGMAPA', 'ACAOTOXICA', 'DILUENTE',
                    'QTDNEGDILUENTE', 'LOTEDILUENTE', 'METODOLOGIA',
                    'TECNICAEXEC', 'TPAPLICACAO', 'AMBIENTE'
                ]]
            );

            $produtos = collect($records)
                ->map(function ($row) {
                    $numos          = $row['f1']['$'] ?? null;
                    $codserv        = $row['f2']['$'] ?? null;
                    $pragaSnk       = $row['f0']['$'] ?? null;
                    $codProdSnk     = $row['f4']['$'] ?? null;
                    $metodologiaSnk = $row['f20']['$'] ?? null;
                    $codProdUtilSnk = $row['f3']['$'] ?? null;

                    if (!$numos || !$codserv || !$codProdUtilSnk) return null;

                    $ordemServicoId = OrdemServico::where('numos', $numos)->value('id');
                    $servicoId      = Servico::where('codprod_snk', $codserv)->value('id');
                    $pragaId        = Praga::where('codpraga_snk', $pragaSnk)->value('id');
                    $produtoId      = Produto::where('codprod_snk', $codProdSnk)->value('id');
                    $metodologiaId  = Metodologia::where('codmetodologia_snk', $metodologiaSnk)->value('id');

                    if (!$ordemServicoId || !$servicoId || !$pragaId || !$produtoId || !$metodologiaId) {
                        return null;
                    }

                    return [
                        'ordem_servico_id' => $ordemServicoId,
                        'servico_id'       => $servicoId,
                        'praga_id'         => $pragaId,
                        'produto_id'       => $produtoId,
                        'metodologia_id'   => $metodologiaId,
                        'qtdneg'           => $row['f5']['$'] ?? null,
                        'lote'             => $row['f6']['$'] ?? null,
                        'dtfab'            => $row['f7']['$'] ?? null,
                        'dtval'            => $row['f8']['$'] ?? null,
                        'calda'            => $row['f9']['$'] ?? null,
                        'concentracao'     => $row['f10']['$'] ?? null,
                        'grupoquim'        => $row['f11']['$'] ?? null,
                        'principioatv'     => $row['f12']['$'] ?? null,
                        'sintomas'         => $row['f13']['$'] ?? null,
                        'antidoto'         => $row['f14']['$'] ?? null,
                        'codregmapa'       => $row['f15']['$'] ?? null,
                        'acaotoxica'       => $row['f16']['$'] ?? null,
                        'diluente'         => $row['f17']['$'] ?? null,
                        'qtdnegdiluente'   => $row['f18']['$'] ?? null,
                        'lotediluente'     => $row['f19']['$'] ?? null,
                        'tecnicaexec'      => $row['f21']['$'] ?? null,
                        'tpaplicacao'      => $row['f22']['$'] ?? null,
                        'ambiente'         => $row['f23']['$'] ?? null,
                        'codprodutil_snk'  => $codProdUtilSnk,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ];
                })
                ->filter();

            if ($produtos->isNotEmpty()) {
                ProdutoUtilizado::upsert($produtos->toArray(), ['codprodutil_snk'], array_keys($produtos->first()));
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$produtos->count()} produtos utilizados em {$duracao}s.");

            $syncLog->finish($produtos->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
