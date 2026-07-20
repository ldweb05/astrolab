<?php
declare(strict_types=1);

final class ExecutiveSummaryNarrativeBuilder
{
    public function build(array $summary): string
    {
        $dominantTheme = (string)($summary['dominant_theme'] ?? '');

        if ($dominantTheme === '') {
            return '';
        }

        $strengths = array_values(array_filter(
            array_map('strval', $summary['top_strengths'] ?? [])
        ));

        $attention = array_values(array_filter(
            array_map('strval', $summary['top_attention'] ?? [])
        ));

        $text = sprintf(
            "Questa Rivoluzione Solare sembrerebbe orientare l'anno soprattutto "
            ."verso %s. Tale ambito potrebbe rappresentare il filo conduttore "
            ."principale delle esperienze, delle decisioni e delle priorità annuali.",
            $this->label($dominantTheme)
        );

        if ($strengths !== []) {
            $text .= sprintf(
                " Le risorse più significative sembrerebbero concentrarsi in %s, "
                ."offrendo possibili elementi di sostegno, facilitazione e recupero.",
                $this->joinLabels($strengths)
            );
        }

        if ($attention !== []) {
            $text .= sprintf(
                " Parallelamente, %s potrebbero richiedere maggiore attenzione, "
                ."prudenza e capacità di intervenire con tempestività.",
                $this->joinLabels($attention)
            );
        }

        return $text
            ." Nel complesso, il valore dell'anno potrebbe dipendere soprattutto "
            ."dal modo in cui queste dinamiche verranno riconosciute e integrate.";
    }

    private function label(string $theme): string
    {
        return match ($theme) {
            'carriera' => 'la carriera e la realizzazione personale',
            'lavoro' => 'il lavoro e l’organizzazione quotidiana',
            'amore' => 'le relazioni e la vita affettiva',
            'salute' => 'il benessere e la gestione delle energie',
            'famiglia' => 'la famiglia e la vita privata',
            'casa' => 'la casa e la stabilità personale',
            'denaro' => 'le risorse economiche',
            'studio' => 'lo studio e la crescita personale',
            'viaggi' => 'i viaggi e le nuove prospettive',
            'amicizie' => 'le amicizie e la vita sociale',
            'figli' => 'i figli e le responsabilità affettive',
            'creativita' => 'la creatività e l’espressione personale',
            'spiritualita' => 'l’interiorità e la ricerca personale',
            'trasformazione' => 'il cambiamento e la trasformazione',
            'prove' => 'le responsabilità e le prove',
            default => str_replace('_', ' ', $theme),
        };
    }

    private function joinLabels(array $themes): string
    {
        $labels = array_values(array_unique(array_map(
            fn(string $theme): string => $this->label($theme),
            $themes
        )));

        if (count($labels) === 1) {
            return $labels[0];
        }

        $last = array_pop($labels);

        return implode(', ', $labels).' e '.$last;
    }
}
