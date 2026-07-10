<?php
declare(strict_types=1);

require_once __DIR__.'/DominantThemeEngine.php';
require_once __DIR__.'/PlanetarySymbolEngine.php';

final class NarrativeComposer
{
    private DominantThemeEngine $dominant;
    private PlanetarySymbolEngine $symbols;

    public function __construct()
    {
        $this->dominant = new DominantThemeEngine();
        $this->symbols  = new PlanetarySymbolEngine();
    }

    public function compose(array $forecast): array
    {
        $themes = $forecast['polarities'] ?? [];

        $dominant = $this->dominant->extract($themes);

        $result = [
            'headline' => '',
            'dominant_themes' => [],
            'opportunities' => [],
            'challenges' => [],
            'technical_notes' => [],
        ];

        $result['headline'] = $this->dominant->headline($dominant);

        foreach ($dominant as $name => $theme) {

            $item = [
                'theme' => $name,
                'polarity' => $theme['polarity'] ?? 'neutral',
                'role' => $theme['role'] ?? '',
                'explanation' => $theme['explanation'] ?? '',
                'reason' => $this->reasonFromSources($forecast, $name),
                'symbolic_notes' => $this->symbolicNotes($forecast, $name),
                'text' => '',
            ];

            switch ($theme['polarity'] ?? 'neutral') {

                case 'positive':
                    $item['text'] =
                        'Il tema della ' . ($theme['role'] ?? $name) .
                        ' emerge come una delle aree più dinamiche dell\'anno. ' .
                        'Le configurazioni presenti suggeriscono possibilità di crescita, sviluppo e consolidamento.' .
                        ($item['reason'] ?? '');
                    $result['opportunities'][] = $name;
                    break;

                case 'critical':
                    $item['text'] =
                        'Il tema della ' . ($theme['role'] ?? $name) .
                        ' richiede maggiore consapevolezza e capacità di gestione. ' .
                        'Può rappresentare un\'area di maturazione attraverso esperienze significative.';
                    $result['challenges'][] = $name;
                    break;

                default:
                    $item['text'] =
                        'Il tema della ' . ($theme['role'] ?? $name) .
                        ' presenta opportunità da sviluppare mantenendo equilibrio e attenzione.';
            }

            $result['dominant_themes'][] = $item;
        }

        foreach (($forecast['contributions'] ?? []) as $theme => $data) {
            $result['technical_notes'][$theme] = $data;
        }

        return $result;
    }

    private function reasonFromSources(array $forecast, string $theme): string
    {
        $sources = $forecast['contributions'][$theme] ?? [];

        if (!$sources) {
            return '';
        }

        $planets = [];

        foreach ($sources as $source) {
            if (!empty($source['planet'])) {
                $planets[] = ucfirst($source['planet']);
            }
        }

        $planets = array_values(array_unique($planets));

        if (!$planets) {
            return '';
        }

        return ' I fattori simbolici principali coinvolgono ' .
            implode(', ', $planets) . '.';
    }


    private function symbolicNotes(array $forecast, string $theme): array
    {
        $sources = $forecast['contributions'][$theme] ?? [];

        $planets = [];

        foreach ($sources as $source) {
            if (!empty($source['planet'])) {
                $planets[] = strtolower($source['planet']);
            }
        }

        $planets = array_values(array_unique($planets));

        return $this->symbols->interpret($planets);
    }

}
