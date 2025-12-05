<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Skip WooCommerce default hooks - we handle everything ourselves
remove_all_actions( 'woocommerce_before_checkout_form' );

// Check if guest checkout is allowed (either globally or temporarily in session)
$guest_checkout_enabled = get_option( 'woocommerce_enable_guest_checkout' ) === 'yes';

// Check session only if WooCommerce session is properly initialized
if ( ! $guest_checkout_enabled && is_object( WC()->session ) ) {
	$session_value = WC()->session->get( 'guest_checkout_enabled' );
	if ( $session_value ) {
		$guest_checkout_enabled = true;
	}
}

// Check if user is logged in - allow guest checkout
if ( ! is_user_logged_in() && ! $guest_checkout_enabled ) {
	// Redirect to login with return URL (back to checkout after login)
	$return_url = add_query_arg( 'keep_cart', '1', wc_get_checkout_url() );
	$login_url = add_query_arg( 'redirect_to', urlencode( $return_url ), wc_get_page_permalink( 'myaccount' ) );
	?>
	<div class="checkout-container">
		<div class="checkout-login-prompt">
			<div class="checkout-login-card">
				<div class="login-main-content">
					<div class="login-icon">
						<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="#5899e2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<circle cx="12" cy="7" r="4" stroke="#5899e2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<h2><?php esc_html_e( 'Finaliser votre commande', 'aleaulavage-v2' ); ?></h2>
					<p class="login-subtitle"><?php esc_html_e( 'Connectez-vous pour accéder à vos informations ou continuez en tant qu\'invité', 'aleaulavage-v2' ); ?></p>

					<div class="login-actions">
						<a href="<?php echo esc_url( $login_url ); ?>" class="btn-login btn-primary">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
								<polyline points="10 17 15 12 10 7"></polyline>
								<line x1="15" y1="12" x2="3" y2="12"></line>
							</svg>
							<?php esc_html_e( 'Se connecter', 'aleaulavage-v2' ); ?>
						</a>

						<form method="post" action="<?php echo esc_url( wc_get_checkout_url() ); ?>">
							<input type="hidden" name="enable_guest_checkout_temp" value="1" />
							<?php wp_nonce_field( 'enable_guest_checkout', 'guest_checkout_nonce' ); ?>
							<button type="submit" class="btn-guest btn-secondary">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
									<circle cx="8.5" cy="7" r="4"></circle>
									<line x1="20" y1="8" x2="20" y2="14"></line>
									<line x1="23" y1="11" x2="17" y2="11"></line>
								</svg>
								<?php esc_html_e( 'Continuer en tant qu\'invité', 'aleaulavage-v2' ); ?>
							</button>
						</form>
					</div>
				</div>

				<div class="login-benefits">
					<h4><?php esc_html_e( 'Avantages de la connexion :', 'aleaulavage-v2' ); ?></h4>
					<ul>
						<li>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
								<polyline points="20 6 9 17 4 12"></polyline>
							</svg>
							<?php esc_html_e( 'Accès à vos commandes précédentes', 'aleaulavage-v2' ); ?>
						</li>
						<li>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
								<polyline points="20 6 9 17 4 12"></polyline>
							</svg>
							<?php esc_html_e( 'Informations pré-remplies', 'aleaulavage-v2' ); ?>
						</li>
						<li>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
								<polyline points="20 6 9 17 4 12"></polyline>
							</svg>
							<?php esc_html_e( 'Suivi de commande simplifié', 'aleaulavage-v2' ); ?>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php
	return;
}

// Get cart
$cart = WC()->cart;
$cart_items = $cart->get_cart();

// Empty cart - redirect
if ( empty( $cart_items ) ) {
	wp_redirect( wc_get_cart_url() );
	exit;
}

// Get current user data
$current_user = wp_get_current_user();
$user_meta = get_user_meta( $current_user->ID );

$billing_first_name = $user_meta['billing_first_name'][0] ?? $current_user->first_name;
$billing_last_name = $user_meta['billing_last_name'][0] ?? $current_user->last_name;
$billing_company = $user_meta['billing_company'][0] ?? '';
$billing_email = $user_meta['billing_email'][0] ?? $current_user->user_email;
$billing_phone = $user_meta['billing_phone'][0] ?? '';
$billing_address_1 = $user_meta['billing_address_1'][0] ?? '';
$billing_postcode = $user_meta['billing_postcode'][0] ?? '';
$billing_city = $user_meta['billing_city'][0] ?? '';

$shipping_first_name = $user_meta['shipping_first_name'][0] ?? $billing_first_name;
$shipping_last_name = $user_meta['shipping_last_name'][0] ?? $billing_last_name;
$shipping_company = $user_meta['shipping_company'][0] ?? $billing_company;
$shipping_address_1 = $user_meta['shipping_address_1'][0] ?? $billing_address_1;
$shipping_postcode = $user_meta['shipping_postcode'][0] ?? $billing_postcode;
$shipping_city = $user_meta['shipping_city'][0] ?? $billing_city;
$shipping_siret = $user_meta['shipping_siret'][0] ?? '';

// Check if billing and shipping addresses are different
$addresses_are_different = (
	$billing_address_1 !== $shipping_address_1 ||
	$billing_postcode !== $shipping_postcode ||
	$billing_city !== $shipping_city
);
?>

<div class="checkout-container">

	<!-- HEADER FULL WIDTH -->
	<div class="checkout-header">
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="back-link">
			← <?php esc_html_e( 'Retour au panier', 'aleaulavage-v2' ); ?>
		</a>
		<h1><?php esc_html_e( 'Validation de commande', 'aleaulavage-v2' ); ?></h1>
	</div>

	<!-- WooCommerce Form Wrapper (Hidden but functional) -->
	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

		<!-- Hidden fields for country, state, address_2 (not shown in form) -->
		<input type="hidden" name="billing_address_2" id="billing_address_2" value="" />
		<input type="hidden" name="billing_country" id="billing_country" value="FR" />
		<input type="hidden" name="billing_state" id="billing_state" value="" />
		<input type="hidden" name="shipping_first_name" id="hidden_shipping_first_name" value="<?php echo esc_attr( $shipping_first_name ); ?>" />
		<input type="hidden" name="shipping_last_name" id="hidden_shipping_last_name" value="<?php echo esc_attr( $shipping_last_name ); ?>" />
		<input type="hidden" name="shipping_company" id="hidden_shipping_company" value="<?php echo esc_attr( $shipping_company ); ?>" />
		<input type="hidden" name="shipping_siret" id="hidden_shipping_siret" value="<?php echo esc_attr( $shipping_siret ); ?>" />
		<input type="hidden" name="shipping_address_1" id="hidden_shipping_address_1" value="<?php echo esc_attr( $shipping_address_1 ); ?>" />
		<input type="hidden" name="shipping_postcode" id="hidden_shipping_postcode" value="<?php echo esc_attr( $shipping_postcode ); ?>" />
		<input type="hidden" name="shipping_city" id="hidden_shipping_city" value="<?php echo esc_attr( $shipping_city ); ?>" />
		<input type="hidden" name="shipping_address_2" id="shipping_address_2" value="" />
		<input type="hidden" name="shipping_country" id="shipping_country" value="FR" />
		<input type="hidden" name="shipping_state" id="shipping_state" value="" />

	<div class="checkout-grid">
		<!-- GAUCHE : ÉTAPES -->
		<div class="checkout-steps">

			<!-- ÉTAPE 1 : ADRESSE -->
			<section class="checkout-step active" data-step="1">
				<div class="step-header">
					<div class="step-number">1</div>
					<h2><?php esc_html_e( 'Adresse', 'aleaulavage-v2' ); ?></h2>
				</div>

				<div class="step-content">
					<!-- ADRESSE FACTURATION -->
					<h3 class="sub-title"><?php esc_html_e( 'Facturation', 'aleaulavage-v2' ); ?></h3>
					<div class="form-grid">
						<div class="form-group half">
							<label><?php esc_html_e( 'Prénom', 'aleaulavage-v2' ); ?> <span class="required">*</span></label>
							<input type="text" id="billing_first_name" name="billing_first_name" value="<?php echo esc_attr( $billing_first_name ); ?>" required />
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'Nom', 'aleaulavage-v2' ); ?> <span class="required">*</span></label>
							<input type="text" id="billing_last_name" name="billing_last_name" value="<?php echo esc_attr( $billing_last_name ); ?>" required />
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'Société', 'aleaulavage-v2' ); ?> <span class="required">*</span></label>
							<input type="text" id="billing_company" name="billing_company" value="<?php echo esc_attr( $billing_company ); ?>" required />
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'SIRET', 'aleaulavage-v2' ); ?> <span class="required">*</span></label>
							<input type="text" id="billing_siret" name="billing_siret" value="<?php echo esc_attr( $user_meta['billing_siret'][0] ?? '' ); ?>" required />
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'Email', 'aleaulavage-v2' ); ?> <span class="required">*</span></label>
							<input type="email" id="billing_email" name="billing_email" value="<?php echo esc_attr( $billing_email ); ?>" required />
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'Téléphone', 'aleaulavage-v2' ); ?> <span class="required">*</span></label>
							<input type="tel" id="billing_phone" name="billing_phone" value="<?php echo esc_attr( $billing_phone ); ?>" required />
						</div>
						<div class="form-group full">
							<label><?php esc_html_e( 'Adresse', 'aleaulavage-v2' ); ?> <span class="required">*</span></label>
							<div class="address-autocomplete-wrapper">
								<input type="text" id="billing_address_1" name="billing_address_1" value="<?php echo esc_attr( $billing_address_1 ); ?>" placeholder="<?php esc_attr_e( 'N° et nom de rue', 'aleaulavage-v2' ); ?>" required />
								<div id="billing-address-suggestions" class="address-suggestions"></div>
							</div>
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'Code Postal', 'aleaulavage-v2' ); ?> <span class="required">*</span></label>
							<input type="text" id="billing_postcode" name="billing_postcode" value="<?php echo esc_attr( $billing_postcode ); ?>" required />
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'Ville', 'aleaulavage-v2' ); ?> <span class="required">*</span></label>
							<input type="text" id="billing_city" name="billing_city" value="<?php echo esc_attr( $billing_city ); ?>" required />
						</div>
					</div>

					<!-- OPTION LIVRAISON -->
					<div class="billing-toggle">
						<label class="checkbox-label">
							<input type="checkbox" id="same-billing" <?php echo $addresses_are_different ? '' : 'checked'; ?> />
							<?php esc_html_e( 'Adresse de livraison identique à la facturation', 'aleaulavage-v2' ); ?>
						</label>
					</div>

					<!-- ADRESSE LIVRAISON (SI DIFFÉRENTE) -->
					<div class="billing-form" id="billing-form" style="display: <?php echo $addresses_are_different ? 'block' : 'none'; ?>;">
						<h3 class="sub-title"><?php esc_html_e( 'Livraison', 'aleaulavage-v2' ); ?></h3>
						<div class="form-grid">
							<div class="form-group full">
								<label><?php esc_html_e( 'Adresse', 'aleaulavage-v2' ); ?></label>
								<div class="address-autocomplete-wrapper">
									<input type="text" id="shipping_address_1_display" class="shipping-field-display" data-target="hidden_shipping_address_1" value="<?php echo esc_attr( $shipping_address_1 ); ?>" placeholder="<?php esc_attr_e( 'N° et nom de rue', 'aleaulavage-v2' ); ?>" />
									<div id="shipping-address-suggestions" class="address-suggestions"></div>
								</div>
							</div>
							<div class="form-group half">
								<label><?php esc_html_e( 'Code Postal', 'aleaulavage-v2' ); ?></label>
								<input type="text" id="shipping_postcode_display" class="shipping-field-display" data-target="hidden_shipping_postcode" value="<?php echo esc_attr( $shipping_postcode ); ?>" />
							</div>
							<div class="form-group half">
								<label><?php esc_html_e( 'Ville', 'aleaulavage-v2' ); ?></label>
								<input type="text" id="shipping_city_display" class="shipping-field-display" data-target="hidden_shipping_city" value="<?php echo esc_attr( $shipping_city ); ?>" />
							</div>
						</div>
					</div>

					<button type="button" class="btn-next" id="btn-to-step-2">
						<?php esc_html_e( 'Continuer vers le paiement', 'aleaulavage-v2' ); ?>
					</button>
				</div>

				<!-- Summary when completed -->
				<div class="step-summary" style="display: none;">
					<div>
						<p><strong><?php esc_html_e( 'Facturation :', 'aleaulavage-v2' ); ?></strong> <span id="summary-address"></span></p>
						<p class="text-sm text-gray" id="summary-billing-note"><?php esc_html_e( 'Livraison identique', 'aleaulavage-v2' ); ?></p>
					</div>
					<button type="button" class="btn-edit" id="btn-edit-step-1"><?php esc_html_e( 'Modifier', 'aleaulavage-v2' ); ?></button>
				</div>
			</section>

			<!-- ÉTAPE 2 : PAIEMENT -->
			<section class="checkout-step" data-step="2">
				<div class="step-header">
					<div class="step-number">2</div>
					<h2><?php esc_html_e( 'Paiement', 'aleaulavage-v2' ); ?></h2>
				</div>

				<div class="step-content step-hidden" id="step2-content">
					<?php
					// WooCommerce payment methods
					woocommerce_checkout_payment();
					?>

					<div class="secure-footer">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
							<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
						</svg>
						<?php esc_html_e( 'Transaction chiffrée SSL', 'aleaulavage-v2' ); ?>
					</div>
				</div>
			</section>
		</div>

		<!-- DROITE : RÉSUMÉ -->
		<div class="checkout-sidebar">
			<div class="order-summary">
				<h3><?php esc_html_e( 'Récapitulatif', 'aleaulavage-v2' ); ?></h3>

				<div class="summary-items">
					<?php foreach ( $cart_items as $cart_item_key => $cart_item ) : ?>
						<?php
						$product = $cart_item['data'];
						$quantity = $cart_item['quantity'];
						$product_name = $product->get_name();
						$product_image = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
						if ( ! $product_image ) {
							$product_image = wc_placeholder_img_src();
						}
						$line_price = (float) $product->get_price() * $quantity;
						?>
						<div class="mini-item">
							<div class="mini-img">
								<img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( $product_name ); ?>" />
							</div>
							<div class="mini-info">
								<span><?php echo esc_html( wp_trim_words( $product_name, 4, '...' ) ); ?></span>
								<span class="mini-qty">x<?php echo esc_html( $quantity ); ?></span>
							</div>
							<span class="mini-price"><?php echo wc_price( $line_price ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="summary-totals">
					<div class="line">
						<span><?php esc_html_e( 'Sous-total HT', 'aleaulavage-v2' ); ?></span>
						<span><?php echo $cart->get_cart_subtotal(); ?></span>
					</div>
					<div class="line">
						<span><?php esc_html_e( 'TVA', 'aleaulavage-v2' ); ?></span>
						<span><?php echo wc_price( $cart->get_total_tax() ); ?></span>
					</div>
					<?php if ( $cart->needs_shipping() && $cart->show_shipping() ) : ?>
					<div class="line">
						<span><?php esc_html_e( 'Livraison', 'aleaulavage-v2' ); ?></span>
						<span>
							<?php
							$shipping_total = $cart->get_shipping_total();
							if ( $shipping_total > 0 ) {
								echo wc_price( $shipping_total );
							} else {
								esc_html_e( 'Offerte', 'aleaulavage-v2' );
							}
							?>
						</span>
					</div>
					<?php endif; ?>
					<div class="line total">
						<span><?php esc_html_e( 'Total TTC', 'aleaulavage-v2' ); ?></span>
						<span><?php echo $cart->get_total(); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	</form>

</div>
