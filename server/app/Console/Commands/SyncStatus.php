<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncStatus extends Command
{
    protected $signature   = 'sync:status';
    protected $description = 'Exibe o status da ultima execucao de cada entidade sincronizada.';

    public function handle(): int
    {
        $rows = DB::select("
            SELECT
                sl.entidade,
                sl.status,
                sl.started_at,
                sl.finished_at,
                sl.total_registros,
                sl.erro
            FROM sync_logs sl
            INNER JOIN (
                SELECT entidade, MAX(id) AS max_id
                FROM sync_logs
                GROUP BY entidade
            ) last ON last.entidade = sl.entidade AND last.max_id = sl.id
            ORDER BY sl.entidade
        ");

        if (empty($rows)) {
            $this->warn('Nenhum sync registrado ainda.');
            return 0;
        }

        $this->table(
            ['Entidade', 'Status', 'Ultimo sync', 'Duracao', 'Registros', 'Erro'],
            collect($rows)->map(fn($r) => [
                $r->entidade,
                match ($r->status) {
                    'success' => 'success',
                    'error'   => 'error',
                    'running' => 'running',
                    default   => $r->status,
                },
                $r->started_at ?? '-',
                ($r->started_at && $r->finished_at)
                    ? Carbon::parse($r->started_at)->diffInSeconds(Carbon::parse($r->finished_at)) . 's'
                    : '-',
                $r->total_registros,
                $r->erro ? substr($r->erro, 0, 60) . '...' : '-',
            ])
        );

        return 0;
    }
}