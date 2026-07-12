<?php
declare(strict_types=1);

final class Rule0009_Pluto8
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['plutone'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 8) {
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
                'RULE-0009',
                'trasformazione',
                90,
                86 * $strength,
                [
                    'planet' => 'plutone',
                    'house' => 8,
                    'condition_id' => $conditionId,
                ]
            )
        );
    }
}
