<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/JupiterAtlas.php';

final class Rule0073_Jupiter8 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['giove'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 8) {
            return;
        }

        $definition = JupiterAtlas::houses()[8] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0073',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'giove',
                        'house' => 8,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
