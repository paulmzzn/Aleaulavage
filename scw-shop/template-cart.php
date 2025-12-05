<?php
/**
 * Template Name: Page Panier
 * Template for shopping cart page
 *
 * @package SCW_Shop
 */

get_header();

$user_role = scw_shop_get_user_role();

// Check if user is logged in
if ( ! is_user_logged_in() ) {
	?>
	<main id="main" class="site-main">
		<div class="cart-container">
			<div class="cart-empty-state guest-mode">
				<div class="empty-icon-wrapper">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
						<circle cx="9" cy="21" r="1"></circle>
						<circle cx="20" cy="21" r="1"></circle>
						<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
					</svg>
				</div>
				<h2><?php esc_html_e( 'Connectez-vous pour commander', 'scw-shop' ); ?></h2>
				<p><?php esc_html_e( 'L\'accès aux tarifs et à la commande est réservé aux professionnels du lavage.', 'scw-shop' ); ?></p>
				<div class="guest-actions">
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'mon-compte' ) ) ); ?>" class="btn-primary">
						<?php esc_html_e( 'Se connecter', 'scw-shop' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-secondary">
						<?php esc_html_e( 'Retour au catalogue', 'scw-shop' ); ?>
					</a>
				</div>
			</div>
		</div>
	</main>
	<?php
	get_footer();
	return;
}

// Get cart
$cart = WC()->cart;
$cart_items = $cart->get_cart();

// Empty cart state
if ( empty( $cart_items ) ) {
	?>
	<main id="main" class="site-main">
		<div class="cart-container">
			<div class="cart-empty-state">
				<h2><?php esc_html_e( 'Votre panier est vide', 'scw-shop' ); ?></h2>
				<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'boutique' ) ) ); ?>" class="btn-primary">
					<?php esc_html_e( 'Continuer mes achats', 'scw-shop' ); ?>
				</a>
			</div>
		</div>
	</main>
	<?php
	get_footer();
	return;
}

// Calculate totals
$subtotal = (float) $cart->get_subtotal();
$FREE_SHIPPING_THRESHOLD = 550.00;
$remaining_for_free_shipping = max( 0, $FREE_SHIPPING_THRESHOLD - $subtotal );
$progress_percent = min( 100, ( $subtotal / $FREE_SHIPPING_THRESHOLD ) * 100 );

$shipping_cost = $subtotal >= $FREE_SHIPPING_THRESHOLD ? 0 : 19.00;
$tax = $subtotal * 0.20; // TVA only on products, not shipping
$total_ttc = $subtotal + $shipping_cost + $tax;
?>

<main id="main" class="site-main">
	<div class="cart-container">
		<div class="cart-header">
			<h1><?php esc_html_e( 'Mon Panier', 'scw-shop' ); ?></h1>
			<span class="item-count"><?php echo count( $cart_items ); ?> <?php esc_html_e( 'articles', 'scw-shop' ); ?></span>
		</div>

		<div class="cart-layout">
			<!-- CART ITEMS -->
			<div class="cart-items">
				<?php foreach ( $cart_items as $cart_item_key => $cart_item ) : ?>
					<?php
					$product = $cart_item['data'];
					$product_id = $cart_item['product_id'];
					$quantity = $cart_item['quantity'];

					// Get product details
					$product_name = $product->get_name();
					$product_sku = $product->get_sku();
					$product_image = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
					if ( ! $product_image ) {
						$product_image = wc_placeholder_img_src();
					}

					// Get category
					$terms = get_the_terms( $product_id, 'product_cat' );
					$category_name = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : __( 'Non classé', 'scw-shop' );

					// Get price based on user role
					$price = scw_shop_get_product_price( $product_id, $user_role );
					$line_total = $price * $quantity;
					?>
					<div class="cart-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
						<div class="item-visual">
							<img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( $product_name ); ?>" />
						</div>

						<div class="item-core">
							<div class="item-infos">
								<h3><?php echo esc_html( $product_name ); ?></h3>
								<span class="item-meta">
									<?php echo esc_html( $category_name ); ?> • <?php esc_html_e( 'Réf.', 'scw-shop' ); ?> <?php echo esc_html( $product_sku ); ?>
								</span>
							</div>

							<div class="item-controls">
								<div class="qty-selector">
									<button class="qty-btn minus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="Diminuer">−</button>
									<input type="number" class="qty-input" value="<?php echo esc_attr( $quantity ); ?>" min="1" max="999" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" />
									<button class="qty-btn plus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="Augmenter">+</button>
								</div>
								<button class="remove-link" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
									<?php esc_html_e( 'Supprimer', 'scw-shop' ); ?>
								</button>
							</div>
						</div>

						<div class="item-price-block">
							<span class="total-price"><?php echo number_format( $line_total, 2, ',', ' ' ); ?> € HT</span>
							<span class="unit-price"><?php echo number_format( $price, 2, ',', ' ' ); ?> € HT /u</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- SIDEBAR SUMMARY -->
			<div class="cart-sidebar">

				<!-- SHIPPING PROGRESS BAR -->
				<div class="shipping-progress-card">
					<?php if ( $remaining_for_free_shipping > 0 ) : ?>
						<p class="shipping-msg">
							<?php
							printf(
								/* translators: %s: remaining amount for free shipping */
								__( 'Ajoutez <strong>%s € HT</strong> pour la <strong>livraison offerte</strong>', 'scw-shop' ),
								number_format( $remaining_for_free_shipping, 2, ',', ' ' )
							);
							?>
						</p>
						<div class="progress-track">
							<div class="progress-fill" style="width: <?php echo esc_attr( $progress_percent ); ?>%"></div>
						</div>
					<?php else : ?>
						<p class="shipping-msg success">
							⭐ <?php esc_html_e( 'Vous bénéficiez de la', 'scw-shop' ); ?> <strong><?php esc_html_e( 'livraison offerte', 'scw-shop' ); ?></strong> !
						</p>
					<?php endif; ?>
				</div>

				<!-- SUMMARY BOX -->
				<div class="summary-box">
					<h3><?php esc_html_e( 'Mode de livraison', 'scw-shop' ); ?></h3>

					<div class="delivery-option selected">
						<div class="delivery-icon">
							<img src="https://toppng.com/uploads/preview/fedex-logo-png-transparent-background-62228-fedex-tnt-logo-11563241359alb8jildzs.png" alt="FedEx TNT" style="height: 28px; width: auto;" />
						</div>
						<div class="delivery-info">
							<span class="delivery-name"><?php esc_html_e( 'Livraison à domicile', 'scw-shop' ); ?></span>
							<span class="delivery-delay"><?php esc_html_e( '1 à 4 jours ouvrables', 'scw-shop' ); ?></span>
							<span class="delivery-cost">
								<?php if ( $shipping_cost === 0 ) : ?>
									<span class="text-green"><?php esc_html_e( 'Offerte', 'scw-shop' ); ?></span>
								<?php else : ?>
									<?php echo number_format( $shipping_cost, 2, ',', ' ' ); ?> € HT
								<?php endif; ?>
							</span>
						</div>
						<div class="delivery-check">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3">
								<polyline points="20 6 9 17 4 12"></polyline>
							</svg>
						</div>
					</div>

					<div class="summary-divider"></div>

					<div class="summary-line">
						<span><?php esc_html_e( 'Sous-total HT', 'scw-shop' ); ?></span>
						<span><?php echo number_format( $subtotal, 2, ',', ' ' ); ?> €</span>
					</div>

					<div class="summary-line">
						<span><?php esc_html_e( 'TVA (20%)', 'scw-shop' ); ?></span>
						<span><?php echo number_format( $tax, 2, ',', ' ' ); ?> €</span>
					</div>

					<div class="summary-line">
						<span><?php esc_html_e( 'Livraison HT', 'scw-shop' ); ?></span>
						<span>
							<?php if ( $shipping_cost === 0 ) : ?>
								<?php esc_html_e( 'Offerte', 'scw-shop' ); ?>
							<?php else : ?>
								<?php echo number_format( $shipping_cost, 2, ',', ' ' ); ?> €
							<?php endif; ?>
						</span>
					</div>

					<div class="total-line">
						<span><?php esc_html_e( 'Total TTC', 'scw-shop' ); ?></span>
						<span class="big-price">
							<?php echo number_format( $total_ttc, 2, ',', ' ' ); ?> €
						</span>
					</div>

					<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-btn">
						<?php esc_html_e( 'Valider mon panier', 'scw-shop' ); ?>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
							<line x1="5" y1="12" x2="19" y2="12"></line>
							<polyline points="12 5 19 12 12 19"></polyline>
						</svg>
					</a>

					<div class="secure-badge">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
							<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
							<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
						</svg>
						<?php esc_html_e( 'Paiement sécurisé par Stripe', 'scw-shop' ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
