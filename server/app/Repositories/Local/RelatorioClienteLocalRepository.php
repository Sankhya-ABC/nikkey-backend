<?php

namespace App\Repositories\Local;

use App\Helpers\MysqlRangeHelper;
use App\Repositories\Contracts\RelatorioClienteRepositoryInterface;
use Illuminate\Support\Facades\DB;

class RelatorioClienteLocalRepository implements RelatorioClienteRepositoryInterface
{
    public function getConsumoProdutos(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.hrfin', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},

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

            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getConsumoInsumos(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.hrfin', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},
                SUM(IFNULL(pu.qtdneg, 0)) AS quantidade
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            INNER JOIN produtos_utilizados pu ON pu.ordem_servico_id = os.id
            WHERE os.hrfin IS NOT NULL
            AND DATE(os.hrfin) BETWEEN ? AND ?
            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getFocoPragasEncontradas(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.hrfin', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},
                SUM(IFNULL(ep.quantidade, 0)) AS quantidade
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            INNER JOIN evidencias_pragas ep ON ep.ordem_servico_id = os.id
            WHERE os.hrfin IS NOT NULL
            AND DATE(os.hrfin) BETWEEN ? AND ?
            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getInseticidasXPragas(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.hrfin', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},

                SUM(CASE
                    WHEN gp_ev.id <> 1
                    THEN IFNULL(ep.quantidade, 0)
                    ELSE 0
                END) AS quantidadePragasEncontradas,

                SUM(CASE
                    WHEN gp_pu.id <> 1
                    THEN IFNULL(pu.qtdneg, 0)
                    ELSE 0
                END) AS quantidadeInseticida

            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            LEFT JOIN evidencias_pragas ep ON ep.ordem_servico_id = os.id
            LEFT JOIN pragas pra_ev ON pra_ev.id = ep.praga_id
            LEFT JOIN grupo_pragas gp_ev ON gp_ev.id = pra_ev.grupo_praga_id
            LEFT JOIN produtos_utilizados pu ON pu.ordem_servico_id = os.id
            LEFT JOIN pragas pra_pu ON pra_pu.id = pu.praga_id
            LEFT JOIN grupo_pragas gp_pu ON gp_pu.id = pra_pu.grupo_praga_id

            WHERE os.hrfin IS NOT NULL
            AND DATE(os.hrfin) BETWEEN ? AND ?

            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getArmadilhasFeromonio(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.hrfin', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},

                SUM(CASE WHEN p.codprod_snk = 502 THEN IFNULL(pu.qtdneg, 0) ELSE 0 END) AS quantidadeGachon,
                SUM(CASE WHEN p.codprod_snk = 503 THEN IFNULL(pu.qtdneg, 0) ELSE 0 END) AS quantidadeBioSerrico

            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            INNER JOIN produtos_utilizados pu ON pu.ordem_servico_id = os.id
            INNER JOIN produtos p ON p.id = pu.produto_id

            WHERE os.hrfin IS NOT NULL
            AND DATE(os.hrfin) BETWEEN ? AND ?
            AND p.codprod_snk IN (502, 503)

            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getArmadilhasLuminosas(string $dataInicio, string $dataFim, int $idCliente, int $idGrupoPraga, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.hrfin', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},
                SUM(IFNULL(ep.quantidade, 0)) AS quantidade
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            INNER JOIN evidencias_pragas ep ON ep.ordem_servico_id = os.id
            LEFT JOIN pragas pra ON pra.id = ep.praga_id
            WHERE os.hrfin IS NOT NULL
            AND pra.grupo_praga_id = ?
            AND DATE(os.hrfin) BETWEEN ? AND ?
            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $idGrupoPraga, $dataInicio, $dataFim]);
    }

    public function getRoedoresMortos(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('ep.data_evidencia', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},
                SUM(IFNULL(ep.quantidade, 0)) AS quantidade
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            INNER JOIN evidencias_pragas ep ON ep.ordem_servico_id = os.id
            INNER JOIN individuos ind ON ind.id = ep.individuo_id AND ind.codigo = 'M'
            INNER JOIN tipos_praga tp ON tp.id = ep.tipo_praga_id AND tp.codigo = 'R'
            WHERE DATE(ep.data_evidencia) BETWEEN ? AND ?
            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getPlacaColaArmadilhaMecanica(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.hrfin', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},
                COUNT(*) AS quantidade
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            INNER JOIN pontos_monitoramento pm ON pm.ordem_servico_id = os.id
            WHERE os.hrfin IS NOT NULL
            AND DATE(os.hrfin) BETWEEN ? AND ?
            AND pm.tpmonit IN (5, 8)
            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getIscasRoidas(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.hrfin', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},
                COUNT(DISTINCT pm.idequp) AS quantidade
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            INNER JOIN pontos_monitoramento pm ON pm.ordem_servico_id = os.id
            WHERE (pm.consumometade IS NOT NULL OR pm.consumo IS NOT NULL)
            AND os.hrfin IS NOT NULL
            AND DATE(os.hrfin) BETWEEN ? AND ?
            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getRodenticidasXRoedores(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.hrfin', $rangeType);

        return DB::select("
            SELECT
                {$range['select']},

                SUM(CASE
                    WHEN gp_ev.id = 1 AND ind.codigo = 'M'
                    THEN IFNULL(ep.quantidade, 0)
                    ELSE 0
                END) AS quantidadeRoedoresMortos,

                SUM(CASE
                    WHEN gp_pu.id = 1
                    THEN IFNULL(pu.qtdneg, 0)
                    ELSE 0
                END) AS quantidadeRodenticida

            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            LEFT JOIN evidencias_pragas ep ON ep.ordem_servico_id = os.id
            LEFT JOIN pragas pra_ev ON pra_ev.id = ep.praga_id
            LEFT JOIN grupo_pragas gp_ev ON gp_ev.id = pra_ev.grupo_praga_id
            LEFT JOIN individuos ind ON ind.id = ep.individuo_id
            LEFT JOIN produtos_utilizados pu ON pu.ordem_servico_id = os.id
            LEFT JOIN pragas pra_pu ON pra_pu.id = pu.praga_id
            LEFT JOIN grupo_pragas gp_pu ON gp_pu.id = pra_pu.grupo_praga_id

            WHERE os.hrfin IS NOT NULL
            AND DATE(os.hrfin) BETWEEN ? AND ?

            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getNaoConformidades(string $dataInicio, string $dataFim, int $idCliente): array
    {
        return DB::select("
            SELECT
                DATE_FORMAT(os.hrfin, '%d/%m/%Y')  AS data,
                nc.codenc_snk                       AS numOs,
                nc.setor                            AS areaLocal,
                tnc.descricao                       AS naoConformidade,
                nc.tipores                          AS acaoSugerida
            FROM ordens_servico os
            INNER JOIN clientes c ON c.id = os.cliente_id AND c.codparc_snk = ?
            INNER JOIN nao_conformidades nc ON nc.ordem_servico_id = os.id
            LEFT JOIN tipos_nao_conformidade tnc ON tnc.id = nc.tipo_nao_conformidade_id
            WHERE os.hrfin IS NOT NULL
            AND DATE(os.hrfin) BETWEEN ? AND ?
            ORDER BY os.hrfin
        ", [$idCliente, $dataInicio, $dataFim]);
    }

    public function getGrupoPragas(): array
    {
        return DB::select("
            SELECT id, descricao
            FROM grupo_pragas
            WHERE id IN (SELECT DISTINCT grupo_praga_id FROM pragas WHERE grupo_praga_id IS NOT NULL)
            ORDER BY descricao
        ");
    }

    public function getPragasPorGrupo(int $idGrupoPraga): array
    {
        return DB::select("
            SELECT codpraga_snk AS id, nome_praga AS descricao
            FROM pragas
            WHERE grupo_praga_id = ?
            ORDER BY nome_praga
        ", [$idGrupoPraga]);
    }
}
