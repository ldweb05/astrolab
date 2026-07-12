<?php
declare(strict_types=1);

final class Rule0002_Saturn12
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['saturno'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 12) {
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
                'RULE-0002',
                'prove',
                95,
                90 * $strength,
                [
                    'planet' => 'saturno',
                    'house' => 12,
                    'condition_id' => $conditionId,
                ]
            )
        );
    }
}
