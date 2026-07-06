<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitaController extends Controller
{
    public function getVisitas(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->startOfDay()->toDateString();
        $dataFim    = Carbon::parse($request->query('dataFim'))->startOfDay()->toDateString();

        $rows = DB::select("
            SELECT
                os.numos                                                   AS idVisita,
                c.codparc_snk                                              AS idEmpresa,
                COALESCE(c.razao_social, c.nome_fantasia)                  AS nomeEmpresa,
                t.codtec_snk                                               AS idTecnico,
                t.nome                                                     AS nomeTecnico,
                DATE(COALESCE(os.hrfin, os.dhprevista))                    AS data,
                TIME(COALESCE(os.hrini, os.dhprevista))                    AS horaInicio,
                TIME(COALESCE(os.hrfin, os.dhprevistafin))                 AS horaFim
            FROM ordens_servico os
            LEFT JOIN clientes c ON c.id = os.cliente_id
            LEFT JOIN tecnicos t ON t.id = os.tecnico_id
            WHERE DATE(os.dhprevista) BETWEEN ? AND ?
              AND os.dhprevista IS NOT NULL
            ORDER BY data, horaInicio, os.numos
        ", [$dataInicio, $dataFim]);

        $result = array_map(function ($item) {
            $data = $item->data ?? null;
            if ($data && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                try {
                    $data = Carbon::createFromFormat('d/m/Y', $data)->format('Y-m-d');
                } catch (\Exception $e) {
                    $data = Carbon::parse($data)->format('Y-m-d');
                }
            }

            $horaInicio = $item->horaInicio ?? null;
            $horaFim    = $item->horaFim    ?? null;

            return [
                'idVisita'    => $item->idVisita,
                'idEmpresa'   => $item->idEmpresa,
                'nomeEmpresa' => $item->nomeEmpresa,
                'idTecnico'   => $item->idTecnico,
                'nomeTecnico' => $item->nomeTecnico,
                'data'        => $data,
                'horaInicio'  => $horaInicio ? Carbon::parse($horaInicio)->format('H:i') : null,
                'horaFim'     => $horaFim    ? Carbon::parse($horaFim)->format('H:i')    : null,
            ];
        }, $rows);

        return response()->json($result);
    }

    public function getCronogramaVisita(Request $request)
    {
        $nomeTecnico = $request->query('nomeTecnico');

        $params = [];
        $where  = "WHERE DATE(os.dhprevista) >= CURDATE()";

        if (!empty($nomeTecnico)) {
            $where   .= " AND UPPER(t.nome) LIKE UPPER(?)";
            $params[] = "%{$nomeTecnico}%";
        }

        $rows = DB::select("
            SELECT
                t.codtec_snk   AS idTecnico,
                t.nome         AS nomeTecnico,
                DATE(os.dhprevista) AS data,
                TIME(os.dhprevista) AS hora,
                t.telefone     AS telefone,
                os.statusos    AS status
            FROM ordens_servico os
            LEFT JOIN tecnicos t ON t.id = os.tecnico_id
            {$where}
            ORDER BY DATE(os.dhprevista), TIME(os.dhprevista)
        ", $params);

        return response()->json(array_map(fn($r) => (array) $r, $rows));
    }
}