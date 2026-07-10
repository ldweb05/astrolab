<?php
declare(strict_types=1);

require_once __DIR__.'/../atlas/AtlasLoader.php';

final class SourceCollector
{
    private array $atlas;

    public function __construct()
    {
        $this->atlas = AtlasLoader::load();
    }

    public function collect(array $temaRS): array
    {
        $fonti = [];

        foreach (($temaRS['pianeti'] ?? []) as $nome => $info) {

            $planet = mb_strtolower($nome);
            $casa   = (int)($info['casa'] ?? 0);

            if (!isset($this->atlas[$planet][$casa])) {
                continue;
            }

            foreach ($this->atlas[$planet][$casa]['themes'] as $tema => $peso) {

                $fonti[$tema][] = ucfirst($planet).' in '.$casa.'ª casa';

            }
        }

        foreach ($fonti as &$lista) {
            $lista = array_values(array_unique($lista));
        }

        return $fonti;
    }
}
