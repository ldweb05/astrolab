<?php
declare(strict_types=1);

require_once __DIR__.'/ForecastEngineV2.php';
require_once __DIR__.'/SourceCollector.php';
require_once __DIR__.'/TextTemplates.php';
require_once __DIR__.'/ThemePresenter.php';
require_once __DIR__.'/ThemePolarityAggregator.php';

final class ForecastEngineV3
{
    private ForecastEngineV2 $engine;
    private SourceCollector $sources;
    private ThemePolarityAggregator $polarity;

    public function __construct()
    {
        $this->engine   = new ForecastEngineV2();
        $this->sources  = new SourceCollector();
        $this->polarity = new ThemePolarityAggregator();
    }

    public function generate(array $temaRS): array
    {
        $data       = $this->engine->generate($temaRS);
        $fonti      = $this->sources->collect($temaRS);
        $polarities = $this->polarity->aggregate($temaRS);

        foreach ($data['paragraphs'] as &$p) {
            $tema = (string)$p['theme'];

            $p['text'] = TextTemplates::INTRO[$tema]
                ?? ('L\'anno evidenzia il tema "' . $tema . '".');

            $p['sources'] = $fonti[$tema] ?? [];

            $p['polarity'] = $polarities[$tema]['polarity'] ?? 'mixed';
            $p['positive'] = $polarities[$tema]['positive'] ?? 0.0;
            $p['critical'] = $polarities[$tema]['critical'] ?? 0.0;
            $p['neutral']  = $polarities[$tema]['neutral'] ?? 0.0;
            $p['balance']  = $polarities[$tema]['balance'] ?? 0.0;
        }
        unset($p);

        $data['paragraphs'] = ThemePresenter::present($data['paragraphs']);
        $data['polarities'] = $polarities;

        return $data;
    }
}
