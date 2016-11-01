<?php
/**
 * Product Barcodes Integration
 *
 * @class   	WC_Product_Barcodes_Table
 * @extends  	WP_List_Table
 * @since   	1.0.3
 * @category  	Class
 * @author  	Jack Gregory
 * @package  	woocommerce-dymo-barcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WC_Product_Barcodes_Table
 *
 * @author      Jack Gregory
 * @category    Admin
 * @version     1.0.1
 */
class WC_Product_Barcodes_Table extends WP_List_Table {

	/**
	 * __construct function.
	 *
	 * @access public
	 */
	public function __construct() {

		parent::__construct( array(
				'singular'  => __( 'Product Barcode', 'wc-product-barcodes' ),
				'plural'    => __( 'Product Barcodes', 'wc-product-barcodes' ),
				'ajax'      => false,
			) );
	}

	/**
	 * No items found text
	 */
	public function no_items() {
		_e( 'No products found.', 'wc-product-barcodes' );
	}

	/**
	 * Don't need this
	 *
	 * @param string $position table nav position.
	 */
	public function display_tablenav( $position ) {
		if ( $position != 'top' ) {
			parent::display_tablenav( $position );
		}
	}

	/**
	 * Output the report
	 */
	public function output_report() {
		$this->prepare_items();
		$this->display();
	}

	/**
	 * Get formated meta
	 *
	 * @param object $product product object.
	 */
	public function get_formated_meta( $product ) {

		$formatted_meta = array();

		foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {

			$name = urldecode( str_replace( 'attribute_', '', $attribute_name ) );

			if ( taxonomy_exists( $name ) ) {
				$term = get_term_by( 'slug', $attribute, $name );

				if ( ! is_wp_error( $term ) && is_object( $term ) && $term->name ) {
					$attribute = $term->name;
				}
			}

			$formatted_meta[] = array(
				'label' => wc_attribute_label( $name, $product ),
				'value' => $attribute,
			);
		}

		return $formatted_meta;
	}

	/**
	 * Column default
	 *
	 * @param mixed $product product object.
	 * @param mixed $column_name column name.
	 */
	public function column_default( $product, $column_name ) {
		$action_id = $product->is_type( 'variation' ) ? $product->parent->id : $product->id;

		switch ( $column_name ) {
			case 'product_image' :
				echo '<strong><a href="' . esc_url( get_edit_post_link( $action_id ) ) . '">' .  $product->get_image() . '</a></strong>';

				break;
			case 'product' :
				// Get variation data.
				if ( $product->is_type( 'variation' ) ) {

					$meta_list = array();

					$formatted_meta = $this->get_formated_meta( $product );

					if ( ! empty( $formatted_meta ) ) {
						foreach ( $formatted_meta as $meta ) {
							$meta_list[] = $meta['label'] . ': <strong>' . $meta['value'] . '</strong>';
						}
					}

					echo '<a href="' . esc_url( get_edit_post_link( $action_id ) ) . '">' . esc_attr( $product->parent->get_title() ) . '</a>';
					echo '<div class="description">' . implode( ', ', $meta_list ) . '</div>';
				} else {
					echo '<a href="' . esc_url( get_edit_post_link( $action_id ) ) . '">' . esc_attr( $product->get_title() ) . '</a>';
				}

				break;
			case 'product_price' :
				echo $product->get_price_html() ? $product->get_price_html() : '<span class="na">&ndash;</span>';

				break;

			case 'product_sku' :
				if ( $sku = $product->get_sku() ) {
					echo esc_html( $sku );
				} else {
					echo '<span class="na">&ndash;</span>';
				}

				break;

			case 'stock_level' :
				echo (int) $product->get_stock_quantity();

				break;

			case 'wcb_barcodes' :
				echo '<p>';
				echo "<input type='number' class='product-label-input' value='0' min='0' tabindex='1'>";

				$options = get_option( 'woocommerce_product_barcodes_settings' );

				if ( $sku = $product->get_sku() ) {
					$barcode = $sku;
				} else {
					$barcode = $product->id;
				}

				$metadata = array();

				$metadata[] = 'yes' === $options['show_price'] ? get_woocommerce_currency_symbol() . wc_format_decimal( $product->get_price(), 2 ) : '';
				$metadata[] = 'yes' === $options['show_sku'] ? $barcode : '';

				// Get variation data.
				if ( $product->is_type( 'variation' ) ) {

					$meta_list = array();

					$formatted_meta = $this->get_formated_meta( $product );

					if ( ! empty( $formatted_meta ) ) {
						foreach ( $formatted_meta as $meta ) {
							$meta_list[] = $meta['value'];
						}
					}

					$metadata[] = 'yes' === $options['show_option'] ? join( ' / ', $meta_list ) : '';
				}

				echo "<input type='hidden' class='product-metadata' value='" . esc_attr( join( ' ', $metadata ) ) . "'>";
				echo "<input type='hidden' class='product-name' value='" . esc_attr( $product->get_title() ) . "'>";
				echo "<input type='hidden' class='product-barcode' value='" . esc_attr( $barcode ) . "'>";
				echo '</p>';

				break;
		}
	}

	/**
	 * Get columns
	 */
	public function get_columns() {
		$columns = array(
			'product_image'     => '<span class="wc-image tips" data-tip="' . __( 'Image', 'woocommerce' ) . '">' . __( 'Image', 'woocommerce' ) . '</span>',
			'product'           => __( 'Product', 'wc-product-barcodes' ),
			'product_price'     => __( 'Price', 'wc-product-barcodes' ),
			'product_sku'       => __( 'SKU', 'wc-product-barcodes' ),
			'stock_level'       => __( 'Quantity', 'wc-product-barcodes' ),
			'wcb_barcodes'      => __( 'Barcodes', 'wc-product-barcodes' ),
		);

		return $columns;
	}

	/**
	 * Get products post type.
	 */
	public function prepare_items() {

		$per_page = apply_filters( 'woocommerce_product_barcodes_products_per_page', 20 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// Column headers.
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		// Query args.
		$args = array(
			'post_type'           => array( 'product' ),
			'posts_per_page'      => $per_page,
			'ignore_sticky_posts' => true,
			'paged'               => $current_page,
		);

		if ( isset( $_REQUEST['product_ids'] ) ) {
			$ids = explode( ',', $_REQUEST['product_ids'] );

			$args['post__in'] 		= array_merge( array( 0 ), wc_clean( $ids ) );
			$args['posts_per_page']	= -1;
		}

		$products = new WP_Query( $args );

		$items = array();

		foreach ( $products->posts as $item ) {
			$product = wc_get_product( $item->ID );

			if ( $product->is_type( 'variable' ) && $product->has_child() ) {
				foreach ( $product->get_children() as $child_id ) {
					$variation = $product->get_child( $child_id );
					if ( ! $variation->exists() ) {
						continue;
					}

					$items[] = $variation;
				}
			} else {
				$items[] = $product;
			}
		}

		$this->items = $items;
	}
}
