<?php
declare(strict_types=1);

require_once __DIR__.'/forecast/AdvancedForecastEngine.php';
require_once __DIR__.'/forecast/FinalNarrativeEngine.php';

final class AnnualForecastEngine
{
    private AdvancedForecastEngine $engine;
    private FinalNarrativeEngine $narrative;

    public function __construct()
    {
        $this->engine    = new AdvancedForecastEngine();
        $this->narrative = new FinalNarrativeEngine();
    }

    public function genera(array $temaRS, array $valutazione = []): array
    {
        $forecast = $this->engine->generate($temaRS);

        $forecast['sintesi'] =
            $this->narrative->compose($forecast);

        $forecast['introduzione'] =
            $this->introduzione($valutazione);

        return $forecast;
    }


    private function introduzione(array $valutazione): string
    {
        if (!empty($valutazione['veti'])) {

            return
                'La Rivoluzione Solare evidenzia aree che richiedono attenzione e gestione consapevole delle energie.';
        }

        return
            'La previsione annuale integra posizioni planetarie, forza angolare, dignità, aspetti e dinamiche evolutive.';
    }
}
