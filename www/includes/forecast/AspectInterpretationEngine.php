<?php
declare(strict_types=1);

final class AspectInterpretationEngine
{
    private const MEANINGS = [

        'congiunzione' => [
            'positive' => 'unione e concentrazione delle energie dei pianeti coinvolti.',
            'critical' => 'forte intensificazione del tema che richiede equilibrio.',
        ],

        'trigono' => [
            'positive' => 'facilità di espressione, opportunità e sviluppo naturale.',
            'critical' => 'rischio di adagiarsi senza utilizzare pienamente il potenziale.',
        ],

        'sestile' => [
            'positive' => 'possibilità favorevoli che richiedono iniziativa personale.',
            'critical' => 'opportunità che devono essere attivate con impegno.',
        ],

        'quadratura' => [
            'positive' => 'stimolo alla crescita attraverso sfide e trasformazioni.',
            'critical' => 'tensioni e necessità di adattamento.',
        ],

        'opposizione' => [
            'positive' => 'possibilità di integrazione tra esigenze diverse.',
            'critical' => 'confronti e polarità da gestire consapevolmente.',
        ],
    ];


    public function interpret(array $aspects): array
    {
        $out = [];

        foreach ($aspects as $aspect) {

            $name = $aspect['aspect'] ?? null;

            if (!isset(self::MEANINGS[$name])) {
                continue;
            }

            $out[] = [
                'planets' =>
                    ($aspect['planet1'] ?? '')
                    .' - '.
                    ($aspect['planet2'] ?? ''),

                'aspect' => $name,

                'orb' =>
                    $aspect['orb'] ?? null,

                'text' =>
                    self::MEANINGS[$name]['positive'],
            ];
        }

        return $out;
    }
}
