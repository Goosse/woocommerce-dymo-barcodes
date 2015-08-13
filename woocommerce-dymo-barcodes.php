<?php

/*
 Plugin Name: WooCommerce Dymo Barcodes
 Description: Print WooCommerce Product barcode labels using Dymo Label Printers.
 Author: Jack Gregory
 Version: 1.0.3
 Author URI: http://media.platformplatform.com
 */

// Add the integration to WooCommerce
function wc_product_barcodes_add_integration( $integrations ) {
	global $woocommerce;

	if ( is_object( $woocommerce ) && version_compare( $woocommerce->version, '2.1-beta-1', '>=' ) ) {
		include_once( 'includes/class-wc-product-barcodes.php' );
		$integrations[] = 'WC_Product_Barcodes';
	}

	return $integrations;
}

add_filter( 'woocommerce_integrations', 'wc_product_barcodes_add_integration', 10 );

?>