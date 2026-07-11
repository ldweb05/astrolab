<?php
declare(strict_types=1);

/**
 * Evidence Engine
 *
 * Colleziona e organizza le evidenze astrologiche
 * prodotte dal Rule Engine.
 */
final class EvidenceEngine
{
    /** @var array<int,array> */
    private array $items = [];

    public function add(array $evidence): void
    {
        $this->items[] = $evidence;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function byCategory(string $category): array
    {
        return array_values(array_filter(
            $this->items,
            static fn(array $e) => ($e['category'] ?? '') === $category
        ));
    }

    public function sort(): void
    {
        usort(
            $this->items,
            static function(array $a, array $b): int {

                $pa = (int)($a['priority'] ?? 0);
                $pb = (int)($b['priority'] ?? 0);

                if ($pa !== $pb) {
                    return $pb <=> $pa;
                }

                $sa = (float)($a['strength'] ?? 0);
                $sb = (float)($b['strength'] ?? 0);

                return $sb <=> $sa;
            }
        );
    }
}
