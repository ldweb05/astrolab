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

    private array $houseContexts = [

        1 => [
            'identita personale',
            'affermazione individuale',
            'nuovo ciclo di esperienza'
        ],

        5 => [
            'creativita',
            'espressione personale',
            'piaceri e interessi personali'
        ],

        6 => [
            'gestione delle energie quotidiane',
            'organizzazione degli impegni',
            'rapporto con lavoro e abitudini'
        ],

        8 => [
            'trasformazione profonda',
            'risorse condivise',
            'processi di cambiamento interiore'
        ],

        9 => [
            'apertura verso nuove esperienze',
            'viaggi e conoscenza',
            'ampliamento della propria visione'
        ],

        10 => [
            'realizzazione sociale',
            'riconoscimento del proprio ruolo',
            'obiettivi professionali'
        ],

        12 => [
            'mondo interiore',
            'chiusura di cicli',
            'elaborazione personale'
        ],

    ];


    public function interpretWithContext(array $planets): array
    {
        $out = [];

        foreach ($planets as $name => $data) {

            $key = strtolower($name);

            if (!isset($this->symbols[$key])) {
                continue;
            }

            $out[$key] = [
                'symbols' => $this->symbols[$key],
                'context' =>
                    $this->houseContexts[$data['casa'] ?? 0] ?? []
            ];
        }

        return $out;
    }

}
