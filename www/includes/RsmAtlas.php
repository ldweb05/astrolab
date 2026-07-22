<?php
declare(strict_types=1);

/**
 * Atlante delle configurazioni RSM
 * Scuola di Astrologia Attiva di Ciro Discepolo
 *
 * Questo file contiene esclusivamente CONOSCENZA.
 * Nessuna logica.
 */

final class RsmAtlas
{
    public static function ascendenteRadix(): array
    {
        return [

            1 => [
                'priority' => 100,
                'rating'   => 5,
                'themes' => [
                    'identita' => 100,
                    'salute'   => 90,
                    'iniziative' => 80,
                ],
                'keywords' => [
                    'rinascita',
                    'energia',
                    'nuovi inizi'
                ],
            ],

            2 => [
                'priority' => 100,
                'rating'   => 5,
                'themes' => [
                    'denaro' => 100,
                    'patrimonio' => 90,
                    'sicurezza' => 70,
                ],
                'keywords' => [
                    'entrate',
                    'risorse',
                    'beni'
                ],
            ],

            3 => [
                'priority' => 90,
                'rating'   => 4,
                'themes' => [
                    'comunicazione' => 90,
                    'viaggi_brevi' => 80,
                    'studio' => 70,
                ],
                'keywords' => [
                    'contatti',
                    'movimento',
                    'apprendimento'
                ],
            ],

            // Le altre case verranno aggiunte nei prossimi commit.

        ];
    }
}
