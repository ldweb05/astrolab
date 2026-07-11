<?php
declare(strict_types=1);

/**
 * Registro centrale delle Rule astrologiche.
 */
final class RuleRegistry
{
    /**
     * @return array<object>
     */
    public static function all(): array
    {
        return [
            new Rule0001_Jupiter10(),
        ];
    }
}
