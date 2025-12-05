<?php
/**
 * My Account Page
 *
 * @package SCW_Shop
 */

defined( 'ABSPATH' ) || exit;

get_header();

do_action( 'woocommerce_before_main_content' );

$user_role = scw_shop_get_user_role();
$user_mode = scw_shop_get_user_mode();

// Check if we're on a WooCommerce endpoint by checking the request URI
global $wp;
$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
$wc_endpoints = array( 'edit-address', 'edit-account', 'orders', 'view-order', 'downloads', 'payment-methods' );
$is_wc_endpoint = false;

foreach ( $wc_endpoints as $endpoint ) {
	if ( strpos( $request_uri, $endpoint ) !== false ) {
		$is_wc_endpoint = true;
		break;
	}
}
?>

<div class="scw-account-page">
	<?php
	// If on a WooCommerce endpoint, use default WooCommerce templates
	if ( $is_wc_endpoint ) {
		// Let WooCommerce handle its own endpoints
		do_action( 'woocommerce_account_content' );
	} else {
		// Route to appropriate template based on user role for main account page
		if ( $user_role === 'guest' ) {
			// Login form for guests
			get_template_part( 'template-parts/account/login-form' );
		} elseif ( $user_role === 'reseller' ) {
			// Reseller dashboard
			get_template_part( 'template-parts/account/reseller-dashboard' );
		} elseif ( $user_role === 'client' ) {
			// Client account
			get_template_part( 'template-parts/account/client-account' );
		}
	}
	?>
</div>

<?php
do_action( 'woocommerce_after_main_content' );

get_footer();
