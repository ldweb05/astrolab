<?php
declare(strict_types=1);

final class OpportunitiesNarrativeBuilder
{
    public function build(array $themes, array $profiles): string
    {
        $themes = array_values(array_filter(
            array_map('strval', array_slice($themes, 0, 4)),
            static fn(string $theme): bool => isset($profiles[$theme])
        ));

        if ($themes === []) {
            return "Le opportunità dell'anno potrebbero emergere soprattutto "
                ."attraverso una gestione consapevole delle circostanze e delle risorse disponibili.";
        }

        $labels = array_map(
            fn(string $theme): string => $this->label($theme),
            $themes
        );

        $sourcePlanets = [];

        foreach ($themes as $theme) {
            foreach (($profiles[$theme]['sources'] ?? []) as $source) {
                $planet = (string)($source['planet'] ?? '');

                if ($planet !== '') {
                    $sourcePlanets[] = ucfirst($planet);
                }
            }
        }

        $sourcePlanets = array_values(array_unique($sourcePlanets));

        $text = sprintf(
            "Le configurazioni più favorevoli sembrerebbero concentrarsi soprattutto "
            ."negli ambiti legati a %s. Queste aree potrebbero offrire occasioni di crescita, "
            ."consolidamento o maggiore capacità di affrontare le situazioni che dovessero presentarsi.",
            $this->joinWords($labels)
        );

        if ($sourcePlanets !== []) {
            $text .= sprintf(
                " Il sostegno simbolico sembrerebbe provenire in particolare dalla presenza di %s.",
                $this->joinWords($sourcePlanets)
            );
        }

        return trim(
            $text
            ." Tali risorse non garantirebbero l'assenza di difficoltà, ma potrebbero "
            ."funzionare come fattori di protezione, facilitazione o migliore gestione degli eventi."
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
