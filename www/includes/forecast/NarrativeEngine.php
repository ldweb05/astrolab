<?php
declare(strict_types=1);

require_once __DIR__.'/ThemeRating.php';

final class NarrativeEngine
{
    public function build(array $scores): array
    {
        $themes = ThemeRating::summarize($scores);

        $paragraphs = [];

        foreach ($themes as $theme => $info) {

            $paragraphs[] = [
                'theme'  => $theme,
                'score'  => $info['score'],
                'stars'  => $info['string'],
                'text'   => '',
                'sources'=> [],
            ];
        }

        return $paragraphs;
    }
}
