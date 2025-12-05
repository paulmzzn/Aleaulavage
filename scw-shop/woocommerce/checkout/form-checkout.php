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

$user_role = scw_shop_get_user_role();
$is_reseller = ( $user_role === 'reseller' );

// Check if user is logged in
if ( ! is_user_logged_in() ) {
	?>
	<div class="checkout-container">
		<div class="checkout-empty-state">
			<h2><?php esc_html_e( 'Connectez-vous pour continuer', 'scw-shop' ); ?></h2>
			<p><?php esc_html_e( 'Vous devez être connecté pour valider votre commande.', 'scw-shop' ); ?></p>
			<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'mon-compte' ) ) ); ?>" class="btn-primary">
				<?php esc_html_e( 'Se connecter', 'scw-shop' ); ?>
			</a>
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

// Calculate totals
$subtotal = (float) $cart->get_subtotal();
$FREE_SHIPPING_THRESHOLD = 550.00;
$shipping_cost = $subtotal >= $FREE_SHIPPING_THRESHOLD ? 0 : 19.00;
$tax = $subtotal * 0.20;
$total_ttc = $subtotal + $shipping_cost + $tax;

// Get current user data
$current_user = wp_get_current_user();
$user_meta = get_user_meta( $current_user->ID );

$billing_first_name = $user_meta['billing_first_name'][0] ?? $current_user->first_name;
$billing_last_name = $user_meta['billing_last_name'][0] ?? $current_user->last_name;
$billing_company = $user_meta['billing_company'][0] ?? '';
$billing_address_1 = $user_meta['billing_address_1'][0] ?? '';
$billing_postcode = $user_meta['billing_postcode'][0] ?? '';
$billing_city = $user_meta['billing_city'][0] ?? '';

$shipping_first_name = $user_meta['shipping_first_name'][0] ?? $billing_first_name;
$shipping_last_name = $user_meta['shipping_last_name'][0] ?? $billing_last_name;
$shipping_company = $user_meta['shipping_company'][0] ?? $billing_company;
$shipping_address_1 = $user_meta['shipping_address_1'][0] ?? $billing_address_1;
$shipping_postcode = $user_meta['shipping_postcode'][0] ?? $billing_postcode;
$shipping_city = $user_meta['shipping_city'][0] ?? $billing_city;
?>

<div class="checkout-container">
	
	<!-- HEADER FULL WIDTH -->
	<div class="checkout-header">
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="back-link">
			← <?php esc_html_e( 'Retour au panier', 'scw-shop' ); ?>
		</a>
		<h1><?php esc_html_e( 'Validation de commande', 'scw-shop' ); ?></h1>
	</div>

	<div class="checkout-grid">
		<!-- GAUCHE : ÉTAPES -->
		<div class="checkout-steps">
			
			<!-- ÉTAPE 1 : ADRESSE -->
			<section class="checkout-step active" data-step="1">
				<div class="step-header">
					<div class="step-number">1</div>
					<h2><?php esc_html_e( 'Adresse', 'scw-shop' ); ?></h2>
				</div>
				
				<div class="step-content">
					<!-- ADRESSE LIVRAISON -->
					<h3 class="sub-title"><?php esc_html_e( 'Livraison', 'scw-shop' ); ?></h3>
					<div class="form-grid">
						<div class="form-group half">
							<label><?php esc_html_e( 'Prénom', 'scw-shop' ); ?></label>
							<input type="text" name="shipping_first_name" value="<?php echo esc_attr( $shipping_first_name ); ?>" />
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'Nom', 'scw-shop' ); ?></label>
							<input type="text" name="shipping_last_name" value="<?php echo esc_attr( $shipping_last_name ); ?>" />
						</div>
						<div class="form-group full">
							<label><?php esc_html_e( 'Société (Optionnel)', 'scw-shop' ); ?></label>
							<input type="text" name="shipping_company" value="<?php echo esc_attr( $shipping_company ); ?>" />
						</div>
						<div class="form-group full">
							<label><?php esc_html_e( 'Adresse', 'scw-shop' ); ?></label>
							<input type="text" name="shipping_address_1" value="<?php echo esc_attr( $shipping_address_1 ); ?>" placeholder="<?php esc_attr_e( 'N° et nom de rue', 'scw-shop' ); ?>" />
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'Code Postal', 'scw-shop' ); ?></label>
							<input type="text" name="shipping_postcode" value="<?php echo esc_attr( $shipping_postcode ); ?>" />
						</div>
						<div class="form-group half">
							<label><?php esc_html_e( 'Ville', 'scw-shop' ); ?></label>
							<input type="text" name="shipping_city" value="<?php echo esc_attr( $shipping_city ); ?>" />
						</div>
					</div>

					<!-- OPTION FACTURATION -->
					<div class="billing-toggle">
						<label class="checkbox-label">
							<input type="checkbox" id="same-billing" checked />
							<?php esc_html_e( 'Adresse de facturation identique à la livraison', 'scw-shop' ); ?>
						</label>
					</div>

					<!-- ADRESSE FACTURATION (SI DIFFÉRENTE) -->
					<div class="billing-form" id="billing-form" style="display: none;">
						<h3 class="sub-title"><?php esc_html_e( 'Facturation', 'scw-shop' ); ?></h3>
						<div class="form-grid">
							<div class="form-group full">
								<label><?php esc_html_e( 'Adresse', 'scw-shop' ); ?></label>
								<input type="text" name="billing_address_1" value="<?php echo esc_attr( $billing_address_1 ); ?>" placeholder="<?php esc_attr_e( 'N° et nom de rue', 'scw-shop' ); ?>" />
							</div>
							<div class="form-group half">
								<label><?php esc_html_e( 'Code Postal', 'scw-shop' ); ?></label>
								<input type="text" name="billing_postcode" value="<?php echo esc_attr( $billing_postcode ); ?>" />
							</div>
							<div class="form-group half">
								<label><?php esc_html_e( 'Ville', 'scw-shop' ); ?></label>
								<input type="text" name="billing_city" value="<?php echo esc_attr( $billing_city ); ?>" />
							</div>
						</div>
					</div>
					
					<button class="btn-next" id="btn-to-step-2">
						<?php esc_html_e( 'Continuer vers le paiement', 'scw-shop' ); ?>
					</button>
				</div>

				<!-- Summary when completed -->
				<div class="step-summary" style="display: none;">
					<div>
						<p><strong><?php esc_html_e( 'Livraison :', 'scw-shop' ); ?></strong> <span id="summary-address"></span></p>
						<p class="text-sm text-gray" id="summary-billing-note"><?php esc_html_e( 'Facturation identique', 'scw-shop' ); ?></p>
					</div>
					<button class="btn-edit" id="btn-edit-step-1"><?php esc_html_e( 'Modifier', 'scw-shop' ); ?></button>
				</div>
			</section>

			<!-- ÉTAPE 2 : PAIEMENT -->
			<section class="checkout-step" data-step="2">
				<div class="step-header">
					<div class="step-number">2</div>
					<h2><?php esc_html_e( 'Paiement', 'scw-shop' ); ?></h2>
				</div>

				<div class="step-content" style="display: none;">
					<div class="payment-options">
						<label class="payment-option selected">
							<input type="radio" name="payment_method" value="card" checked />
							<span class="payment-label"><?php esc_html_e( 'Carte Bancaire', 'scw-shop' ); ?></span>
							<div class="payment-icons">
								<span class="card-icon cb">CB</span>
								<span class="card-icon visa">Visa</span>
							</div>
						</label>

						<?php if ( $is_reseller ) : ?>
						<label class="payment-option">
							<input type="radio" name="payment_method" value="lcr" />
							<span class="payment-label"><?php esc_html_e( 'Paiement différé (30 jours)', 'scw-shop' ); ?></span>
							<span class="badge-pro">Pro</span>
						</label>
						<?php endif; ?>
					</div>

					<!-- Card Form -->
					<div class="card-form" id="card-form">
						<div class="form-group full">
							<label><?php esc_html_e( 'Numéro de carte', 'scw-shop' ); ?></label>
							<input type="text" name="card_number" placeholder="0000 0000 0000 0000" />
						</div>
						<div class="form-row">
							<div class="form-group">
								<label><?php esc_html_e( 'Expiration', 'scw-shop' ); ?></label>
								<input type="text" name="card_expiry" placeholder="MM / AA" />
							</div>
							<div class="form-group">
								<label>CVC</label>
								<input type="text" name="card_cvc" placeholder="123" />
							</div>
						</div>
					</div>

					<!-- LCR Info -->
					<div class="info-box" id="lcr-info" style="display: none;">
						<p><?php esc_html_e( 'Votre commande sera validée immédiatement. La facture sera émise en fin de mois.', 'scw-shop' ); ?></p>
					</div>

					<button class="btn-pay" id="btn-pay">
						<?php esc_html_e( 'Payer', 'scw-shop' ); ?> <?php echo number_format( $total_ttc, 2, ',', ' ' ); ?> €
					</button>
					
					<div class="secure-footer">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
							<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
						</svg>
						<?php esc_html_e( 'Transaction chiffrée SSL', 'scw-shop' ); ?>
					</div>
				</div>
			</section>
		</div>

		<!-- DROITE : RÉSUMÉ -->
		<div class="checkout-sidebar">
			<div class="order-summary">
				<h3><?php esc_html_e( 'Récapitulatif', 'scw-shop' ); ?></h3>
				
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
						$line_price = scw_shop_get_product_price( $product ) * $quantity;
						?>
						<div class="mini-item">
							<div class="mini-img">
								<img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( $product_name ); ?>" />
							</div>
							<div class="mini-info">
								<span><?php echo esc_html( wp_trim_words( $product_name, 4, '...' ) ); ?></span>
								<span class="mini-qty">x<?php echo esc_html( $quantity ); ?></span>
							</div>
							<span class="mini-price"><?php echo number_format( $line_price, 2, ',', ' ' ); ?> €</span>
						</div>
					<?php endforeach; ?>
				</div>
				
				<div class="summary-totals">
					<div class="line">
						<span><?php esc_html_e( 'Sous-total HT', 'scw-shop' ); ?></span>
						<span><?php echo number_format( $subtotal, 2, ',', ' ' ); ?> €</span>
					</div>
					<div class="line">
						<span><?php esc_html_e( 'TVA (20%)', 'scw-shop' ); ?></span>
						<span><?php echo number_format( $tax, 2, ',', ' ' ); ?> €</span>
					</div>
					<div class="line">
						<span><?php esc_html_e( 'Livraison', 'scw-shop' ); ?></span>
						<span>
							<?php if ( $shipping_cost === 0 ) : ?>
								<?php esc_html_e( 'Offerte', 'scw-shop' ); ?>
							<?php else : ?>
								<?php echo number_format( $shipping_cost, 2, ',', ' ' ); ?> €
							<?php endif; ?>
						</span>
					</div>
					<div class="line total">
						<span><?php esc_html_e( 'Total TTC', 'scw-shop' ); ?></span>
						<span><?php echo number_format( $total_ttc, 2, ',', ' ' ); ?> €</span>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
