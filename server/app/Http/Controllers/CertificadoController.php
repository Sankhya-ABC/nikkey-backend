<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Services\Sankhya\SankhyaDbExplorerSPService;

class DashboardAdminController extends Controller {
    public function getCertificados(Request $request)
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
}
