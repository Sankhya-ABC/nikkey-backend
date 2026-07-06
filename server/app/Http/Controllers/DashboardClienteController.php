<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Repositories\Contracts\DashboardClienteRepositoryInterface;

class DashboardClienteController extends Controller
{
    public function __construct(private DashboardClienteRepositoryInterface $repo) {}

    public function getCompleto(Request $request)
    {
        $dataInicio = Carbon::parse($request->query('dataInicio'))->toDateString();
        $dataFim    = Carbon::parse($request->query('dataFim'))->toDateString();
        $idCliente  = (int) $request->query('idCliente');
        $rangeType  = $request->query('rangeType', 'day');

        return response()->json(
            $this->repo->getCompleto($dataInicio, $dataFim, $idCliente, $rangeType)
        );
    }
}
