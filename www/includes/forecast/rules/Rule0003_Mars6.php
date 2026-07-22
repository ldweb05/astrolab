<?php
declare(strict_types=1);

final class Rule0003_Mars6 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['marte'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 6) {
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

        $engine->add(
            AARuleEngine::evidence(
                'RULE-0003',
                'salute',
                92,
                85 * $strength,
                [
                    'planet' => 'marte',
                    'house' => 6,
                    'condition_id' => $conditionId,
                ]
            )
        );
    }
}
