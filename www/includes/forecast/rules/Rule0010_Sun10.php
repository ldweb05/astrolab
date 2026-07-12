<?php
declare(strict_types=1);

final class Rule0010_Sun10 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['sole'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 10) {
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
                'RULE-0010',
                'carriera',
                94,
                90 * $strength,
                [
                    'planet' => 'sole',
                    'house' => 10,
                    'condition_id' => $conditionId,
                ]
            )
        );
    }
}
