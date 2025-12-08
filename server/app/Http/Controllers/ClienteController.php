<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Retorna todos os clientes
     */
    public function index()
    {
        // Carrega usuários + tipo usuário + departamento
        $clientes = Cliente::with(['usuarios.tipoUsuario', 'usuarios.departamento'])->get();

        $clientesFormatados = $clientes->map(function ($cliente) {

            // Pegamos somente o primeiro usuário vinculado (caso exista)
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
                
                // Campos fixos ou vindos do usuário
                'funcao' => '',
                'fax' => '',
                'email' => $cliente->email ?? '',
                'observacoes' => '',

                // Campos de acesso vindos do usuário
                'nomeAcesso' => $user ? $user->name : "",
                'emailAcesso' => $user ? $user->email : "",
                'departamento' => $user && $user->departamento ? $user->departamento->descricao : "",

                // Senha nunca é retornada (segurança) → string vazia
                'senha' => "",
                'confirmarSenha' => "",

                'ativo' => (bool) $cliente->ativo,
                'dataCadastro' => $cliente->created_at ?? null,
            ];
        });

        return response()->json($clientesFormatados);
    }

    /**
     * Retorna um cliente específico
     */
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
