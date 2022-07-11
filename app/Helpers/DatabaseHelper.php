<?php

namespace WooProductSets\Helpers;

use WooProductSets\Controllers\Database;
use WooProductSets\Models\WC_Product_Set;

class DatabaseHelper
{
    protected static ?DatabaseHelper $_instance = null;

    protected \wpdb $wpdb;
    protected string $table_name;

    private function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->table_name = $wpdb->prefix . Database::instance()->table_name;
    }

    public static function instance(): DatabaseHelper
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function all()
    {
        $results = $this->wpdb->query("SELECT * FROM $this->table_name");

        return $results;
    }

    public function allById(int $set_id): array
    {
        $query = $this->wpdb->prepare("SELECT product_id FROM $this->table_name WHERE set_id = %d", $set_id);

        return $this->wpdb->get_col($query);
    }

    public function migrateOne(int|WC_Product_Set $product_id): void
    {
        if (!$product_id instanceof WC_Product_Set) {
            $product = new WC_Product_Set($product_id);
        } else {
            $product = $product_id;
        }

        $children = $product->get_meta(WC_Product_Set::K_CHILD_PRODUCT);

        var_dump($children);

        if (!$children) {
            return;
        }

        foreach ($children as $child) {
            $this->store($child, $product->get_id());
        }

        $product->delete_meta_data(WC_Product_Set::K_CHILD_PRODUCT);
    }

    public function store(int $product_id, int $set_id): void
    {
        $this->wpdb->insert(
            $this->table_name,
            [
                'product_id' => $product_id,
                'set_id' => $set_id,
            ],
            [
                '%d',
                '%d',
            ]
        );
    }

    public function removeAllById(int $set_id): void
    {
        $this->wpdb->delete(
            $this->table_name,
            [
                'set_id' => $set_id,
            ],
            [
                '%d',
            ]
        );
    }
}