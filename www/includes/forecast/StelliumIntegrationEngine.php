<?php
declare(strict_types=1);

require_once __DIR__.'/StelliumDetector.php';

final class StelliumIntegrationEngine
{
    private StelliumDetector $detector;

    public function __construct()
    {
        $this->detector = new StelliumDetector();
    }


    public function calculate(array $temaRS): array
    {
        $stelliums = $this->detector->detect($temaRS);

        $result = [];

        foreach ($stelliums as $stellium) {

            $house = (int)$stellium['house'];

            $result[$house] = [
                'planets' => $stellium['planets'] ?? [],
                'count'   => $stellium['count'] ?? 0,
                'factor'  => $stellium['weight'] ?? 1.0,
            ];
        }

        return $result;
    }
}
