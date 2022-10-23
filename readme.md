# WooCommerce Product Sets

This WordPress plugin aims to provide a developer and user-friendly
way of creating sets of products that can be displayed. 

The product stock is connected so selling a set also reduces the
stock for the items in it. A set cannot be bought when one of the
products is out of stock.

The pricing can be done with a fixed price, an auto sum of the products
it contains, or a percentage of the sum of the products it contains.
This percentage can be set to 100 for full price, or anything below
that for a reduction. In case you want to up the price, you can set
the percentage to a higher number than 100.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-product-sets` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

**Composer**

You can also install the plugin via composer, it is registered as
a "type": "wordpress-plugin". This way you can require it in a
bedrock installation.