<?php
declare(strict_types=1);

final class PlanetResolver
{
    private const PLANETS = [
        0  => 'Sole',
        1  => 'Luna',
        2  => 'Mercurio',
        3  => 'Venere',
        4  => 'Marte',
        5  => 'Giove',
        6  => 'Saturno',
        7  => 'Urano',
        8  => 'Nettuno',
        9  => 'Plutone',
        11 => 'NodoNord',
        12 => 'NodoSud',
    ];

    public static function name(int|string $key, array $planet = []): ?string
    {
        if (is_string($key) && $key !== '') {
            return $key;
        }

        foreach (['nome', 'pianeta', 'name'] as $field) {
            if (
                isset($planet[$field]) &&
                is_string($planet[$field]) &&
                $planet[$field] !== ''
            ) {
                return $planet[$field];
            }
        }

        if (is_numeric($key) && isset(self::PLANETS[(int)$key])) {
            return self::PLANETS[(int)$key];
        }

        if (
            isset($planet['id']) &&
            is_numeric($planet['id']) &&
            isset(self::PLANETS[(int)$planet['id']])
        ) {
            return self::PLANETS[(int)$planet['id']];
        }

        return null;
    }

    public static function normalized(int|string $key, array $planet = []): ?string
    {
        $name = self::name($key, $planet);

        return $name === null
            ? null
            : mb_strtolower($name);
    }
}
