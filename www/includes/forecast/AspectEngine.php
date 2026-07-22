<?php
declare(strict_types=1);

final class AspectEngine
{
    private const ASPECTS = [
        'congiunzione' => [
            'angle' => 0,
            'orb'   => 8,
            'power' => 1.20,
        ],
        'opposizione' => [
            'angle' => 180,
            'orb'   => 8,
            'power' => 0.90,
        ],
        'quadratura' => [
            'angle' => 90,
            'orb'   => 6,
            'power' => 0.95,
        ],
        'trigono' => [
            'angle' => 120,
            'orb'   => 6,
            'power' => 1.15,
        ],
        'sestile' => [
            'angle' => 60,
            'orb'   => 4,
            'power' => 1.05,
        ],
    ];

    public function calculate(array $temaRS): array
    {
        $planets = $temaRS['pianeti'] ?? [];
        $result  = [];

        $keys = array_keys($planets);

        for ($i = 0; $i < count($keys); $i++) {

            for ($j = $i + 1; $j < count($keys); $j++) {

                $a = $keys[$i];
                $b = $keys[$j];

                $lonA = $planets[$a]['longitudine'] ?? null;
                $lonB = $planets[$b]['longitudine'] ?? null;

                if ($lonA === null || $lonB === null) {
                    continue;
                }

                $distance = abs((float)$lonA - (float)$lonB);

                if ($distance > 180) {
                    $distance = 360 - $distance;
                }

                foreach (self::ASPECTS as $name => $aspect) {

                    $delta = abs($distance - $aspect['angle']);

                    if ($delta <= $aspect['orb']) {

                        $result[] = [
                            'planet1' => $a,
                            'planet2' => $b,
                            'aspect'  => $name,
                            'orb'     => round($delta,2),
                            'power'   => $aspect['power'],
                        ];
                    }
                }
            }
        }

        usort(
            $result,
            static fn($a,$b) =>
                $a['orb'] <=> $b['orb']
        );

        return $result;
    }
}
