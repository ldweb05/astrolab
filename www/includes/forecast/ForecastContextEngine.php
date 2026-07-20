<?php
declare(strict_types=1);

require_once __DIR__.'/StelliumDetector.php';
require_once __DIR__.'/HouseDominanceEngine.php';
require_once __DIR__.'/AngularPowerEngine.php';

final class ForecastContextEngine
{
    private StelliumDetector $stelliumDetector;
    private HouseDominanceEngine $houseDominance;
    private AngularPowerEngine $angularPower;

    public function __construct()
    {
        $this->stelliumDetector = new StelliumDetector();
        $this->houseDominance   = new HouseDominanceEngine();
        $this->angularPower     = new AngularPowerEngine();
    }

    public function analyze(array $temaRS): array
    {
        $stelliums = $this->stelliumDetector->detect($temaRS);
        $houses    = $this->houseDominance->analyze($temaRS);
        $angular   = $this->angularPower->calculate($temaRS);

        return [
            'stelliums'       => $stelliums,
            'dominant_houses' => array_slice($houses, 0, 3, true),
            'angular_planets' => array_filter(
                $angular,
                static fn(array $data): bool =>
                    (float)($data['factor'] ?? 1.0) > 1.0
            ),
        ];
    }
}
