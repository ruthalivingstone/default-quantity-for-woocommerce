<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/beyond88
 * @since      1.0.0
 *
 * @package    Default_Quantity_For_Woocommerce
 * @subpackage Default_Quantity_For_Woocommerce/public
 * @subpackage Default_Quantity_For_Woocommerce/public
 * @author     Mohiuddin Abdul Kader <muhin.cse.diu@gmail.com>
 */
class Default_Quantity_For_Woocommerce_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Save default quantity meta value for individual products.
	 *
	 * @since   1.0.0
	 * @params 	array, object		
	 * @return 	array
	*/	
	public function dqfwc_quantity_input_args( $args, $product ) {		
		// get default quantity for each product
		$args       =  $this->get_default_quantity($args,$product);
		// get step size for each product
		$dqfwc_step =  $this->get_step_size($args,$product);
		if (!empty($dqfwc_step) ){
			$args['min_value']   =  $dqfwc_step;
			$args['step'] 		 =	$dqfwc_step;
		}
		return $args;
	}

	/**
	 * Get default quantity (also used for "add to cart" button)
	 *
	 * @since   1.0.4
	 * @params 	object		
	 * @return 	float
	*/ 
	function get_default_quantity($args,$product) {
		
		/* Global default quantity */
		$dqfwc_global = get_option( 'woocommerce_default_quantity' );
		
		/* Category default quantity */
		$dqfwc_product_cats = wp_get_post_terms( $product->get_id(), 'product_cat' );
		foreach( $dqfwc_product_cats as $term ) {
			$term_id 	= $term->term_id;
			$term_meta  = get_option( "taxonomy_" . $term_id );
			$dqfwc_cat =  $term_meta['dqfwc_quantity']; 
		}
		
		/* Individual product default quantity */
		$dqfwc_ind = get_post_meta( $product->get_id(), 'dqfwc_default_quantity', true );

		/* Choose which value to use */
		if ( !empty( $dqfwc_ind ) ) {
			$args['quantity'] = $dqfwc_ind;
		} elseif ( !empty( $dqfwc_cat ) ) {
			$args['quantity'] = $dqfwc_cat;
		} elseif ( !empty( $dqfwc_global ) ) {
			$args['quantity'] = $dqfwc_global;
		} else { //if nothing is set, don't update the quantity
		}

		/* Update value on single product page */
		if ( ! is_cart() ) {
			$args['input_value'] = $args['quantity'];
		}

		return $args;
	}

	/**
	 * Add step value to the quantity field (default = 1)
	 *
	 * @since   1.0.4
	 * @params 	object		
	 * @return 	float
	 * Note: empty returns true if variable does not exist
	*/ 
	function get_step_size($args,$product) {

		/* Global step size */
		$step_global = get_option( 'woocommerce_decimal_quantity_step' );
		
		/* Category step size */
		$dqfwc_product_cats = wp_get_post_terms( $product->get_id(), 'product_cat' );
		foreach( $dqfwc_product_cats as $term ) {
			$term_id 	= $term->term_id;
			$term_meta  = get_option( "taxonomy_" . $term_id );
			$step_cat =  $term_meta['dqfwc_step']; 
		}

		/* Individual step size (If there is no unit name, step = 1)/
		if( empty (get_post_meta( $product->get_id(), 'izettle_unit_name_meta', true )) ){
			$step_ind = 1;
		}
		
		/* Choose which value to use */
		if( !empty( $step_ind ) ) {
			$dqfwc_step = $step_ind;
		} elseif( !empty( $step_cat ) ) {
			$dqfwc_step = $step_cat;
		} elseif( !empty( $step_global ) ) {
			$dqfwc_step = $step_global;
		} else {
			$dqfwc_step = 1; // if nothing is set, step size is 1
		}
		return $dqfwc_step;
	}
	
	/**
	 * Add unit price fix when showing the unit price on processed orders
	 *
	 * @since   1.0.4
	 * @params 	object		
	 * @return 	float
	*/ 
	function unit_price_fix($price, $order, $item, $inc_tax = false, $round = true) {
		$qty = (!empty($item['qty']) && $item['qty'] != 0) ? $item['qty'] : 1;
		if($inc_tax) {
			$price = ($item['line_total'] + $item['line_tax']) / $qty;
		} else {
			$price = $item['line_total'] / $qty;
		}
		$price = $round ? round( $price, 2 ) : $price;
		return $price;
	}
	/* END */

}
