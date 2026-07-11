<?php
declare(strict_types=1);

final class EvidenceBuilder
{
    public function build(array $contributions): array
    {
        $evidences = [];

        foreach ($contributions as $theme => $items) {

            foreach ($items as $item) {

                $key = implode('|',[
                    $item['planet'],
                    $item['house'],
                    $item['source']
                ]);

                if (!isset($evidences[$key])) {

                    $evidences[$key] = [
                        'planet' => $item['planet'],
                        'house'  => $item['house'],
                        'source' => $item['source'],
                        'themes' => [],
                        'value'  => 0,
                    ];
                }

                $evidences[$key]['themes'][] = $theme;
                $evidences[$key]['value'] += $item['value'];
            }
        }

        return array_values($evidences);
    }
}
