<?php
declare(strict_types=1);

require_once __DIR__.'/../atlas/AtlasLoader.php';
require_once __DIR__.'/ThemeMap.php';
require_once __DIR__.'/AngularPowerEngine.php';
require_once __DIR__.'/PlanetStrengthEngine.php';
require_once __DIR__.'/PlanetNature.php';
require_once __DIR__.'/PlanetResolver.php';

final class ThemePolarityAggregator
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
        $themes      = [];
        $angularData = $this->angularPower->calculate($temaRS);

        foreach (($temaRS['pianeti'] ?? []) as $nome => $info) {
            $planet = PlanetResolver::normalized($nome, $info);

            if ($planet === null) {
                continue;
            }

            $casa   = (int)($info['casa'] ?? 0);

            if (!isset($this->atlas[$planet][$casa]['themes'])) {
                continue;
            }

            $nature        = PlanetNature::value($planet);
            $angularFactor = (float)($angularData[$planet]['factor'] ?? 1.0);
            $zones         = $angularData[$planet]['zones'] ?? [];

            foreach ($this->atlas[$planet][$casa]['themes'] as $theme => $baseWeight) {
                $theme = ThemeMap::normalize((string)$theme);

                $weight = $this->planetStrength->intensity(
                    (float)$baseWeight,
                    $planet
                );

                $weight = $this->angularPower->apply(
                    $weight,
                    $angularFactor
                );

                if (!isset($themes[$theme])) {
                    $themes[$theme] = [
                        'intensity' => 0.0,
                        'positive'  => 0.0,
                        'critical'  => 0.0,
                        'neutral'   => 0.0,
                        'balance'   => 0.0,
                        'sources'   => [],
                    ];
                }

                $themes[$theme]['intensity'] += $weight;

                if ($nature > 0) {
                    $themes[$theme]['positive'] += $weight;
                    $themes[$theme]['balance']  += $weight;
                } elseif ($nature < 0) {
                    $themes[$theme]['critical'] += $weight;
                    $themes[$theme]['balance']  -= $weight;
                } else {
                    $themes[$theme]['neutral'] += $weight;
                }

                $source = ucfirst($planet).' in '.$casa.'ª casa';

                if ($zones !== []) {
                    $source .= ' — '.implode(', ', $zones);
                }

                $themes[$theme]['sources'][] = $source;
            }
        }

        foreach ($themes as &$theme) {
            foreach (['intensity', 'positive', 'critical', 'neutral', 'balance'] as $field) {
                $theme[$field] = round($theme[$field], 2);
            }

            $theme['sources'] = array_values(array_unique($theme['sources']));

            $theme['polarity'] = match (true) {
                $theme['balance'] > 50  => 'positive',
                $theme['balance'] < -50 => 'critical',
                default                 => 'mixed',
            };
        }
        unset($theme);

        uasort(
            $themes,
            static fn(array $a, array $b): int =>
                $b['intensity'] <=> $a['intensity']
        );

        return $themes;
    }
}
