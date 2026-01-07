<?php

namespace App\Policies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VisibilityPolicy
{
    /**
     * Filtra a query de acordo com o perfil do usuário.
     *
     * @param User $user
     * @param Builder<Model> $query
     * @param string|null $clienteField Nome do campo de cliente na tabela (ex: cliente_id)
     * @return Builder<Model>
     */
    
    public static function apply(User $user, Builder $query, ?string $clienteField = 'cliente_id'): Builder
    {
        if (strtoupper($user->tipoUsuario->descricao ?? '') === 'COMMON' && $clienteField) {
            $query->where($clienteField, $user->cliente_id);
        }

        return $query;
    }
}
