<?php
/**
 * Plugin Name: WooCommerce Dymo Barcodes
 * Description: Print WooCommerce product barcode labels using a Dymo Label Printer.
 * Author: Jack Gregory
 * Version: 1.1.1
 * Author URI: http://media.platformplatform.com
 *
 * @package  woocommerce-dymo-barcodes
 */

if ( ! class_exists( 'WC_Product_Barcodes' ) ) :

	/**
	 * Add integration to WooCommerce.
	 */
	class WC_Product_Barcodes {

		/**
		* Construct the plugin.
		*/
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'updater' ) );
		}

		/**
		* Initialize the plugin.
		*/
		public function init() {
			// Checks if WooCommerce is installed.
			if ( class_exists( 'WC_Integration' ) ) {
				// Include integration class.
				include_once 'includes/class-wc-product-barcodes.php';
				// Register the integration.
				add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
			}
		}

		/**
		 * Add a new integration to WooCommerce.
		 */
		public function add_integration( $integrations ) {
			$integrations[] = 'WC_Product_Barcodes_Integration';

			return $integrations;
		}

		/**
		 * Plugin updater
		 */
		public function updater() {
			if ( ! class_exists( 'PucFactory' ) ) {
				require plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker/plugin-update-checker.php';
			}
			/** @var \PucGitHubChecker_3_1 $checker */
			$updater = PucFactory::getLatestClassVersion( 'PucGitHubChecker' );

			if ( class_exists( $updater ) ) {
				$checker = new $updater( 'https://github.com/Goosse/woocommerce-dymo-barcodes/', __FILE__, 'master' );
			}
		}
	}

	$WC_Product_Barcodes = new WC_Product_Barcodes( __FILE__ );

endif;
