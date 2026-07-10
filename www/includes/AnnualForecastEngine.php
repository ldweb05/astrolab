<?php
declare(strict_types=1);

require_once __DIR__.'/forecast/ForecastEngineV3.php';
require_once __DIR__.'/forecast/NarrativeComposer.php';

final class AnnualForecastEngine
{
    private ForecastEngineV3 $engine;
    private NarrativeComposer $composer;

    public function __construct()
    {
        $this->engine   = new ForecastEngineV3();
        $this->composer = new NarrativeComposer();
    }

    public function genera(array $temaRS, array $valutazione): array
    {
        $forecast = $this->engine->generate($temaRS);
        $sintesi  = $this->composer->compose($forecast);

        return [
            'titolo'       => 'Previsione Annuale',
            'introduzione' => $this->introduzione($valutazione),
            'sintesi'      => $sintesi,
            'temi'         => array_slice($forecast['paragraphs'], 0, 8),
            'paragrafi'    => array_slice($forecast['paragraphs'], 0, 6),
            'scores'       => $forecast['scores'],
            'polarities'   => $forecast['polarities'] ?? [],
        ];
    }

    private function introduzione(array $valutazione): string
    {
        if (!empty($valutazione['veti'])) {
            return 'La Rivoluzione Solare presenta configurazioni che richiedono particolare prudenza. La previsione descrive i temi principali dell’anno senza considerarli eventi inevitabili.';
        }

        $stelle = (int)($valutazione['stelline'] ?? 0);

        return match (true) {
            $stelle >= 4 => 'L’anno appare complessivamente ricco di possibilità, con diversi settori capaci di offrire crescita e risultati concreti.',
            $stelle === 3 => 'L’anno presenta opportunità significative insieme ad alcune situazioni che richiederanno attenzione e capacità di adattamento.',
            default       => 'L’anno può richiedere prudenza, disciplina e una gestione consapevole delle energie e delle priorità.',
        };
    }
}
