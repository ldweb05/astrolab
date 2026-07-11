<?php
declare(strict_types=1);

final class NarrativeStyleEngine
{
    /**
     * Varia alcune formule ripetitive mantenendo invariato
     * il significato prudenziale della relazione.
     */
    public function refine(array $sections): array
    {
        $replacements = [
            'sembrerebbe svilupparsi' => [
                'potrebbe svilupparsi',
                'tenderebbe a svilupparsi',
                'lascerebbe intravedere uno sviluppo',
            ],
            'sembrerebbe assumere' => [
                'potrebbe assumere',
                'tenderebbe ad assumere',
                'apparirebbe destinato ad assumere',
            ],
            'sembrerebbero offrire' => [
                'potrebbero offrire',
                'tenderebbero a offrire',
                'lascerebbero intravedere risorse utili per',
            ],
            'sembrerebbero concentrarsi' => [
                'potrebbero concentrarsi',
                'tenderebbero a concentrarsi',
                'apparirebbero maggiormente presenti',
            ],
            'sembrerebbe sostenuto' => [
                'potrebbe essere sostenuto',
                'parrebbe sostenuto',
                'risulterebbe sostenuto',
            ],
            'potrebbe rappresentare' => [
                'potrebbe costituire',
                'tenderebbe a rappresentare',
                'potrebbe assumere il significato di',
            ],
            'potrebbero richiedere' => [
                'potrebbero domandare',
                'tenderebbero a richiedere',
                'potrebbero rendere utile',
            ],
            'potrebbero offrire' => [
                'potrebbero mettere a disposizione',
                'potrebbero favorire',
                'potrebbero rendere più accessibili',
            ],
        ];

        $counters = [];

        foreach ($sections as $index => $section) {
            $text = (string)($section['text'] ?? '');

            foreach ($replacements as $needle => $variants) {
                if (!str_contains($text, $needle)) {
                    continue;
                }

                $position = $counters[$needle] ?? 0;
                $replacement = $variants[$position % count($variants)];

                $text = preg_replace(
                    '/'.preg_quote($needle, '/').'/u',
                    $replacement,
                    $text,
                    1
                ) ?? $text;

                $counters[$needle] = $position + 1;
            }

            $sections[$index]['text'] = $text;
        }

        return $sections;
    }
}
