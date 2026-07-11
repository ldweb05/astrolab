<?php
declare(strict_types=1);

final class ThemeNarrativeBuilder
{
    public function build(string $theme, array $profile): string
    {
        $polarity = (string)($profile['polarity'] ?? 'mixed');
        $sources  = $profile['sources'] ?? [];

        $planets = [];

        foreach ($sources as $source) {
            $planet = (string)($source['planet'] ?? '');

            if ($planet !== '') {
                $planets[] = ucfirst($planet);
            }
        }

        $planets = array_values(array_unique($planets));

        $sourceText = $planets !== []
            ? ' Il quadro sarebbe sostenuto soprattutto dalla presenza di '
                .$this->joinWords($planets).'.'
            : '';

        $opening = sprintf(
            "L'area %s sembrerebbe assumere un ruolo significativo nel corso dell'anno. "
            ."Le configurazioni presenti potrebbero portare maggiore attenzione verso "
            ."questo settore, rendendolo uno dei principali ambiti di esperienza e riflessione.",
            $this->label($theme)
        );

        $development = match ($polarity) {
            'positive' =>
                "Gli elementi favorevoli sembrerebbero offrire risorse utili, "
                ."sostegno e una migliore capacità di cogliere le occasioni che dovessero presentarsi. "
                ."Ciò non garantirebbe l'assenza di difficoltà, ma potrebbe favorire "
                ."una gestione più efficace delle circostanze.",

            'critical' =>
                "Questo ambito potrebbe richiedere maggiore prudenza, continuità "
                ."e capacità di osservare tempestivamente ciò che dovesse emergere. "
                ."Le eventuali difficoltà non andrebbero considerate come eventi inevitabili, "
                ."ma come richieste di attenzione e partecipazione consapevole.",

            default =>
                "L'andamento di questo settore potrebbe dipendere soprattutto "
                ."dal modo in cui verranno affrontate le circostanze concrete. "
                ."Flessibilità, ascolto e capacità di adattamento potrebbero risultare "
                ."particolarmente utili nel corso dell'anno.",
        };

        $closing = sprintf(
            "Nel complesso, il tema %s potrebbe rappresentare un'importante occasione "
            ."per comprendere meglio priorità, bisogni e modalità di azione.",
            $this->label($theme)
        );

        return trim(
            $opening
            .$sourceText
            ." "
            .$development
            ." "
            .$closing
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

    private function joinWords(array $words): string
    {
        $count = count($words);

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return $words[0];
        }

        $last = array_pop($words);

        return implode(', ', $words).' e '.$last;
    }
}
