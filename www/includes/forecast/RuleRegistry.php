<?php
declare(strict_types=1);

require_once __DIR__.'/rules/Rule0001_Jupiter10.php';
require_once __DIR__.'/rules/Rule0002_Saturn12.php';

/**
 * Registro centrale delle regole astrologiche.
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
            new Rule0002_Saturn12(),
        ];
    }
}
