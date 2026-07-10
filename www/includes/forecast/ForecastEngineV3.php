<?php
declare(strict_types=1);

require_once __DIR__.'/ForecastEngineV2.php';
require_once __DIR__.'/SourceCollector.php';
require_once __DIR__.'/TextTemplates.php';

final class ForecastEngineV3
{
    private ForecastEngineV2 $engine;
    private SourceCollector $sources;

    public function __construct()
    {
        $this->engine  = new ForecastEngineV2();
        $this->sources = new SourceCollector();
    }

    public function generate(array $temaRS): array
    {
        $data   = $this->engine->generate($temaRS);
        $fonti  = $this->sources->collect($temaRS);

        foreach ($data['paragraphs'] as &$p) {

            $tema = $p['theme'];

            $p['text'] =
                TextTemplates::INTRO[$tema]
                ?? ucfirst($tema).'.';

            $p['sources'] = $fonti[$tema] ?? [];

        }

        return $data;
    }
}
