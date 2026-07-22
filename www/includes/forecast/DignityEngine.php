<?php
declare(strict_types=1);

final class DignityEngine
{
    /*
     * +2 domicilio
     * +1 esaltazione
     * -1 esilio
     * -2 caduta
     */

    private const TABLE = [

        'sole' => [
            'domicile'     => ['Leone'],
            'exaltation'   => ['Ariete'],
            'detriment'    => ['Acquario'],
            'fall'         => ['Bilancia'],
        ],

        'luna' => [
            'domicile'     => ['Cancro'],
            'exaltation'   => ['Toro'],
            'detriment'    => ['Capricorno'],
            'fall'         => ['Scorpione'],
        ],

        'mercurio' => [
            'domicile'     => ['Gemelli','Vergine'],
            'exaltation'   => ['Vergine'],
            'detriment'    => ['Sagittario','Pesci'],
            'fall'         => ['Pesci'],
        ],

        'venere' => [
            'domicile'     => ['Toro','Bilancia'],
            'exaltation'   => ['Pesci'],
            'detriment'    => ['Scorpione','Ariete'],
            'fall'         => ['Vergine'],
        ],

        'marte' => [
            'domicile'     => ['Ariete','Scorpione'],
            'exaltation'   => ['Capricorno'],
            'detriment'    => ['Toro','Bilancia'],
            'fall'         => ['Cancro'],
        ],

        'giove' => [
            'domicile'     => ['Sagittario','Pesci'],
            'exaltation'   => ['Cancro'],
            'detriment'    => ['Gemelli','Vergine'],
            'fall'         => ['Capricorno'],
        ],

        'saturno' => [
            'domicile'     => ['Capricorno','Acquario'],
            'exaltation'   => ['Bilancia'],
            'detriment'    => ['Cancro','Leone'],
            'fall'         => ['Ariete'],
        ],
    ];

    public function coefficient(string $planet,string $sign): float
    {
        $planet = mb_strtolower($planet);

        if (!isset(self::TABLE[$planet])) {
            return 1.0;
        }

        $d = self::TABLE[$planet];

        return match(true) {
            in_array($sign,$d['domicile'],true)   => 1.30,
            in_array($sign,$d['exaltation'],true) => 1.20,
            in_array($sign,$d['detriment'],true)  => 0.85,
            in_array($sign,$d['fall'],true)       => 0.70,
            default                               => 1.00
        };
    }
}
