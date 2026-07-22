<?php
declare(strict_types=1);

final class StelliumDetector
{
    public function detect(array $temaRS): array
    {
        $houses = [];

        foreach (($temaRS['pianeti'] ?? []) as $planet => $info) {
            $house = (int)($info['casa'] ?? 0);

            if ($house < 1 || $house > 12) {
                continue;
            }

            $houses[$house][] = $planet;
        }

        $out = [];

        foreach ($houses as $house => $planets) {

            if (count($planets) < 3) {
                continue;
            }

            $out[] = [
                'house'   => $house,
                'count'   => count($planets),
                'planets' => array_values($planets),
                'weight'  => match(count($planets)) {
                    3 => 1.25,
                    4 => 1.50,
                    5 => 1.80,
                    default => 2.00
                }
            ];
        }

        usort(
            $out,
            static fn($a,$b)=>$b['count']<=>$a['count']
        );

        return $out;
    }
}
