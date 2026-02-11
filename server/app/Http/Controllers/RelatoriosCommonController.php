<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Services\Sankhya\SankhyaDbExplorerSPService;

class RelatoriosCommonController extends Controller {
    public function getConsumoProdutos(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->toDateString();
        $dataFim    = Carbon::parse($request->query('dataFim'))->toDateString();
        $rangeType  = $request->query('rangeType', 'day');
        $idCliente  = (int) $request->query('idCliente');

        if ($rangeType === 'month') {
            $select = "FORMAT(OSE.HRFIN, 'MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM'),
                FORMAT(OSE.HRFIN, 'MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM')";
        } elseif ($rangeType === 'year') {
            $select = "FORMAT(OSE.HRFIN, 'yyyy') AS data";
            $groupBy = "FORMAT(OSE.HRFIN, 'yyyy')";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy')";
        } else {
            $select = "FORMAT(OSE.HRFIN, 'dd/MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM-dd'),
                FORMAT(OSE.HRFIN, 'dd/MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM-dd')";
        }

        $sql = "
            SELECT
                {$select},

                SUM(CASE 
                    WHEN PRA.GRU_PRAGAS <> 1 AND PRO.CODVOL IN ('LT','ML')
                    THEN ISNULL(PUR.QTDNEG,0)
                    ELSE 0
                END) AS quantidadeInseticidaLiquido,

                SUM(CASE 
                    WHEN PRA.GRU_PRAGAS <> 1 AND PRO.CODVOL NOT IN ('LT','ML')
                    THEN ISNULL(PUR.QTDNEG,0)
                    ELSE 0
                END) AS quantidadeInseticidaSolido,

                SUM(CASE 
                    WHEN PRA.GRU_PRAGAS = 1
                    THEN ISNULL(PUR.QTDNEG,0)
                    ELSE 0
                END) AS quantidadeRodenticida

            FROM sankhya.AD_VGFOSE OSE
            LEFT JOIN sankhya.AD_VGFOSESERPRGPRDUTIL PUR
                ON PUR.NUMOS = OSE.NUMOS
            LEFT JOIN sankhya.AD_TABPRAGAS PRA
                ON PRA.CODPRAGA = PUR.PRAGA
            LEFT JOIN sankhya.TGFPRO PRO
                ON PRO.CODPROD = PUR.CODPROD

            WHERE OSE.HRFIN IS NOT NULL
            AND OSE.CODPARC = {$idCliente}
            AND CAST(OSE.HRFIN AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'

            GROUP BY {$groupBy}
            ORDER BY {$orderBy}
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getConsumoInsumos(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->toDateString();
        $dataFim    = Carbon::parse($request->query('dataFim'))->toDateString();
        $rangeType  = $request->query('rangeType', 'day');
        $idCliente  = (int) $request->query('idCliente');

        if ($rangeType === 'month') {
            $select = "FORMAT(OSE.HRFIN, 'MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM'),
                FORMAT(OSE.HRFIN, 'MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM')";
        } elseif ($rangeType === 'year') {
            $select = "FORMAT(OSE.HRFIN, 'yyyy') AS data";
            $groupBy = "FORMAT(OSE.HRFIN, 'yyyy')";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy')";
        } else {
            $select = "FORMAT(OSE.HRFIN, 'dd/MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM-dd'),
                FORMAT(OSE.HRFIN, 'dd/MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM-dd')";
        }

        $sql = "
            SELECT
                {$select},
                SUM(ISNULL(PRU.QTDNEG, 0)) AS quantidade
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.AD_VGFOSESERPRGPRDUTIL PRU
                ON PRU.NUMOS = OSE.NUMOS
            WHERE OSE.HRFIN IS NOT NULL
            AND OSE.CODPARC = {$idCliente}
            AND CAST(OSE.HRFIN AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            GROUP BY {$groupBy}
            ORDER BY {$orderBy}
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getFocoPragasEncontradas(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->toDateString();
        $dataFim    = Carbon::parse($request->query('dataFim'))->toDateString();
        $rangeType  = $request->query('rangeType', 'day');
        $idCliente  = (int) $request->query('idCliente');

        if ($rangeType === 'month') {
            $select = "FORMAT(OSE.HRFIN, 'MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM'),
                FORMAT(OSE.HRFIN, 'MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM')";
        } elseif ($rangeType === 'year') {
            $select = "FORMAT(OSE.HRFIN, 'yyyy') AS data";
            $groupBy = "FORMAT(OSE.HRFIN, 'yyyy')";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy')";
        } else {
            $select = "FORMAT(OSE.HRFIN, 'dd/MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM-dd'),
                FORMAT(OSE.HRFIN, 'dd/MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM-dd')";
        }

        $sql = "
            SELECT
                {$select},
                SUM(ISNULL(EVI.QTDPRAGA, 0)) AS quantidade
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.AD_VGFOSEEV EVI
                ON EVI.NUMOS = OSE.NUMOS
            WHERE OSE.HRFIN IS NOT NULL
            AND OSE.CODPARC = {$idCliente}
            AND CAST(OSE.HRFIN AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            GROUP BY {$groupBy}
            ORDER BY {$orderBy}
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getInseticidasXPragas(Request $request)
    {
        return 'getInseticidasXPragas';
    }

    public function getArmadilhasFeromonio(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->toDateString();
        $dataFim    = Carbon::parse($request->query('dataFim'))->toDateString();
        $rangeType  = $request->query('rangeType', 'day');
        $idCliente  = (int) $request->query('idCliente');

        if ($rangeType === 'month') {
            $select = "FORMAT(OSE.HRFIN, 'MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM'),
                FORMAT(OSE.HRFIN, 'MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM')";
        } elseif ($rangeType === 'year') {
            $select = "FORMAT(OSE.HRFIN, 'yyyy') AS data";
            $groupBy = "FORMAT(OSE.HRFIN, 'yyyy')";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy')";
        } else {
            $select = "FORMAT(OSE.HRFIN, 'dd/MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM-dd'),
                FORMAT(OSE.HRFIN, 'dd/MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM-dd')";
        }

        $sql = "
            SELECT
                {$select},

                SUM(CASE 
                    WHEN PRU.CODPROD = 502
                    THEN ISNULL(PRU.QTDNEG, 0)
                    ELSE 0
                END) AS quantidadeGachon,

                SUM(CASE 
                    WHEN PRU.CODPROD = 503
                    THEN ISNULL(PRU.QTDNEG, 0)
                    ELSE 0
                END) AS quantidadeBioSerrico

            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.AD_VGFOSESERPRGPRDUTIL PRU
                ON PRU.NUMOS = OSE.NUMOS

            WHERE OSE.HRFIN IS NOT NULL
            AND OSE.CODPARC = {$idCliente}
            AND CAST(OSE.HRFIN AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            AND PRU.CODPROD IN (502, 503)

            GROUP BY {$groupBy}
            ORDER BY {$orderBy}
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getArmadilhasLuminosas(Request $request)
    {
        return 'getArmadilhasLuminosas';
    }

    public function getRoedoresMortos(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim    = Carbon::parse($request->query('dataFim'))->startOfDay();
        $rangeType  = $request->query('rangeType', 'day');
        $idCliente  = (int) $request->query('idCliente');

        if ($rangeType === 'month') {
            $select = "
                FORMAT(EVI.DTEV, 'MM/yyyy') AS data
            ";
            $groupBy = "
                FORMAT(EVI.DTEV, 'yyyy-MM'),
                FORMAT(EVI.DTEV, 'MM/yyyy')
            ";
            $orderBy = "FORMAT(EVI.DTEV, 'yyyy-MM')";
        } elseif ($rangeType === 'year') {
            $select = "
                FORMAT(EVI.DTEV, 'yyyy') AS data
            ";
            $groupBy = "FORMAT(EVI.DTEV, 'yyyy')";
            $orderBy = "FORMAT(EVI.DTEV, 'yyyy')";
        } else {
            $select = "
                FORMAT(EVI.DTEV, 'dd/MM/yyyy') AS data
            ";
            $groupBy = "
                FORMAT(EVI.DTEV, 'yyyy-MM-dd'),
                FORMAT(EVI.DTEV, 'dd/MM/yyyy')
            ";
            $orderBy = "FORMAT(EVI.DTEV, 'yyyy-MM-dd')";
        }

        $sql = "
            SELECT
                {$select},
                SUM(ISNULL(EVI.QTDPRAGA, 0)) AS quantidade
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.AD_VGFOSEEV EVI
                ON EVI.NUMOS = OSE.NUMOS
                AND EVI.INDIVIDUO = 'M'
            WHERE OSE.CODPARC = {$idCliente}
            AND CAST(EVI.DTEV AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            AND EVI.TIPPRAGA = 'R'
            GROUP BY {$groupBy}
            ORDER BY {$orderBy}
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getPlacaColaArmadilhaMecanica(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->toDateString();
        $dataFim    = Carbon::parse($request->query('dataFim'))->toDateString();
        $rangeType  = $request->query('rangeType', 'day');
        $idCliente  = (int) $request->query('idCliente');

        if ($rangeType === 'month') {
            $select = "FORMAT(OSE.HRFIN, 'MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM'),
                FORMAT(OSE.HRFIN, 'MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM')";
        } elseif ($rangeType === 'year') {
            $select = "FORMAT(OSE.HRFIN, 'yyyy') AS data";
            $groupBy = "FORMAT(OSE.HRFIN, 'yyyy')";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy')";
        } else {
            $select = "FORMAT(OSE.HRFIN, 'dd/MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM-dd'),
                FORMAT(OSE.HRFIN, 'dd/MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM-dd')";
        }

        $sql = "
            SELECT
                {$select},
                COUNT(1) AS quantidade
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.AD_VGFOSESERPRGPTMON PTO
                ON PTO.NUMOS = OSE.NUMOS
            WHERE OSE.HRFIN IS NOT NULL
            AND OSE.CODPARC = {$idCliente}
            AND CAST(OSE.HRFIN AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            AND PTO.TPMONIT IN (5, 8)
            GROUP BY {$groupBy}
            ORDER BY {$orderBy}
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getIscasRoidas(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->toDateString();
        $dataFim    = Carbon::parse($request->query('dataFim'))->toDateString();
        $rangeType  = $request->query('rangeType', 'day');
        $idCliente  = (int) $request->query('idCliente');

        if ($rangeType === 'month') {
            $select = "FORMAT(OSE.HRFIN, 'MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM'),
                FORMAT(OSE.HRFIN, 'MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM')";
        } elseif ($rangeType === 'year') {
            $select = "FORMAT(OSE.HRFIN, 'yyyy') AS data";
            $groupBy = "FORMAT(OSE.HRFIN, 'yyyy')";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy')";
        } else {
            $select = "FORMAT(OSE.HRFIN, 'dd/MM/yyyy') AS data";
            $groupBy = "
                FORMAT(OSE.HRFIN, 'yyyy-MM-dd'),
                FORMAT(OSE.HRFIN, 'dd/MM/yyyy')
            ";
            $orderBy = "FORMAT(OSE.HRFIN, 'yyyy-MM-dd')";
        }

        $sql = "
            SELECT
                {$select},
                COUNT(DISTINCT MON.IDEQUP) AS quantidade
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.AD_VGFOSESERPRGPTMON MON
                ON MON.NUMOS = OSE.NUMOS
            WHERE (MON.CONSUMOMETADE IS NOT NULL
                OR MON.CONSUMO IS NOT NULL)
            AND OSE.HRFIN IS NOT NULL
            AND OSE.CODPARC = {$idCliente}
            AND CAST(OSE.HRFIN AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            GROUP BY {$groupBy}
            ORDER BY {$orderBy}
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getRodenticidasXRoedores(Request $request)
    {
        return 'getRodenticidasXRoedores';
    }

    public function getNaoConformidades(Request $request)
    {
        return 'getNaoConformidades';
    }
}
