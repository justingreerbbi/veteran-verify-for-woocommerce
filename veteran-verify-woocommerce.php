<?php
/**
 * Plugin Name: Military Discounts for WooCommerce
 * Description: Provide discounts to U.S. Military Veterans and Active Service Members using Veteran Verifys service. Developed by a Veteran for a Veteran.
 * Version: 1.0.0
 * Requires at least: 6.0.0
 * Requires PHP:      7.4
 * Author:            justingreerbbi
 * Author URI:        https://dash10.digital/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       veteran-verify-woocommerce
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Load the plugin files
require_once( dirname( __FILE__ ) . '/includes/functions.php' );
require_once( dirname( __FILE__ ) . '/includes/settings.php' );
require_once( dirname( __FILE__ ) . '/includes/coupon-settings.php' );
require_once( dirname( __FILE__ ) . '/includes/actions.php' );
require_once( dirname( __FILE__ ) . '/includes/ajax.php' );

// Enqueue your JS file
add_action( 'wp_enqueue_scripts', 'veteran_verify_enqueue_scripts' );
function veteran_verify_enqueue_scripts( $hook ) {
	wp_enqueue_script( 'veteran-verify-js', plugins_url( '/js/veteran-verify.js', __FILE__ ), array( 'jquery' ) );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url
	wp_localize_script( 'veteran-verify-js', 'veteranVerify', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'veteran_verify' )
	) );
}

// Admin Nag if there is no API Key present but the plugin is enabled.
add_action( 'admin_notices', 'vet_verify_enabled_missing_api_key_admin_nag' );
function vet_verify_enabled_missing_api_key_admin_nag() {
	// If there is a key present, bail.
	if ( vet_verify_has_api_key() ) {
		return;
	}
	?>
<div class="error notice">
    <p>
        <?php _e( 'Militarty Discounts is enabled but there is no API Key present. Add an API Key by visiting the <a href="' . admin_url( 'admin.php?page=wc-settings&tab=advanced&section=veteran_verify' ) . '">plugin settings</a>.', 'veteran-verify-woocommerce' ); ?>
    </p>
</div>
<?php
}