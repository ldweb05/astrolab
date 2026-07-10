<?php
/**
 * AstroUtils — utility matematiche condivise.
 *
 * Contiene funzioni piccole ma critiche usate da RuleEngine, SweCalc e
 * ricerche batch. Centralizzarle evita divergenze future tra file diversi.
 */
final class AstroUtils
{
    private function __construct() {}

    /**
     * Differenza angolare firmata tra due longitudini, normalizzata in (-180, +180].
     * Valore negativo: $a precede $b nello zodiaco; valore positivo: $a segue $b.
     */
    public static function diffAngolo(float $a, float $b): float
    {
        $d = fmod($a - $b + 360.0, 360.0);
        return $d > 180.0 ? $d - 360.0 : $d;
    }

    /** Normalizza una longitudine in [0, 360). */
    public static function normalizzaLongitudine(float $lon): float
    {
        return fmod($lon + 360.0, 360.0);
    }
}
