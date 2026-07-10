<?php
declare(strict_types=1);

final class FinalNarrativeEngine
{
    public function compose(array $forecast): array
    {
        $narrative = $forecast['narrative_v3'] ?? [];

        return [
            'headline' => $narrative['headline'] ?? '',
            'text' => $this->buildText($narrative),
            'themes' => $narrative['dominant_themes'] ?? [],
            'opportunities' => $narrative['opportunities'] ?? [],
            'challenges' => $narrative['challenges'] ?? [],
            'technical_notes' => $narrative['technical_notes'] ?? [],
            'annual_story' => $narrative['annual_story'] ?? [],
        ];
    }


    private function buildText(array $narrative): string
    {
        $parts = [];

        if (!empty($narrative['headline'])) {
            $parts[] = $narrative['headline'];
        }

        foreach (($narrative['dominant_themes'] ?? []) as $theme) {

            if (!empty($theme['text'])) {
                $parts[] = $theme['text'];
            }
        }

        return implode("\n\n", $parts);
    }
}
