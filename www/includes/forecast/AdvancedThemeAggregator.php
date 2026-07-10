<?php
declare(strict_types=1);

require_once __DIR__.'/../atlas/AtlasLoader.php';
require_once __DIR__.'/ThemeMap.php';
require_once __DIR__.'/AdvancedContextEngine.php';
require_once __DIR__.'/AspectScoreEngine.php';
require_once __DIR__.'/PlanetResolver.php';

final class AdvancedThemeAggregator
{
    private array $atlas;
    private AdvancedContextEngine $context;
    private AspectScoreEngine $aspectScore;

    public function __construct()
    {
        $this->atlas = AtlasLoader::load();
        $this->context = new AdvancedContextEngine();
        $this->aspectScore = new AspectScoreEngine();
    }

    public function aggregate(array $temaRS): array
    {
        $scores = [];
        $contributions = [];

        $context = $this->context->analyze($temaRS);

        foreach (($temaRS['pianeti'] ?? []) as $planet => $info) {

            $p = PlanetResolver::normalized($planet, $info);

            if ($p === null) {
                continue;
            }

            $house = (int)($info['casa'] ?? 0);

            if (!isset($this->atlas[$p][$house]['themes'])) {
                continue;
            }

            $strength = 1.0;

            $strength *=
                (float)($context['dignities'][$p]['coefficient'] ?? 1.0);

            if (isset($context['structure']['angular_planets'][$p])) {
                $strength *=
                    (float)$context['structure']['angular_planets'][$p]['factor'];
            }

            foreach ($this->atlas[$p][$house]['themes'] as $theme => $weight) {

                $theme = ThemeMap::normalize((string)$theme);

                $value = (float)$weight * $strength;

                $scores[$theme] =
                    ($scores[$theme] ?? 0)
                    + $value;

                $contributions[$theme][] = [
                    'planet'   => $p,
                    'house'    => $house,
                    'value'    => round($value,2),
                ];
            }
        }

        $scores = $this->aspectScore->apply(
            $scores,
            $context['aspects'] ?? []
        );

        arsort($scores);

        return [
            'scores'        => $scores,
            'context'       => $context,
            'contributions' => $contributions,
        ];
    }
}
