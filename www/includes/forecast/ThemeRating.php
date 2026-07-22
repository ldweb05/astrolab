<?php
declare(strict_types=1);

final class ThemeRating
{
    public static function stars(int|float $score): int
    {
        return match (true) {
            $score >= 450 => 5,
            $score >= 300 => 4,
            $score >= 180 => 3,
            $score >= 90  => 2,
            $score > 0    => 1,
            default        => 0,
        };
    }

    public static function color(int $stars): string
    {
        return match (true) {
            $stars >= 5 => 'green',
            $stars >= 4 => 'green',
            $stars == 3 => 'yellow',
            $stars == 2 => 'orange',
            default     => 'red',
        };
    }

    public static function starsString(int $stars): string
    {
        return str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
    }

    public static function summarize(array $scores): array
    {
        $out = [];

        foreach ($scores as $theme => $score) {
            $stars = self::stars((int)round($score));

            $out[$theme] = [
                'score' => (int)round($score),
                'stars' => $stars,
                'string'=> self::starsString($stars),
                'color' => self::color($stars),
            ];
        }

        return $out;
    }
}
