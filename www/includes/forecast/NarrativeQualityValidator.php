<?php
declare(strict_types=1);

final class NarrativeQualityValidator
{
    private const FORBIDDEN_PATTERNS = [
        '/\bsicuramente\b/iu',
        '/\bcertamente\b/iu',
        '/\binevitabilmente\b/iu',
        '/\baccadrà\b/iu',
        '/\bsuccederà\b/iu',
        '/\bavrai\b/iu',
        '/\bperderai\b/iu',
        '/\btroverai\b/iu',
        '/\bsubirai\b/iu',
        '/\bti ammalerai\b/iu',
    ];

    public function validate(array $report): array
    {
        $issues = [];
        $texts = [];
        $seenTexts = [];

        foreach (($report['sections'] ?? []) as $section) {
            $id = (string)($section['id'] ?? '');
            $text = trim((string)($section['text'] ?? ''));

            if ($text === '') {
                $issues[] = [
                    'type' => 'empty_section',
                    'section' => $id,
                ];

                continue;
            }

            $texts[] = $text;

            $normalizedText = mb_strtolower(
                preg_replace('/\\s+/u', ' ', $text) ?? $text
            );

            if (isset($seenTexts[$normalizedText])) {
                $issues[] = [
                    'type' => 'duplicate_section',
                    'section' => $id,
                    'duplicate_of' => $seenTexts[$normalizedText],
                ];
            } else {
                $seenTexts[$normalizedText] = $id;
            }

            foreach (self::FORBIDDEN_PATTERNS as $pattern) {
                if (preg_match($pattern, $text) === 1) {
                    $issues[] = [
                        'type' => 'deterministic_language',
                        'section' => $id,
                        'pattern' => $pattern,
                    ];
                }
            }
        }

        $fullText = implode(' ', $texts);
        $wordCount = str_word_count($fullText);

        return [
            'valid' => $issues === [],
            'word_count' => $wordCount,
            'section_count' => count($report['sections'] ?? []),
            'issues' => $issues,
        ];
    }
}
