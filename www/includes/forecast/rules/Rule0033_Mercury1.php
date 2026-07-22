<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/MercuryAtlas.php';

final class Rule0033_Mercury1 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['mercurio'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 1) {
            return;
        }

        $definition = MercuryAtlas::houses()[1] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0033',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'mercurio',
                        'house' => 1,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
