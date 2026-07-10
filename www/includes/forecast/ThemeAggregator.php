<?php
declare(strict_types=1);

require_once __DIR__ . '/../atlas/AtlasLoader.php';
require_once __DIR__ . '/ThemeMap.php';
require_once __DIR__ . '/AngularPowerEngine.php';

final class ThemeAggregator
{
    private array $atlas;
    private AngularPowerEngine $angularPower;

    public function __construct()
    {
        $this->atlas        = AtlasLoader::load();
        $this->angularPower = new AngularPowerEngine();
    }

    public function aggregate(array $temaRS): array
    {
        $scores       = [];
        $angularData  = $this->angularPower->calculate($temaRS);

        foreach (($temaRS['pianeti'] ?? []) as $nome => $info) {
            $chiave = mb_strtolower((string)$nome);
            $casa   = (int)($info['casa'] ?? 0);

            if (!isset($this->atlas[$chiave][$casa]['themes'])) {
                continue;
            }

            $fattoreAngolare = (float)($angularData[$chiave]['factor'] ?? 1.0);

            foreach ($this->atlas[$chiave][$casa]['themes'] as $tema => $peso) {
                $temaNormalizzato = ThemeMap::normalize((string)$tema);
                $pesoPotenziato   = $this->angularPower->apply(
                    (float)$peso,
                    $fattoreAngolare
                );

                $scores[$temaNormalizzato] =
                    ($scores[$temaNormalizzato] ?? 0) + $pesoPotenziato;
            }
        }

        arsort($scores);

        return $scores;
    }
}
