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

        return response()->json($result[0]);
        return $result;
    }

    public function getDownloadCertificado(Request $request)
    {
        return 'download-certificado';
    }

    public function getFocoPragasEncontradas(Request $request)
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
                CONVERT(VARCHAR(10), EVI.DTEV, 103) AS data,
                EVI.CODPRAGA AS idPraga,
                PRA.NOME_PRAGA AS nomePraga,
                ISNULL(EVI.QTDPRAGA, 0) AS quantidade
            FROM sankhya.AD_VGFOSE OSE 
            INNER JOIN sankhya.AD_VGFOSEEV EVI
                ON EVI.NUMOS = OSE.NUMOS
            LEFT JOIN sankhya.AD_TABPRAGAS PRA
                ON PRA.CODPRAGA = EVI.CODPRAGA
            WHERE OSE.CODPARC = {$idCliente}
            AND CAST(EVI.DTEV AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            AND EVI.TIPPRAGA <> 'R'
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getRoedoresCapturados(Request $request)
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
                CONVERT(VARCHAR(10), EVI.DTEV, 103) AS data,
                EVI.CODPRAGA AS idPraga,
                PRA.NOME_PRAGA AS nomePraga,
                ISNULL(EVI.QTDPRAGA, 0) AS quantidade
            FROM sankhya.AD_VGFOSE OSE 
            INNER JOIN sankhya.AD_VGFOSEEV EVI
                ON EVI.NUMOS = OSE.NUMOS
            LEFT JOIN sankhya.AD_TABPRAGAS PRA
                ON PRA.CODPRAGA = EVI.CODPRAGA
            WHERE OSE.CODPARC = {$idCliente}
            AND CAST(EVI.DTEV AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            AND EVI.TIPPRAGA = 'R'
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
