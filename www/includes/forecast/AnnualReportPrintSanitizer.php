<?php
declare(strict_types=1);

final class AnnualReportPrintSanitizer
{
    private const MAX_SECTIONS = 20;
    private const MAX_TITLE_LENGTH = 180;
    private const MAX_NOTE_LENGTH = 3000;
    private const MAX_SECTION_TEXT_LENGTH = 15000;

    public function sanitize(array $report): array
    {
        $sections = [];

        foreach (
            array_slice(
                is_array($report['sections'] ?? null)
                    ? $report['sections']
                    : [],
                0,
                self::MAX_SECTIONS
            ) as $section
        ) {
            if (!is_array($section)) {
                continue;
            }

            $text = $this->scalarText(
                $section['text'] ?? '',
                self::MAX_SECTION_TEXT_LENGTH
            );

            if ($text === '') {
                continue;
            }

            $sections[] = [
                'id' => $this->scalarText(
                    $section['id'] ?? '',
                    self::MAX_TITLE_LENGTH
                ),
                'title' => $this->scalarText(
                    $section['title'] ?? '',
                    self::MAX_TITLE_LENGTH
                ),
                'text' => $text,
            ];
        }

        if ($sections === []) {
            return [];
        }

        return [
            'title' => $this->scalarText(
                $report['title'] ?? 'Rivoluzione Solare',
                self::MAX_TITLE_LENGTH
            ),
            'methodological_note' => $this->scalarText(
                $report['methodological_note'] ?? '',
                self::MAX_NOTE_LENGTH
            ),
            'dominant_theme' => $this->scalarText(
                $report['dominant_theme'] ?? '',
                self::MAX_TITLE_LENGTH
            ),
            'sections' => $sections,
        ];
    }

    private function scalarText(mixed $value, int $maximumLength): string
    {
        if (!is_scalar($value) && $value !== null) {
            return '';
        }

        $text = trim((string)$value);

        if ($text === '') {
            return '';
        }

        return mb_substr($text, 0, $maximumLength);
    }
}
