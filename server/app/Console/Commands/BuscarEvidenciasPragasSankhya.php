<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\OrdemServico;
use App\Models\OrdemServicoAmbiente;
use App\Models\Praga;
use App\Models\TipoPraga;
use App\Models\TipoEvidencia;
use App\Models\Individuo;
use App\Models\Evidenciador;
use App\Models\EvidenciaPraga;

class BuscarEvidenciasPragasSankhya extends Command
{
    protected $signature = 'sankhya:buscar-evidencias-pragas';

    protected $description = 'Busca evidências de pragas no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização de evidências de pragas...');

        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_VGFOSEEV',
            fields: [
                '' => [
                    'NUMOS',
                    'ID',
                    'CODPRAGA',
                    'TIPPRAGA',
                    'DTEV',
                    'TPEV',
                    'INDIVIDUO',
                    'OBS',
                    'QTDPRAGA',
                    'FASEPRAGA',
                    'EVI',
                    'EVINOME',
                    'SETOR',
                ]
            ]
        );

        if (empty($records)) {
            $this->info('Nenhuma evidência encontrada.');
            return 0;
        }

        $evidencias = collect($records)
            ->map(function ($row) {

                $numos = $row['f0']['$'] ?? null;
                $codSnk = $row['f1']['$'] ?? null;
                $pragaSnk = $row['f2']['$'] ?? null;
                $tipoPragaCod = $row['f3']['$'] ?? null;
                $tipoEvidenciaSnk = $row['f5']['$'] ?? null;
                $individuoCod = $row['f6']['$'] ?? null;
                $evidenciadorCod = $row['f10']['$'] ?? null;
                $setor = $row['f12']['$'] ?? null;

                if (!$numos || !$codSnk || !$setor) {
                    return null;
                }

                $ordemServicoId = OrdemServico::where('numos', $numos)->value('id');
                if (!$ordemServicoId) return null;

            $ordemServicoAmbienteId = OrdemServicoAmbiente::where('ordem_servico_id', $ordemServicoId)
                ->where('setor', $setor)
                ->value('id');
                    
                $pragaId = Praga::where('codpraga_snk', $pragaSnk)->value('id');
                $tipoPragaId = TipoPraga::where('codigo', $tipoPragaCod)->value('id');
                $tipoEvidenciaId = TipoEvidencia::where('codenvidencia_snk', $tipoEvidenciaSnk)->value('id');
                $individuoId = Individuo::where('codigo', $individuoCod)->value('id');
                $evidenciadorId = Evidenciador::where('codigo', $evidenciadorCod)->value('id');

                return [
                    'codevidencia_snk' => $codSnk,

                    'ordem_servico_id' => $ordemServicoId,
                    'ordem_servico_ambiente_id' => $ordemServicoAmbienteId,

                    'setor' => $setor,

                    'praga_id' => $pragaId,
                    'tipo_praga_id' => $tipoPragaId,
                    'tipo_evidencia_id' => $tipoEvidenciaId,
                    'individuo_id' => $individuoId,
                    'evidenciador_id' => $evidenciadorId,

                    'data_evidencia' => $this->parseDate($row['f4']['$'] ?? null),

                    'quantidade' => $row['f8']['$'] ?? 0,
                    'fase_praga' => $row['f9']['$'] ?? null,
                    'evidenciador_nome' => $row['f11']['$'] ?? null,
                    'observacoes' => $row['f7']['$'] ?? null,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->filter();

        if ($evidencias->isNotEmpty()) {
            EvidenciaPraga::upsert(
                $evidencias->toArray(),
                ['codevidencia_snk'],
                array_keys($evidencias->first())
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);

        $this->info("🎯 Total sincronizado: {$evidencias->count()} evidências em {$duracao}s.");

        return 0;
    }

    private function parseDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return substr($value, 0, 10);
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            [$d, $m, $y] = explode('/', $value);
            return "{$y}-{$m}-{$d}";
        }

        return null;
    }
}
