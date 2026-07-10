<?php
declare(strict_types=1);

final class PlanetarySymbolEngine
{
    private array $symbols = [

        'sole' => [
            'identita personale',
            'affermazione',
            'visibilita'
        ],

        'luna' => [
            'bisogni emotivi',
            'sicurezza interiore',
            'dimensione familiare'
        ],

        'mercurio' => [
            'pensiero',
            'comunicazione',
            'apprendimento'
        ],

        'venere' => [
            'relazioni',
            'armonia',
            'valori personali'
        ],

        'marte' => [
            'iniziativa',
            'energia',
            'capacita decisionale'
        ],

        'giove' => [
            'crescita',
            'espansione',
            'nuove opportunita'
        ],

        'saturno' => [
            'responsabilita',
            'consolidamento',
            'maturazione'
        ],

        'urano' => [
            'cambiamento',
            'innovazione',
            'liberta personale'
        ],

        'nettuno' => [
            'ispirazione',
            'sensibilita',
            'ricerca interiore'
        ],

        'plutone' => [
            'trasformazione',
            'rigenerazione',
            'profondita'
        ],
    ];


    public function interpret(array $planets): array
    {
        $out = [];

        foreach ($planets as $planet) {

            $key = strtolower($planet);

            if (isset($this->symbols[$key])) {
                $out[$key] = $this->symbols[$key];
            }
        }

        return $out;
    }
}
