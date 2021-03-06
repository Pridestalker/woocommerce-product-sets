<?php

namespace WooProductSets\System;

use League\Plates\Engine;

class View
{
    protected $engine;

    public function __construct()
    {
        $this->engine = new Engine($this->get_template_path());
        $this->addFunctions();
    }

    protected function addFunctions()
    {
        do_action('woo_product_sets/view/functions', $this->engine);

        $this->engine->registerFunction('esc_url', function (...$params) {
            return esc_url(...$params);
        });
    }

    protected function get_template_path()
    {
        return WOO_PROD_SETS_DIR . '/resources/views';
    }

    public function write(string $template, array $data = [])
    {
        echo $this->render($template, $data);
    }

    public function render(string $template, array $data = [])
    {
        return $this->engine->render($template, $data);
    }
}
