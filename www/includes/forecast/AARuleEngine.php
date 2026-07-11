<?php
declare(strict_types=1);

require_once __DIR__.'/AARules.php';
require_once __DIR__.'/EvidenceEngine.php';

/**
 * Active Astrology Rule Engine
 *
 * Livello di dominio del Forecast V3.
 *
 * Produce esclusivamente evidenze astrologiche.
 */
final class AARuleEngine
{
    public function evaluate(array $temaRS): array
    {
        $engine = new EvidenceEngine();

        /*
         * RULE-0001
         * Giove in X Casa
         */
        foreach ($temaRS['planets'] ?? [] as $planet) {

            $name  = strtoupper((string)($planet['name'] ?? ''));
            $house = (int)($planet['house'] ?? 0);

            if ($name === 'JUPITER' && $house === 10) {

                $engine->add(
                    self::evidence(
                        'RULE-0001',
                        'career',
                        90,
                        80,
                        [
                            'planet' => 'JUPITER',
                            'house'  => 10,
                        ]
                    )
                );
            }
        }

        return [
            'events' => [],
            'priorities' => [],
            'planet_conditions' => [],
            'theme_modifiers' => [],
            'evidences' => $engine->all(),
        ];
    }

    /**
     * Formato standard di una evidenza astrologica.
     */
    public static function evidence(
        string $code,
        string $category,
        int $priority,
        float $strength,
        array $data = []
    ): array {
        return [
            'code' => $code,
            'category' => $category,
            'priority' => $priority,
            'strength' => round($strength, 2),
            'data' => $data,
        ];
    }
}
