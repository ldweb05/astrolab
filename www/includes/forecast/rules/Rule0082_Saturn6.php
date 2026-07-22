<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/SaturnAtlas.php';

final class Rule0082_Saturn6 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['saturno'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 6) {
            return;
        }

        $definition = SaturnAtlas::houses()[6] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0082',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'saturno',
                        'house' => 6,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
