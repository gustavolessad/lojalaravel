<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ProductScopeInterface
{
    /**
     * Aplica o escopo a uma query de produtos.
     */
    public function applyToQuery(Builder $query): Builder;

    /**
     * Retorna os IDs de produtos ativos neste escopo (para atributos/faixa de preço).
     */
    public function baseProductIds(): Collection;
}
