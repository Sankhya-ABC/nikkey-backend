<?php

namespace App\Services;

use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;

class SyncLogService
{
    private SyncLog $log;

    public function __construct(private string $entidade) {}

    public function start(): static
    {
        $id = DB::table('sync_logs')->insertGetId([
            'entidade'   => $this->entidade,
            'status'     => 'running',
            'started_at' => DB::raw('NOW()'),
        ]);

        $this->log = SyncLog::find($id);

        return $this;
    }

    public function finish(int $total): void
    {
        DB::table('sync_logs')->where('id', $this->log->id)->update([
            'status'          => 'success',
            'finished_at'     => DB::raw('NOW()'),
            'total_registros' => $total,
        ]);
    }

    public function fail(\Throwable $e): void
    {
        DB::table('sync_logs')->where('id', $this->log->id)->update([
            'status'      => 'error',
            'finished_at' => DB::raw('NOW()'),
            'erro'        => substr($e->getMessage(), 0, 2000),
        ]);
    }
}
