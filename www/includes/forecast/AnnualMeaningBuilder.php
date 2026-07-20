<?php
declare(strict_types=1);

final class AnnualMeaningBuilder
{
    public function build(string $theme, array $profile): string
    {
        if ($theme === '' || $profile === []) {
            return '';
        }

        $polarity = (string)($profile['polarity'] ?? 'mixed');
        $sources  = $profile['sources'] ?? [];
        $planets  = [];

        foreach ($sources as $source) {
            $planet = (string)($source['planet'] ?? '');

            if ($planet !== '') {
                $planets[] = ucfirst($planet);
            }
        }

        $planets = array_values(array_unique($planets));

        $sourceText = $planets !== []
            ? ' Il quadro sembrerebbe sostenuto soprattutto dalla presenza di '
                .$this->joinWords($planets).'.'
            : '';

        $development = match ($polarity) {
            'positive' =>
                "Le configurazioni prevalenti sembrerebbero offrire risorse utili "
                ."per valorizzare questo ambito, senza escludere la possibilità "
                ."che possano presentarsi responsabilità o situazioni da gestire.",

            'critical' =>
                "L'anno potrebbe richiedere maggiore prudenza e capacità di osservare "
                ."tempestivamente ciò che dovesse emergere, trasformando eventuali "
                ."difficoltà in occasioni di maggiore consapevolezza.",

            default =>
                "L'evoluzione dell'anno potrebbe dipendere soprattutto dal modo "
                ."in cui verranno affrontate le circostanze concrete, mantenendo "
                ."flessibilità e capacità di adattamento.",
        };

        return trim(
            "Questa Rivoluzione Solare sembrerebbe svilupparsi principalmente "
            ."intorno al tema ".$this->label($theme).". "
            ."Tale ambito potrebbe rappresentare il filo conduttore dell'anno, "
            ."influenzando scelte, priorità e modalità di azione."
            .$sourceText
            ." "
            .$development
            ." Nel complesso, il periodo potrebbe invitare a partecipare attivamente "
            ."agli eventi, utilizzando con consapevolezza le risorse disponibili."
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
        if (count($words) === 1) {
            return $words[0];
        }

        $last = array_pop($words);

        return implode(', ', $words).' e '.$last;
    }
}
