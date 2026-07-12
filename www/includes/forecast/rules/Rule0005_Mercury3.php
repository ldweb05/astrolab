<?php
declare(strict_types=1);

final class Rule0005_Mercury3 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['mercurio'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 3) {
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
                'RULE-0005',
                'studio',
                88,
                84 * $strength,
                [
                    'planet' => 'mercurio',
                    'house' => 3,
                    'condition_id' => $conditionId,
                ]
            )
        );
    }
}
