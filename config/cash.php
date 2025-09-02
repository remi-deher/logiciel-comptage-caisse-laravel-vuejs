<?php

return [
    'currency' => [
        'code' => 'EUR',
        'symbol' => '€',
    ],

    'denominations' => [
        'billets' => [
            ['nom' => 'b500', 'valeur' => 500],
            ['nom' => 'b200', 'valeur' => 200],
            ['nom' => 'b100', 'valeur' => 100],
            ['nom' => 'b50', 'valeur' => 50],
            ['nom' => 'b20', 'valeur' => 20],
            ['nom' => 'b10', 'valeur' => 10],
            ['nom' => 'b5', 'valeur' => 5],
        ],
        'pieces' => [
            ['nom' => 'p200', 'valeur' => 2],
            ['nom' => 'p100', 'valeur' => 1],
            ['nom' => 'p050', 'valeur' => 0.50],
            ['nom' => 'p020', 'valeur' => 0.20],
            ['nom' => 'p010', 'valeur' => 0.10],
            ['nom' => 'p005', 'valeur' => 0.05],
            ['nom' => 'p002', 'valeur' => 0.02],
            ['nom' => 'p001', 'valeur' => 0.01],
        ],
    ],
];
