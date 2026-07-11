<?php
declare(strict_types=1);

require_once __DIR__.'/../atlas/AtlasLoader.php';
require_once __DIR__.'/ThemeMap.php';
require_once __DIR__.'/AdvancedContextEngine.php';
require_once __DIR__.'/AspectScoreEngine.php';
require_once __DIR__.'/PlanetResolver.php';
require_once __DIR__.'/CompositeEvidenceEngine.php';
require_once __DIR__.'/ContributionNormalizer.php';
require_once __DIR__.'/EvidenceBuilder.php';

final class AdvancedThemeAggregator
{
    private array $atlas;
    private AdvancedContextEngine $context;
    private AspectScoreEngine $aspectScore;
    private CompositeEvidenceEngine $compositeEvidence;
    private ContributionNormalizer $normalizer;
    private EvidenceBuilder $builder;

    public function __construct()
    {
        $this->atlas = AtlasLoader::load();
        $this->context = new AdvancedContextEngine();
        $this->aspectScore = new AspectScoreEngine();
        $this->compositeEvidence = new CompositeEvidenceEngine();
        $this->normalizer = new ContributionNormalizer();
        $this->builder = new EvidenceBuilder();
    }

    public function aggregate(array $temaRS): array
    {
        $scores = [];
        $contributions = [];
        $evidences = [];

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

                $evidences[] = [
                    'planet'   => $p,
                    'house'    => $house,
                    'theme'    => $theme,
                    'weight'   => (float)$weight,
                    'strength' => round($strength,3),
                    'value'    => round($value,2),
                ];

                $scores[$theme] =
                    ($scores[$theme] ?? 0)
                    + $value;

                $contributions[$theme][] = [
                    'planet'   => $p,
                    'house'    => $house,
                    'weight'   => (float)$weight,
                    'strength' => round($strength,3),
                    'value'    => round($value,2),
                    'source'   => 'atlas',
                ];
            }
        }

        $scores = $this->aspectScore->apply(
            $scores,
            $context['aspects'] ?? []
        );

        arsort($scores);

        $evidences = $this->compositeEvidence->build(
            $evidences ?? []
        );

        return [
            'scores'                   => $scores,
            'context'                  => $context,
            'contributions'            => $contributions,
            'normalized_contributions' => $this->normalizer->normalize($contributions),
            'evidence_groups'          => $this->builder->build($contributions),
            'evidences'                => $evidences,
        ];
    }
}
