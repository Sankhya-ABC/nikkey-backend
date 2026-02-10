<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Services\Sankhya\SankhyaDbExplorerSPService;

class DashboardCommonController extends Controller {
    public function getUltimaVisita(Request $request)
    {
        $idCliente = (int) $request->query('idCliente');

        $sql = "
        SELECT TOP 1
            OSE.NUMOS AS numOS,
            OSE.AD_NUMNIKKEY AS contrato,
            OSE.NUMOSNIKKEY AS idOS,
            OSE.TIPOOS AS tipoOS,
            CONVERT(VARCHAR(10), OSE.HRFIN, 103) AS data,
            CONVERT(VARCHAR(8), OSE.HRFIN, 108) AS hora
        FROM sankhya.AD_VGFOSE OSE
        WHERE OSE.HRFIN IS NOT NULL
        AND OSE.CODPARC = {$idCliente}
        ORDER BY OSE.HRFIN DESC;
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result[0] ?? null);
        return $result;
    }

    public function getDownloadCertificado(Request $request)
    {
        return 'download-certificado';
    }

    public function getFocoPragasEncontradas(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim    = Carbon::parse($request->query('dataFim'))->startOfDay();
        $idCliente  = (int) $request->query('idCliente');
        $rangeType  = $request->query('rangeType', 'day');

        if ($rangeType === 'month') {
            $select = "FORMAT(EVI.DTEV, 'MM/yyyy') AS data";
            $groupBy = "
                FORMAT(EVI.DTEV, 'yyyy-MM'),
                FORMAT(EVI.DTEV, 'MM/yyyy')
            ";
            $orderBy = "FORMAT(EVI.DTEV, 'yyyy-MM')";
        } elseif ($rangeType === 'year') {
            $select = "FORMAT(EVI.DTEV, 'yyyy') AS data";
            $groupBy = "FORMAT(EVI.DTEV, 'yyyy')";
            $orderBy = "FORMAT(EVI.DTEV, 'yyyy')";
        } else {
            $select = "FORMAT(EVI.DTEV, 'dd/MM/yyyy') AS data";
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
            WHERE OSE.CODPARC = {$idCliente}
            AND CAST(EVI.DTEV AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            AND EVI.TIPPRAGA <> 'R'
            GROUP BY {$groupBy}
            ORDER BY {$orderBy}
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getRoedoresCapturados(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim    = Carbon::parse($request->query('dataFim'))->startOfDay();
        $idCliente  = (int) $request->query('idCliente');
        $rangeType  = $request->query('rangeType', 'day');

        if ($rangeType === 'month') {
            $select = "FORMAT(EVI.DTEV, 'MM/yyyy') AS data";
            $groupBy = "
                FORMAT(EVI.DTEV, 'yyyy-MM'),
                FORMAT(EVI.DTEV, 'MM/yyyy')
            ";
            $orderBy = "FORMAT(EVI.DTEV, 'yyyy-MM')";
        } elseif ($rangeType === 'year') {
            $select = "FORMAT(EVI.DTEV, 'yyyy') AS data";
            $groupBy = "FORMAT(EVI.DTEV, 'yyyy')";
            $orderBy = "FORMAT(EVI.DTEV, 'yyyy')";
        } else {
            $select = "FORMAT(EVI.DTEV, 'dd/MM/yyyy') AS data";
            $groupBy = "
                FORMAT(EVI.DTEV, 'yyyy-MM-dd'),
                FORMAT(EVI.DTEV, 'dd/MM/yyyy')
            ";
            $orderBy = "FORMAT(EVI.DTEV, 'yyyy-MM-dd')";
        }

        $sql = "
            SELECT
                {$select},
                SUM(ISNULL(EVI.QTDPRAGA, 0)) AS quantidadeRoedoresMortos
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.AD_VGFOSEEV EVI
                ON EVI.NUMOS = OSE.NUMOS
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


    public function getConsumoProdutos(Request $request)
    {
        return 'consumo-produtos';
    }

    public function getProximasVisitas(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim    = Carbon::parse($request->query('dataFim'))->endOfDay();
        $idCliente  = (int) $request->query('idCliente');

        $sql = "
            SELECT
                OSE.NUMOS AS numOS,
                OSE.AD_NUMNIKKEY AS contrato,
                OSE.NUMOSNIKKEY AS idOS,
                OSE.TIPOOS AS tipoOS,
                OSE.CODTEC AS idTecnico,
                OSE.NOMETEC AS nomeTecnico,
                CONVERT(VARCHAR(10), OSE.DHPREVISTA, 103) AS data,
                CONVERT(VARCHAR(8), OSE.DHPREVISTA, 108) AS hora
            FROM sankhya.AD_VGFOSE OSE
            WHERE OSE.HRFIN IS NULL
            AND OSE.DHPREVISTA IS NOT NULL
            AND OSE.CODPARC = {$idCliente}
            AND CAST(OSE.DHPREVISTA AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            ORDER BY 
                OSE.DHPREVISTA,
                OSE.AD_NUMNIKKEY,
                OSE.NUMOSNIKKEY
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }
}
