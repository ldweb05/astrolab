<?php
declare(strict_types=1);

require_once __DIR__.'/ThemeRating.php';
require_once __DIR__.'/TextTemplates.php';

final class NarrativeEngine
{
    public function build(array $scores, array $fonti = []): array
    {
        $themes = ThemeRating::summarize($scores);

        $out = [];

        foreach ($themes as $tema => $info) {

            $testo = TextTemplates::INTRO[$tema]
                ?? ('L\'anno evidenzia il tema "' . $tema . '".');

            $out[] = [
                'theme'   => $tema,
                'score'   => $info['score'],
                'stars'   => $info['stars'],
                'string'  => $info['string'],
                'color'   => $info['color'],
                'text'    => $testo,
                'sources' => $fonti[$tema] ?? [],
            ];
        }

        usort(
            $out,
            fn($a, $b) => $b['score'] <=> $a['score']
        );

        return $out;
    }
}
