<?php
declare(strict_types=1);

require_once __DIR__ . '/../atlas/AtlasLoader.php';
require_once __DIR__ . '/ThemeMap.php';

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
            $chiave = mb_strtolower((string)$nome);
            $casa   = (int)($info['casa'] ?? 0);

            if (!isset($this->atlas[$chiave][$casa]['themes'])) {
                continue;
            }

            foreach ($this->atlas[$chiave][$casa]['themes'] as $tema => $peso) {
                $temaNormalizzato = ThemeMap::normalize((string)$tema);
                $scores[$temaNormalizzato] =
                    ($scores[$temaNormalizzato] ?? 0) + (int)$peso;
            }
        }

        arsort($scores);

        return $scores;
    }
}
