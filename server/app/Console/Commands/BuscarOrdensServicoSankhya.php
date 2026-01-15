<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadViewService;
use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\Tecnico;
use Illuminate\Support\Collection;
use GuzzleHttp\Exception\RequestException;

class BuscarOrdensServicoSankhya extends Command
{
    protected $signature = 'sankhya:buscar-os';
    protected $description = 'Busca ordens de serviço no Sankhya e atualiza a base local (full sync).';

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização total de Ordens de Serviço...');
        $inicio = microtime(true);

        try {
            $token = (new AuthSankhya())->login();

            if (!$token) {
                $this->error("❌ Falha ao autenticar no Sankhya.");
                return 1;
            }

            $service = new SankhyaLoadViewService();
            $totalProcessadas = 0;

            foreach ($service->fetchPaginated(
                token: $token,
                viewName: 'AD_VGFOSE',
                fields: [
                    "NUMOS", "CODPARC", "NOMEPARC", "CODTEC", "NOMETEC",
                    "TIPOOS", "STATUSOS", "HRINI", "HRFIN",
                    "ASSCLI", "ASSTEC", "LATITUDE", "LONGITUDE",
                    "AD_NUMNIKKEY", "LATITUDEINI", "LONGITUDEINI",
                    "DHPREVISTA", "DHPREVISTAFIN", "IDCLIENTE", "CODVEI",
                    "CONFAGE", "DURACAO", "SERVICO"
                ],
                criteria: [],
                pageSize: 500
            ) as $page) {

                $records = $page['records'];

                if (empty($records)) {
                    $this->warn("⚠ Página vazia ({$page['startRow']}). Continuando...");
                    continue;
                }

                $ordens = $this->transformarOrdensServico($records);

                if ($ordens->isNotEmpty()) {
                    OrdemServico::upsert(
                        $ordens->toArray(),
                        ['numos'],
                        [
                            'tipoos', 'statusos', 'hrini', 'hrfin', 'asscli', 'asstec',
                            'latitude', 'longitude', 'ad_numnikkey', 'latitudeini',
                            'longitudeini', 'dhprevista', 'dhprevistafin', 'cliente_id',
                            'tecnico_id', 'codvei', 'confage', 'duracao', 'servico',
                            'updated_at'
                        ]
                    );
                }

                $totalProcessadas += $ordens->count();
                $this->info("➡ Página {$page['startRow']}–{$page['endRow']} processada ({$ordens->count()} OSs)");
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("\n🎯 Total sincronizado: {$totalProcessadas} OSs em {$duracao}s.");

            return 0;

        } catch (RequestException $e) {
            $this->error("🌐 Erro HTTP Sankhya: " . $e->getMessage());
        } catch (\Throwable $e) {
            $this->error("❌ Erro inesperado: " . $e->getMessage());
        }

        return 1;
    }

    private function transformarOrdensServico(array $records): Collection
    {
        return collect($records)->map(function ($os) {

            $tecnico = null;
            $codtec = $this->val($os, 'CODTEC');
            if ($codtec) {
                $tecnico = Tecnico::firstOrCreate(
                    ['codtec_snk' => $codtec],
                    ['nome' => $this->val($os, 'NOMETEC')]
                );
            }

            $cliente = null;
            $codparc = $this->val($os, 'CODPARC');
            if ($codparc) {
                $cliente = Cliente::firstOrCreate(
                    ['codparc_snk' => $codparc],
                    ['nome' => $this->val($os, 'NOMEPARC')]
                );
            }

            return [
                'numos'         => (int) $this->val($os, 'NUMOS'),
                'tipoos'        => $this->val($os, 'TIPOOS'),
                'statusos'      => $this->val($os, 'STATUSOS'),
                'hrini'         => $this->parseDate($this->val($os, 'HRINI')),
                'hrfin'         => $this->parseDate($this->val($os, 'HRFIN')),
                'asscli'        => $this->val($os, 'ASSCLI'),
                'asstec'        => $this->val($os, 'ASSTEC'),
                'latitude'      => $this->val($os, 'LATITUDE'),
                'longitude'     => $this->val($os, 'LONGITUDE'),
                'ad_numnikkey'  => $this->val($os, 'AD_NUMNIKKEY'),
                'latitudeini'   => $this->val($os, 'LATITUDEINI'),
                'longitudeini'  => $this->val($os, 'LONGITUDEINI'),
                'dhprevista'    => $this->parseDate($this->val($os, 'DHPREVISTA')),
                'dhprevistafin' => $this->parseDate($this->val($os, 'DHPREVISTAFIN')),
                'cliente_id'    => $cliente?->id,
                'tecnico_id'    => $tecnico?->id,
                'codvei'        => $this->val($os, 'CODVEI'),
                'confage'       => $this->val($os, 'CONFAGE') === 'S',
                'duracao'       => $this->val($os, 'DURACAO'),
                'servico'       => $this->val($os, 'SERVICO'),
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        })->filter(fn($os) => !empty($os['numos']));
    }

    private function val(array $os, string $key): ?string
    {
        return $os[$key]['$'] ?? null;
    }

    private function parseDate(?string $value): ?string
    {
        if (!$value) return null;
        $formats = ['d/m/Y H:i:s', 'Y-m-d H:i:s'];

        foreach ($formats as $fmt) {
            $date = \DateTime::createFromFormat($fmt, $value);
            if ($date) return $date->format('Y-m-d H:i:s');
        }

        return null;
    }
}
