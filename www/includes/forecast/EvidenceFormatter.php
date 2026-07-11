<?php
declare(strict_types=1);

final class EvidenceFormatter
{
    public function build(array $evidences): array
    {
        $rows = [];

        foreach ($evidences as $evidence) {

            $planet = (string)(
                $evidence['planet']
                ?? $evidence['data']['planet']
                ?? ''
            );

            $house = (int)(
                $evidence['house']
                ?? $evidence['data']['house']
                ?? 0
            );

            if ($planet === '' || $house === 0) {
                continue;
            }

            $planets = $evidence['data']['planets'] ?? [];

            if (is_array($planets) && $planets !== []) {
                $labels = array_map(
                    static fn(string $name): string =>
                        ucfirst(strtolower($name)),
                    array_map('strval', $planets)
                );

                $textLabel = implode(' e ', $labels).' in Casa '.$house;
            } else {
                $textLabel = sprintf(
                    '%s in Casa %d',
                    ucfirst(strtolower($planet)),
                    $house
                );
            }

            $rows[] = [
                'code' => (string)(
                    $evidence['code']
                    ?? $evidence['theme']
                    ?? ''
                ),
                'rule_id' => (string)(
                    $evidence['rule_id']
                    ?? ''
                ),
                'text' => $textLabel,
                'priority' => (int)(
                    $evidence['priority']
                    ?? $evidence['value']
                    ?? 0
                ),
            ];
        }

        $deduplicated = [];

        foreach ($rows as $row) {
            $key = implode('|', [
                $row['code'],
                $row['text'],
            ]);

            if (
                !isset($deduplicated[$key])
                || $row['priority'] > $deduplicated[$key]['priority']
            ) {
                $deduplicated[$key] = $row;
            }
        }

        $rows = array_values($deduplicated);

        usort(
            $rows,
            fn($a,$b) => $b['priority'] <=> $a['priority']
        );

        return $rows;
    }
}
