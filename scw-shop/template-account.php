<?php
/**
 * Template Name: Page Compte
 * Template for account/login page
 *
 * @package SCW_Shop
 */

get_header();

$user_role = scw_shop_get_user_role();
$user_mode = scw_shop_get_user_mode();
?>

<main id="main" class="site-main">
	<div class="scw-account-page">
		<?php
		// Route to appropriate template based on user role
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
		?>
	</div>
</main>

<?php
get_footer();
