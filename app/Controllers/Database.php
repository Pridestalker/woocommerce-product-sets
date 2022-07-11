<?php

namespace WooProductSets\Controllers;

class Database
{
    protected static ?Database $_instance = null;

    public string $table_name = 'woo_product_sets_children';
    public string $table_version = '1.0.0';

    protected \wpdb $wpdb;

    private function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;

        if (!$this->hasDatabase()) {
            $this->installDatabase();
        }
    }

    public static function instance(): Database
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    protected function hasDatabase(): bool
    {
        return get_option('woo_product_sets_db_version');
    }

    protected function installDatabase(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;
        $tableName = $prefix . $this->table_name;

        // language=mysql
        $sql = "CREATE TABLE $tableName (
            product_id mediumint(9) NOT NULL , -- The product in this set.
            set_id mediumint(9) NOT NULL, -- The set the product is in.
            PRIMARY KEY (product_id, set_id) -- Composite key for the table, the product_id/set_id should be unique.
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('woo_product_sets_db_version', $this->table_version);
    }
}