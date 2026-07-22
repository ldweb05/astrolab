<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/NeptuneAtlas.php';

final class Rule0106_Neptune8 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['nettuno'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 8) {
            return;
        }

        $definition = NeptuneAtlas::houses()[8] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0106',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'nettuno',
                        'house' => 8,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
