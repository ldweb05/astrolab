<?php
declare(strict_types=1);

final class PlanetNature
{
    /*
     * +1 = benefico
     * -1 = malefico
     *  0 = neutro/contestuale
     */

    private const MAP = [
        'sole'      =>  0,
        'luna'      =>  0,
        'mercurio'  =>  0,
        'venere'    =>  1,
        'giove'     =>  1,
        'marte'     => -1,
        'saturno'   => -1,
        'urano'     => -1,
        'nettuno'   =>  0,
        'plutone'   => -1,
    ];

    public static function value(string $planet): int
    {
        return self::MAP[mb_strtolower($planet)] ?? 0;
    }

    public static function isBenefic(string $planet): bool
    {
        return self::value($planet) > 0;
    }

    public static function isMalefic(string $planet): bool
    {
        return self::value($planet) < 0;
    }
}
