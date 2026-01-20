<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Services\Sankhya\SankhyaDbExplorerSPService;

class VisitaController extends Controller {
    public function getVisitas(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay();
        $dataFim = Carbon::parse($request->query('dataFim'))->startOfDay();

        $sql = "
            SELECT 
                OSE.NUMOSNIKKEY AS idVisita,
                OSE.CODPARC AS idEmpresa,
                PAR.RAZAOSOCIAL AS nomeEmpresa,
                OSE.CODTEC AS idTecnico,
                OSE.NOMETEC AS nomeTecnico,    
                CONVERT(VARCHAR(10), ISNULL(OSE.HRFIN,OSE.DHPREVISTA), 103) AS data,
                CONVERT(VARCHAR(8), ISNULL(OSE.HRINI,OSE.DHPREVISTA), 108) AS horaInicio,
                CONVERT(VARCHAR(8), ISNULL(OSE.HRFIN,OSE.DHPREVISTAFIN), 108) AS horaFim
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.TGFPAR PAR
                ON PAR.CODPARC = OSE.CODPARC
            WHERE CAST(OSE.DHPREVISTA AS date) BETWEEN '{$dataInicio}' AND '{$dataFim}'
            AND CAST(OSE.DHPREVISTA AS date)  IS NOT NULL
            ORDER BY 
                CONVERT(VARCHAR(10), ISNULL(OSE.HRFIN,OSE.DHPREVISTA), 103),
                CONVERT(VARCHAR(8), ISNULL(OSE.HRINI,OSE.DHPREVISTA), 108),
                OSE.NUMOS
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        $result = array_map(function ($item) {
            $item['horaInicio'] = Carbon::parse($item['horaInicio'])->format('H:i');
            $item['horaFim'] = Carbon::parse($item['horaFim'])->format('H:i');
            $item['data'] = str_replace('\/', '/', $item['data']);
            return $item;
        }, $result);

        return response()->json($result);
    }
}
