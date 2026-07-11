<?php
declare(strict_types=1);

final class CrossDynamicsBuilder
{
    public function build(array $themes, array $profiles): string
    {
        $themes = array_values(array_filter(
            array_map('strval', array_slice($themes, 0, 4)),
            static fn(string $theme): bool => isset($profiles[$theme])
        ));

        if ($themes === []) {
            return '';
        }

        $labels = array_map(
            fn(string $theme): string => $this->label($theme),
            $themes
        );

        $positive = [];
        $critical = [];

        foreach ($themes as $theme) {
            $polarity = (string)($profiles[$theme]['polarity'] ?? 'mixed');

            if ($polarity === 'positive') {
                $positive[] = $this->label($theme);
            }

            if ($polarity === 'critical') {
                $critical[] = $this->label($theme);
            }
        }

        $text = sprintf(
            "Le aree relative a %s sembrerebbero costituire un sistema strettamente collegato. "
            ."Le scelte compiute in uno di questi ambiti potrebbero produrre conseguenze anche "
            ."negli altri, rendendo utile osservare l'anno nel suo insieme anziché affrontare "
            ."ogni situazione in modo isolato.",
            $this->joinWords($labels)
        );

        if ($positive !== []) {
            $text .= sprintf(
                " Le risorse presenti soprattutto nei settori %s potrebbero offrire sostegno "
                ."anche alle altre aree della vita, facilitando la ricerca di soluzioni "
                ."e una migliore capacità di adattamento.",
                $this->joinWords($positive)
            );
        }

        if ($critical !== []) {
            $text .= sprintf(
                " Parallelamente, gli ambiti %s potrebbero richiedere maggiore attenzione, "
                ."poiché eventuali tensioni potrebbero riflettersi sull'equilibrio generale "
                ."dell'anno.",
                $this->joinWords($critical)
            );
        }

        return trim(
            $text
            ." Una gestione consapevole delle priorità potrebbe quindi aiutare a mantenere "
            ."un equilibrio più stabile tra responsabilità, bisogni personali e opportunità."
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
