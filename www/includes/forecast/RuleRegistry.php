<?php
declare(strict_types=1);

require_once __DIR__.'/rules/Rule0001_Jupiter10.php';
require_once __DIR__.'/rules/Rule0002_Saturn12.php';
require_once __DIR__.'/rules/Rule0003_Mars6.php';
require_once __DIR__.'/rules/Rule0004_Venus5.php';
require_once __DIR__.'/rules/Rule0005_Mercury3.php';
require_once __DIR__.'/rules/Rule0006_Moon4.php';
require_once __DIR__.'/rules/Rule0007_Uranus11.php';
require_once __DIR__.'/rules/Rule0008_Neptune9.php';
require_once __DIR__.'/rules/Rule0009_Pluto8.php';
require_once __DIR__.'/rules/Rule0010_Sun10.php';

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
            new Rule0003_Mars6(),
            new Rule0004_Venus5(),
            new Rule0005_Mercury3(),
            new Rule0006_Moon4(),
            new Rule0007_Uranus11(),
            new Rule0008_Neptune9(),
            new Rule0009_Pluto8(),
            new Rule0010_Sun10(),
        ];
    }
}
