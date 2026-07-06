<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\OrdemServico;
use App\Models\OrdemServicoAmbiente;
use App\Models\Praga;
use App\Models\TipoPraga;
use App\Models\TipoEvidencia;
use App\Models\Individuo;
use App\Models\Evidenciador;
use App\Models\EvidenciaPraga;
use App\Services\SyncLogService;

class SankhyaSyncEvidenciasPragas extends Command
{
    protected $signature = 'sankhya:sync-evidencias-pragas';
    protected $description = 'Busca evidencias de pragas no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('evidencias-pragas'))->start();
        try {
            $this->info('Iniciando sincronizacao de evidencias de pragas...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_VGFOSEEV',
                fields: ['' => [
                    'NUMOS', 'ID', 'CODPRAGA', 'TIPPRAGA', 'DTEV',
                    'TPEV', 'INDIVIDUO', 'OBS', 'QTDPRAGA', 'FASEPRAGA',
                    'EVI', 'EVINOME', 'SETOR',
                ]]
            );

            $evidencias = collect($records)
                ->map(function ($row) {
                    $numos           = $row['f0']['$'] ?? null;
                    $codSnk          = $row['f1']['$'] ?? null;
                    $pragaSnk        = $row['f2']['$'] ?? null;
                    $tipoPragaCod    = $row['f3']['$'] ?? null;
                    $tipoEvidSnk     = $row['f5']['$'] ?? null;
                    $individuoCod    = $row['f6']['$'] ?? null;
                    $evidenciadorCod = $row['f10']['$'] ?? null;
                    $setor           = $row['f12']['$'] ?? null;

                    if (!$numos || !$codSnk || !$setor) return null;

                    $ordemServicoId = OrdemServico::where('numos', $numos)->value('id');
                    if (!$ordemServicoId) return null;

                    $ordemServicoAmbienteId = OrdemServicoAmbiente::where('ordem_servico_id', $ordemServicoId)
                        ->where('setor', $setor)
                        ->value('id');

                    return [
                        'codevidencia_snk'         => $codSnk,
                        'ordem_servico_id'          => $ordemServicoId,
                        'ordem_servico_ambiente_id' => $ordemServicoAmbienteId,
                        'setor'                     => $setor,
                        'praga_id'                  => Praga::where('codpraga_snk', $pragaSnk)->value('id'),
                        'tipo_praga_id'             => TipoPraga::where('codigo', $tipoPragaCod)->value('id'),
                        'tipo_evidencia_id'         => TipoEvidencia::where('codenvidencia_snk', $tipoEvidSnk)->value('id'),
                        'individuo_id'              => Individuo::where('codigo', $individuoCod)->value('id'),
                        'evidenciador_id'           => Evidenciador::where('codigo', $evidenciadorCod)->value('id'),
                        'data_evidencia'            => $this->parseDate($row['f4']['$'] ?? null),
                        'quantidade'                => $row['f8']['$'] ?? 0,
                        'fase_praga'                => $row['f9']['$'] ?? null,
                        'evidenciador_nome'         => $row['f11']['$'] ?? null,
                        'observacoes'               => $row['f7']['$'] ?? null,
                        'created_at'                => now(),
                        'updated_at'                => now(),
                    ];
                })
                ->filter();

            if ($evidencias->isNotEmpty()) {
                EvidenciaPraga::upsert($evidencias->toArray(), ['codevidencia_snk'], array_keys($evidencias->first()));
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$evidencias->count()} evidencias em {$duracao}s.");

            $syncLog->finish($evidencias->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }

    private function parseDate(?string $value): ?string
    {
        if (!$value) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) return substr($value, 0, 10);
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            [$d, $m, $y] = explode('/', $value);
            return "{$y}-{$m}-{$d}";
        }
        return null;
    }
}
