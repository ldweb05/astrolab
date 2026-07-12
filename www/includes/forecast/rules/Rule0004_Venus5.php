<?php
declare(strict_types=1);

final class Rule0004_Venus5
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['venere'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 5) {
            return;
        }

        $conditionId = (string)(
            $condition['condition_id']
            ?? ''
        );

        $strength = (float)(
            $condition['strength']
            ?? 1.0
        );

        foreach ([
            ['theme' => 'amore', 'priority' => 90, 'strength' => 88],
            ['theme' => 'creativita', 'priority' => 86, 'strength' => 82],
        ] as $definition) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0004',
                    $definition['theme'],
                    $definition['priority'],
                    $definition['strength'] * $strength,
                    [
                        'planet' => 'venere',
                        'house' => 5,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
