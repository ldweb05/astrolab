<?php
declare(strict_types=1);

require_once __DIR__.'/ForecastContextEngine.php';
require_once __DIR__.'/DignityIntegrationEngine.php';
require_once __DIR__.'/AspectEngine.php';
require_once __DIR__.'/RetrogradeEngine.php';
require_once __DIR__.'/SolarConditionEngine.php';

final class AdvancedContextEngine
{
    private ForecastContextEngine $context;
    private DignityIntegrationEngine $dignity;
    private AspectEngine $aspects;
    private RetrogradeEngine $retrograde;
    private SolarConditionEngine $solar;

    public function __construct()
    {
        $this->context    = new ForecastContextEngine();
        $this->dignity    = new DignityIntegrationEngine();
        $this->aspects    = new AspectEngine();
        $this->retrograde = new RetrogradeEngine();
        $this->solar      = new SolarConditionEngine();
    }

    public function analyze(array $temaRS): array
    {
        return [
            'structure'  => $this->context->analyze($temaRS),
            'dignities'  => $this->dignity->calculate($temaRS),
            'aspects'    => $this->aspects->calculate($temaRS),
            'retrograde' => $this->retrograde->calculate($temaRS),
            'solar'      => $this->solar->calculate($temaRS),
        ];
    }
}
