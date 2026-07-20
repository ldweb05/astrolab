<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/UranusAtlas.php';

final class Rule0098_Uranus12 implements AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void {
        $condition = $planetConditions['urano'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 12) {
            return;
        }

        $definition = UranusAtlas::houses()[12] ?? null;

        if (!is_array($definition)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);
        $priority = (int)($definition['priority'] ?? 0);

        foreach (($definition['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0098',
                    (string)$theme,
                    $priority,
                    (float)$weight * $strength,
                    [
                        'planet' => 'urano',
                        'house' => 12,
                        'condition_id' => $conditionId,
                    ]
                )
            );
        }
    }
}
