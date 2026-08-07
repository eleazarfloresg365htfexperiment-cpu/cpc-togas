<?php

return [
    'states' => [
        'RESERVADO' => ['next' => ['EN_FABRICACION', 'CANCELADO']],
        'EN_FABRICACION' => ['next' => ['LISTO_PARA_ENTREGA', 'CANCELADO']],
        'LISTO_PARA_ENTREGA' => ['next' => ['ENTREGADO', 'CANCELADO']],
        'ENTREGADO' => ['next' => ['DEVUELTO']],
        'DEVUELTO' => ['next' => []],
        'CANCELADO' => ['next' => []],
    ],

    'discounts' => [
        'enabled' => true,
        'per_toga' => 5.00,
    ],

    'required_accessories' => [
        'TOGA' => ['COLLARIN'],
        'TOGA_UNIVERSITARIA' => ['COLLARIN', 'CAPA'],
    ],

    'accessory_types' => [
        'COLLARIN' => [
            'allowed_for' => ['TOGA', 'TOGA_UNIVERSITARIA'],
            'default_price' => 0.0,
        ],
        'CAPA' => [
            'allowed_for' => ['TOGA_UNIVERSITARIA'],
            'default_price' => 0.0,
        ],
        'BIRRETE' => [
            'allowed_for' => ['TOGA', 'TOGA_UNIVERSITARIA'],
            'default_price' => 25.0,
            'price_by_tipo' => [
                'UNIVERSITARIO' => 50.0,
                'ESTANDAR' => 25.0,
            ],
        ],
        'BORLA' => [
            'allowed_for' => ['TOGA', 'TOGA_UNIVERSITARIA'],
            'default_price' => 5.0,
        ],
    ],
];
