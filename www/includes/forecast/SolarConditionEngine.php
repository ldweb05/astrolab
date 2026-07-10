<?php
declare(strict_types=1);

final class SolarConditionEngine
{
    public function calculate(array $temaRS): array
    {
        $sun = null;

        foreach (($temaRS['pianeti'] ?? []) as $planet => $data) {
            if (mb_strtolower((string)$planet) === 'sole') {
                $sun = $data;
                break;
            }
        }

        if ($sun === null || !isset($sun['longitudine'])) {
            return [];
        }

        $out = [];

        foreach (($temaRS['pianeti'] ?? []) as $planet => $data) {

            if (mb_strtolower((string)$planet) === 'sole') {
                continue;
            }

            if (!isset($data['longitudine'])) {
                continue;
            }

            $distance = abs(
                (float)$sun['longitudine'] -
                (float)$data['longitudine']
            );

            if ($distance > 180) {
                $distance = 360 - $distance;
            }

            if ($distance <= 1) {

                $out[$planet] = [
                    'condition' => 'cazimi',
                    'factor'    => 1.40,
                    'distance'  => round($distance,2),
                ];

            } elseif ($distance <= 8) {

                $out[$planet] = [
                    'condition' => 'combusto',
                    'factor'    => 0.75,
                    'distance'  => round($distance,2),
                ];

            } elseif ($distance <= 17) {

                $out[$planet] = [
                    'condition' => 'sotto_raggi',
                    'factor'    => 0.90,
                    'distance'  => round($distance,2),
                ];
            }
        }

        return $out;
    }
}
