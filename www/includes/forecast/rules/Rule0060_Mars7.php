<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/MarsAtlas.php';

final class Rule0060_Mars7 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['marte'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 7) {
            return;
        }

        $definition = MarsAtlas::houses()[7] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0060',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'marte',
                        'house' => 7,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
