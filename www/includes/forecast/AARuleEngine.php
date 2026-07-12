<?php
declare(strict_types=1);

require_once __DIR__.'/EvidenceEngine.php';
require_once __DIR__.'/RuleRegistry.php';

/**
 * Active Astrology Rule Engine.
 *
 * Riceve esclusivamente condizioni planetarie
 * e produce esclusivamente evidenze astrologiche.
 */
final class AARuleEngine
{
    public function evaluate(array $planetConditions): array
    {
        $engine = new EvidenceEngine();

        foreach (RuleRegistry::all() as $rule) {
            $rule->apply(
                $planetConditions,
                $engine
            );
        }

        return [
            'events' => [],
            'priorities' => [],
            'planet_conditions' => $planetConditions,
            'theme_modifiers' => [],
            'evidences' => $engine->all(),
        ];
    }

    public static function evidence(
        string $code,
        string $category,
        int $priority,
        float $strength,
        array $data = []
    ): array {
        return [
            'code' => $code,
            'rule_id' => $code,
            'category' => $category,
            'theme' => $category,
            'source' => 'rule',
            'priority' => $priority,
            'strength' => round($strength, 2),
            'condition_id' => (string)(
                $data['condition_id']
                ?? ''
            ),
            'data' => $data,
        ];
    }
}
