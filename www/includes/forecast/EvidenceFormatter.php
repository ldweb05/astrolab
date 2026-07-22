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

            $planets = $evidence['data']['planets'] ?? [];
            $houses = $evidence['data']['houses'] ?? [];

            $hasSingleHouse = $house >= 1 && $house <= 12;
            $hasMultipleHouses =
                is_array($houses)
                && is_array($planets)
                && $houses !== []
                && count($houses) === count($planets);

            if (
                $planet === ''
                || (!$hasSingleHouse && !$hasMultipleHouses)
            ) {
                continue;
            }

            if (
                is_array($planets)
                && $planets !== []
                && $hasMultipleHouses
            ) {
                $parts = [];

                foreach ($planets as $index => $name) {
                    $parts[] = sprintf(
                        '%s in Casa %d',
                        ucfirst(strtolower((string)$name)),
                        (int)$houses[$index]
                    );
                }

                $textLabel = implode(' e ', $parts);
            } elseif (is_array($planets) && $planets !== []) {
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

            $code = (string)(
                $evidence['code']
                ?? $evidence['theme']
                ?? $evidence['category']
                ?? ''
            );

            $theme = (string)(
                $evidence['theme']
                ?? $evidence['category']
                ?? $code
            );

            $ruleId = (string)(
                $evidence['rule_id']
                ?? ''
            );

            $evidenceId = 'EVIDENCE_'.strtoupper(substr(
                hash('sha256', implode('|', [
                    $code,
                    $theme,
                    $ruleId,
                    strtolower($textLabel),
                ])),
                0,
                16
            ));

            $conditionIds = array_values(array_filter(array_map(
                'strval',
                $evidence['condition_ids']
                ?? [($evidence['condition_id'] ?? '')]
            )));

            $rows[] = [
                'evidence_id' => $evidenceId,
                'condition_id' => $conditionIds[0] ?? '',
                'condition_ids' => $conditionIds,
                'code' => $code,
                'theme' => $theme,
                'rule_id' => $ruleId,
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
                $row['theme'] ?? '',
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
