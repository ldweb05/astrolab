<?php
declare(strict_types=1);

require_once __DIR__.'/PlanetResolver.php';

final class PlanetConditionEngine
{
    public function build(array $temaRS, array $context): array
    {
        $conditions = [];

        foreach (($temaRS['pianeti'] ?? []) as $planet => $info) {
            $normalized = PlanetResolver::normalized(
                (string)$planet,
                is_array($info) ? $info : []
            );

            if ($normalized === null) {
                continue;
            }

            $house = (int)($info['casa'] ?? 0);

            if ($house < 1 || $house > 12) {
                continue;
            }

            $dignityCoefficient = (float)(
                $context['dignities'][$normalized]['coefficient']
                ?? 1.0
            );

            $angularFactor = (float)(
                $context['structure']['angular_planets'][$normalized]['factor']
                ?? 1.0
            );

            $retrograde = isset(
                $context['retrograde'][$normalized]
            );

            $conditionId = 'CONDITION_'.strtoupper(substr(
                hash('sha256', implode('|', [
                    $normalized,
                    (string)$house,
                    (string)$dignityCoefficient,
                    (string)$angularFactor,
                    $retrograde ? '1' : '0',
                ])),
                0,
                16
            ));

            $conditions[$normalized] = [
                'condition_id' => $conditionId,
                'planet' => $normalized,
                'house' => $house,
                'dignity_coefficient' => round($dignityCoefficient, 3),
                'angular_factor' => round($angularFactor, 3),
                'retrograde' => $retrograde,
                'strength' => round(
                    $dignityCoefficient * $angularFactor,
                    3
                ),
            ];
        }

        return $conditions;
    }
}
