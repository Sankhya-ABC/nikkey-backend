<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page    = (int) $request->query('page', 1);
        $search  = trim($request->query('search'));

        $query = Cliente::with([
            'usuarios.tipoUsuario',
            'usuarios.departamento',
            'endereco',
            'bairro',
            'cidade.uf'
        ]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('razao_social', 'LIKE', "%{$search}%")
                ->orWhere('nome_fantasia', 'LIKE', "%{$search}%")
                ->orWhere('cnpj_cpf', 'LIKE', "%{$search}%");
            });
        }

        $clientes = $query->paginate($perPage, ['*'], 'page', $page);

        $clientesFormatados = $clientes->getCollection()->map(function ($cliente) {
            $user = $cliente->usuarios->first();

            return [
                'id' => $cliente->id,
                'razaoSocial' => $cliente->razao_social,
                'nomeFantasia' => $cliente->nome_fantasia,
                'cnpjCpf' => $cliente->cnpj_cpf,

                'validadeCertificado' => $cliente->validade_certificado
                    ? $cliente->validade_certificado->timestamp
                    : "",

                'tipoAtividade' => $cliente->tipo_atividade,
                'possuiContrato' => (bool) $cliente->tem_contrato,

                'logradouro'  => optional($cliente->endereco)->logradouro ?? "",
                'numero'      => $cliente->numero ?? "",
                'complemento' => $cliente->complemento ?? "",
                'bairro'      => optional($cliente->bairro)->nome ?? "",
                'cidade'      => optional($cliente->cidade)->nome ?? "",
                'estado'      => optional(optional($cliente->cidade)->uf)->sigla ?? "",
                'cep'         => $cliente->cep ?? "",

                'contato'  => $cliente->contato ?? "",
                'telefone' => $cliente->telefone ?? "",
                'email'    => $cliente->email ?? "",

                'nomeAcesso' => $user?->name ?? "",
                'emailAcesso' => $user?->email ?? "",
                'departamento' => $user?->departamento?->descricao ?? "",

                'ativo' => (bool) $cliente->ativo,
                'dataCadastro' => $cliente->created_at,
            ];
        });

        $clientes->setCollection($clientesFormatados);

        return response()->json([
            'data' => $clientes->items(),
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'per_page'     => $clientes->perPage(),
                'total'        => $clientes->total(),
                'last_page'    => $clientes->lastPage(),
            ]
        ]);
    }
}
