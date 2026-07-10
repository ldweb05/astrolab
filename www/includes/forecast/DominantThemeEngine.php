<?php
declare(strict_types=1);

final class DominantThemeEngine
{
    public function extract(array $themes,int $limit=5): array
    {
        uasort(
            $themes,
            static fn($a,$b)=>
                $b['intensity'] <=> $a['intensity']
        );

        $dominant = array_slice(
            $themes,
            0,
            $limit,
            true
        );

        foreach ($dominant as $name => &$theme) {

            $theme['role'] = match($name) {
                'carriera' => 'realizzazione personale e professionale',
                'amore' => 'vita affettiva e relazionale',
                'salute' => 'equilibrio personale e gestione delle energie',
                'trasformazione' => 'cambiamento e rinnovamento',
                'prove' => 'crescita attraverso esperienze impegnative',
                'studio' => 'apprendimento e sviluppo',
                default => 'area significativa dell esperienza annuale'
            };

            $theme['explanation'] =
                'Tema evidenziato dalla combinazione di intensità astrologica e dinamiche presenti nella Rivoluzione Solare.';
        }

        return $dominant;
    }

    public function headline(array $dominant): string
    {
        if (!$dominant) {
            return '';
        }

        $first = array_key_first($dominant);

        return match($first) {

            'carriera' =>
                'L\'anno ruota principalmente attorno alla realizzazione professionale.',

            'amore' =>
                'L\'anno pone al centro la vita affettiva e relazionale.',

            'salute' =>
                'L\'energia personale sarà il tema dominante dell\'anno.',

            'trasformazione' =>
                'L\'anno rappresenta un importante processo di cambiamento.',

            'prove' =>
                'L\'anno richiederà maturazione e capacità di affrontare le prove.',

            default =>
                'L\'anno presenta alcuni temi dominanti che guideranno l\'intero percorso.'
        };
    }
}
