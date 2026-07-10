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
            'annual_story' => [
                'opening' => '',
                'themes' => [],
                'closing' => ''
            ],
        ];

        $result['headline'] = $this->dominant->headline($dominant);

        $result['annual_story']['opening'] =
            $this->composeOpening($dominant);

        foreach ($dominant as $name => $theme) {

            $item = [
                'theme' => $name,
                'polarity' => $theme['polarity'] ?? 'neutral',
                'role' => $theme['role'] ?? '',
                'explanation' => $theme['explanation'] ?? '',
                'reason' => $this->reasonFromSources($forecast, $name),
                'symbolic_notes' => $this->symbolicNotes($forecast, $name),
                'context_notes' => $this->contextNotes($forecast, $name),
                'text' => '',
            ];

            $item['text'] = $this->composeThemeNarrative(
                $name,
                $theme,
                $item
            );

            if (($theme['polarity'] ?? 'neutral') === 'positive') {
                $result['opportunities'][] = $name;
            }

            if (($theme['polarity'] ?? 'neutral') === 'critical') {
                $result['challenges'][] = $name;
            }

            $result['dominant_themes'][] = $item;
        }

        foreach (($forecast['contributions'] ?? []) as $theme => $data) {
            $result['technical_notes'][$theme] = $data;
        }

        $result['annual_story']['themes'] =
            array_map(
                static function(array $theme): array {
                    return [
                        'theme' => $theme['theme'],
                        'text'  => $theme['text']
                    ];
                },
                array_slice($result['dominant_themes'], 0, 3)
            );

        $result['annual_story']['closing'] =
            $this->composeClosing($result);

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


    private function composeOpening(array $dominant): string
    {
        if (!$dominant) {
            return '';
        }

        $first = array_key_first($dominant);

        return match($first) {
            'carriera' =>
                'L\'anno presenta una forte concentrazione sui processi di realizzazione personale, crescita e sviluppo dei propri obiettivi.',

            'amore' =>
                'L\'anno evidenzia una particolare attenzione alle dinamiche affettive, relazionali e ai valori personali.',

            'trasformazione' =>
                'L\'anno apre una fase di cambiamento profondo e di ridefinizione di alcuni aspetti importanti della propria esperienza.',

            default =>
                'L\'anno presenta alcune direttrici principali che guideranno il percorso evolutivo del periodo.'
        };
    }


    private function composeClosing(array $result): string
    {
        $opportunities = count($result['opportunities'] ?? []);
        $challenges = count($result['challenges'] ?? []);

        if ($challenges > $opportunities) {
            return 'Il periodo richiede particolare consapevolezza nella gestione delle esperienze, trasformando le prove in occasioni di crescita.';
        }

        return 'Il periodo invita a valorizzare le opportunità presenti mantenendo equilibrio e capacità di adattamento.';
    }


    private function composeThemeNarrative(
        string $theme,
        array $data,
        array $item
    ): string {

        $role = $data['role'] ?? $theme;
        $polarity = $data['polarity'] ?? 'neutral';

        $planets = '';

        if (!empty($item['reason'])) {
            $planets = rtrim(trim($item['reason']), '.');
            $planets = str_replace(
                'I fattori simbolici principali coinvolgono',
                'Le configurazioni di',
                $planets
            );
        }

        $concepts = [];

        foreach (($item['symbolic_notes'] ?? []) as $values) {
            foreach ($values as $value) {
                $concepts[] = $value;
            }
        }

        $concepts = array_values(array_unique($concepts));

        $symbolText = '';

        if ($concepts) {
            $symbolText =
                ' richiamano ' .
                implode(', ', array_slice($concepts, 0, 3)) .
                '.';
        }

        $contextText = '';

        if (!empty($item['context_notes'])) {
            $contextText =
                ' Il contesto evolutivo riguarda ' .
                implode(', ', array_slice(
                    array_unique($item['context_notes']),
                    0,
                    3
                )) .
                '.';
        }

        $opening = match($theme) {
            'carriera' => 'La realizzazione personale e professionale',
            'studio' => 'L\'apprendimento e lo sviluppo',
            'salute' => 'L\'equilibrio personale e la gestione delle energie',
            'amore' => 'La vita affettiva e relazionale',
            default => ucfirst($role)
        };

        return match($polarity) {

            'positive' =>
                $opening .
                ' emerge come uno dei temi centrali dell\'anno. ' .
                $planets .
                $symbolText .
                $contextText,

            'critical' =>
                $opening .
                ' richiede attenzione e consapevolezza. ' .
                'Può rappresentare un percorso di maturazione e trasformazione. ' .
                $planets .
                $symbolText .
                $contextText,

            default =>
                $opening .
                ' rappresenta un ambito di crescita progressiva. ' .
                $planets .
                $symbolText .
                $contextText
        };
    }

    private function contextNotes(array $forecast, string $theme): array
    {
        $sources = $forecast['contributions'][$theme] ?? [];

        if (!$sources) {
            return [];
        }

        $planets = [];

        foreach ($sources as $source) {

            if (!empty($source['planet'])) {
                $planet = ucfirst($source['planet']);

                $planets[$planet] = [
                    'casa' => $source['house'] ?? null
                ];
            }
        }

        if (!$planets) {
            return [];
        }

        $engine = new PlanetarySymbolEngine();

        $interpreted = $engine->interpretWithContext($planets);

        $out = [];

        foreach ($interpreted as $data) {

            foreach (($data['context'] ?? []) as $item) {
                $out[] = $item;
            }
        }

        return array_values(array_unique($out));
    }
}
