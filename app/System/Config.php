<?php

namespace WooProductSets\System;

class Config
{

    protected $items = [];

    /**
     * Config constructor.
     *
     * @param string|null $file Pass the file name to get config for starting at the config directory.
     */
    public function __construct(?string $file = null)
    {
        if (!file_exists(WOO_PROD_SETS_DIR . '/config/' . $file)) {
            return;
        }

        $this->items = include WOO_PROD_SETS_DIR . '/config/' . $file;
    }

    public function get($key, $default = null)
    {
        if (!is_array($this->items)) {
            return \WooProductSets\value($default);
        }

        if (is_null($key)) {
            return $this->items;
        }

        if (count($key) === 1) {
            return $this->items[$key[0]] ?? value($default);
        }

        $array = $this->items;
        foreach ($key as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }
}
