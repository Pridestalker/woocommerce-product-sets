<?php

namespace Elderbraum\WooProductSet;

use Elderbraum\WooProductSet\Models\WC_Product_Set;

/**
 * Class WooProductSetMain
 * @package Elderbraum\WooProductSet
 *
 * @version 1.0.0
 * @author  Mitch Hijlkema <mail@mitchijlkema.nl>
 */
class WooProductSetMain
{
	/**
	 * @var WooProductSetMain|null $_instance
	 */
	protected static ?WooProductSetMain $_instance = null;

	/**
	 * WooProductSetMain constructor.
	 *
	 * @private Ensures the use of load {@see load()} function to prevent multiple instances.
	 */
	private function __construct()
	{
		add_filter( 'product_type_selector', [ $this, 'addType' ] );
		add_filter( 'woocommerce_product_class', [ $this, 'fixAutoloadClass' ], 10, 2 );

		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'addWrapper' ] );
		add_action( 'post_updated', [ $this, 'saveChildProducts' ] );
		add_action( 'post_updated', [ $this, 'savePriceData' ], PHP_INT_MAX );
		add_action( 'admin_footer', [ $this, 'enqueueScripts' ] );

		add_action( 'woocommerce_order_status_processing', [ $this, 'editStockOnOrder' ] );
		add_action( Cron::HOOK_NAME, [ Cron::class, 'install' ] );
	}

	/**
	 * Loads the plugin.
	 *
	 * @return WooProductSetMain
	 */
	public static function load(): WooProductSetMain
	{
		if ( static::$_instance === null ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * Adds the required type to the WooCommerce type array
	 *
	 * @param  array  $types
	 *
	 * @return array
	 */
	public function addType( array $types ): array
	{
		if ( ! isset( $types[ 'set' ] ) ) {
			$types[ 'set' ] = __( 'Product Set', 'woo-prod-set' );
		}

		return $types;
	}

	/**
	 * Edits the stock of the items in this bundle, and add to order note.
	 *
	 * @param  int|\WC_Order  $order_id
	 */
	public function editStockOnOrder( $order_id )
	{
		$order = wc_get_order( $order_id );

		foreach ( $order->get_items() as $item ) {
			if ( $item->get_type() !== 'line_item' ) {
				continue;
			}
			$product = $item->get_product();
			if ( $product->get_type() === 'set' ) {
				/** @var WC_Product_Set $product */
				foreach ( $product->get_child_products_as_wc_product() as $childProduct ) {
					$newStock = $childProduct->get_stock_quantity( 'edit' ) - $item->get_quantity();
					/* Translators: 1: Parent product name 2: Child product name 3: Original quantity 4: New quantity */
					$order_note = sprintf(
						__( '[%1$s]: Reduced stock for %2%s from %3$s -> %4$s ', 'woo-prod-set' ),
						$product->get_title(),
						$childProduct->get_title(),
						$childProduct->get_stock_quantity( 'edit' ),
						$newStock
					);

					$order->add_order_note( $order_note );
					wc_update_product_stock(
						$childProduct->get_id(),
						$newStock
					);
				}
			}
		}
	}

	/**
	 * Outputs the template for the WooCommerce admin.
	 */
	public function addWrapper()
	{
		/** @var WC_Product_Set $product */
		$product = wc_get_product();
		if ( $product->get_type() !== 'set' ) {
			return;
		}
		?>
        <div class="options_group show_if_set clear">
			<?php
			woocommerce_wp_select(
				[
					'id'      => '_price_type',
					'label'   => __( 'Price type', 'woo-prod-set' ),
					'options' => [
						'fixed'              => __( 'Fixed', 'woo-prod-set' ),
						'dynamic-sum'        => __( 'Dynamic', 'woo-prod-set' ),
						'dynamic-percentage' => __( 'Dynamic percentage fee', 'woo-prod-set' ),
					],
				]
			);

			woocommerce_wp_text_input(
				[
					'id'          => '_set_price',
					'label'       => __( 'Set price', 'woo-prod-set' ),
					'value'       => $product->get_meta( '_set_price' ),
					'data_type'   => 'price',
					'description' => __( 'This is the base price of the product. Only available when price type is set to fixed',
						'woo-prod-set' ),
				]
			);

			woocommerce_wp_text_input(
				[
					'id'          => '_set_price_percentage_fee',
					'label'       => __( 'Set price percentage fee', 'woo-prod-set' ),
					'value'       => $product->get_meta( '_set_price_percentage_fee' ) ?: 100,
					'data_type'   => 'decimal',
					'description' => __( 'Change this number if you want to edit the fee on this product. > 100 is more expensive, < 100 is percentage reduction',
						'woo-prod-set' ),
				]
			);
			?>

            <div class="options_group">
                <p class="form-field">
                    <label for="child_products">Child products</label>
                    <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="child_products"
                            name="child_products[]"
                            data-placeholder="<?= esc_attr__( 'Search for a product&hellip;', 'woocommerce' ); ?>"
                            data-action="woocommerce_json_search_products_and_variations"
                            data-exclude="<?= (int) $product->get_id(); ?>">
						<?php
						$product_object = $product;
						$product_ids    = $product_object->get_child_products();

						foreach ( $product_ids as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( is_object( $product ) ) { ?>
                                <option value="<?= esc_attr( $product_id ); ?>" <?= selected( true, true, false ); ?>>
									<?= htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) ?>
                                </option>
								<?php
							}
						}
						?>
                    </select>
                </p>
            </div>

        </div>
		<?php
	}

	/**
	 * Update price data to match set pricing
	 *
	 * @param $post_id
	 */
	public function savePriceData( $post_id )
	{
		/** @var WC_Product_Set $product */
		$product = wc_get_product( $post_id );
		if ( ! $product || $product->get_type() !== 'set' ) {
			return;
		}
		if ( ! isset( $_POST[ '_price_type' ] ) ) {
			return;
		}

		$product->update_meta_data( '_price_type', $_POST[ '_price_type' ] );
		$product->save_meta_data();

		$this->updateProductPrice( $product );;
	}

	/**
	 * @param  WC_Product_Set  $product
	 */
	public function updateProductPrice( WC_Product_Set $product )
	{
		$newPrice = 0.00;

		$product->delete_meta_data( '_sale_price' );
		$product->delete_meta_data( '_regular_price' );
		$product->delete_meta_data( '_price' );
		foreach ( $product->get_child_products() as $childProduct ) {
			$childProductTemp = wc_get_product( $childProduct );
			$newPrice         += $childProductTemp->get_price( 'edit' );
		}

		$product->add_meta_data( '_regular_price', $newPrice );

		switch ( $product->get_meta( '_price_type' ) ) {
			case 'fixed':
				if ( isset( $_POST[ '_set_price' ] ) ) {
					$price = sanitize_text_field( $_POST[ '_set_price' ] );
					$product->update_meta_data( '_sale_price', $price );
					$product->set_price( $price );
					$product->update_meta_data( '_set_price', $price );
				}
				break;
			case 'dynamic-sum':
				if ( isset( $_POST[ '_set_products' ] ) ) {
					$product->update_meta_data( '_price', $newPrice );
					$product->update_meta_data( '_set_price', $newPrice );
				}
				break;
			case 'dynamic-percentage':
				if ( isset( $_POST[ '_set_price_percentage_fee' ] ) ) {
					$reducedPrice = 0.00;
					foreach ( $product->get_child_products() as $id ) {
						$temp_product = wc_get_product( $id );
						$reducedPrice += $temp_product->get_price( 'edit' );
					}

					$reducedPrice *= (int) $_POST[ '_set_price_percentage_fee' ] / 100;
					$product->update_meta_data( '_sale_price', $reducedPrice );
					$product->update_meta_data( '_price', $reducedPrice );
					$product->update_meta_data( '_set_price', $reducedPrice );
					$product->update_meta_data( '_set_price_percentage_fee', $_POST[ '_set_price_percentage_fee' ] );
				}
				break;
		}
		$product->save_meta_data();
		$product->save();
		do_action( 'woocommerce_updated_product_price', $product->get_id() );
	}

	/**
	 * Saves the child products to parent SET product meta.
	 *
	 * @param $post_id
	 */
	public function saveChildProducts( $post_id )
	{
		/** @var WC_Product_Set $product */
		$product = wc_get_product( $post_id );

		if ( ! $product || $product->get_type() !== 'set' ) {
			return;
		}

		if ( ! isset( $_POST[ 'child_products' ] ) ) {
			return;
		}

		$children = $_POST[ 'child_products' ];
		$product->set_child_products( $children );
		$product->save_meta_data();
	}

	/**
	 * Enqueue the .js file to handle selections.
	 */
	public function enqueueScripts(): void
	{
		wp_enqueue_script(
			'woocommerce-product-sets-main',
			plugin_dir_url( WOOPRODSET_FILE ) . '/dist/scripts/main.iife.js',
			[ 'jquery' ],
			filemtime( plugin_dir_path( WOOPRODSET_FILE ) . '/dist/scripts/main.iife.js' ),
			true
		);
	}

	/**
	 * Modify WooCommerce class autoload to use namespaced {@see WC_Product_Set}.
	 *
	 * @param  string  $className     The original classFQN sought by WooCommerce.
	 * @param  string  $product_type  The product type set in the {@see addType()} function.
	 *
	 * @return string The classFQN used in the WooCommerce autoloader
	 */
	public function fixAutoloadClass( string $className, string $product_type ): string
	{
		if ( $product_type === 'set' ) {
			return WC_Product_Set::class;
		}

		return $className;
	}
}

