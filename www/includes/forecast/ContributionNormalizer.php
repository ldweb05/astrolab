<?php
declare(strict_types=1);

/**
 * Aggrega i contributi dello stesso pianeta, nella stessa casa
 * e provenienti dalla stessa sorgente, mantenendoli separati per tema.
 */
final class ContributionNormalizer
{
    public function normalize(array $contributions): array
    {
        $normalized = [];

        foreach ($contributions as $theme => $items) {
            $groups = [];

            foreach ($items as $item) {
                $planet = (string)($item['planet'] ?? '');
                $house  = (int)($item['house'] ?? 0);
                $source = (string)($item['source'] ?? 'unknown');

                if ($planet === '' || $house < 1 || $house > 12) {
                    continue;
                }

                $key = $planet.'|'.$house.'|'.$source;

                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'planet'        => $planet,
                        'house'         => $house,
                        'source'        => $source,
                        'count'         => 0,
                        'total_weight'  => 0.0,
                        'total_value'   => 0.0,
                        'strength_sum'  => 0.0,
                        'weights'       => [],
                    ];
                }

                $weight   = (float)($item['weight'] ?? 0.0);
                $strength = (float)($item['strength'] ?? 1.0);
                $value    = (float)($item['value'] ?? 0.0);

                $groups[$key]['count']++;
                $groups[$key]['total_weight'] += $weight;
                $groups[$key]['total_value'] += $value;
                $groups[$key]['strength_sum'] += $strength;
                $groups[$key]['weights'][] = $weight;
            }

            foreach ($groups as &$group) {
                $count = max(1, (int)$group['count']);

                $group['total_weight'] = round($group['total_weight'], 2);
                $group['total_value'] = round($group['total_value'], 2);
                $group['average_strength'] = round(
                    $group['strength_sum'] / $count,
                    3
                );

                unset($group['strength_sum']);

                $group['weights'] = array_values(array_unique(
                    array_map(
                        static fn(float $weight): float => round($weight, 2),
                        $group['weights']
                    )
                ));
            }
            unset($group);

            uasort(
                $groups,
                static fn(array $a, array $b): int =>
                    $b['total_value'] <=> $a['total_value']
            );

            $normalized[(string)$theme] = array_values($groups);
        }

        return $normalized;
    }
}
