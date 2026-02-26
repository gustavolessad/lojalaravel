<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Frete grátis
    |--------------------------------------------------------------------------
    | Valor mínimo do carrinho para frete grátis.
    | Defina 0 para desativar.
    */
    'free_threshold' => (float) env('SHIPPING_FREE_THRESHOLD', 0),
];
