<?php

namespace App\Repositories\Contracts;

interface RelatorioClienteRepositoryInterface
{
    public function getConsumoProdutos(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;

    public function getConsumoInsumos(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;

    public function getFocoPragasEncontradas(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;

    public function getInseticidasXPragas(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;

    public function getArmadilhasFeromonio(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;

    public function getArmadilhasLuminosas(string $dataInicio, string $dataFim, int $idCliente, int $idGrupoPraga, string $rangeType): array;

    public function getRoedoresMortos(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;

    public function getPlacaColaArmadilhaMecanica(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;

    public function getIscasRoidas(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;

    public function getRodenticidasXRoedores(string $dataInicio, string $dataFim, int $idCliente, string $rangeType): array;

    public function getNaoConformidades(string $dataInicio, string $dataFim, int $idCliente): array;

    public function getGrupoPragas(): array;

    public function getPragasPorGrupo(int $idGrupoPraga): array;
}
