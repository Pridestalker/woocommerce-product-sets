<?php

return [
	'filters'    => [
		\WooProductSets\Hooks\Filters\WooCommerce\AddToTypeSelector::class,
		\WooProductSets\Hooks\Filters\WooCommerce\FixAutoloadClass::class,
	],
	'actions'    => [
		\WooProductSets\Hooks\Actions\System\Install::class,
		\WooProductSets\Hooks\Actions\System\DeInstall::class,

		\WooProductSets\Hooks\Actions\WooCommerce\AddProductDataTemplate::class,
		\WooProductSets\Hooks\Actions\WooCommerce\EditStockOnOrder::class,
		\WooProductSets\Hooks\Actions\WooCommerce\SaveChildProducts::class,
		\WooProductSets\Hooks\Actions\WooCommerce\UpdateProductPrice::class,

		\WooProductSets\Hooks\Actions\System\EnqueueScripts::class,

		\WooProductSets\Hooks\Actions\Cron\CorrectConnectedProducts::class,
		\WooProductSets\Hooks\Actions\Cron\CorrectProductStock::class,
		\WooProductSets\Hooks\Actions\Cron\CorrectProductPrice::class,

        \WooProductSets\Hooks\Actions\WooCommerce\RenderAddToCart::class,
	],
	'cron_jobs'  => [
		\WooProductSets\Hooks\Cron\StockCron::class,
	],
	'post_types' => [],
	'taxonomies' => []
];
