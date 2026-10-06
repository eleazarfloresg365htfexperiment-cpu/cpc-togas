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
        'enabled' => false,
        'per_toga' => 0,
    ],

    'required_accessories' => [
        'TOGA' => ['COLLARIN'],
        'TOGA_UNIVERSITARIA' => ['COLLARIN', 'CAPA'],
    ],

    /*
    | Color de borla que corresponde a cada carrera (capa universitaria).
    | Debe coincidir con los colores de borla del formulario de productos.
    */
    'colores_borla_por_carrera' => [
        'AGRONOMIA' => 'Verde',
        'DERECHO' => 'Rojo',
        'PEDAGOGIA' => 'Celeste',
        'MEDICINA' => 'Amarillo',
        'CIENCIAS_ECONOMICAS' => 'Naranja',
    ],

    /*
    | Borlas por tipo y el código de cada color. Es la única fuente: el
    | formulario de productos la usa para ofrecer colores y completar el
    | código, y el servidor la usa si el código llega vacío.
    |
    | - NORMAL: para togas estándar; su color debe coincidir con el collarín.
    | - UNIVERSITARIA: para togas universitarias; su color es el de la
    |   carrera de la capa. Rojo y Verde existen en los dos tipos, por eso
    |   las universitarias llevan "-U" en el código.
    */
    'colores_borla_por_tipo' => [
        'NORMAL' => [
            'Dorado' => 'B-DOR',
            'Rojo' => 'B-RO',
            'Verde' => 'B-VE',
        ],
        'UNIVERSITARIA' => [
            'Celeste' => 'B-CE',
            'Rojo' => 'B-RO-U',
            'Verde' => 'B-VE-U',
            'Amarillo' => 'B-AM',
            'Naranja' => 'B-NA',
        ],
    ],

    /*
    | Carreras de las capas y su código (los colores de cada carrera están en
    | colores_borla_por_carrera). El formulario de productos los usa para
    | completar color y código al elegir la carrera.
    */
    'carreras_capa' => [
        'AGRONOMIA' => ['nombre' => 'Agronomía', 'codigo' => 'AGR'],
        'DERECHO' => ['nombre' => 'Derecho', 'codigo' => 'DER'],
        'PEDAGOGIA' => ['nombre' => 'Pedagogía', 'codigo' => 'PED'],
        'MEDICINA' => ['nombre' => 'Medicina', 'codigo' => 'MED'],
        'CIENCIAS_ECONOMICAS' => ['nombre' => 'Ciencias Económicas', 'codigo' => 'ECO'],
    ],

    /*
    | Colores de collarín según su tipo y el código de cada color. El normal
    | usa Dorado, Rojo o Verde; el universitario solo Azul.
    */
    'colores_collarin_por_tipo' => [
        'NORMAL' => ['Dorado' => 'C-DO', 'Rojo' => 'C-RO', 'Verde' => 'C-VE'],
        'UNIVERSITARIO' => ['Azul' => 'C-AZ'],
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
                'NORMAL' => 25.0,
                'ESTANDAR' => 25.0, // nombre antiguo de Normal
            ],
        ],
        'BORLA' => [
            'allowed_for' => ['TOGA', 'TOGA_UNIVERSITARIA'],
            'default_price' => 5.0,
        ],
    ],
];
