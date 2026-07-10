<?php
declare(strict_types=1);

require_once __DIR__.'/ForecastContextEngine.php';
require_once __DIR__.'/DignityIntegrationEngine.php';
require_once __DIR__.'/AspectEngine.php';
require_once __DIR__.'/AspectInterpretationEngine.php';
require_once __DIR__.'/RetrogradeEngine.php';
require_once __DIR__.'/SolarConditionEngine.php';

final class AdvancedContextEngine
{
    private ForecastContextEngine $context;
    private DignityIntegrationEngine $dignity;
    private AspectEngine $aspects;
    private AspectInterpretationEngine $aspectInterpret;
    private RetrogradeEngine $retrograde;
    private SolarConditionEngine $solar;

    public function __construct()
    {
        $this->context         = new ForecastContextEngine();
        $this->dignity         = new DignityIntegrationEngine();
        $this->aspects         = new AspectEngine();
        $this->aspectInterpret = new AspectInterpretationEngine();
        $this->retrograde      = new RetrogradeEngine();
        $this->solar           = new SolarConditionEngine();
    }

    public function analyze(array $temaRS): array
    {
        $aspects = $this->aspects->calculate($temaRS);

        return [
            'structure' => $this->context->analyze($temaRS),

            'dignities' => 
                $this->dignity->calculate($temaRS),

            'aspects' =>
                $aspects,

            'aspect_texts' =>
                $this->aspectInterpret->interpret($aspects),

            'retrograde' =>
                $this->retrograde->calculate($temaRS),

            'solar' =>
                $this->solar->calculate($temaRS),
        ];
    }
}
