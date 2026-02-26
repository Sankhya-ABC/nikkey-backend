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
            if (!empty($item['data'])) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $item['data'])) {
                } else {
                    try {
                        $carbonDate = Carbon::createFromFormat('d/m/Y', $item['data']);
                        $item['data'] = $carbonDate->format('Y-m-d');
                    } catch (\Exception $e) {
                        $carbonDate = Carbon::parse($item['data']);
                        $item['data'] = $carbonDate->format('Y-m-d');
                    }
                }
            }
            
            if (!empty($item['horaInicio'])) {
                $item['horaInicio'] = Carbon::parse($item['horaInicio'])->format('H:i');
            }
            
            if (!empty($item['horaFim'])) {
                $item['horaFim'] = Carbon::parse($item['horaFim'])->format('H:i');
            }
            
            return $item;
        }, $result);

        return response()->json($result);
    }

    public function getCronogramaVisita(Request $request)
    {
        $nomeTecnico = $request->query('nomeTecnico');

        $sql = "
            SELECT
                OSE.CODTEC AS idTecnico,
                OSE.NOMETEC AS nomeTecnico,
                CONVERT(VARCHAR(10), OSE.DHPREVISTA, 103) AS data,
                CONVERT(VARCHAR(8), OSE.DHPREVISTA, 108) AS hora,
                USU.AD_TELEFONE AS telefone,
                OSE.STATUSOS AS status
            FROM sankhya.AD_VGFOSE OSE
            INNER JOIN sankhya.TSIUSU USU
                ON USU.CODUSU = OSE.CODTEC
            WHERE CAST(OSE.DHPREVISTA AS DATE) >= CAST(GETDATE() AS DATE)
        ";

        if (!empty($nomeTecnico)) {
            $sql .= " AND UPPER(OSE.NOMETEC) LIKE UPPER('%{$nomeTecnico}%')";
        }

        $sql .= "
            ORDER BY 
                CONVERT(VARCHAR(10), OSE.DHPREVISTA, 103),
                CONVERT(VARCHAR(8), OSE.DHPREVISTA, 108)
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        return response()->json($result);
    }
}
