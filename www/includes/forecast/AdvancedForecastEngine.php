<?php
declare(strict_types=1);

require_once __DIR__.'/ForecastEngineV3.php';

final class AdvancedForecastEngine
{
    private ForecastEngineV3 $forecast;

    public function __construct()
    {
        $this->forecast = new ForecastEngineV3();
    }

    public function generate(array $temaRS): array
    {
        $forecast = $this->forecast->generate($temaRS);

        $context = $forecast['context'] ?? [];

        $forecast['advanced'] = [
            'aspects'    => count($context['aspects'] ?? []),
            'retrograde' => count($context['retrograde'] ?? []),
            'solar'      => count($context['solar'] ?? []),
            'dignities'  => count($context['dignities'] ?? []),
        ];

        return $forecast;
    }
}
