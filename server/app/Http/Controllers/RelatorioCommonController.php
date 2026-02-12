<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Services\Sankhya\SankhyaDbExplorerSPService;

class RelatorioCommonController extends Controller {
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
                    WHEN PRA_EVI.GRU_PRAGAS <> 1
                    THEN ISNULL(EVI.QTDPRAGA,0)
                    ELSE 0
                END) AS quantidadePragasEncontradas,

                SUM(CASE
                    WHEN PRA_PUR.GRU_PRAGAS <> 1
                    THEN ISNULL(PUR.QTDNEG,0)
                    ELSE 0
                END) AS quantidadeInseticida

            FROM sankhya.AD_VGFOSE OSE
            LEFT JOIN sankhya.AD_VGFOSEEV EVI
                ON EVI.NUMOS = OSE.NUMOS
            LEFT JOIN sankhya.AD_TABPRAGAS PRA_EVI
                ON PRA_EVI.CODPRAGA = EVI.CODPRAGA
            LEFT JOIN sankhya.AD_VGFOSESERPRGPRDUTIL PUR
                ON PUR.NUMOS = OSE.NUMOS
            LEFT JOIN sankhya.AD_TABPRAGAS PRA_PUR
                ON PRA_PUR.CODPRAGA = PUR.PRAGA

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
        $dataInicio   = Carbon::parse($request->query('dataInicio'))->toDateString();
        $dataFim      = Carbon::parse($request->query('dataFim'))->toDateString();
        $rangeType    = $request->query('rangeType', 'day');
        $idCliente    = (int) $request->query('idCliente');
        $idGrupoPraga = (int) $request->query('idGrupoPraga');

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
                SUM(ISNULL(EVI.QTDPRAGA,0)) AS quantidade
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.AD_VGFOSEEV EVI
                ON EVI.NUMOS = OSE.NUMOS
            LEFT JOIN sankhya.AD_TABPRAGAS PRA
                ON PRA.CODPRAGA = EVI.CODPRAGA
            WHERE OSE.HRFIN IS NOT NULL
            AND OSE.CODPARC = {$idCliente}
            AND PRA.GRU_PRAGAS = {$idGrupoPraga}
            AND CAST(OSE.HRFIN AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            GROUP BY {$groupBy}
            ORDER BY {$orderBy}
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
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
                    WHEN PRA_EVI.GRU_PRAGAS = 1 AND EVI.INDIVIDUO = 'M'
                    THEN ISNULL(EVI.QTDPRAGA,0)
                    ELSE 0
                END) AS quantidadeRoedoresMortos,

                SUM(CASE
                    WHEN PRA_PUR.GRU_PRAGAS = 1
                    THEN ISNULL(PUR.QTDNEG,0)
                    ELSE 0
                END) AS quantidadeRodenticida

            FROM sankhya.AD_VGFOSE OSE
            LEFT JOIN sankhya.AD_VGFOSEEV EVI
                ON EVI.NUMOS = OSE.NUMOS
            LEFT JOIN sankhya.AD_TABPRAGAS PRA_EVI
                ON PRA_EVI.CODPRAGA = EVI.CODPRAGA
            LEFT JOIN sankhya.AD_VGFOSESERPRGPRDUTIL PUR
                ON PUR.NUMOS = OSE.NUMOS
            LEFT JOIN sankhya.AD_TABPRAGAS PRA_PUR
                ON PRA_PUR.CODPRAGA = PUR.PRAGA

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

    public function getNaoConformidades(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->toDateString();
        $dataFim    = Carbon::parse($request->query('dataFim'))->toDateString();
        $idCliente  = (int) $request->query('idCliente');

        $sql = "
            SELECT
                FORMAT(OSE.HRFIN, 'dd/MM/yyyy') AS data,
                ONC.NUMOS       AS numOs,
                ONC.SETOR       AS areaLocal,
                TNC.DESCRICAO   AS naoConformidade,
                ONC.TIPORES     AS acaoSugerida
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.AD_VGFOSENC ONC
                ON ONC.NUMOS = OSE.NUMOS
            LEFT JOIN sankhya.AD_TIPNAOCONFORM TNC
                ON TNC.ID = ONC.TIPONC
            WHERE OSE.HRFIN IS NOT NULL
            AND OSE.CODPARC = {$idCliente}
            AND CAST(OSE.HRFIN AS DATE) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            ORDER BY OSE.HRFIN
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    // combobox para getArmadilhasLuminosas
    public function getGrupoPragas()
    {
        $sql = "
            SELECT
                PRA.GRU_PRAGAS AS id,
                OPC.OPCAO      AS nome
            FROM sankhya.AD_TABPRAGAS PRA
            INNER JOIN sankhya.TDDOPC OPC
                ON OPC.VALOR = PRA.GRU_PRAGAS
            INNER JOIN sankhya.TDDCAM CAM
                ON CAM.NUCAMPO = OPC.NUCAMPO
            AND CAM.NOMETAB = 'AD_TABPRAGAS'
            AND CAM.NOMECAMPO = 'GRU_PRAGAS'
            GROUP BY
                PRA.GRU_PRAGAS,
                OPC.OPCAO
            ORDER BY OPC.OPCAO
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }

    public function getPragasPorGrupo(Request $request)
    {
        $idGrupoPraga = (int) $request->query('idGrupoPraga');

        $sql = "
            SELECT
                PRA.CODPRAGA   AS id,
                PRA.NOME_PRAGA AS nome
            FROM sankhya.AD_TABPRAGAS PRA
            WHERE PRA.GRU_PRAGAS = {$idGrupoPraga}
            ORDER BY PRA.NOME_PRAGA
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }
}
