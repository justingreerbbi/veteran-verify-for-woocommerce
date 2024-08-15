<?php
/**
 *  WooCommerce Settings Hooks
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_filter( 'woocommerce_get_sections_advanced', 'wcslider_add_section' );
function wcslider_add_section( $sections ) {
	$sections['veteran_verify'] = __( 'Military Discounts', 'veteran-verify-woocommerce' );
	return $sections;
}

add_filter( 'woocommerce_get_settings_advanced', 'veteran_verify_wc_settings', 10, 2 );
function veteran_verify_wc_settings( $settings, $current_section ) {
	if ( $current_section == 'veteran_verify' ) {
		$settings_slider = array();
		$settings_slider[] = array(
			'name' => __( 'Veteran Verification Settings', 'veteran-verify-woocommerce' ),
			'type' => 'title',
			'desc' => __( 'The following settings are used to configure Veteran verification services and discounts. If the plugin is enabled, it will attempt to verify military customers.', 'veteran-verify-woocommerce' ),
			'id' => 'wcslider',
		);

		$settings_slider[] = array(
			'name' => __( 'Veteran Verify API Key', 'veteran-verify-woocommerce' ),
			'desc_tip' => __( 'An API Key is required to use Veteran Verify\'s API services.', 'veteran-verify-woocommerce' ),
			'id' => 'veteran_verify_api_key',
			'type' => 'text',
			'desc' => __( 'Visit <a href="https://veteranverify.app/account" target="_blank">https://veteranverify.app</a> to get an API Key', 'veteran-verify-woocommerce' ),
		);

		$settings_slider[] = array( 'type' => 'sectionend', 'id' => 'wcslider' );

		return $settings_slider;
	} else {
		return $settings;
	}
}