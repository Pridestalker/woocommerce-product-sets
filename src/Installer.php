<?php

namespace Elderbraum\WooProductSet;

class Installer
{
    protected static $_instance = null;


    public static function install()
    {
        if (null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct()
    {
        $this->createTaxonomyIfNotExists();
    }

    private function createTaxonomyIfNotExists()
    {
        if (!get_term_by('slug', 'set', 'product_type')) {
            wp_insert_term('set', 'product_type');
        }
    }
}
