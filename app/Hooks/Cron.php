<?php

namespace WooProductSets\Hooks;

abstract class Cron
{
    abstract public static function hook_name();

    abstract public static function recurrence();
}
