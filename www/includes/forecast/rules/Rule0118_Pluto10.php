<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/PlutoAtlas.php';

final class Rule0118_Pluto10 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['plutone'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 10) {
            return;
        }

        $definition = PlutoAtlas::houses()[10] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0118',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'plutone',
                        'house' => 10,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
