<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Tecnico;
use App\Services\SyncLogService;

class SankhyaSyncTecnicos extends Command
{
    protected $signature   = 'sankhya:sync-tecnicos';
    protected $description = 'Enriquece tecnicos com telefone, CREA, CRBIO e assinatura vindos do Sankhya.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('tecnicos'))->start();
        try {
            $this->info('Iniciando sync de tecnicos...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token:      $token,
                rootEntity: 'Usuario',
                fields:     [
                    '' => ['CODUSU', 'AD_TELEFONE', 'AD_ASSINATURA', 'CODVEND'],
                    'Vendedor' => ['AD_REGICREA', 'AD_CRBIO'],
                ]
            );

            $atualizados = 0;

            foreach ($records as $row) {
                $codusu = $row['f0']['$'] ?? null;
                if (!$codusu) continue;

                $tecnico = Tecnico::where('codtec_snk', $codusu)->first();
                if (!$tecnico) continue;

                $tecnico->update([
                    'telefone'    => $row['f1']['$'] ?? null,
                    'assinatura'  => $this->decodeImage($row['f2']['$'] ?? null),
                    'codvend_snk' => isset($row['f3']['$']) ? (int) $row['f3']['$'] : null,
                    'crea'        => $row['f4']['$'] ?? null,
                    'crbio'       => $row['f5']['$'] ?? null,
                ]);

                $atualizados++;
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Atualizados: {$atualizados} tecnicos em {$duracao}s.");

            $syncLog->finish($atualizados);
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }

    private function decodeImage(?string $value): ?string
    {
        if (!$value) return null;
        if (str_starts_with($value, '[B@')) return null;
        $decoded = base64_decode($value, true);
        return $decoded !== false ? $value : null;
    }
}