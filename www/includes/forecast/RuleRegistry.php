<?php
declare(strict_types=1);

require_once __DIR__.'/AstrologyRuleInterface.php';

/**
 * Registro automatico delle regole astrologiche.
 */
final class RuleRegistry
{
    /**
     * @return AstrologyRuleInterface[]
     */
    public static function all(): array
    {
        foreach (glob(__DIR__.'/rules/Rule*.php') as $file) {
            require_once $file;
        }

        $rules = [];

        foreach (get_declared_classes() as $class) {
            if (!preg_match('/^Rule\d{4}_/', $class)) {
                continue;
            }

            $rule = new $class();

            if (!$rule instanceof AstrologyRuleInterface) {
                throw new LogicException(
                    $class.' non implementa AstrologyRuleInterface'
                );
            }

            $rules[] = $rule;
        }

        usort(
            $rules,
            static fn(object $a, object $b): int =>
                strcmp(get_class($a), get_class($b))
        );

        return $rules;
    }
}
