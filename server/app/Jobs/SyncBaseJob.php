<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;

abstract class SyncBaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $pageSize;
    protected int $startOffset;
    protected string $rootEntity;
    protected array $fields;
    protected array $criteria = [];

    public function __construct(int $pageSize = 200, int $startOffset = 0)
    {
        $this->pageSize = $pageSize;
        $this->startOffset = $startOffset;
    }

    public function handle()
    {
        $this->beforeRun();

        $token = (new AuthSankhya())->login();
        if (!$token) {
            Log::error("Falha ao autenticar no Sankhya para " . static::class);
            return;
        }

        $service = new SankhyaLoadRecordsService();

        foreach ($service->fetchPaginatedFrom(
            $token,
            $this->rootEntity,
            $this->fields,
            $this->criteria,
            $this->pageSize,
            $this->startOffset
        ) as $page) {
            $mapped = [];
            foreach ($page['records'] as $record) {
                $r = $this->mapRecord($record);
                if ($r) $mapped[] = $r;
            }

            if (!empty($mapped)) {
                $this->persistChunk($mapped);
            }
        }

        $this->afterRun();
    }

    abstract protected function mapRecord(array $rec): ?array;
    abstract protected function persistChunk(array $mappedChunk): void;

    protected function beforeRun(): void {}
    protected function afterRun(): void {}
}
