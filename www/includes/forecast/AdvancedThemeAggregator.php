<?php
declare(strict_types=1);

require_once __DIR__.'/../atlas/AtlasLoader.php';
require_once __DIR__.'/ThemeMap.php';
require_once __DIR__.'/AdvancedContextEngine.php';

final class AdvancedThemeAggregator
{
    private array $atlas;
    private AdvancedContextEngine $context;

    public function __construct()
    {
        $this->atlas = AtlasLoader::load();
        $this->context = new AdvancedContextEngine();
    }

    public function aggregate(array $temaRS): array
    {
        $scores = [];

        $context = $this->context->analyze($temaRS);

        foreach (($temaRS['pianeti'] ?? []) as $planet => $info) {

            $p = mb_strtolower((string)$planet);
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

                $scores[$theme] =
                    ($scores[$theme] ?? 0)
                    +
                    ((float)$weight * $strength);
            }
        }

        arsort($scores);

        return [
            'scores' => $scores,
            'context' => $context,
        ];
    }
}
