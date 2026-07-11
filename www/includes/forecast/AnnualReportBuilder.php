<?php
declare(strict_types=1);

final class AnnualReportBuilder
{
    public function build(
        array $summary,
        array $outline,
        array $draft
    ): array {
        $sections = [];

        foreach ($draft as $section) {
            $text = trim((string)($section['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            $sections[] = [
                'id'    => (string)($section['id'] ?? ''),
                'title' => (string)($section['title'] ?? ''),
                'text'  => $text,
            ];
        }

        $plainText = implode(
            "\n\n",
            array_map(
                static fn(array $section): string =>
                    $section['title']."\n".$section['text'],
                $sections
            )
        );

        return [
            'title' => 'Rivoluzione Solare',
            'methodological_note' =>
                "Questa relazione propone una lettura simbolica della Rivoluzione "
                ."Solare secondo i principi dell'Astrologia Attiva. Le indicazioni "
                ."descrivono tendenze e possibilità, non eventi certi.",
            'dominant_theme' => $summary['dominant_theme'] ?? null,
            'outline' => $outline,
            'sections' => $sections,
            'word_count' => str_word_count($plainText),
        ];
    }
}
