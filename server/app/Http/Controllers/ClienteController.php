<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Lista todos os clientes com estrutura organizada.
     */
    public function index()
    {
        // Busca os clientes com seus usuários vinculados
        $clientes = Cliente::with('usuarios')->get();

        // Mapeia para o formato desejado
        $dados = $clientes->map(function ($cliente) {
            return [
                'id' => $cliente->id,
                'razaoSocial' => $cliente->razao_social,
                'nomeFantasia' => $cliente->nome_fantasia,
                'cnpjCpf' => $cliente->cnpj_cpf,
                'validadeCertificadoDias' => $cliente->validade_certificado,
                'idTipoAtividade' => $cliente->tipo_atividade,
                'endereco' => [
                    'logradouro' => $cliente->logradouro,
                    'numero' => $cliente->numero,
                    'complemento' => $cliente->complemento,
                    'bairro' => $cliente->bairro,
                    'estado' => $cliente->estado,
                    'cidade' => $cliente->cidade,
                    'cep' => $cliente->cep,
                ],
                'contato' => [
                    'telefone' => $cliente->telefone,
                    'funcao' => $cliente->contato, // campo contato na tabela
                    'fax' => null,
                    'email' => $cliente->email,
                ],
                'observacoes' => $cliente->observacoes ?? null,
                'usuario' => $cliente->usuarios->map(function ($usuario) {
                    return [
                        'nome' => $usuario->name ?? '',
                        'email' => $usuario->email ?? '',
                        'departamento' => $usuario->departamento ?? '',
                        'senha' => $usuario->senha ?? '',
                    ];
                }),
            ];
        });

        return response()->json($dados);
    }
}
