<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantResolver
{
    /**
     * Resolve o codparc_snk (identificador do cliente na Sankhya) que deve
     * ser usado para filtrar dados do portal do cliente.
     *
     * Usuários COMMON (Portal do Cliente) sempre são restritos ao próprio
     * cliente, ignorando qualquer idCliente enviado pela requisição — isso
     * evita que um usuário troque o idCliente na URL e veja dados de outra
     * empresa. Usuários ADMIN (Portal Master) podem informar idCliente para
     * consultar um cliente específico.
     */
    public static function resolveCodParc(User $user, Request $request): int
    {
        if ($user->isCommon()) {
            $codParc = $user->cliente?->codparc_snk;

            if (!$codParc) {
                throw new HttpException(403, 'Usuário sem cliente vinculado.');
            }

            return (int) $codParc;
        }

        $idCliente = (int) $request->query('idCliente');

        if ($idCliente <= 0) {
            throw new HttpException(422, 'O parâmetro idCliente é obrigatório.');
        }

        return $idCliente;
    }
}
