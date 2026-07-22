<?php
declare(strict_types=1);

final class ThemeSummaryNarrativeBuilder
{
    public function build(array $themes, array $profiles): string
    {
        $themes = array_values(array_filter(
            array_map('strval', array_slice($themes, 0, 5)),
            static fn(string $theme): bool => isset($profiles[$theme])
        ));

        if ($themes === []) {
            return '';
        }

        $descriptions = [];

        foreach ($themes as $theme) {
            $profile = $profiles[$theme];
            $polarity = (string)($profile['polarity'] ?? 'mixed');

            $tone = match ($polarity) {
                'positive' => 'sembra disporre di risorse favorevoli',
                'critical' => 'potrebbe richiedere maggiore attenzione',
                default => 'presenta dinamiche articolate e variabili',
            };

            $descriptions[] = sprintf(
                "%s %s",
                ucfirst($this->label($theme)),
                $tone
            );
        }

        return "Il profilo complessivo dell'anno non dipenderebbe da un solo ambito, "
            ."ma dalla combinazione dei temi principali. "
            .implode('. ', $descriptions)
            .". La loro importanza relativa potrebbe cambiare nel corso dell'anno, "
            ."pur mantenendo una continuità con il tema dominante.";
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
}
