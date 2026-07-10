<?php
declare(strict_types=1);

require_once __DIR__.'/AdvancedThemeAggregator.php';
require_once __DIR__.'/ThemeRating.php';
require_once __DIR__.'/ThemePresenter.php';

final class ForecastEngineV3
{
    private AdvancedThemeAggregator $aggregator;

    public function __construct()
    {
        $this->aggregator = new AdvancedThemeAggregator();
    }

    public function generate(array $temaRS): array
    {
        $result = $this->aggregator->aggregate($temaRS);

        $paragraphs = [];

        foreach ($result['scores'] as $theme => $score) {

            $paragraphs[] = [
                'theme' => $theme,
                'score' => (int)round($score),
                'rating' => ThemeRating::stars((int)round($score)),
            ];
        }

        return [
            'scores'     => $result['scores'],
            'paragraphs' => ThemePresenter::present($paragraphs),
            'context'    => $result['context'],
        ];
    }
}
