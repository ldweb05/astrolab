<?php
declare(strict_types=1);

final class AnnualReportPrintRenderer
{
    public function render(array $report): string
    {
        $sections = $report['sections'] ?? [];

        if (!is_array($sections) || $sections === []) {
            return '';
        }

        $title = $this->escape(
            (string)($report['title'] ?? 'Rivoluzione Solare')
        );

        $methodologicalNote = trim(
            (string)($report['methodological_note'] ?? '')
        );

        $html = "<div style='page-break-before:always'></div>";
        $html .= "<div class='report-section annual-report-print'>";
        $html .= "<div class='report-section-title'>{$title}</div>";

        if ($methodologicalNote !== '') {
            $html .= "<div class='annual-report-note'>"
                .$this->paragraphs($methodologicalNote)
                ."</div>";
        }

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $sectionTitle = trim((string)($section['title'] ?? ''));
            $sectionText = trim((string)($section['text'] ?? ''));

            if ($sectionText === '') {
                continue;
            }

            $html .= "<div class='annual-report-section'>";

            if ($sectionTitle !== '') {
                $html .= "<div class='annual-report-section-title'>"
                    .$this->escape($sectionTitle)
                    ."</div>";
            }

            $html .= "<div class='annual-report-section-text'>"
                .$this->paragraphs($sectionText)
                ."</div>";

            $html .= "</div>";
        }

        $html .= "</div>";

        return $html;
    }

    private function paragraphs(string $text): string
    {
        $parts = preg_split('/\R{2,}/u', trim($text)) ?: [];
        $html = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part !== '') {
                $html[] = '<p>'.$this->escape($part).'</p>';
            }
        }

        return implode('', $html);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}
