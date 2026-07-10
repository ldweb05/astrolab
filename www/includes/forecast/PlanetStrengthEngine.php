<?php
declare(strict_types=1);

final class PlanetStrengthEngine
{
    /*
     * Coefficienti di forza intrinseca del pianeta.
     * Verranno moltiplicati successivamente per:
     *  - coefficiente Gauquelin
     *  - eventuali bonus/malus futuri
     */

    private const FACTORS = [
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

    public function factor(string $planet): float
    {
        return self::FACTORS[mb_strtolower($planet)] ?? 1.0;
    }

    public function apply(float|int $score,string $planet): float
    {
        return round(
            $score * $this->factor($planet),
            2
        );
    }
}
