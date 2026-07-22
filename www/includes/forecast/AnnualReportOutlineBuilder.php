<?php
declare(strict_types=1);

final class AnnualReportOutlineBuilder
{
    public function build(array $summary, array $profiles): array
    {
        $sections = [];

        $sections[] = [
            'id' => 'executive_summary',
            'title' => 'Sintesi esecutiva',
            'theme' => $summary['dominant_theme'] ?? null,
        ];

        $sections[] = [
            'id' => 'meaning_of_year',
            'title' => "Il significato dell'anno",
            'theme' => $summary['dominant_theme'] ?? null,
        ];

        $sections[] = [
            'id' => 'theme_summary',
            'title' => 'Profilo dei temi principali',
            'themes' => $summary['primary_themes'] ?? [],
        ];

        foreach (($summary['primary_themes'] ?? []) as $theme) {
            if (!isset($profiles[$theme])) {
                continue;
            }

            $sections[] = [
                'id' => 'theme_'.$theme,
                'title' => $this->titleFor((string)$theme),
                'theme' => (string)$theme,
            ];
        }

        $sections[] = [
            'id' => 'cross_dynamics',
            'title' => 'Le dinamiche dell’anno',
            'themes' => $summary['primary_themes'] ?? [],
        ];

        $sections[] = [
            'id' => 'opportunities',
            'title' => 'Le opportunità',
            'themes' => $summary['support_themes'] ?? [],
        ];

        $sections[] = [
            'id' => 'attention',
            'title' => 'Le aree da osservare',
            'themes' => $summary['attention_themes'] ?? [],
        ];

        $sections[] = [
            'id' => 'conclusion',
            'title' => 'Sintesi conclusiva',
            'theme' => $summary['dominant_theme'] ?? null,
        ];

        return $sections;
    }

    private function titleFor(string $theme): string
    {
        return match ($theme) {
            'carriera' => 'Carriera e realizzazione personale',
            'lavoro' => 'Lavoro e organizzazione quotidiana',
            'amore' => 'Relazioni e vita affettiva',
            'salute' => 'Benessere e gestione delle energie',
            'famiglia' => 'Famiglia e vita privata',
            'casa' => 'Casa e stabilità personale',
            'denaro' => 'Risorse economiche',
            'studio' => 'Studio e crescita personale',
            'viaggi' => 'Viaggi e nuove prospettive',
            'amicizie' => 'Amicizie e vita sociale',
            'figli' => 'Figli e responsabilità affettive',
            'creativita' => 'Creatività ed espressione personale',
            'spiritualita' => 'Interiorità e ricerca personale',
            'trasformazione' => 'Cambiamento e trasformazione',
            'prove' => 'Responsabilità e prove dell’anno',
            default => ucfirst($theme),
        };
    }
}
