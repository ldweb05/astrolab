<?php
declare(strict_types=1);

require_once __DIR__.'/AARules.php';

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
        return [
            'events' => [],
            'priorities' => [],
            'planet_conditions' => [],
            'theme_modifiers' => [],
            'evidences' => [],
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
