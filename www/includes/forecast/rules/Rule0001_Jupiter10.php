<?php
declare(strict_types=1);

final class Rule0001_Jupiter10
{
    public function apply(array $temaRS, EvidenceEngine $engine): void
    {
        foreach ($temaRS['planets'] ?? [] as $planet) {

            $name  = strtoupper((string)($planet['name'] ?? ''));
            $house = (int)($planet['house'] ?? 0);

            if ($name !== 'JUPITER') {
                continue;
            }

            if ($house !== 10) {
                continue;
            }

            $engine->add(
                AARuleEngine::evidence(
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
}
