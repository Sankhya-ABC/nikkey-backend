<?php

namespace App\Repositories\Local;

use App\Helpers\MysqlRangeHelper;
use App\Repositories\Contracts\DashboardAdminRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DashboardAdminLocalRepository implements DashboardAdminRepositoryInterface
{
    public function getCompleto(string $dataInicio, string $dataFim, string $rangeType): array
    {
        $range = MysqlRangeHelper::resolve('os.dhprevista', $rangeType);

        $dadosBasicos = DB::selectOne("
            SELECT
                COUNT(*)                       AS qtdOrdemServico,
                COUNT(DISTINCT os.cliente_id)  AS qtdCliente,
                COUNT(DISTINCT os.tecnico_id)  AS qtdTecnico
            FROM ordens_servico os
            WHERE DATE(os.dhprevista) BETWEEN ? AND ?
        ", [$dataInicio, $dataFim]);

        $ordensServico = DB::select("
            SELECT
                {$range['select']},
                COUNT(*) AS quantidade
            FROM ordens_servico os
            WHERE DATE(os.dhprevista) BETWEEN ? AND ?
            AND os.dhprevista IS NOT NULL
            GROUP BY {$range['groupBy']}
            ORDER BY {$range['orderBy']}
        ", [$dataInicio, $dataFim]);

        $atendimentosTecnico = DB::select("
            SELECT
                t.codtec_snk AS idTecnico,
                t.nome       AS nomeTecnico,
                COUNT(*)     AS qtdOrdemServico
            FROM ordens_servico os
            INNER JOIN tecnicos t ON t.id = os.tecnico_id
            WHERE DATE(os.dhprevista) BETWEEN ? AND ?
            AND os.dhprevista IS NOT NULL
            GROUP BY t.id, t.codtec_snk, t.nome
            ORDER BY qtdOrdemServico DESC
        ", [$dataInicio, $dataFim]);

        $consumoProdutos = DB::select("
            SELECT
                p.codprod_snk              AS codProduto,
                p.descricao                AS descProduto,
                SUM(IFNULL(pp.qtdneg, 0)) AS qnt
            FROM produtos_previstos pp
            INNER JOIN produtos p ON p.id = pp.produto_id
            INNER JOIN ordens_servico os ON os.id = pp.ordem_servico_id
            WHERE DATE(os.dhprevista) BETWEEN ? AND ?
            AND os.dhprevista IS NOT NULL
            GROUP BY p.id, p.codprod_snk, p.descricao
            ORDER BY qnt DESC
        ", [$dataInicio, $dataFim]);

        $proximasVisitas = DB::select("
            SELECT
                os.numos                                        AS numOS,
                DATE_FORMAT(os.dhprevista, '%d/%m/%Y')          AS data,
                DATE_FORMAT(os.dhprevista, '%H:%i')             AS horaInicio,
                DATE_FORMAT(os.dhprevistafin, '%H:%i')          AS horaFim,
                IFNULL(c.nome_fantasia, c.razao_social)         AS nomeCliente
            FROM ordens_servico os
            LEFT JOIN clientes c ON c.id = os.cliente_id
            WHERE DATE(os.dhprevista) BETWEEN ? AND ?
            AND os.dhprevista IS NOT NULL
            AND DATE(os.dhprevista) >= CURDATE()
            ORDER BY os.dhprevista ASC
        ", [$dataInicio, $dataFim]);

        return [
            'dadosBasicos'        => (array) $dadosBasicos,
            'ordensServico'       => $ordensServico,
            'atendimentosTecnico' => $atendimentosTecnico,
            'consumoProdutos'     => $consumoProdutos,
            'proximasVisitas'     => $proximasVisitas,
        ];
    }
}
