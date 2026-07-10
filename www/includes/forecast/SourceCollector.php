<?php
declare(strict_types=1);

require_once __DIR__.'/../atlas/AtlasLoader.php';
require_once __DIR__.'/ThemeMap.php';

final class SourceCollector
{
    private array $atlas;

    public function __construct()
    {
        $this->atlas = AtlasLoader::load();
    }

    public function collect(array $temaRS): array
    {
        $out = [];

        foreach (($temaRS['pianeti'] ?? []) as $nome => $info) {

            $planet = mb_strtolower((string)$nome);
            $casa   = (int)($info['casa'] ?? 0);

            if (!isset($this->atlas[$planet][$casa]['themes'])) {
                continue;
            }

            foreach ($this->atlas[$planet][$casa]['themes'] as $tema => $peso) {

                $tema = ThemeMap::normalize((string)$tema);

                $out[$tema][] = ucfirst($planet) . ' in ' . $casa . 'ª casa';
            }
        }

        foreach ($out as &$v) {
            sort($v);
            $v = array_values(array_unique($v));
        }

        ksort($out);

        return $out;
    }
}
