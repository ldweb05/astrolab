<?php
declare(strict_types=1);

require_once __DIR__.'/ThemeAggregator.php';
require_once __DIR__.'/NarrativeEngine.php';

final class ForecastEngineV2
{
    private ThemeAggregator $aggregator;
    private NarrativeEngine $narrator;

    public function __construct()
    {
        $this->aggregator = new ThemeAggregator();
        $this->narrator   = new NarrativeEngine();
    }

    public function generate(array $temaRS): array
    {
        $scores = $this->aggregator->aggregate($temaRS);

        return [
            'scores'     => $scores,
            'paragraphs' => $this->narrator->build($scores),
        ];
    }
}
