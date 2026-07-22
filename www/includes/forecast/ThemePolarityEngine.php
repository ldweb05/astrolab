<?php
declare(strict_types=1);

require_once __DIR__.'/PlanetNature.php';

final class ThemePolarityEngine
{
    public function build(array $contributions): array
    {
        $result = [];

        foreach ($contributions as $theme => $items) {
            $intensity = 0.0;
            $positive  = 0.0;
            $critical  = 0.0;
            $neutral   = 0.0;

            foreach ($items as $item) {
                $planet = (string)($item['planet'] ?? '');
                $value  = abs((float)($item['value'] ?? 0));

                $intensity += $value;

                $nature = PlanetNature::value($planet);

                if ($nature > 0) {
                    $positive += $value;
                } elseif ($nature < 0) {
                    $critical += $value;
                } else {
                    $neutral += $value;
                }
            }

            $balance = $positive - $critical;

            $result[$theme] = [
                'intensity' => round($intensity, 2),
                'positive'  => round($positive, 2),
                'critical'  => round($critical, 2),
                'neutral'   => round($neutral, 2),
                'balance'   => round($balance, 2),
                'polarity'  => $this->resolvePolarity(
                    $positive,
                    $critical,
                    $neutral
                ),
                'sources'   => array_values($items),
            ];
        }

        uasort(
            $result,
            static fn(array $a, array $b): int =>
                $b['intensity'] <=> $a['intensity']
        );

        return $result;
    }

    private function resolvePolarity(
        float $positive,
        float $critical,
        float $neutral
    ): string {
        if ($positive === 0.0 && $critical === 0.0) {
            return 'neutral';
        }

        if ($positive > 0.0 && $critical > 0.0) {
            return 'mixed';
        }

        if ($positive > $critical) {
            return 'positive';
        }

        if ($critical > $positive) {
            return 'critical';
        }

        return $neutral > 0.0 ? 'neutral' : 'mixed';
    }
}
