<?php
declare(strict_types=1);

require_once __DIR__ . '/../atlas/AtlasLoader.php';

final class ThemeAggregator
{
    private array $atlas;

    public function __construct()
    {
        $this->atlas = AtlasLoader::load();
    }

    public function aggregate(array $temaRS): array
    {
        $scores = [];

        foreach (($temaRS['pianeti'] ?? []) as $nome => $info) {

            $chiave = mb_strtolower($nome);

            if (!isset($this->atlas[$chiave])) {
                continue;
            }

            $casa = (int)($info['casa'] ?? 0);

            if (!isset($this->atlas[$chiave][$casa])) {
                continue;
            }

            foreach ($this->atlas[$chiave][$casa]['themes'] as $tema => $peso) {

                $scores[$tema] = ($scores[$tema] ?? 0) + $peso;

            }
        }

        arsort($scores);

        return $scores;
    }
}
