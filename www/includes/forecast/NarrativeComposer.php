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
                'advanced_notes' => $this->advancedNotes($forecast, $name),
                'aspect_notes' => $this->aspectNotes($forecast, $name),
                'text' => '',
            ];

            $item['text'] = $this->composeThemeNarrative(
                $name,
                $theme,
                $item
            );

            $item['text'] = str_replace(
                [
                    'L\'apprendimento e lo sviluppo personale rappresenta',
                    'Crescita attraverso esperienze impegnative richiede',
                ],
                [
                    'L\'apprendimento e lo sviluppo personale rappresentano',
                    'La crescita attraverso esperienze impegnative richiede',
                ],
                $item['text']
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

        $advancedText = '';

        if (!empty($item['advanced_notes'])) {
            $advancedText =
                ' Le condizioni avanzate evidenziano ' .
                implode(', ', array_slice(
                    array_unique($item['advanced_notes']),
                    0,
                    2
                )) .
                '.';
        }

        $aspectText = '';

        if (!empty($item['aspect_notes'])) {
            $aspectText =
                ' Le dinamiche planetarie includono ' .
                implode(', ', array_slice(
                    array_unique($item['aspect_notes']),
                    0,
                    2
                )) .
                '.';
        }

        $opening = match($theme) {
            'carriera' => 'La realizzazione personale e professionale',
            'studio' => 'L\'apprendimento e lo sviluppo personale',
            'salute' => 'L\'equilibrio personale e la gestione consapevole delle energie',
            'amore' => 'La vita affettiva e relazionale',
            default => ucfirst($role)
        };

        $grammar = match($theme) {
            'studio' => [
                'positive' => ' emerge come uno dei temi centrali dell\'anno. ',
                'critical' => ' richiede attenzione e consapevolezza. ',
                'default' => ' rappresenta un ambito di crescita progressiva. ',
            ],

            'salute' => [
                'positive' => ' emergono come uno dei temi centrali dell\'anno. ',
                'critical' => ' richiedono attenzione e consapevolezza. ',
                'default' => ' rappresentano un ambito di crescita progressiva. ',
            ],
            'prove' => [
                'positive' => ' emerge come uno dei temi centrali dell\'anno. ',
                'critical' => ' richiede attenzione e consapevolezza. ',
                'default' => ' rappresenta un ambito di crescita progressiva. ',
            ],

            default => [
                'positive' => ' emerge come uno dei temi centrali dell\'anno. ',
                'critical' => ' richiede attenzione e consapevolezza. ',
                'default' => ' rappresenta un ambito di crescita progressiva. ',
            ],
        };

        return match($polarity) {

            'positive' =>
                $opening .
                $grammar['positive'] .
                $planets .
                $symbolText .
                $contextText .
                $advancedText .
                $aspectText,

            'critical' =>
                $opening .
                $grammar['critical'] .
                'Può rappresentare un percorso di maturazione e trasformazione. ' .
                $planets .
                $symbolText .
                $contextText .
                $advancedText .
                $aspectText,

            default =>
                $opening .
                $grammar['default'] .
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

    private function advancedNotes(array $forecast, string $theme): array
    {
        $notes = [];

        $sources = $forecast['contributions'][$theme] ?? [];

        if (!$sources) {
            return [];
        }

        $planets = [];

        foreach ($sources as $source) {
            if (!empty($source['planet'])) {
                $planets[] = strtolower($source['planet']);
            }
        }

        $planets = array_unique($planets);

        foreach (($forecast['context']['retrograde'] ?? []) as $planet => $data) {
            if (in_array(strtolower($planet), $planets, true)) {
                $notes[] = $data['meaning'] ?? 'fase retrograda';
            }
        }

        foreach (($forecast['context']['solar'] ?? []) as $planet => $data) {
            if (in_array(strtolower($planet), $planets, true)) {
                $notes[] = match($data['condition'] ?? '') {
                    'cazimi' =>
                        'una forte concentrazione di energia e consapevolezza',
                    'combusto' =>
                        'una fase in cui crescita ed espressione richiedono equilibrio',
                    'sotto_raggi' =>
                        'un processo di maturazione graduale',
                    default =>
                        'una particolare condizione solare'
                };
            }
        }

        foreach (($forecast['context']['dignities'] ?? []) as $planet => $data) {
            if (in_array(strtolower($planet), $planets, true)) {
                $notes[] =
                    'una maggiore espressione delle qualità legate al pianeta in ' .
                    ($data['sign'] ?? '');
            }
        }

        return array_values(array_unique($notes));
    }


    private function aspectNotes(array $forecast, string $theme): array
    {
        $sources = $forecast['contributions'][$theme] ?? [];

        if (!$sources) {
            return [];
        }

        $planets = [];

        foreach ($sources as $source) {
            if (!empty($source['planet'])) {
                $planets[] = strtolower($source['planet']);
            }
        }

        $planets = array_unique($planets);

        $notes = [];

        foreach (($forecast['context']['aspects'] ?? []) as $aspect) {

            $p1 = strtolower($aspect['planet1'] ?? '');
            $p2 = strtolower($aspect['planet2'] ?? '');

            if (
                in_array($p1, $planets, true) ||
                in_array($p2, $planets, true)
            ) {
                $notes[] =
                    ucfirst($aspect['planet1']) .
                    ' ' .
                    ($aspect['aspect'] ?? '') .
                    ' ' .
                    ucfirst($aspect['planet2']);
            }

            if (count($notes) >= 2) {
                break;
            }
        }

        return array_values(array_unique($notes));
    }

}
