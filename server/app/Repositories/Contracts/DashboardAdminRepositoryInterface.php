<?php

namespace App\Repositories\Contracts;

interface DashboardAdminRepositoryInterface
{
    public function getCompleto(string $dataInicio, string $dataFim, string $rangeType): array;
}
