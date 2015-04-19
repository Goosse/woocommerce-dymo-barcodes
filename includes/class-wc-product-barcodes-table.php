<?php

/**
 * Product Barcodes Integration
 *
 * @class   WC_Product_Barcodes_Table
 * @extends  WP_List_Table
 * @since   1.0.1
 * @category  Class
 * @author  Jack Gregory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
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
			'ajax'      => false
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
	 * column_default function.
	 *
	 * @param mixed $item
	 * @param mixed $column_name
	 */
	public function column_default( $product, $column_name ) {
		$action_id = $product->is_type( 'variation' ) ? $product->parent->id : $product->id;
		
		switch( $column_name ) {
			case 'product_image' :	
      			echo '<a href="' . get_edit_post_link( $action_id ) . '">' . $product->get_image() . '</a>';
			break;
			
			case 'product' :
				// Get variation data
				if ( $product->is_type( 'variation' ) ) {
					$list_attributes = array();
					$attributes = $product->get_variation_attributes();

					foreach ( $attributes as $name => $attribute ) {
						$list_attributes[] = ucwords( str_replace( '-', ' ', $attribute ) );
					}

					echo '<a href="' . get_edit_post_link( $action_id ) . '">' . $product->parent->get_title() . '</a>';
					echo '<br>' . implode( ' / ', $list_attributes );
				} else {
					echo '<a href="' . get_edit_post_link( $action_id ) . '">' . $product->get_title() . '</a>';
				}
			break;
			case 'product_price' :
				echo $product->get_price_html() ? $product->get_price_html() : '<span class="na">&ndash;</span>';
			break;
			
			case 'product_sku' :
				if ( $sku = $product->get_sku() ) {
					echo $sku;
				} else {
  					echo '<span class="na">&ndash;</span>';
        		}
			break;

			case 'stock_level' :
				echo $product->get_stock_quantity();
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
				    
				$metadata[] = $options['show_price'] == 'yes' ? get_woocommerce_currency_symbol() . wc_format_decimal( $product->get_price(), 2 ) : '';
            	$metadata[] = $options['show_sku'] == 'yes' ? $barcode : '';	
				    
				// Get variation data
	            if ( $product->is_type( 'variation' ) ) {
		            	$list_attributes = array();
		            	$attributes = $product->get_variation_attributes();

		              	foreach ( $attributes as $name => $attribute ) {
		               		$list_attributes[] = ucwords( str_replace( '-', ' ', $attribute ) );
						}
							    
		              	$metadata[] = $options['show_option'] == 'yes' ? join(' / ', $list_attributes ) : '';
	              		echo "<input type='hidden' class='product-metadata' value='" . esc_attr( join( ' ', $metadata ) ) . "'>";
					}
            
				echo "<input type='hidden' class='product-name' value='" . esc_attr( $product->get_title() ) . "'>";
            	echo "<input type='hidden' class='product-barcode' value='" . esc_attr( $barcode ) . "'>";
				echo '</p>';
			break;
		}
	}

	/**
	 * get_columns function.
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
	 *
	 * @access public
	 * @return array
	 */
	public function prepare_items() {
		
		$per_page = apply_filters( 'woocommerce_product_barcodes_products_per_page', 20 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		// Query args
		$args = array(
			'post_type'           => array( 
				'product', 
				'product_variation' 
			),
			'posts_per_page'      => $per_page,
			'ignore_sticky_posts' => true,
			'paged'               => $current_page
		);

		if ( isset( $_REQUEST['id'] ) ) {
	  		$product = wc_get_product( $_REQUEST['id'] );
	  				
	  		if( $product->has_child() ) {
	    		$args['post_parent__in'] = array( $_REQUEST['id'] );
	  		} else {
	    		$args['post__in'] = array( $_REQUEST['id'] );
	      	}
		}

		$products = new WP_Query( $args );

		$items = array();

		foreach( $products->posts as $item ) {
			$product = wc_get_product( $item->ID );
			
			if( $product->is_type( 'variable' ) && $product->has_child() ) {
				continue;
			}

			$items[] = $product;
		}

		$this->items = $items;
	}
}
