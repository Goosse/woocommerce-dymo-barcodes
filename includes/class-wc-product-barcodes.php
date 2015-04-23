<?php

/**
 * Product Barcodes Integration
 *
 * Allows printing of labels using a DYMO LabelWriter printer.
 *
 * @class   WC_Product_Barcodes
 * @extends  WC_Integration
 * @since   1.0.2
 * @category  Class
 * @author  Jack Gregory
 */

class WC_Product_Barcodes extends WC_Integration {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $version = '1.0.2';

	/**
	 * Init and hook in the integration.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->id               	= 'product_barcodes';
		$this->method_title        	= __( 'Product Barcodes', 'wc-product-barcodes' );
		$this->method_description  	= __( 'Print simple barcode labels with your Dymo LabelWriter printer or export your products into Dymo label software.', 'wc-product-barcodes' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		$this->dymo_printer  = $this->get_option( 'dymo_printer' );
		$this->label_size    = $this->get_option( 'label_size' );

		// save settings
		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );

		// styles
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_styles' ) );

		// hooks
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_data_field' ) );
		add_action( 'admin_menu', array( $this, 'register_submenu_page' ) );
		add_action( 'admin_notices', array( $this, 'error_admin_notice' ) );
		add_action( 'load-edit.php', array( $this, 'do_bulk_action' ) );
		add_filter( 'admin_footer', array( $this, 'add_bulk_action' ) );
	}

	/**
	 * Load javascript, css files and localise parameters
	 *
	 * @access public
	 * @param mixed $hook
	 * @return void
	 */

	public function load_admin_styles( $hook ) {

		if ( $hook == 'product_page_product_barcodes' ||  $hook == 'woocommerce_page_wc-settings' ) {
			wp_register_script( 'woocommerce-product-barcode-dymo', plugins_url( '/assets/js/DYMO.Label.Framework.1.2.6.js', dirname( __FILE__ ) ), null, $this->version  );
			wp_register_script( 'woocommerce-product-barcode-script', plugins_url( '/assets/js/script.min.js', dirname( __FILE__ ) ), null, $this->version );

			$localize_array = array(
				'plugin_url'         => plugins_url( null, dirname( __FILE__ ) ),
				'dymo_printer'       =>  $this->get_option( 'dymo_printer') ? $this->get_option( 'dymo_printer' )  : null,
				'label_size'         =>  $this->get_option( 'label_size') ? $this->get_option( 'label_size' ) : 'medium',
				'label_loaded_error' => __( 'Cant print, label is not loaded.', 'wc-product-barcodes' ),
				'data_loaded_error'  => __( 'Cant print, label data is not loaded.', 'wc-product-barcodes' ),
			);

			wp_localize_script( 'woocommerce-product-barcode-script', 'wcb_params', $localize_array );

			wp_enqueue_script( 'woocommerce-product-barcode-dymo' );
			wp_enqueue_script( 'woocommerce-product-barcode-script' );

			wp_register_style( 'woocommerce-product-barcode', plugins_url( '/assets/css/admin.css', dirname( __FILE__ ) ), null, $this->version );
			wp_enqueue_style( 'woocommerce-product-barcode' );
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wc-product-barcodes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Initialise Settings Form Fields.
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {

		$this->form_fields = array (
			'dymo_printer'   	=> array (
				'title'        	=> __( 'Dymo Printer', 'wc-product-barcodes' ),
				'description'   => __( 'Choose an installed Dymo LabelWriter Printer.', 'wc-product-barcodes' ),
				'type'        	=> 'select',
				'css'        	=> 'min-width:300px;',
				'options'     	=> array (
					''         	=> __( 'Choose printer&hellip;', 'wc-product-barcodes' )
				),
				'default'       =>  ''
			),
			'label_size'     	=> array (
				'title'        	=> __( 'Label Size', 'wc-product-barcodes' ),
				'description'   => sprintf( __( 'Select a label size above. You can preview the options to be included on your label bellow. %s', 'wc-product-barcodes' ), '<span id="woocommerce-dymo-print-preview"><img src="" id="woocommerce-dymo-print-preview-img"></span>' ),
				'type'         	=> 'select',
				'css'        	=> 'min-width:300px;',
				'options'     	=> array (
					'medium'    => __( 'Medium - 32mm x 57mm', 'wc-product-barcodes' ),
					'large'     => __( 'Large - 70mm x 54mm', 'wc-product-barcodes' )
				),
				'default'       => 'medium',
			),
			'show_name'       	=> array (
				'title'        	=> __( 'Label Options', 'wc-product-barcodes' ),
				'label'        	=> __( 'Product Name', 'wc-product-barcodes' ),
				'description'   => __( 'Display product name.', 'wc-product-barcodes' ),
				'type'         	=> 'checkbox',
				'checkboxgroup' => 'start',
				'class'        	=> 'label-preview-option name',
				'default'      	=> 'yes',
				'desc_tip'    	=> true
			),
			'show_price'    	=> array (
				'label'        	=> __( 'Price', 'wc-product-barcodes' ),
				'description'   => __( 'Display product or variant price.', 'wc-product-barcodes' ),
				'type'         	=> 'checkbox',
				'checkboxgroup' => '',
				'class'        	=> 'label-preview-option metadata',
				'default'       => 'yes',
				'desc_tip'    	=>  true
			),
			'show_sku'      	=> array (
				'label'        	=> __( 'SKU', 'wc-product-barcodes' ),
				'description'   => __( 'Display product or variant SKU.', 'wc-product-barcodes' ),
				'type'         	=> 'checkbox',
				'checkboxgroup' => '',
				'class'        	=> 'label-preview-option metadata',
				'default'       => 'yes',
				'desc_tip'    	=>  true
			),
			'show_option'   	=> array (
				'label'        	=> __( 'Variant Option', 'wc-product-barcodes' ),
				'description'   => __( 'Display variant option.', 'wc-product-barcodes' ),
				'type'         	=> 'checkbox',
				'class'        	=> 'label-preview-option metadata',
				'default'       => 'yes',
				'desc_tip'    	=> true
			),
			'show_barcode'   	=> array (
				'label'        	=> __( 'Barcode', 'wc-product-barcodes' ),
				'description'   => __( 'Display barcode.', 'wc-product-barcodes' ),
				'type'        	=> 'checkbox',
				'checkboxgroup' => 'end',
				'class'       	=> 'label-preview-option barcode',
				'default'       => 'yes',
				'desc_tip'    	=> true
			),
			'use_sku'      		=> array (
				'title'        	=> __( 'Barcode Value', 'wc-product-barcodes' ),
				'label'        	=> __( 'Use SKU as the barcode value.', 'wc-product-barcodes' ),
				'description'   => __( 'By default the ID is used.', 'wc-product-barcodes' ),
				'type'         	=> 'checkbox',
				'default'       => 'no'
			)
		);

		// Select dynamic printer from list after save
		$selected_printer = '';

		if ( isset( $_POST[$this->plugin_id . $this->id . '_dymo_printer'] ) ) {
			$selected_printer = $_POST[$this->plugin_id . $this->id . '_dymo_printer'];
		} elseif ( $this->get_option( 'dymo_printer') ) {
			$selected_printer = $this->get_option( 'dymo_printer' );
		}

		if( is_admin() ) {
			wc_enqueue_js("
		    	$( window ).on( 'load', function() {
          			$( \"#woocommerce_product_barcodes_dymo_printer option[value='". esc_attr( $selected_printer ) ."']\" ).prop( 'selected', true );
        		} );
      		");
		}
	}

	/**
	 * Add sub menu page to products menu.
	 *
	 * @access public
	 * @return void
	 */
	public function register_submenu_page() {
		add_submenu_page( 'edit.php?post_type=product', __( 'Product Barcodes', 'wc-product-barcodes' ), __( 'Barcodes', 'wc-product-barcodes' ), 'manage_woocommerce', $this->id, array( $this, 'submenu_page_callback' ) );
	}

	/**
	 * Create a url for integrations settings tab.
	 *
	 * @access public
	 * @return void
	 */
	public function settings_url() {
		return add_query_arg( array( 'tab' => 'integration', 'section' => $this->id ), admin_url( 'admin.php?page=wc-settings' ) );
	}

	/**
	 * Display warning notice if settings or printer havent been set up.
	 *
	 * @access public
	 * @return string
	 */

	public function error_admin_notice() {
		global $typenow, $pagenow, $plugin_page;

		if ( $pagenow == 'edit.php' && $plugin_page == $this->id && $typenow == 'product' && $this->dymo_printer == '' ) {
			echo sprintf( "<div class=\"error\"><p>%s <a href=\"%s\">%s</a></p></div>", __( 'You need to set up your Dymo printer and label settings before you can print.', 'wc-product-barcodes' ), esc_url( $this->settings_url() ), __( 'View settings', 'wc-product-barcodes' ) );
		}
	}

	/**
	 * Display sub menu page.
	 *
	 * @access public
	 * @return string
	 */
	public function submenu_page_callback() {
		include_once( 'class-wc-product-barcodes-table.php' );

		?><div class="wrap">
			<h2><?php _e( 'Print Product Barcodes', 'wc-product-barcodes' ); ?></h2>
			<div class="tablenav top">
				<div class="actions alignleft">
					<p><a href="<?php echo esc_url( $this->settings_url() ); ?>" class="button" title="<?php echo esc_attr_e( 'View Settings', 'wc-product-barcodes' ); ?>"><?php _e( 'Settings', 'wc-product-barcodes' ); ?></a></p>
				</div>
				<div class="actions alignright">
					<p><button type="button" id="wcb_print_btn" class="button button-primary" disabled="disabled"><?php echo __( 'Print <span class="print_no"></span> barcodes', 'wc-product-barcodes' ); ?></button></p>
				</div>
			</div>
    <?php

		if ( ! class_exists( 'WC_Product_Barcodes_Table' ) ) {
			return;
		}

		$report = new WC_Product_Barcodes_Table();
		$report->output_report();
	}

	/**
	 * Get link for barcode print screen.
	 * @param  int $id
	 * @access public
	 * @return string
	 */
	public function get_print_screen_link( $id ) {
		return add_query_arg( array( 'page' => $this->id, 'id' => $id ), admin_url( 'edit.php?post_type=product' ) );
	}

	/**
	 * Get product attributes.
	 *
	 * @param object $product
	 * @access public
	 * @return array
	 */
	private function get_attributes( $product ) {
		$attributes = array();

		if ( $product->is_type( 'variation' ) ) {
			// variation attributes
			foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
				$attributes[] = ucwords( str_replace( '-', ' ', $attribute ) );
			}
		}

		return $attributes;
	}


	/**
	 * Add print button and label data to product data meta box
	 *
	 * @access public
	 * @return void
	 */
	public function product_data_field() {
		global $post;
		?><div class="options_group hide_if_downloadable hide_if_virtual">
		  	<p class="form-field wcb_general_print_btn">
			  <label for="wcb_general_print_btn"><?php _e( 'Barcodes', 'wc-product-barcodes' ); ?></label>
				<a href="<?php echo esc_url( $this->get_print_screen_link( $post->ID ) ); ?>" class="button"><?php _e( 'Print barcodes', 'wc-product-barcodes' ); ?></a>
        		<img class="help_tip" data-tip="<?php esc_attr_e( 'Print barcode labels for this product.', 'wc-product-barcodes' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
        	</p>
    	</div><?php 
	}

	/**
	 * Add extra bulk action options to export orders
	 *
	 * @access public
	 * @return void
	 */
	public function add_bulk_action() {
		global $post_type;

		if ( 'product' == $post_type ) {
			wc_enqueue_js("
				$( '<option>' ).val( 'export_barcodes' ).text( '" . __( 'Export Labels', 'wc-product-barcodes' ) . "' ).appendTo( \"select[name='action']\" );
				$( '<option>' ).val( 'export_barcodes' ).text( '" . __( 'Export Labels', 'wc-product-barcodes' ) . "' ).appendTo( \"select[name='action2']\" );
			");
		}
	}

	/**
	 * Process the bulk action and export products to a csv file
	 *
	 * @access public
	 * @return void
	 */
	function do_bulk_action() {
		global $typenow;
		$post_type = $typenow;

		if ( $post_type == 'product' ) {
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();

			$allowed_actions = array( 'export_barcodes' );

			if ( ! in_array( $action, $allowed_actions ) ) {
				return;
			}

			header( "Content-Type: text/csv;charset=utf-8" );
			header( "Content-Disposition: attachment;filename=\"" . apply_filters( 'woocommerce_product_barcodes_export_filename', 'product_labels' ) . ".csv\"" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );

			$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );
			$csv      = fopen( 'php://output', 'w' );

			$headers = apply_filters( 'woocommerce_product_barcodes_csv_headers', __( 'product_name, price, sku, id, variant_option', 'wc-product-barcodes' ) ) ."\n";

			fwrite( $csv, $headers );

			foreach( $post_ids as $post_id ) {
				$fields = array();
				$_product = get_product( $post_id );

				if ( $_product->is_type( 'variable' ) && $_product->has_child() ) {
					foreach ( $_product->get_children() as $child_id ) {
						$fields_variant = $fields;

						$_variation = $_product->get_child( $child_id );
						$attributes = $this->get_attributes( $_variation );

						$fields_variant[] = $_product->get_title();
						$fields_variant[] = wc_format_decimal( $_variation->get_price(), 2 );
						$fields_variant[] = $_variation->get_sku();
						$fields_variant[] = $_variation->get_variation_id();
						$fields_variant[] = join(' / ', $attributes );

						fputcsv( $csv, $fields_variant );
					}

				} else {
					$fields[] = $_product->get_title();
					$fields[] = wc_format_decimal( $_product->get_price(), 2 );
					$fields[] = $_product->get_sku();
					$fields[] = $_product->id;

					fputcsv( $csv, $fields );
				}

			}

			exit();
		}
	}
}

?>