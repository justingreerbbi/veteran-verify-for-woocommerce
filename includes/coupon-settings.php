<?php
/**
 * Actions specific to WooCommerce Coupons and Military Discounts
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Add a custom field to Admin coupon settings pages
add_action( 'woocommerce_coupon_options', 'add_coupon_text_field', 10 );
function add_coupon_text_field() {
	woocommerce_wp_checkbox( array(
		'id' => 'military_discount',
		'label' => __( 'Military Discount', 'veteran-verify-woocommerce' ),
		'type' => 'checkbox',
		'css' => 'min-width:300px;',
		'placeholder' => '',
		'description' => __( 'Forces the customer to verify their military status before it can be applied.', 'veteran-verify-woocommerce' ),
		'desc_tip' => true,

	) );
}

// Save the custom field value from Admin coupon settings pages
add_action( 'woocommerce_coupon_options_save', 'save_coupon_text_field', 10, 2 );
function save_coupon_text_field( $post_id, $coupon ) {
	$military_discount = isset( $_POST['military_discount'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, 'military_discount', $military_discount );
}