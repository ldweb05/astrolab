<?php
declare(strict_types=1);

final class AspectScoreEngine
{
    private const PLANET_THEMES = [

        'sole' => [
            'carriera',
            'prestigio',
            'successo'
        ],

        'giove' => [
            'carriera',
            'espansione',
            'successo',
            'viaggi'
        ],

        'saturno' => [
            'prove',
            'lavoro',
            'responsabilita'
        ],

        'venere' => [
            'amore',
            'creativita'
        ],

        'marte' => [
            'energia',
            'lavoro',
            'salute'
        ],

        'mercurio' => [
            'studio',
            'comunicazione'
        ],

        'luna' => [
            'famiglia',
            'casa'
        ],
    ];


    public function apply(array $scores, array $aspects): array
    {
        foreach ($aspects as $aspect) {

            $p1 = mb_strtolower(
                (string)($aspect['planet1'] ?? '')
            );

            $p2 = mb_strtolower(
                (string)($aspect['planet2'] ?? '')
            );

            $bonus = $this->bonus(
                (string)($aspect['aspect'] ?? '')
            );


            foreach (
                array_merge(
                    self::PLANET_THEMES[$p1] ?? [],
                    self::PLANET_THEMES[$p2] ?? []
                )
                as $theme
            ) {

                $scores[$theme] =
                    ($scores[$theme] ?? 0)
                    + $bonus;
            }
        }

        arsort($scores);

        return $scores;
    }


    private function bonus(string $aspect): int
    {
        return match($aspect) {

            'trigono' =>
                35,

            'sestile' =>
                20,

            'congiunzione' =>
                40,

            'quadratura' =>
                10,

            'opposizione' =>
                5,

            default =>
                0,
        };
    }
}
