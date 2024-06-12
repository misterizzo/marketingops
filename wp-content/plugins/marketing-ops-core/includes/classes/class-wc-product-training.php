<?php
/**
 * Custom product type - training class.
 *
 * @link       https://www.cmsminds.com/
 * @since      1.0.0
 *
 * @package    Marketing_Ops_Core
 * @subpackage Marketing_Ops_Core/public
 */

/**
 * custom product type - training
 *
 *
 * @package    Marketing_Ops_Core
 * @subpackage Marketing_Ops_Core/public
 * @author     cmsMinds <info@cmsminds.com>
 */
class WC_Product_Training extends WC_Product {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @param WC_Product $product WooCommerce product object.
	 * @since 1.0.0
	 */
	public function __construct( $product ) {
		$this->product_type = 'training';
		parent::__construct( $product );
	}
}
