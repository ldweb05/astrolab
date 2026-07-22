<?php
declare(strict_types=1);

final class RsmAtlas
{
    public static function ascendenteRadix(): array
    {
        return [

            1 => [
                'priority' => 100,
                'rating'   => 5,
                'themes' => [
                    'identita'   => 100,
                    'salute'     => 90,
                    'iniziative' => 80,
                ],
            ],

            2 => [
                'priority' => 100,
                'rating'   => 5,
                'themes' => [
                    'denaro'     => 100,
                    'patrimonio' => 90,
                    'sicurezza'  => 70,
                ],
            ],

            3 => [
                'priority' => 90,
                'rating'   => 4,
                'themes' => [
                    'comunicazione' => 90,
                    'studio'        => 80,
                    'viaggi_brevi'  => 70,
                ],
            ],

            4 => [
                'priority' => 100,
                'rating'   => 5,
                'themes' => [
                    'casa'      => 100,
                    'famiglia'  => 95,
                    'immobili'  => 80,
                ],
            ],

            5 => [
                'priority' => 100,
                'rating'   => 5,
                'themes' => [
                    'amore'      => 100,
                    'figli'      => 95,
                    'creativita' => 90,
                ],
            ],

            6 => [
                'priority' => 100,
                'rating'   => 5,
                'themes' => [
                    'lavoro'  => 95,
                    'salute'  => 100,
                    'dovere'  => 80,
                ],
            ],

            7 => [
                'priority' => 100,
                'rating'   => 5,
                'themes' => [
                    'matrimonio' => 100,
                    'relazioni'  => 95,
                    'societa'    => 80,
                ],
            ],

            8 => [
                'priority' => 90,
                'rating'   => 4,
                'themes' => [
                    'trasformazione' => 100,
                    'eredita'        => 70,
                    'prove'          => 90,
                ],
            ],

            9 => [
                'priority' => 90,
                'rating'   => 4,
                'themes' => [
                    'viaggi' => 100,
                    'estero' => 90,
                    'studio' => 80,
                ],
            ],

            10 => [
                'priority' => 110,
                'rating'   => 5,
                'themes' => [
                    'carriera'         => 100,
                    'realizzazione'    => 100,
                    'prestigio'        => 95,
                    'successo'         => 90,
                    'immagine_pubblica'=> 85,
                ],
            ],

            11 => [
                'priority' => 90,
                'rating'   => 4,
                'themes' => [
                    'amicizie' => 100,
                    'progetti' => 90,
                    'protezione' => 70,
                ],
            ],

            12 => [
                'priority' => 100,
                'rating'   => 5,
                'themes' => [
                    'introspezione' => 100,
                    'prove'         => 95,
                    'spiritualita'  => 80,
                ],
            ],

        ];
    }
}
