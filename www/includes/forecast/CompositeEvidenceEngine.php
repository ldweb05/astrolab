<?php
declare(strict_types=1);

final class CompositeEvidenceEngine
{
    public function build(array $evidences): array
    {
        $result = $evidences;
        $byHouse = [];

        foreach ($evidences as $evidence) {
            $planet = strtolower((string)($evidence['planet'] ?? ''));
            $house = (int)($evidence['house'] ?? 0);

            if ($planet === '' || $house < 1 || $house > 12) {
                continue;
            }

            $byHouse[$house][$planet] = true;
        }

        foreach ($byHouse as $house => $planets) {
            if (isset($planets['sole'], $planets['giove'])) {
                $result[] = [
                    'code' => 'COMPOSITE_SUN_JUPITER_SAME_HOUSE',
                    'planet' => 'sole+giove',
                    'house' => (int)$house,
                    'theme' => 'carriera',
                    'weight' => 120.0,
                    'strength' => 1.0,
                    'value' => 120.0,
                    'source' => 'composite',
                    'rule_id' => 'RULE_COMPOSITE_SUN_JUPITER_SAME_HOUSE',
                    'priority' => 120,
                    'data' => [
                        'planets' => ['sole', 'giove'],
                        'house' => (int)$house,
                    ],
                ];
            }
        }

        return $result;
    }
}
