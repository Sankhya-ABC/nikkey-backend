<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Services\Sankhya\SankhyaDbExplorerSPService;

class DashboardController extends Controller {
    public function getBasicData(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim = Carbon::parse($request->query('dataFim'))->startOfDay();

        $sql = "
            SELECT 
                COUNT(1) AS qtdOrdemServico,
                COUNT(DISTINCT(OSE.CODPARC)) AS qtdCliente,
                COUNT(DISTINCT(OSE.CODTEC)) AS qtdTecnico   
            FROM sankhya.AD_VGFOSE OSE
            WHERE cast(OSE.DHPREVISTA as date) BETWEEN '{$dataInicio}' AND '{$dataFim}'
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result[0]);
    }

    public function getOrdensServico(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim = Carbon::parse($request->query('dataFim'))->startOfDay();

        $sql = "
            SELECT 
                cast(OSE.DHPREVISTA as date) as data,
                COUNT(1) AS qtdOrdemServico
            FROM sankhya.AD_VGFOSE OSE
            WHERE cast(OSE.DHPREVISTA as date) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            AND cast(OSE.DHPREVISTA as date)  IS NOT NULL
            GROUP BY CAST(OSE.DHPREVISTA as date)
            ORDER BY CAST(OSE.DHPREVISTA as date)
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getAtendimentosTecnico(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim = Carbon::parse($request->query('dataFim'))->startOfDay();

        $sql = "
        SELECT 
            OSE.CODTEC AS idTecnico,
            OSE.NOMETEC AS nomeTecnico,
            COUNT(1) AS qtdOrdemServico

        FROM sankhya.AD_VGFOSE OSE
        WHERE cast(OSE.DHPREVISTA as date) BETWEEN '{$dataInicio}' AND '{$dataFim}'
        AND cast(OSE.DHPREVISTA as date)  IS NOT NULL
        GROUP BY
            OSE.CODTEC,
            OSE.NOMETEC
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getConsumoProdutos(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim = Carbon::parse($request->query('dataFim'))->startOfDay();

        $sql = "
        SELECT  PRD.CODPROD AS codProduto,
                PRO.DESCRPROD AS descProduto,
                SUM(ISNULL(PRD.QTDNEG,0)) AS qnt
        FROM sankhya.AD_VGFOSESERPRGPRD PRD
        INNER JOIN sankhya.TGFPRO PRO
            ON PRO.CODPROD = PRD.CODPROD
        WHERE PRD.NUMOS IN (
                            SELECT OSE.NUMOS 
                            FROM sankhya.AD_VGFOSE OSE 
                            WHERE cast(OSE.DHPREVISTA   as date) BETWEEN '{$dataInicio}' AND '{$dataFim}'
                            AND cast(OSE.DHPREVISTA  as date)  IS NOT NULL
                            )
        GROUP BY 
            PRD.CODPROD,
            PRO.DESCRPROD
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getProximasVisitas(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim = Carbon::parse($request->query('dataFim'))->startOfDay();

        $sql = "
        SELECT 
            OSE.NUMOS       AS numOS,
            OSE.DHPREVISTA  AS data,
            OSE.DHPREVISTA  AS horaInicio,
            OSE.DHPREVISTAFIN  AS horaFim,
            OSE.NOMEPARC    AS nomeCliente
        FROM sankhya.AD_VGFOSE OSE
        INNER JOIN sankhya.TGFPAR PAR
            ON PAR.CODPARC = OSE.CODPARC
        LEFT JOIN sankhya.TGFTPP TPP
            ON TPP.CODTIPPARC = PAR.CODTIPPARC
        WHERE cast(OSE.DHPREVISTA as date) BETWEEN '{$dataInicio}' AND '{$dataFim}'
        AND cast(OSE.DHPREVISTA as date) IS NOT NULL
        AND cast(OSE.DHPREVISTA as date) >= CAST(GETDATE() AS DATE)
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }
}
