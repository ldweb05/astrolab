<?php
declare(strict_types=1);

require_once __DIR__.'/AdvancedThemeAggregator.php';
require_once __DIR__.'/ThemeRating.php';
require_once __DIR__.'/ThemePresenter.php';
require_once __DIR__.'/ThemePolarityEngine.php';

final class ForecastEngineV3
{
    private AdvancedThemeAggregator $aggregator;
    private ThemePolarityEngine $polarity;

    public function __construct()
    {
        $this->aggregator = new AdvancedThemeAggregator();
        $this->polarity   = new ThemePolarityEngine();
    }

    public function generate(array $temaRS): array
    {
        $result = $this->aggregator->aggregate($temaRS);

        $polarities = $this->polarity->build($result['contributions'] ?? []);

        $paragraphs = [];

        foreach ($result['scores'] as $theme => $score) {

            $paragraphs[] = [
                'theme' => $theme,
                'score' => (int)round($score),
                'rating' => ThemeRating::stars((int)round($score)),
            ];
        }

        return [
            'scores'         => $result['scores'],
            'paragraphs'     => ThemePresenter::present($paragraphs),
            'polarities'     => $polarities,
            'context'        => array_merge(
                $result['context'],
                [
                    'planets' => $temaRS['pianeti'] ?? []
                ]
            ),

            // Contratto interno V3.1
            'contributions'  => $result['contributions'] ?? [],
        ];
    }
}
