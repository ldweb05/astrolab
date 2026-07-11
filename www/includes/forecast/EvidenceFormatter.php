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

            $rows[] = [
                'code' => (string)(
                    $evidence['code']
                    ?? $evidence['theme']
                    ?? ''
                ),
                'text' => sprintf(
                    '%s in Casa %d',
                    ucfirst(strtolower($planet)),
                    $house
                ),
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
