<?php
declare(strict_types=1);

final class RetrogradeEngine
{
    public function calculate(array $temaRS): array
    {
        $out = [];

        foreach (($temaRS['pianeti'] ?? []) as $planet => $data) {

            if (!isset($data['retrogrado'])) {
                continue;
            }

            if (!$data['retrogrado']) {
                continue;
            }

            $out[mb_strtolower((string)$planet)] = [
                'retrograde' => true,
                'factor'     => 0.90,
                'meaning'    => $this->meaning((string)$planet),
            ];
        }

        return $out;
    }

    private function meaning(string $planet): string
    {
        return match(mb_strtolower($planet)) {

            'mercurio' =>
                'revisione di pensieri, comunicazioni e decisioni',

            'venere' =>
                'riesame dei valori e delle relazioni',

            'marte' =>
                'energia rivolta verso l’interno e revisione delle azioni',

            'giove' =>
                'revisione della crescita e delle convinzioni',

            'saturno' =>
                'ridefinizione di responsabilità e strutture',

            'urano' =>
                'cambiamenti interiori e revisione delle libertà',

            'nettuno' =>
                'rivalutazione delle percezioni e degli ideali',

            'plutone' =>
                'profonda trasformazione interiore',

            default =>
                'fase di revisione e rielaborazione'
        };
    }
}
