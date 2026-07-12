<?php
declare(strict_types=1);

final class Rule0007_Uranus11
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['urano'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 11) {
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
                'RULE-0007',
                'amicizie',
                84,
                80 * $strength,
                [
                    'planet' => 'urano',
                    'house' => 11,
                    'condition_id' => $conditionId,
                ]
            )
        );
    }
}
