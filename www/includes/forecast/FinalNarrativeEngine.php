<?php
declare(strict_types=1);

final class FinalNarrativeEngine
{
    public function compose(array $forecast): array
    {
        $out = [];

        foreach (($forecast['paragraphs'] ?? []) as $item) {

            $theme = $item['theme'] ?? '';

            $out[] = [
                'theme' => $theme,

                'text' =>
                    $this->opening($theme)
                    .' '
                    .$this->contextText($forecast),

                'score' =>
                    $item['score'] ?? 0,
            ];
        }

        return $out;
    }


    private function opening(string $theme): string
    {
        return match($theme) {

            'carriera' =>
                'La dimensione professionale emerge come uno dei punti centrali dell’anno.',

            'amore' =>
                'Le dinamiche affettive richiedono attenzione e consapevolezza.',

            'salute' =>
                'Il benessere personale diventa un area da gestire con equilibrio.',

            default =>
                'Questo settore rappresenta una delle aree significative del periodo.',
        };
    }


    private function contextText(array $forecast): string
    {
        $texts = $forecast['context']['aspect_texts'] ?? [];

        if (!$texts) {
            return 'Le configurazioni presenti indicano una fase di sviluppo e trasformazione.';
        }

        return $texts[0]['text'] ?? 
            'Le configurazioni planetarie suggeriscono dinamiche evolutive.';
    }
}
