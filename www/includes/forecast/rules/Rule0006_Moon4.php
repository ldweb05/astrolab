<?php
declare(strict_types=1);

final class Rule0006_Moon4 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['luna'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 4) {
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
            ['theme' => 'casa', 'priority' => 90, 'strength' => 88],
            ['theme' => 'famiglia', 'priority' => 88, 'strength' => 86],
        ] as $definition) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0006',
                    $definition['theme'],
                    $definition['priority'],
                    $definition['strength'] * $strength,
                    [
                        'planet' => 'luna',
                        'house' => 4,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
