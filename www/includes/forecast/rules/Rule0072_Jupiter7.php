<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/JupiterAtlas.php';

final class Rule0072_Jupiter7 implements AstrologyRuleInterface
{
    public function apply(array $planetConditions, EvidenceEngine $engine): void
    {
        $condition = $planetConditions['giove'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 7) {
            return;
        }

        $definition = JupiterAtlas::houses()[7] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0072',
                    (string)$theme,
                    (int)$definition['priority'],
                    (float)$weight * $strength,
                    [
                        'planet' => 'giove',
                        'house' => 7,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
