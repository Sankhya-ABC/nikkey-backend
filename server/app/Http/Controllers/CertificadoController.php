<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Certificado;
use App\Models\OrdemServico;

class CertificadoController extends Controller
{
    public function getCertificados(Request $request)
    {
        $idCliente = (int) $request->query('idCliente');
        $idOS      = $request->query('idOS');

        if ($idCliente <= 0) {
            return response()->json(['error' => 'idCliente e obrigatorio e deve ser um numero valido'], 400);
        }

        $query = DB::table('certificados AS cert')
            ->join('ordens_servico AS os', 'os.numos', '=', 'cert.numos')
            ->join('clientes AS c', 'c.id', '=', 'os.cliente_id')
            ->where('c.codparc_snk', $idCliente)
            ->select('cert.numos AS idOS', 'os.ad_numnikkey AS numOS', DB::raw('NULL AS validadeContrato'));

        if (!empty($idOS)) {
            $query->where('cert.numos', $idOS);
        }

        return response()->json($query->get());
    }

    public function getCertificadoByIdOS(Request $request)
    {
        $idCliente = (int) $request->query('idCliente');
        $idOS      = $request->query('idOS');

        if (empty($idOS)) {
            return response()->json(['error' => 'idOS e obrigatorio'], 400);
        }

        if (empty($idCliente)) {
            return response()->json(['error' => 'idCliente e obrigatorio'], 400);
        }

        $cert = Certificado::where('numos', $idOS)->first();

        if (!$cert) {
            return response()->json(['error' => 'Certificado nao encontrado para este cliente'], 404);
        }

        $os = OrdemServico::with([
            'cliente',
            'cliente.endereco',
            'cliente.bairro',
            'cliente.cidade.uf',
        ])->where('numos', $idOS)->first();

        if (!$os || $os->cliente?->codparc_snk !== $idCliente) {
            return response()->json(['error' => 'Certificado nao encontrado para este cliente'], 404);
        }

        // Empresa Nikkey
        $empresa = \App\Models\Empresa::where('codemp_snk', $cert->codemp_snk)->first();

        // Tecnico responsavel (via codvend_snk do certificado)
        $tecnico = $cert->codvend_snk
            ? \App\Models\Tecnico::where('codvend_snk', $cert->codvend_snk)->first()
            : $os->tecnico;

        $cliente = $os->cliente;

        $dataInicio = $cert->dt_garantia?->format('d/m/Y');
        $dataFim    = ($cert->dt_garantia && $cert->dias_garantia)
            ? $cert->dt_garantia->addDays($cert->dias_garantia)->format('d/m/Y')
            : null;

        $nomeConselho = null;
        $numConselho  = null;
        if ($tecnico?->crea && $tecnico?->crbio) {
            $nomeConselho = 'CREA/CRBIO';
            $numConselho  = "{$tecnico->crea}/{$tecnico->crbio}";
        } elseif ($tecnico?->crea) {
            $nomeConselho = 'CREA';
            $numConselho  = $tecnico->crea;
        } elseif ($tecnico?->crbio) {
            $nomeConselho = 'CRBIO';
            $numConselho  = $tecnico->crbio;
        }

        return response()->json([
            'idOS'  => $os->numos,
            'numOS' => $os->ad_numnikkey,
            'parceiro' => [
                'codigo'      => $cliente?->codparc_snk,
                'razaoSocial' => $cliente?->razao_social,
                'nomeFantasia'=> $cliente?->nome_fantasia,
                'cpfCnpj'     => $cliente?->cnpj_cpf,
                'endereco'    => [
                    'logradouro' => $cliente?->endereco?->logradouro,
                    'numero'     => $cliente?->numero,
                    'bairro'     => $cliente?->bairro?->nome,
                    'cidade'     => $cliente?->cidade?->nome,
                    'complemento'=> $cliente?->complemento,
                    'cep'        => $cliente?->cep,
                    'estado'     => $cliente?->cidade?->uf?->sigla,
                ],
            ],
            'certificado' => [
                'validadeContrato'         => null,
                'licencaFuncionamento'     => null,
                'dataValidadeLicenca'      => null,
                'dataInicioValidade'       => $dataInicio,
                'dataFimValidade'          => $dataFim,
            ],
            'nikkey' => [
                'razaoSocial' => $empresa?->razao_social,
                'nomeFantasia'=> $empresa?->nome_fantasia,
                'cpfCnpj'     => $empresa?->cgc,
                'telefone'    => $empresa?->telefone,
                'endereco'    => [
                    'logradouro' => $empresa?->logradouro,
                    'numero'     => $empresa?->numero,
                    'bairro'     => $empresa?->bairro,
                    'cidade'     => $empresa?->cidade,
                    'complemento'=> $empresa?->complemento,
                    'cep'        => $empresa?->cep,
                    'estado'     => $empresa?->estado,
                ],
            ],
            'responsavelTecnico' => [
                'razaoSocial'    => $tecnico?->nome,
                'nomeFantasia'   => $tecnico?->nome,
                'cpfCnpj'        => null,
                'conselho'       => [
                    'nome'   => $nomeConselho,
                    'numero' => $numConselho,
                ],
                'imagemAssinatura' => $tecnico?->assinatura,
            ],
        ]);
    }
}