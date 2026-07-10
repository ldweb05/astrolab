<?php
declare(strict_types=1);

require_once __DIR__.'/DignityEngine.php';
require_once __DIR__.'/SignUtils.php';

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

        foreach (($temaRS['pianeti'] ?? []) as $planet => $data) {

            if (!isset($data['longitudine'])) {
                continue;
            }

            $sign = SignUtils::fromLongitude(
                (float)$data['longitudine']
            );

            $out[mb_strtolower($planet)] = [
                'sign'        => $sign,
                'coefficient' => $this->dignity->coefficient(
                    (string)$planet,
                    $sign
                ),
            ];
        }

        return $out;
    }
}
