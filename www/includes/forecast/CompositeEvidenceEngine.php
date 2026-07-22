<?php
declare(strict_types=1);

final class CompositeEvidenceEngine
{
    public function build(
        array $evidences,
        array $planetConditions = []
    ): array {
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
                    'condition_ids' => array_values(array_filter([
                        $planetConditions['sole']['condition_id'] ?? '',
                        $planetConditions['giove']['condition_id'] ?? '',
                    ])),
                    'data' => [
                        'planets' => ['sole', 'giove'],
                        'house' => (int)$house,
                    ],
                ];
            }
        }

        $mars = $planetConditions['marte'] ?? null;
        $saturn = $planetConditions['saturno'] ?? null;

        if (
            is_array($mars)
            && is_array($saturn)
            && (int)($mars['house'] ?? 0) === 6
            && (int)($saturn['house'] ?? 0) === 12
        ) {
            foreach ([
                ['theme' => 'salute', 'priority' => 118],
                ['theme' => 'prove', 'priority' => 116],
            ] as $definition) {
                $result[] = [
                    'code' => 'COMPOSITE_MARS6_SATURN12',
                    'planet' => 'marte+saturno',
                    'house' => 0,
                    'theme' => $definition['theme'],
                    'weight' => 115.0,
                    'strength' => 1.0,
                    'value' => 115.0,
                    'source' => 'composite',
                    'rule_id' => 'RULE_COMPOSITE_MARS6_SATURN12',
                    'priority' => $definition['priority'],
                    'condition_ids' => array_values(array_filter([
                        $mars['condition_id'] ?? '',
                        $saturn['condition_id'] ?? '',
                    ])),
                    'data' => [
                        'planets' => ['marte', 'saturno'],
                        'houses' => [6, 12],
                    ],
                ];
            }
        }

        return $result;
    }
}
