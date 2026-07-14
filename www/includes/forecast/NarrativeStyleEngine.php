<?php
declare(strict_types=1);

final class NarrativeStyleEngine
{
    /**
     * Varia alcune formule ripetitive mantenendo invariato
     * il significato prudenziale della relazione.
     *
     * @param array<int,array> $sections
     * @return array<int,array>
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
                'potrebbe progressivamente assumere',
            ],
            'sembrerebbero offrire' => [
                'potrebbero offrire',
                'tenderebbero a offrire',
                'potrebbero mettere a disposizione',
            ],
            'sembrerebbero concentrarsi' => [
                'potrebbero concentrarsi',
                'tenderebbero a concentrarsi',
                'potrebbero risultare maggiormente presenti',
            ],
            'sembrerebbe sostenuto' => [
                'potrebbe essere sostenuto',
                'parrebbe sostenuto',
                'potrebbe trovare sostegno',
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
            if (!is_array($section)) {
                continue;
            }

            $text = (string)($section['text'] ?? '');

            if ($text === '') {
                continue;
            }

            foreach ($replacements as $needle => $variants) {
                while (str_contains($text, $needle)) {
                    $position = $counters[$needle] ?? 0;
                    $replacement = $variants[
                        $position % count($variants)
                    ];

                    $updated = preg_replace(
                        '/'.preg_quote($needle, '/').'/u',
                        $replacement,
                        $text,
                        1
                    );

                    if (!is_string($updated) || $updated === $text) {
                        break;
                    }

                    $text = $updated;
                    $counters[$needle] = $position + 1;
                }
            }

            $sections[$index]['text'] = $text;
        }

        return $sections;
    }
}
