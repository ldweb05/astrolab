<?php
declare(strict_types=1);

final class Rule0008_Neptune9
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['nettuno'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 9) {
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
                'RULE-0008',
                'spiritualita',
                82,
                78 * $strength,
                [
                    'planet' => 'nettuno',
                    'house' => 9,
                    'condition_id' => $conditionId,
                ]
            )
        );
    }
}
