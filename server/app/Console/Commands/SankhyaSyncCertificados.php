<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Certificado;
use App\Models\OrdemServico;
use App\Services\SyncLogService;

class SankhyaSyncCertificados extends Command
{
    protected $signature   = 'sankhya:sync-certificados';
    protected $description = 'Sincroniza certificados de garantia das OSs (via Cabecalho + Itens).';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('certificados'))->start();
        try {
            $this->info('Iniciando sync de certificados...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            // Passo 1: buscar notas fiscais vinculadas a OSs (AD_NUMNIKKEY preenchido)
            $cabecalhos = $service->fetchAll(
                token:      $token,
                rootEntity: 'Cabecalho',
                fields:     ['' => ['NUNOTA', 'AD_NUMNIKKEY', 'CODEMP', 'AD_CODVEND']],
                criteria:   [
                    ['field' => 'AD_NUMNIKKEY', 'value' => '', 'operator' => '<>', 'type' => 'S'],
                ]
            );

            // Indexar por NUNOTA
            $notasPorNunota = collect($cabecalhos)
                ->filter(fn($r) => !empty($r['f0']['$']) && !empty($r['f1']['$']))
                ->keyBy(fn($r) => $r['f0']['$']);

            if ($notasPorNunota->isEmpty()) {
                $this->info('Nenhuma nota fiscal vinculada encontrada.');
                $syncLog->finish(0);
                return 0;
            }

            // Indexar OS por AD_NUMNIKKEY
            $numosPorNikkey = OrdemServico::whereNotNull('ad_numnikkey')
                ->pluck('numos', 'ad_numnikkey');

            // Passo 2: buscar itens com data de garantia
            $itens = $service->fetchAll(
                token:      $token,
                rootEntity: 'Itens',
                fields:     ['' => ['NUNOTA', 'AD_DT_GARRANTIA', 'AD_DIASGARANTIA']],
                criteria:   [
                    ['field' => 'AD_DT_GARRANTIA', 'value' => '', 'operator' => '<>', 'type' => 'S'],
                ]
            );

            // Pegar o primeiro item com garantia por NUNOTA
            $garantiaPorNunota = collect($itens)
                ->filter(fn($r) => !empty($r['f0']['$']) && !empty($r['f1']['$']))
                ->groupBy(fn($r) => $r['f0']['$'])
                ->map(fn($grupo) => $grupo->first());

            // Montar certificados cruzando as duas fontes
            $data = [];
            foreach ($notasPorNunota as $nunota => $cab) {
                $numnikkey = $cab['f1']['$'] ?? null;
                $numos     = $numosPorNikkey[$numnikkey] ?? null;

                if (!$numos) continue;

                $garantia   = $garantiaPorNunota[$nunota] ?? null;
                $dtGarantia = $garantia ? $this->parseDate($garantia['f1']['$'] ?? null) : null;
                $diasGar    = $garantia ? ((int) ($garantia['f2']['$'] ?? 0)) : null;

                $data[] = [
                    'numos'          => (int) $numos,
                    'nunota'         => (int) $nunota,
                    'codemp_snk'     => isset($cab['f2']['$']) ? (int) $cab['f2']['$'] : null,
                    'codvend_snk'    => isset($cab['f3']['$']) ? (int) $cab['f3']['$'] : null,
                    'dt_garantia'    => $dtGarantia,
                    'dias_garantia'  => $diasGar ?: null,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            $total = count($data);

            foreach (array_chunk($data, 500) as $chunk) {
                Certificado::upsert(
                    $chunk,
                    ['numos'],
                    ['nunota', 'codemp_snk', 'codvend_snk', 'dt_garantia', 'dias_garantia', 'updated_at']
                );
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Sincronizados: {$total} certificados em {$duracao}s.");

            $syncLog->finish($total);
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
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}