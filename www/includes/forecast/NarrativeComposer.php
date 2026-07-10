<?php
declare(strict_types=1);

require_once __DIR__.'/DominantThemeEngine.php';

final class NarrativeComposer
{
    private DominantThemeEngine $dominant;

    public function __construct()
    {
        $this->dominant = new DominantThemeEngine();
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
                'text' => '',
            ];

            switch ($theme['polarity'] ?? 'neutral') {

                case 'positive':
                    $item['text'] =
                        ucfirst($name) .
                        ' rappresenta una delle aree più promettenti dell\'anno.';
                    $result['opportunities'][] = $name;
                    break;

                case 'critical':
                    $item['text'] =
                        ucfirst($name) .
                        ' richiede prudenza, disciplina e capacità di maturazione.';
                    $result['challenges'][] = $name;
                    break;

                default:
                    $item['text'] =
                        ucfirst($name) .
                        ' presenta opportunità e aspetti da gestire con equilibrio.';
            }

            $result['dominant_themes'][] = $item;
        }

        foreach (($forecast['contributions'] ?? []) as $theme => $data) {
            $result['technical_notes'][$theme] = $data;
        }

        return $result;
    }
}
