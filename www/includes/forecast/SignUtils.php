<?php
declare(strict_types=1);

final class SignUtils
{
    private const SIGNS = [
        1=>'Ariete',
        2=>'Toro',
        3=>'Gemelli',
        4=>'Cancro',
        5=>'Leone',
        6=>'Vergine',
        7=>'Bilancia',
        8=>'Scorpione',
        9=>'Sagittario',
        10=>'Capricorno',
        11=>'Acquario',
        12=>'Pesci',
    ];

    public static function fromLongitude(float $longitude): string
    {
        $longitude = fmod($longitude,360);

        if ($longitude < 0) {
            $longitude += 360;
        }

        $index = (int)floor($longitude / 30) + 1;

        return self::SIGNS[$index] ?? '';
    }

    public static function name(int $number): string
    {
        return self::SIGNS[$number] ?? '';
    }
}
