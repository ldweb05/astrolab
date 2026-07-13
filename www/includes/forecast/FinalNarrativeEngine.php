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
            $parts[] = (string)$narrative['headline'];
        }

        foreach (($narrative['dominant_themes'] ?? []) as $theme) {
            $text = trim((string)($theme['text'] ?? ''));

            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return implode(
            "\n\n",
            $this->deduplicateParagraphs($parts)
        );
    }

    /**
     * Elimina i paragrafi narrativi duplicati mantenendo il primo
     * elemento e preservando l'ordine originale.
     *
     * @param array<int,string> $paragraphs
     * @return array<int,string>
     */
    private function deduplicateParagraphs(array $paragraphs): array
    {
        $result = [];
        $seen = [];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            $normalized = mb_strtolower(
                preg_replace('/\\s+/u', ' ', $paragraph) ?? $paragraph
            );

            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $result[] = $paragraph;
        }

        return $result;
    }
}
