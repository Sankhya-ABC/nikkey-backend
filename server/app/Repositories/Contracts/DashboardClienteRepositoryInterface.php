<?php

namespace App\Repositories\Contracts;

interface DashboardClienteRepositoryInterface
{
    public function getCompleto(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;
}
