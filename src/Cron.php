<?php

namespace Elderbraum\WooProductSet;

use Elderbraum\WooProductSet\Models\WC_Product_Set;

/**
 * Class Cron
 *
 * Runs the default cron to match set product price and stock.
 *
 * @package Elderbraum\WooProductSet
 */
class Cron implements Installable
{
	public const HOOK_NAME = 'woo_product_set_stock_cron';

	protected static ?Cron $_instance = null;

	protected array $products = [];

	private function __construct()
	{
		$this->products = wc_get_products( [
			'limit' => - 1,
			'type'  => 'set',
		] );

		array_map( [ $this, 'correctProductPrice' ], $this->products );
		array_map( [ $this, 'correctProductStock' ], $this->products );
	}

	/**
	 * Make a class instance.
	 *
	 * @return Cron
	 */
	public static function install(): Cron
	{
		error_log( 'FROM CRON', E_WARNING );
		if ( null === static::$_instance ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	public function correctProductPrice( WC_Product_Set $product )
	{
		if ($product->get_meta('_price_type') === 'dynamic-sum') {
			$_POST['_set_products'] = true;
			WooProductSetMain::load()->updateProductPrice( $product );
		}

		if ($product->get_meta('_price_type') === 'dynamic-percentage') {
			$_POST['_set_price_percentage_fee'] = $product->get_meta('_set_price_percentage_fee');
			WooProductSetMain::load()->updateProductPrice( $product );
		}
	}

	public function correctProductStock( WC_Product_Set $product )
	{
		$product->set_stock_quantity( $product->get_stock_quantity( 'edit' ) );
		$product->set_stock_status( $product->get_stock_status() );
		$product->save();
	}
}
