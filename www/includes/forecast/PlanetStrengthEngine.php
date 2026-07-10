<?php
declare(strict_types=1);

require_once __DIR__.'/PlanetNature.php';

final class PlanetStrengthEngine
{
    /*
     * Intensità del pianeta.
     * La polarità (benefico/malefico) viene gestita separatamente.
     */

    private const POWER = [
        'sole'      => 1.15,
        'luna'      => 1.10,
        'mercurio'  => 1.00,
        'venere'    => 1.10,
        'marte'     => 1.15,
        'giove'     => 1.20,
        'saturno'   => 1.20,
        'urano'     => 1.15,
        'nettuno'   => 1.15,
        'plutone'   => 1.25,
    ];

    public function coefficient(string $planet): float
    {
        return self::POWER[mb_strtolower($planet)] ?? 1.0;
    }

    public function intensity(float|int $score,string $planet): float
    {
        return round(
            $score * $this->coefficient($planet),
            2
        );
    }

    public function polarity(string $planet): int
    {
        return PlanetNature::value($planet);
    }

    public function amplify(float|int $score,string $planet): float
    {
        return $this->intensity($score,$planet);
    }
}
