<?php
declare(strict_types=1);

require_once __DIR__.'/../atlas/AtlasLoader.php';
require_once __DIR__.'/ThemeMap.php';
require_once __DIR__.'/AngularPowerEngine.php';

final class SourceCollector
{
    private array $atlas;
    private AngularPowerEngine $angularPower;

    public function __construct()
    {
        $this->atlas        = AtlasLoader::load();
        $this->angularPower = new AngularPowerEngine();
    }

    public function collect(array $temaRS): array
    {
        $out         = [];
        $angularData = $this->angularPower->calculate($temaRS);

        foreach (($temaRS['pianeti'] ?? []) as $nome => $info) {
            $planet = mb_strtolower((string)$nome);
            $casa   = (int)($info['casa'] ?? 0);

            if (!isset($this->atlas[$planet][$casa]['themes'])) {
                continue;
            }

            $fonte = ucfirst($planet) . ' in ' . $casa . 'ª casa';
            $zone  = $angularData[$planet]['zones'] ?? [];

            if ($zone !== []) {
                $fonte .= ' — ' . implode(', ', $zone);
            }

            foreach ($this->atlas[$planet][$casa]['themes'] as $tema => $peso) {
                $temaNormalizzato = ThemeMap::normalize((string)$tema);
                $out[$temaNormalizzato][] = $fonte;
            }
        }

        foreach ($out as &$fonti) {
            sort($fonti);
            $fonti = array_values(array_unique($fonti));
        }
        unset($fonti);

        ksort($out);

        return $out;
    }
}
