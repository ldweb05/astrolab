<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/SaturnAtlas.php';

final class Rule0078_Saturn2 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['saturno'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 2) {
            return;
        }

        $definition = SaturnAtlas::houses()[2] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0078',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'saturno',
                        'house' => 2,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
