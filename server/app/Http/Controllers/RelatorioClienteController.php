<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Repositories\Contracts\RelatorioClienteRepositoryInterface;

class RelatorioClienteController extends Controller
{
    public function __construct(private RelatorioClienteRepositoryInterface $repo) {}

    public function getConsumoProdutos(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getConsumoProdutos($ini, $fim, $id, $request->query('rangeType', 'day'))
        );
    }

    public function getConsumoInsumos(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getConsumoInsumos($ini, $fim, $id, $request->query('rangeType', 'day'))
        );
    }

    public function getFocoPragasEncontradas(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getFocoPragasEncontradas($ini, $fim, $id, $request->query('rangeType', 'day'))
        );
    }

    public function getInseticidasXPragas(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getInseticidasXPragas($ini, $fim, $id, $request->query('rangeType', 'day'))
        );
    }

    public function getArmadilhasFeromonio(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getArmadilhasFeromonio($ini, $fim, $id, $request->query('rangeType', 'day'))
        );
    }

    public function getArmadilhasLuminosas(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getArmadilhasLuminosas(
                $ini, $fim, $id,
                (int) $request->query('idGrupoPraga'),
                $request->query('rangeType', 'day')
            )
        );
    }

    public function getRoedoresMortos(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getRoedoresMortos($ini, $fim, $id, $request->query('rangeType', 'day'))
        );
    }

    public function getPlacaColaArmadilhaMecanica(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getPlacaColaArmadilhaMecanica($ini, $fim, $id, $request->query('rangeType', 'day'))
        );
    }

    public function getIscasRoidas(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getIscasRoidas($ini, $fim, $id, $request->query('rangeType', 'day'))
        );
    }

    public function getRodenticidasXRoedores(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json(
            $this->repo->getRodenticidasXRoedores($ini, $fim, $id, $request->query('rangeType', 'day'))
        );
    }

    public function getNaoConformidades(Request $request)
    {
        [$ini, $fim, $id] = $this->params($request);
        return response()->json($this->repo->getNaoConformidades($ini, $fim, $id));
    }

    public function getGrupoPragas()
    {
        return response()->json($this->repo->getGrupoPragas());
    }

    public function getPragasPorGrupo(Request $request)
    {
        return response()->json(
            $this->repo->getPragasPorGrupo((int) $request->query('idGrupoPraga'))
        );
    }

    private function params(Request $request): array
    {
        return [
            Carbon::parse($request->query('dataInicio'))->toDateString(),
            Carbon::parse($request->query('dataFim'))->toDateString(),
            (int) $request->query('idCliente'),
        ];
    }
}
