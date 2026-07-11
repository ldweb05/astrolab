<?php
declare(strict_types=1);

final class AttentionNarrativeBuilder
{
    public function build(array $themes, array $profiles): string
    {
        $themes = array_values(array_filter(
            array_map('strval', array_slice($themes, 0, 4)),
            static fn(string $theme): bool => isset($profiles[$theme])
        ));

        if ($themes === []) {
            return "Non emergerebbero aree nettamente critiche. Resterebbe comunque "
                ."utile mantenere attenzione, equilibrio e capacità di adattamento.";
        }

        $labels = array_map(
            fn(string $theme): string => $this->label($theme),
            $themes
        );

        $planets = [];

        foreach ($themes as $theme) {
            foreach (($profiles[$theme]['sources'] ?? []) as $source) {
                $planet = (string)($source['planet'] ?? '');

                if ($planet !== '') {
                    $planets[] = ucfirst($planet);
                }
            }
        }

        $planets = array_values(array_unique($planets));

        $text = sprintf(
            "Gli ambiti relativi a %s potrebbero richiedere maggiore attenzione "
            ."nel corso dell'anno. Questo non indicherebbe eventi inevitabili, "
            ."ma settori nei quali prudenza, continuità e tempestività potrebbero "
            ."risultare particolarmente utili.",
            $this->joinWords($labels)
        );

        if ($planets !== []) {
            $text .= sprintf(
                " Le principali sollecitazioni sembrerebbero collegate soprattutto "
                ."alla presenza di %s.",
                $this->joinWords($planets)
            );
        }

        return trim(
            $text
            ." Sarebbe opportuno evitare interpretazioni allarmistiche e considerare "
            ."queste indicazioni come inviti a osservare con maggiore consapevolezza "
            ."le circostanze concrete, intervenendo quando necessario."
        );
    }

    private function label(string $theme): string
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
