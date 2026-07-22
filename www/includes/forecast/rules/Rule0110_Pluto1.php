<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/PlutoAtlas.php';

final class Rule0110_Pluto1 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['plutone'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 1) {
            return;
        }

        $definition = PlutoAtlas::houses()[1] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0110',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'plutone',
                        'house' => 1,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
