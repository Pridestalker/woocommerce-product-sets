<?php

namespace Elderbraum\WooProductSet\Models;

class WC_Product_Set extends \WC_Product
{
	public const K_CHILD_PRODUCT = '_child_product_ids';
	public const K_PRICE_TYPE = '_price_type';
	public const K_SET_PRICE = '_set_price';

	public function get_type(): string
	{
		return 'set';
	}

	public function get_price( $context = 'view' )
	{
		return $this->get_meta( static::K_SET_PRICE );
	}

	public function get_regular_price( $context = 'view' ): string
	{
		$price    = 0.00;
		$products = $this->get_child_products_as_wc_product();

		foreach ( $products as $product ) {
			$price += (float) $product->get_price( 'edit' );
		}

		if ( $context === 'view' ) {
			return wc_price( $price );
		}

		return $price;
	}

	/**
	 * @return \WC_Product[]
	 */
	public function get_child_products_as_wc_product(): array
	{
		return array_map( static function ( $id )
		{
			return \wc_get_product( $id );
		}, $this->get_child_products() );
	}

	public function get_child_products(): array
	{
		do_action( 'Elderbraum/WooProductSet/models/get-children/post', $this->get_id() );
		$children = apply_filters( 'Elderbraum/WooProductSet/models/get-children',
			$this->get_meta( self::K_CHILD_PRODUCT ) );

		return $children ?: [];
	}

	public function get_sale_price( $context = 'view' ): string
	{
		return wc_price( $this->get_meta( static::K_SET_PRICE ) );
	}

	public function get_stock_status( $context = 'view' )
	{
		$children     = $this->get_child_products_as_wc_product();
		$stock_status = 'instock';

		foreach ( $children as $child ) {
			$stock_status_child = $child->get_stock_status( 'edit' );

			if ( $stock_status_child === 'outofstock' ) {
				$stock_status = $stock_status_child;
				break;
			}

			if ( $stock_status_child === 'onbackorder' ) {
				$stock_status = $stock_status_child;
				break;
			}
		}

		return apply_filters( 'Elderbraum/WooProductSet/models/stock-status', $stock_status );
	}

	public function get_stock_quantity( $context = 'view' )
	{
		$baseStock = PHP_INT_MAX;

		foreach ( $this->get_child_products_as_wc_product() as $childProduct ) {
			if ( $childProduct->get_stock_quantity( 'edit' ) < $baseStock ) {
				$baseStock = $childProduct->get_stock_quantity( 'edit' );
			}
		}

		return $baseStock;
	}

	public function is_on_sale( $context = 'view' ): bool
	{
		return $this->get_meta( static::K_PRICE_TYPE ) !== 'dynamic-sum';
	}

	public function set_child_products( array $data = [] ): void
	{
		do_action( 'Elderbraum/WooProductSet/models/store-children/pre', $data, $this->get_id() );
		$data = apply_filters( 'Elderbraum/WooProductSet/models/store-children', $data, $this->get_id() );
		$this->update_meta_data( self::K_CHILD_PRODUCT, $data );
		do_action( 'Elderbraum/WooProductSet/models/store-children/post', $data, $this->get_id() );
	}
}
