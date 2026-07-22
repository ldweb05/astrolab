<?php
declare(strict_types=1);

final class HouseDominanceEngine
{
    public function analyze(array $temaRS): array
    {
        $houses = [];

        foreach (($temaRS['pianeti'] ?? []) as $planet => $info) {

            $house = (int)($info['casa'] ?? 0);

            if ($house < 1 || $house > 12) {
                continue;
            }

            $houses[$house]['count'] = ($houses[$house]['count'] ?? 0) + 1;
            $houses[$house]['planets'][] = $planet;
        }

        uasort(
            $houses,
            static fn($a,$b)=>$b['count']<=>$a['count']
        );

        return $houses;
    }
}
