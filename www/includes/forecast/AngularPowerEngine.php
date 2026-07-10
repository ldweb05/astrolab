<?php
declare(strict_types=1);

final class AngularPowerEngine
{
    public function calculate(array $temaRS): array
    {
        $mc = $this->extractLongitude($temaRS['case']['MC'] ?? null);

        if ($mc === null) {
            return [];
        }

        $result = [];

        foreach (($temaRS['pianeti'] ?? []) as $name => $planet) {
            $longitude = $this->extractLongitude($planet);

            if ($longitude === null) {
                continue;
            }

            $house = (int)($planet['casa'] ?? 0);
            $distanceBeforeMc = $this->forwardDistance($longitude, $mc);

            $factor = 1.0;
            $zone = null;

            /*
             * Zona Gauquelin lato IX:
             * pianeta formalmente in IX casa, immediatamente prima del MC.
             */
            if ($house === 9 && $distanceBeforeMc <= 5.0) {
                $factor = match (true) {
                    $distanceBeforeMc <= 1.0 => 2.00,
                    $distanceBeforeMc <= 3.0 => 1.75,
                    default                  => 1.50,
                };

                $zone = 'gauquelin_mc_pre_cusp';
            }

            $result[mb_strtolower((string)$name)] = [
                'factor'      => $factor,
                'zone'        => $zone,
                'distance_mc' => round($distanceBeforeMc, 4),
                'is_active'   => $factor > 1.0,
                'label'       => $factor > 1.0
                    ? sprintf(
                        '%s in zona Gauquelin prima del MC (%.2f°)',
                        ucfirst((string)$name),
                        $distanceBeforeMc
                    )
                    : null,
            ];
        }

        return $result;
    }

    public function apply(int|float $score, float $factor): float
    {
        return round($score * $factor, 2);
    }

    private function extractLongitude(mixed $data): ?float
    {
        if (!is_array($data) || !array_key_exists('longitudine', $data)) {
            return null;
        }

        $longitude = (float)$data['longitudine'];

        return $this->normalize($longitude);
    }

    private function normalize(float $degrees): float
    {
        $degrees = fmod($degrees, 360.0);

        if ($degrees < 0) {
            $degrees += 360.0;
        }

        return $degrees;
    }

    private function forwardDistance(float $from, float $to): float
    {
        return fmod(($to - $from + 360.0), 360.0);
    }
}
