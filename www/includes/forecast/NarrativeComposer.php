<?php
declare(strict_types=1);

require_once __DIR__.'/DominantThemeEngine.php';

final class NarrativeComposer
{
    private DominantThemeEngine $dominant;

    public function __construct()
    {
        $this->dominant = new DominantThemeEngine();
    }

    public function compose(array $forecast): string
    {
        $themes = $forecast['polarities'] ?? [];

        $dominant = $this->dominant->extract($themes);

        $parts = [];

        $headline = $this->dominant->headline($dominant);

        if ($headline !== '') {
            $parts[] = $headline;
        }

        foreach ($dominant as $name => $theme) {

            $sentence = ucfirst($name).': ';

            switch ($theme['polarity']) {

                case 'positive':
                    $sentence .= 'costituisce una delle aree più promettenti dell\'anno.';
                    break;

                case 'critical':
                    $sentence .= 'richiederà prudenza, disciplina e maturazione.';
                    break;

                default:
                    $sentence .= 'presenta opportunità ma anche elementi da gestire con equilibrio.';
            }

            $parts[] = $sentence;
        }

        return implode("\n\n", $parts);
    }
}
