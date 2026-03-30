<?php

namespace App\Helpers;

use App\Models\User;

class UserMapper
{
    public static function toVO(User $u): array
    {
        return [
            'id' => $u->id,
            'nome' => $u->name ?? '',
            'email' => $u->email ?? '',

            'departamento' => [
                'id' => $u->departamento->id ?? '',
                'descricao' => $u->departamento->descricao ?? '',
            ],

            'perfil' => $u->tipoUsuario->descricao ?? null,

            'cliente' => [
                'id' => $u->cliente->id ?? '',
                'codparc' => $u->cliente->codparc_snk ?? '',
                'nomeFantasia' => $u->cliente->nome_fantasia ?? '',
                'cnpjCpf' => $u->cliente->cnpj_cpf ?? '',
            ],

            'telefone' => $u->telefone ?? '',
            'senha' => '',
            'confirmarSenha' => '',
            'ativo' => (bool) $u->ativo,
            'dataCadastro' => $u->created_at?->toISOString(),
        ];
    }
}