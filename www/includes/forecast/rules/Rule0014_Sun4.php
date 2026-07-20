<?php
declare(strict_types=1);

require_once __DIR__.'/../../atlas/SunAtlas.php';

final class Rule0014_Sun4 implements AstrologyRuleInterface
{
    public function apply(array $planetConditions, EvidenceEngine $engine): void
    {
        $condition = $planetConditions['sole'] ?? null;

        if (!is_array($condition)) {
            return;
        }

        if ((int)($condition['house'] ?? 0) !== 4) {
            return;
        }

        $cfg = SunAtlas::houses()[4] ?? null;

        if (!is_array($cfg)) {
            return;
        }

        $conditionId = (string)($condition['condition_id'] ?? '');
        $strength = (float)($condition['strength'] ?? 1.0);

        foreach (($cfg['themes'] ?? []) as $theme => $weight) {
            $engine->add(
                AARuleEngine::evidence(
                    'RULE-0014',
                    (string)$theme,
                    (int)$cfg['priority'],
                    (float)$weight * $strength,
                    [
                        'planet'=>'sole',
                        'house'=>4,
                        'condition_id'=>$conditionId,
                    ]
                )
            );
        }
    }
}
