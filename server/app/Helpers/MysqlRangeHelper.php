<?php

namespace App\Helpers;

class MysqlRangeHelper
{
    public static function resolve(string $campo, string $rangeType): array
    {
        return match ($rangeType) {
            'month' => [
                'select'  => "DATE_FORMAT({$campo}, '%m/%Y') AS data",
                'groupBy' => "DATE_FORMAT({$campo}, '%Y-%m'), DATE_FORMAT({$campo}, '%m/%Y')",
                'orderBy' => "DATE_FORMAT({$campo}, '%Y-%m')",
            ],
            'year' => [
                'select'  => "DATE_FORMAT({$campo}, '%Y') AS data",
                'groupBy' => "DATE_FORMAT({$campo}, '%Y')",
                'orderBy' => "DATE_FORMAT({$campo}, '%Y')",
            ],
            default => [
                'select'  => "DATE_FORMAT({$campo}, '%d/%m/%Y') AS data",
                'groupBy' => "DATE_FORMAT({$campo}, '%Y-%m-%d'), DATE_FORMAT({$campo}, '%d/%m/%Y')",
                'orderBy' => "DATE_FORMAT({$campo}, '%Y-%m-%d')",
            ],
        };
    }
}
