<?php

namespace App\Repositories\Local;

use App\Helpers\MysqlRangeHelper;
use App\Repositories\Contracts\DashboardClienteRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DashboardClienteLocalRepository implements DashboardClienteRepositoryInterface
{
    public function getCompleto(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $ultimaVisita = DB::selectOne("
            SELECT
                os.numos          AS numOS,
                os.ad_numnikkey   AS contrato,
                os.numos          AS idOS,
                os.tipoos         AS tipoOS,
                DATE_FORMAT(os.hrfin, '%d/%m/%Y') AS data,
                DATE_FORMAT(os.hrfin, '%H:%i:%s') AS hora
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            WHERE os.hrfin IS NOT NULL
            ORDER BY os.hrfin DESC
            LIMIT 1
        ", [$idCliente]);

        $rangeFoco = MysqlRangeHelper::resolve('ep.data_evidencia', $rangeType);
        $focoPragas = DB::select("
            SELECT
                {$rangeFoco['select']},
                SUM(IFNULL(ep.quantidade, 0)) AS quantidade
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            INNER JOIN evidencias_pragas ep ON ep.ordem_servico_id = os.id
            INNER JOIN tipos_praga tp ON tp.id = ep.tipo_praga_id
            WHERE DATE(ep.data_evidencia) BETWEEN ? AND ?
            AND tp.codigo <> 'R'
            GROUP BY {$rangeFoco['groupBy']}
            ORDER BY {$rangeFoco['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);

        $rangeRoe = MysqlRangeHelper::resolve('sub.hrfin', $rangeType);
        $roedoresCapturados = DB::select("
            SELECT
                {$rangeRoe['select']},
                SUM(placas_cola)      AS quantidadePlacasCola,
                SUM(roedores_mortos)  AS quantidadeRoedoresMortos
            FROM (
                SELECT
                    os.hrfin AS hrfin,
                    IFNULL((
                        SELECT SUM(IFNULL(pu.qtdneg, 0))
                        FROM produtos_previstos pu
                        INNER JOIN produtos p ON p.id = pu.produto_id
                        WHERE pu.ordem_servico_id = os.id AND p.codprod_snk = 63
                    ), 0) AS placas_cola,
                    IFNULL((
                        SELECT SUM(IFNULL(ep.quantidade, 0))
                        FROM evidencias_pragas ep
                        INNER JOIN pragas pra ON pra.id = ep.praga_id
                        INNER JOIN grupo_pragas gp ON gp.id = pra.grupo_praga_id
                        INNER JOIN individuos ind ON ind.id = ep.individuo_id
                        WHERE ep.ordem_servico_id = os.id AND gp.id = 1 AND ind.codigo = 'M'
                    ), 0) AS roedores_mortos
                FROM ordens_servico os
                INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
                WHERE os.hrfin IS NOT NULL
                AND DATE(os.hrfin) BETWEEN ? AND ?
            ) AS sub
            GROUP BY {$rangeRoe['groupBy']}
            ORDER BY {$rangeRoe['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);

        $rangeConsumo = MysqlRangeHelper::resolve('os.hrfin', $rangeType);
        $consumoProdutos = DB::select("
            SELECT
                {$rangeConsumo['select']},
                SUM(CASE
                    WHEN gp.id <> 1 AND p.codvol IN ('LT','ML')
                    THEN IFNULL(pu.qtdneg, 0)
                    ELSE 0
                END) AS quantidadeInseticidaLiquido,
                SUM(CASE
                    WHEN gp.id <> 1 AND p.codvol NOT IN ('LT','ML')
                    THEN IFNULL(pu.qtdneg, 0)
                    ELSE 0
                END) AS quantidadeInseticidaSolido,
                SUM(CASE
                    WHEN gp.id = 1
                    THEN IFNULL(pu.qtdneg, 0)
                    ELSE 0
                END) AS quantidadeRodenticida
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            LEFT JOIN produtos_utilizados pu ON pu.ordem_servico_id = os.id
            LEFT JOIN pragas pra ON pra.id = pu.praga_id
            LEFT JOIN grupo_pragas gp ON gp.id = pra.grupo_praga_id
            LEFT JOIN produtos p ON p.id = pu.produto_id
            WHERE os.hrfin IS NOT NULL
            AND DATE(os.hrfin) BETWEEN ? AND ?
            GROUP BY {$rangeConsumo['groupBy']}
            ORDER BY {$rangeConsumo['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);

        $proximasVisitas = DB::select("
            SELECT
                os.numos                                    AS numOS,
                os.ad_numnikkey                             AS contrato,
                os.numos                                    AS idOS,
                os.tipoos                                   AS tipoOS,
                t.codtec_snk                                AS idTecnico,
                t.nome                                      AS nomeTecnico,
                DATE_FORMAT(os.dhprevista, '%d/%m/%Y')      AS data,
                DATE_FORMAT(os.dhprevista, '%H:%i')         AS hora
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            LEFT JOIN tecnicos t ON t.id = os.tecnico_id
            WHERE os.hrfin IS NULL
            AND os.dhprevista IS NOT NULL
            AND DATE(os.dhprevista) BETWEEN ? AND ?
            ORDER BY os.dhprevista, os.ad_numnikkey
        ", [$idCliente, $dataInicio, $dataFim]);

        return [
            'ultimaVisita'       => $ultimaVisita ? (array) $ultimaVisita : null,
            'focoPragas'         => $focoPragas,
            'roedoresCapturados' => $roedoresCapturados,
            'consumoProdutos'    => $consumoProdutos,
            'proximasVisitas'    => $proximasVisitas,
        ];
    }
}
