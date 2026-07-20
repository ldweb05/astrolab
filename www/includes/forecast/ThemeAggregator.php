<?php
declare(strict_types=1);

require_once __DIR__.'/../atlas/AtlasLoader.php';
require_once __DIR__.'/ThemeMap.php';
require_once __DIR__.'/AngularPowerEngine.php';
require_once __DIR__.'/PlanetStrengthEngine.php';

final class ThemeAggregator
{
    private array $atlas;
    private AngularPowerEngine $angularPower;
    private PlanetStrengthEngine $planetStrength;

    public function __construct()
    {
        $this->atlas          = AtlasLoader::load();
        $this->angularPower   = new AngularPowerEngine();
        $this->planetStrength = new PlanetStrengthEngine();
    }

    public function aggregate(array $temaRS): array
    {
        $scores      = [];
        $angularData = $this->angularPower->calculate($temaRS);

        foreach (($temaRS['pianeti'] ?? []) as $nome => $info) {

            $planet = mb_strtolower((string)$nome);
            $casa   = (int)($info['casa'] ?? 0);

            if (!isset($this->atlas[$planet][$casa]['themes'])) {
                continue;
            }

            $fattoreAngolare = (float)($angularData[$planet]['factor'] ?? 1.0);

            foreach ($this->atlas[$planet][$casa]['themes'] as $tema => $peso) {

                $tema = ThemeMap::normalize((string)$tema);

                $peso = $this->planetStrength->apply($peso, $planet);
                $peso = $this->angularPower->apply($peso, $fattoreAngolare);

                $scores[$tema] = ($scores[$tema] ?? 0) + $peso;
            }
        }

        arsort($scores);

        return $scores;
    }
}
