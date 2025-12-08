<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        
        $clientes = Cliente::with(['usuarios.tipoUsuario', 'usuarios.departamento'])
            ->paginate($perPage);

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
                'logradouro' => $cliente->logradouro,
                'numero' => $cliente->numero ?? "",
                'complemento' => $cliente->complemento ?? "",
                'bairro' => $cliente->bairro,
                'estado' => $cliente->estado ?? "",
                'cidade' => $cliente->cidade,
                'cep' => $cliente->cep,
                'contato' => $cliente->contato ?? "",
                'telefone' => $cliente->telefone ?? "",
                'funcao' => '',
                'fax' => '',
                'email' => $cliente->email ?? '',
                'observacoes' => '',

                'nomeAcesso' => $user ? $user->name : "",
                'emailAcesso' => $user ? $user->email : "",
                'departamento' => $user && $user->departamento ? $user->departamento->descricao : "",

                'senha' => "",
                'confirmarSenha' => "",

                'ativo' => (bool) $cliente->ativo,
                'dataCadastro' => $cliente->created_at ?? null,
            ];
        });

        $clientes->setCollection($clientesFormatados);

        return response()->json([
            'data' => $clientes->items(),
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'per_page' => $clientes->perPage(),
                'total' => $clientes->total(),
                'last_page' => $clientes->lastPage(),
            ]
        ]);
    }

    public function show($id)
    {
        $cliente = Cliente::with(['usuarios.tipoUsuario', 'usuarios.departamento'])
            ->findOrFail($id);

        $user = $cliente->usuarios->first();

        $result = [
            'id' => $cliente->id,
            'razaoSocial' => $cliente->razao_social,
            'nomeFantasia' => $cliente->nome_fantasia,
            'cnpjCpf' => $cliente->cnpj_cpf,
            'validadeCertificado' => $cliente->validade_certificado 
                ? $cliente->validade_certificado->timestamp 
                : "",
            'tipoAtividade' => $cliente->tipo_atividade,
            'possuiContrato' => (bool) $cliente->tem_contrato,
            'logradouro' => $cliente->logradouro,
            'numero' => $cliente->numero ?? "",
            'complemento' => $cliente->complemento ?? "",
            'bairro' => $cliente->bairro,
            'estado' => $cliente->estado ?? "",
            'cidade' => $cliente->cidade,
            'cep' => $cliente->cep,
            'contato' => $cliente->contato ?? "",
            'telefone' => $cliente->telefone ?? "",
            'funcao' => '',
            'fax' => '',
            'email' => $cliente->email ?? '',
            'observacoes' => '',

            'nomeAcesso' => $user ? $user->name : "",
            'emailAcesso' => $user ? $user->email : "",
            'departamento' => $user && $user->departamento ? $user->departamento->descricao : "",

            'senha' => "",
            'confirmarSenha' => "",

            'ativo' => (bool) $cliente->ativo,
            'dataCadastro' => $cliente->created_at ?? null,
        ];

        return response()->json($result);
    }
}
