<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/SaturnAtlas.php';

final class Rule0081_Saturn5 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['saturno'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 5) {
            return;
        }

        $definition = SaturnAtlas::houses()[5] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0081',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'saturno',
                        'house' => 5,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
