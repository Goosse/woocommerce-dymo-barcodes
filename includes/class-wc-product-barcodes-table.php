<?php

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
	public function column_default( $item, $column_name ) {
		global $product;

		if ( ! $product || $product->id !== $item->id ) {
			$product = wc_get_product( $item->id );
		}
		
		$action_id = $product->is_type( 'variation' ) ? $item->parent : $item->id;

		switch( $column_name ) {

			case 'product_image' :	
      echo '<a href="' . get_edit_post_link( $action_id ) . '">' . $product->get_image() . '</a>';
			break;
			
			case 'product' :
				echo '<a href="' . get_edit_post_link( $action_id ) . '">' . $product->get_title() . '</a>';

				// Get variation data
				if ( $product->is_type( 'variation' ) ) {
					$list_attributes = array();
					$attributes = $product->get_variation_attributes();

					foreach ( $attributes as $name => $attribute ) {
						$list_attributes[] = ucwords( $attribute );
					}

					echo '<br>' . implode( ' / ', $list_attributes );
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
				?><p>
					<?php
  					
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
                $list_attributes[] = ucwords( $attribute );
					    }
					    
              $metadata[] = $options['show_option'] == 'yes' ? join(' / ', $list_attributes ) : '';
              echo "<input type='hidden' class='product-metadata' value='".esc_attr( join( ' ', $metadata ) )."'>";
					  }
            
						echo "<input type='hidden' class='product-name' value='".esc_attr( $product->get_title() )."'>";
            echo "<input type='hidden' class='product-barcode' value='".esc_attr( $barcode )."'>";
					?>
				</p><?php
			break;
		}
	}

	/**
	 * get_columns function.
	 */
	public function get_columns() {

		$columns = array(
  		'product_image'      => '<span class="wc-image tips" data-tip="' . __( 'Image', 'woocommerce' ) . '">' . __( 'Image', 'woocommerce' ) . '</span>',
			'product'            => __( 'Product', 'wc-product-barcodes' ),
			'product_price'      => __( 'Price', 'wc-product-barcodes' ),
      'product_sku'        => __( 'Product SKU', 'wc-product-barcodes' ),
			'stock_level'        => __( 'Quantity', 'wc-product-barcodes' ),
			'wcb_barcodes'       => __( 'Barcodes', 'wc-product-barcodes' ),
		);

		return $columns;
	}

	/**
	 * prepare_items function.
	 */
	public function prepare_items() {

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$current_page          = absint( $this->get_pagenum() );
		$per_page              = apply_filters( 'woocommerce_product_barcodes_products_per_page', 20 );

		$this->get_items( $current_page, $per_page );

		/**
		 * Pagination
		 */
		$this->set_pagination_args( array(
			'total_items' => $this->max_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		) );
	}
	
  /**
	 * Get products post type.
	 *
	 * @access public
	 * @return array
	 */
	public function get_items( $current_page, $per_page ) {
		global $wpdb;
		
		$this->max_items = 0;
		$this->items     = array();
		
		$query_from = "FROM {$wpdb->posts} as posts
			INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
			INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
			WHERE 1=1
			AND posts.post_type IN ( 'product', 'product_variation' )
			AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
			";
		
		if ( isset( $_REQUEST['id'] ) ) {
  		
  		$product = wc_get_product( $_REQUEST['id'] );
  				
  		if( $product->has_child() ) {
    		$query_from .= " AND posts.post_parent = '".$_REQUEST['id']."' " ;
  		} else {
    		$query_from .= " AND posts.ID = '".$_REQUEST['id']."' ";
      }
		}

		$this->items     = $wpdb->get_results( $wpdb->prepare( "SELECT posts.ID as id, posts.post_parent as parent {$query_from} GROUP BY posts.ID ORDER BY posts.post_title DESC LIMIT %d, %d;", ( $current_page - 1 ) * $per_page, $per_page ) );
		$this->max_items = $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" );
	}
}
