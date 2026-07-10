<?php
declare(strict_types=1);

require_once __DIR__.'/DignityEngine.php';
require_once __DIR__.'/SignUtils.php';
require_once __DIR__.'/PlanetResolver.php';

final class DignityIntegrationEngine
{

    private DignityEngine $dignity;

    public function __construct()
    {
        $this->dignity = new DignityEngine();
    }

    public function calculate(array $temaRS): array
    {
        $out = [];

        foreach (($temaRS['pianeti'] ?? []) as $planetKey => $data) {
            if (!is_array($data) || !isset($data['longitudine'])) {
                continue;
            }

            $planet = PlanetResolver::name($planetKey, $data);

            if ($planet === null) {
                continue;
            }

            $sign = SignUtils::fromLongitude(
                (float)$data['longitudine']
            );

            $normalizedPlanet = mb_strtolower($planet);

            $out[$normalizedPlanet] = [
                'sign'        => $sign,
                'coefficient' => $this->dignity->coefficient(
                    $planet,
                    $sign
                ),
            ];
        }

        return $out;
    }
}
