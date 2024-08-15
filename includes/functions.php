<?php
/**
 * Main Plugin Functions
 * This file contains all of the functions for the plugin.
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Check if the current cart has a military discount applied.
 */
function vet_verify_has_military_discount_applied() {
	$has_military_discount = false;
	$coupon_codes = WC()->cart->get_applied_coupons();
	foreach ( $coupon_codes as $code ) {
		$c = new WC_Coupon( $code );
		if ( $c->get_meta( 'military_discount' ) ) {
			$has_military_discount = true;
		}
	}
	return $has_military_discount;
}

/**
 * Check if the current cart session is veteran verified
 */
function vet_verify_is_verified() {
	$woo_session = WC()->session;
	$session_validation = $woo_session->get( 'veteran_certification' );
	if ( $session_validation == 'validated' ) {
		return true;
	}
	return false;
}

/**
 * Check if the api is present
 */
function vet_verify_has_api_key() {
	$api_key = get_option( 'veteran_verify_api_key' );
	if ( ! empty( $api_key ) ) {
		return true;
	}
	return false;
}