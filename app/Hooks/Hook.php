<?php

namespace WooProductSets\Hooks;


/**
 * Base Hook class
 * @package WooProductSets\Hooks
 */
abstract class Hook implements HookContract
{
    /**
     * The hook name to fire into.
     * @return string|string[]
     */
    abstract public static function hook_name();

    /**
     * The priority to fire at. Defaults to 10 (WordPress default)
     *
     * @return int
     */
    public static function priority(): int
    {
        return 10;
    }

    /**
     * The parameter count this hook accepts. Defaults to 1 (WordPress default)
     *
     * @return int The number of parameters you accept
     */
    public static function parameter_count(): int
    {
        return 1;
    }
}
