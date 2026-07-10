<?php
declare(strict_types=1);

final class AngularPowerEngine
{
    private const ORBS = [
        1.0 => 2.00,
        3.0 => 1.75,
        5.0 => 1.50,
    ];

    public function calculate(array $temaRS): array
    {
        $angles = [
            'ASC' => $this->lon($temaRS['case']['ASC'] ?? null),
            'MC'  => $this->lon($temaRS['case']['MC'] ?? null),
            'DSC' => isset($temaRS['case']['ASC'])
                ? $this->normalize($this->lon($temaRS['case']['ASC']) + 180)
                : null,
            'IC'  => isset($temaRS['case']['MC'])
                ? $this->normalize($this->lon($temaRS['case']['MC']) + 180)
                : null,
        ];

        $out = [];

        foreach (($temaRS['pianeti'] ?? []) as $nome => $p) {

            $planet = mb_strtolower($nome);

            $out[$planet] = [
                'factor' => 1.0,
                'zones'  => [],
            ];

            $lon  = $this->lon($p);
            $casa = (int)($p['casa'] ?? 0);

            if ($lon === null) {
                continue;
            }

            /*
             * MC
             */

            if ($casa === 9) {
                $d = $this->forwardDistance($lon, $angles['MC']);

                if ($f = $this->factor($d)) {
                    $out[$planet]['factor'] = max($out[$planet]['factor'], $f);
                    $out[$planet]['zones'][] = "Gauquelin MC (-{$d}°)";
                }
            }

            /*
             * ASC
             */

            if ($casa === 12) {
                $d = $this->forwardDistance($lon, $angles['ASC']);

                if ($f = $this->factor($d)) {
                    $out[$planet]['factor'] = max($out[$planet]['factor'], $f);
                    $out[$planet]['zones'][] = "Gauquelin ASC (-{$d}°)";
                }
            }

            /*
             * DSC
             */

            if ($casa === 6 && $angles['DSC'] !== null) {
                $d = $this->forwardDistance($lon, $angles['DSC']);

                if ($f = $this->factor($d)) {
                    $out[$planet]['factor'] = max($out[$planet]['factor'], $f);
                    $out[$planet]['zones'][] = "Gauquelin DSC (-{$d}°)";
                }
            }

            /*
             * IC
             */

            if ($casa === 3 && $angles['IC'] !== null) {
                $d = $this->forwardDistance($lon, $angles['IC']);

                if ($f = $this->factor($d)) {
                    $out[$planet]['factor'] = max($out[$planet]['factor'], $f);
                    $out[$planet]['zones'][] = "Gauquelin IC (-{$d}°)";
                }
            }
        }

        return $out;
    }

    public function apply(float|int $score, float $factor): float
    {
        return round($score * $factor, 2);
    }

    private function factor(float $distance): ?float
    {
        foreach (self::ORBS as $orb => $f) {
            if ($distance <= $orb) {
                return $f;
            }
        }

        return null;
    }

    private function lon(?array $x): ?float
    {
        if (!$x || !isset($x['longitudine'])) {
            return null;
        }

        return $this->normalize((float)$x['longitudine']);
    }

    private function normalize(float $x): float
    {
        $x = fmod($x,360);

        return $x < 0 ? $x+360 : $x;
    }

    private function forwardDistance(float $from,float $to): float
    {
        return round(fmod(($to-$from+360),360),2);
    }
}
