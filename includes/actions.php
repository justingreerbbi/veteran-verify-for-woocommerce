<?php
/**
 * Main Plugin Actions
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Run right before the checkform is displayed.
 * This will check to see if the user has a military discount applied to their cart and if they are already verified.
 * If they are not verified then we will display a message and a button to start the verification process.
 * 
 * @return mixed
 */
add_action( 'woocommerce_before_checkout_form', 'veteran_verify_checkout_page_hook' );
function veteran_verify_checkout_page_hook() {

	// Only run if there is an API Key set.
	if ( ! vet_verify_has_api_key() ) {
		return;
	}

	$has_military_discount = false;
	$woo_session = WC()->session;
	$session_validation = $woo_session->get( 'veteran_certification' );

	if ( ! empty( $_GET['veteran_certification'] ) ) {
		$certification_key = sanitize_text_field( $_GET['veteran_certification'] );
		$apiKey = sanitize_text_field( get_option( 'veteran_verify_api_key' ) );
		$response = wp_remote_post( 'https://veteranverify.app/api/v1/verify-certification/', array(
			'body' => array(
				'apiKey' => $apiKey,
				'certification_key' => $certification_key,
			),
			'timeout' => 30,
			'headers' => array(),
		) );

		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );
			// Process the response body
			if ( ! empty( $response_body->certification_key_check ) && $response_body->certification_key_check == true ) {
				$woo_session->set( 'veteran_certification', 'validated' );
				wp_safe_redirect( wc_get_checkout_url() );
				exit;
			}
			exit;
		} else {
			// @todo Display an error message about the verification failing.
		}
	}
	?>

	<?php if ( vet_verify_has_military_discount_applied() && ! vet_verify_is_verified() ) : ?>
		<div class="wc-block-components-notice-banner is-error" style="display: block;">
			<p>
				<strong>You have applied a Military discount but have not verified your status yet.</strong> <br />
				Before you can check out you must verify your veteran status or you remove the coupon code to continue.
			</p>
			<a href="<?php print wp_nonce_url( admin_url( 'admin-post.php?action=veteran_verify_initiate_verification' ), 'veteran_verify_initiate_verification', 'security' ); ?>"
			   id="start-veteran-verification" class="button alt wp-element-button">Start Verification</a>
		</div>
	<?php endif; ?>

	<?php if ( vet_verify_has_military_discount_applied() && vet_verify_is_verified() ) : ?>
		<div class="wc-block-components-notice-banner is-success" style="display: block;">
			<p>
				<strong>Thank You For Your Service!</strong> <br />
				You have verified your military status and your military discount has been applied to your order.
			</p>
		</div>
	<?php endif; ?>
<?php
}

/**
 * Run during checkout validation
 * 
 * @param array $fields
 * @param array $errors
 * @return void
 */
add_action( 'woocommerce_after_checkout_validation', 'misha_validate_fname_lname', 10, 2 );
function misha_validate_fname_lname( $fields, $errors ) {

	// Only run if there is an API Key set.
	if ( ! vet_verify_has_api_key() ) {
		return;
	}

	if ( vet_verify_has_military_discount_applied() && ! vet_verify_is_verified() ) {
		$errors->add( 'validation', '<strong>Military Verification Required</strong>: Please verify your military status before checkout or remove the military coupon.' );
	}
}

/**
 * Run when a coupon code is applied to the cart.
 * 
 * @param string $coupon_code
 * @return string
 */
add_action( 'woocommerce_applied_coupon', 'my_custom_coupon_added_action', 1, 1 );
function my_custom_coupon_added_action( $coupon_code ) {

	// Only run if there is an API Key set.
	if ( ! vet_verify_has_api_key() ) {
		return;
	}

	$c = new WC_Coupon( $coupon_code );
	// Check to see if the user entered a military discount coupon and if they are not verified, we need to display a message.
	if ( $c->get_meta( 'military_discount' ) && ! vet_verify_is_verified() ) : ?>
		<div class="wc-block-components-notice-banner is-error" style="display: block;">
			<p>
				<strong>You have applied a Military discount but have not verified your status yet.</strong> <br />
				Before you can check out you must verify your veteran status or you remove the coupon code to continue.
			</p>
			<a href="#" id="start-veteran-verification" class="button alt wp-element-button">Start Verification</a>
		</div>
	<?php endif; ?>

	<?php if ( $c->get_meta( 'military_discount' ) && vet_verify_is_verified() ) : ?>
		<div class="wc-block-components-notice-banner is-success">
			<p>
				<strong>Thank You For Your Service!</strong> <br />
				You have verified your military status and your military discount has been applied to your order.
			</p>
		</div>
	<?php endif;
}

/**
 * ADMIN POST HANDLERS
 */

/**
 * Initiate the verification process.
 * Create a session variable to track the verification process and then redirect to the verification form.
 * This will use the settings API Key to call Veteran Verifiy to get a verification key to pass to the redirect.
 * 
 * This should only continue if the user has a military discount applied to their cart.
 */
add_action( "admin_post_veteran_verify_initiate_verification", 'veteran_verify_gen_verification_redirect' );
function veteran_verify_gen_verification_redirect() {

	// Only run if there is an API Key set.
	if ( ! vet_verify_has_api_key() ) {
		return;
	}

	// Check to see if the nonce is valid.
	$security = sanitize_text_field( $_GET['security'] );
	if ( ! wp_verify_nonce( $security, 'veteran_verify_initiate_verification' ) ) {
		wp_safe_redirect( add_query_arg( 'vv_error', 'failed', wc_get_checkout_url() ) );
		exit;
	}

	// Set the redirect URL to the checkout page.
	$redirect_url = wc_get_checkout_url();

	// Get the API Key from the settings page.
	$veteran_verify_api_key = get_option( 'veteran_verify_api_key' );

	// Call Veteran Verify Endpoint with the API Key to get a token to use for the
	$response = wp_remote_post( 'https://veteranverify.app/api/v1/createtoken', array(
		'method' => 'POST',
		'timeout' => 45,
		'redirection' => 2,
		'httpversion' => '1.1',
		'blocking' => true,
		'headers' => array(),
		'body' => array(
			'apiKey' => $veteran_verify_api_key,
			'redirect_url' => $redirect_url,
		),
		'cookies' => array(),
	)
	);

	// If we get a valid response then we can redirect to the verification form.
	$response = json_decode( wp_remote_retrieve_body( $response ) );
	if ( isset( $response->status ) && $response->status == true ) {
		$form_key = sanitize_text_field( $response->form_key );
		wp_redirect( 'https://veteranverify.app/veteran-verification-form/?form_key=' . $form_key );
		exit;
	}

	// If we get here then something went wrong. Lte's redirect back to the chart
	wp_safe_redirect( add_query_arg( 'vv_error', 'failed', wc_get_checkout_url() ) );
	exit;
}