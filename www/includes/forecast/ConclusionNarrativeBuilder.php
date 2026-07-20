<?php
declare(strict_types=1);

final class ConclusionNarrativeBuilder
{
    public function build(
        string $dominantTheme,
        array $summary,
        array $profiles
    ): string {
        if ($dominantTheme === '' || !isset($profiles[$dominantTheme])) {
            return '';
        }

        $primaryThemes = array_values(array_filter(
            array_map('strval', array_slice(
                $summary['primary_themes'] ?? [],
                0,
                4
            )),
            static fn(string $theme): bool => isset($profiles[$theme])
        ));

        $supportThemes = array_values(array_filter(
            array_map('strval', array_slice(
                $summary['support_themes'] ?? [],
                0,
                3
            )),
            static fn(string $theme): bool => isset($profiles[$theme])
        ));

        $attentionThemes = array_values(array_filter(
            array_map('strval', array_slice(
                $summary['attention_themes'] ?? [],
                0,
                3
            )),
            static fn(string $theme): bool => isset($profiles[$theme])
        ));

        $text = sprintf(
            "Nel complesso, il percorso annuale sembrerebbe trovare il proprio centro "
            ."nel tema %s.",
            $this->label($dominantTheme)
        );

        $secondaryThemes = array_values(array_filter(
            $primaryThemes,
            static fn(string $theme): bool => $theme !== $dominantTheme
        ));

        if ($secondaryThemes !== []) {
            $text .= sprintf(
                " Accanto a questo nucleo, %s potrebbero rappresentare gli ambiti "
                ."nei quali le dinamiche dell'anno troveranno maggiore espressione.",
                $this->joinWords(array_map(
                    fn(string $theme): string => $this->shortLabel($theme),
                    $secondaryThemes
                ))
            );
        }

        $supportThemes = array_values(array_diff(
            $supportThemes,
            $attentionThemes
        ));

        if ($supportThemes !== []) {
            $text .= sprintf(
                " Le risorse riconoscibili in %s potrebbero offrire sostegno, capacità "
                ."di recupero e margini utili per affrontare le circostanze.",
                $this->joinWords(array_map(
                    fn(string $theme): string => $this->shortLabel($theme),
                    $supportThemes
                ))
            );
        }

        if ($attentionThemes !== []) {
            $text .= sprintf(
                " Una cura particolare potrebbe invece essere necessaria in %s, "
                ."dove prudenza, continuità e tempestività potrebbero risultare decisive.",
                $this->joinWords(array_map(
                    fn(string $theme): string => $this->shortLabel($theme),
                    $attentionThemes
                ))
            );
        }

        return trim(
            $text
            ." Questa lettura non descrive eventi inevitabili, ma un insieme di "
            ."possibilità simboliche da osservare nel contesto concreto della vita. "
            ."La consapevolezza delle priorità potrebbe aiutare a utilizzare meglio "
            ."le risorse disponibili e ad affrontare con maggiore equilibrio gli "
            ."aspetti più impegnativi dell'anno."
        );
    }

    private function label(string $theme): string
    {
        return match ($theme) {
            'carriera' => 'della carriera e della realizzazione personale',
            'lavoro' => 'del lavoro e dell’organizzazione quotidiana',
            'amore' => 'delle relazioni e della vita affettiva',
            'salute' => 'del benessere e della gestione delle energie',
            'famiglia' => 'della famiglia e della vita privata',
            'casa' => 'della casa e della stabilità personale',
            'studio' => 'dello studio e della crescita personale',
            'viaggi' => 'dei viaggi e delle nuove prospettive',
            'amicizie' => 'delle amicizie e della vita sociale',
            'figli' => 'dei figli e delle responsabilità affettive',
            'creativita' => 'della creatività e dell’espressione personale',
            'spiritualita' => 'dell’interiorità e della ricerca personale',
            'trasformazione' => 'del cambiamento e della trasformazione',
            'prove' => 'delle responsabilità e delle prove',
            default => 'di '.$theme,
        };
    }


    private function shortLabel(string $theme): string
    {
        return match ($theme) {
            'carriera' => 'carriera e realizzazione personale',
            'lavoro' => 'lavoro e organizzazione quotidiana',
            'amore' => 'relazioni e vita affettiva',
            'salute' => 'benessere e gestione delle energie',
            'famiglia' => 'famiglia e vita privata',
            'casa' => 'casa e stabilità personale',
            'studio' => 'studio e crescita personale',
            'viaggi' => 'viaggi e nuove prospettive',
            'amicizie' => 'amicizie e vita sociale',
            'figli' => 'figli e responsabilità affettive',
            'creativita' => 'creatività ed espressione personale',
            'spiritualita' => 'interiorità e ricerca personale',
            'trasformazione' => 'cambiamento e trasformazione',
            'prove' => 'responsabilità e prove',
            default => $theme,
        };
    }

    private function joinWords(array $words): string
    {
        $words = array_values(array_unique($words));

        if ($words === []) {
            return '';
        }

        if (count($words) === 1) {
            return $words[0];
        }

        $last = array_pop($words);

        return implode(', ', $words).' e '.$last;
    }
}
