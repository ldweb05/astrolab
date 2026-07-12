<?php
declare(strict_types=1);

final class Rule0001_Jupiter10 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['giove'] ?? null;

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
                'RULE-0001',
                'carriera',
                90,
                80 * $strength,
                [
                    'planet' => 'giove',
                    'house' => 10,
                    'condition_id' => $conditionId,
                ]
            )
        );
    }
}
