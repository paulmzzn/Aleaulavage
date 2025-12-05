<?php
/**
 * The template for displaying product content within loops
 *
 * @package SCW_Shop
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$user_role = scw_shop_get_user_role();
$user_mode = scw_shop_get_user_mode();

// Get product data
$product_id = $product->get_id();
$product_name = $product->get_name();
$product_image = wp_get_attachment_image_url( $product->get_image_id(), 'medium' );
$product_url = get_permalink( $product_id );
// Get only the primary (first) category
$product_categories_array = get_the_terms( $product_id, 'product_cat' );
$primary_category = '';
if ( $product_categories_array && ! is_wp_error( $product_categories_array ) ) {
    // Filter out 'Uncategorized' and get the first one
    foreach ( $product_categories_array as $cat ) {
        if ( $cat->slug !== 'uncategorized' && $cat->parent !== 0 ) {
            // Prefer child category (more specific)
            $primary_category = $cat->name;
            break;
        }
    }
    // If no child found, use the first non-uncategorized
    if ( empty( $primary_category ) ) {
        foreach ( $product_categories_array as $cat ) {
            if ( $cat->slug !== 'uncategorized' ) {
                $primary_category = $cat->name;
                break;
            }
        }
    }
}
$product_sku = $product->get_sku() ?: 'N/A';

// Stock status
$stock_status = $product->get_stock_status();
$stock_qty = $product->get_stock_quantity();
$in_stock = $stock_status === 'instock';

// Prices
$regular_price = $product->get_regular_price();
$sale_price = $product->get_sale_price();
$price = $sale_price ? $sale_price : $regular_price;
$price = $price ? (float) $price : 0;
$has_discount = $sale_price && $regular_price && $sale_price < $regular_price;
$discount_percent = $has_discount ? round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 ) : 0;

// Custom meta for reseller (buy price, margin)
$buy_price = get_post_meta( $product_id, '_scw_buy_price', true ) ?: 0;
$buy_price = $buy_price ? (float) $buy_price : 0;
$margin = 0;
$margin_percent = 0;
if ( $price && $buy_price ) {
	$margin = $price - $buy_price;
	$margin_percent = ( $margin / $price ) * 100;
}

// Margin level
$margin_level = 'good';
if ( $margin_percent < 15 ) {
	$margin_level = 'low';
} elseif ( $margin_percent < 30 ) {
	$margin_level = 'medium';
}

// Check if favorite (via cookie or user meta)
$favorites = array();
if ( is_user_logged_in() ) {
	$favorites = get_user_meta( get_current_user_id(), 'scw_favorites', true ) ?: array();
} else {
	$favorites = isset( $_COOKIE['scw_favorites'] ) ? json_decode( stripslashes( $_COOKIE['scw_favorites'] ), true ) : array();
}
$is_favorite = in_array( $product_id, (array) $favorites );

// Popular/new badges
$is_new = ( strtotime( $product->get_date_created() ) > strtotime( '-30 days' ) );
$total_sales = $product->get_total_sales();
$is_popular = $total_sales > 10;
?>

<article <?php wc_product_class( 'product-card ' . esc_attr( $user_role ) . ( $user_role === 'reseller' ? ' mode-' . esc_attr( $user_mode ) : '' ), $product ); ?> data-product-id="<?php echo esc_attr( $product_id ); ?>">

	<!-- Quick Actions (Top Right) -->
	<div class="card-quick-actions">
		<button class="action-btn favorite-btn <?php echo $is_favorite ? 'is-favorite' : ''; ?>" 
				data-product-id="<?php echo esc_attr( $product_id ); ?>" 
				aria-label="<?php echo $is_favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
			<svg class="heart-empty" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
			</svg>
			<svg class="heart-filled" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
				<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
			</svg>
		</button>
	</div>

	<!-- Badges (Top Left) -->
	<div class="card-badges">
		<?php if ( $has_discount ) : ?>
			<span class="badge promo">-<?php echo esc_html( $discount_percent ); ?>%</span>
		<?php endif; ?>
		<?php if ( $is_new && ! $has_discount ) : ?>
			<span class="badge new">Nouveau</span>
		<?php endif; ?>
		<?php if ( $is_popular && ! $is_new && ! $has_discount ) : ?>
			<span class="badge popular">Populaire</span>
		<?php endif; ?>
		<?php if ( ! $in_stock ) : ?>
			<span class="badge out-of-stock">Réappro.</span>
		<?php endif; ?>
	</div>

	<!-- Image -->
	<a href="<?php echo esc_url( $product_url ); ?>" class="card-image">
		<?php if ( $product_image ) : ?>
			<img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( $product_name ); ?>" loading="lazy" />
		<?php else : ?>
			<img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" alt="<?php echo esc_attr( $product_name ); ?>" loading="lazy" />
		<?php endif; ?>
	</a>

	<!-- Content -->
	<div class="card-body">
		<!-- Product Info -->
		<a href="<?php echo esc_url( $product_url ); ?>" class="card-info">
			<?php if ( $primary_category ) : ?>
			<span class="card-brand"><?php echo esc_html( $primary_category ); ?></span>
			<?php endif; ?>
			<h3 class="card-title"><?php echo esc_html( $product_name ); ?></h3>
			<span class="card-sku">Réf. <?php echo esc_html( $product_sku ); ?></span>
		</a>

		<!-- Price & Actions -->
		<div class="card-footer">

			<?php if ( $user_role === 'guest' ) : ?>
			<!-- ═══════════════ GUEST ═══════════════ -->
			<div class="card-pricing guest-pricing">
				<div class="price-hidden">
					<span class="price-lock">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
							<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
						</svg>
					</span>
					<span class="price-blur">€ •••</span>
				</div>
			</div>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="card-cta guest-cta">
				<span>Accès tarifs pro</span>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M5 12h14M12 5l7 7-7 7"/>
				</svg>
			</a>

			<?php elseif ( $user_role === 'client' ) : ?>
			<!-- ═══════════════ CLIENT ═══════════════ -->
			<div class="card-pricing client-pricing">
				<?php if ( $has_discount ) : ?>
					<span class="price-old"><?php echo esc_html( number_format( $regular_price, 2, ',', ' ' ) ); ?> €</span>
				<?php endif; ?>
				<span class="price-main"><?php echo esc_html( number_format( $price, 2, ',', ' ' ) ); ?> <small>€ HT</small></span>
			</div>
			<div class="card-actions">
				<?php if ( $in_stock ) : ?>
				<div class="qty-selector">
					<button class="qty-btn minus" aria-label="Diminuer">−</button>
					<input type="number" class="qty-input" value="1" min="1" max="99" data-product-id="<?php echo esc_attr( $product_id ); ?>" />
					<button class="qty-btn plus" aria-label="Augmenter">+</button>
				</div>
				<button class="card-cta add-to-cart-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="9" cy="21" r="1"></circle>
						<circle cx="20" cy="21" r="1"></circle>
						<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
					</svg>
					<span class="cta-text">Ajouter</span>
				</button>
				<?php else : ?>
				<button class="card-cta notify-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
						<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
					</svg>
					<span>M'alerter</span>
				</button>
				<?php endif; ?>
			</div>

			<?php elseif ( $user_role === 'reseller' && $user_mode === 'gestion' ) : ?>
			<!-- ═══════════════ RESELLER - GESTION ═══════════════ -->
			<div class="card-pricing reseller-gestion">
				<div class="price-row">
					<div class="price-info">
						<span class="price-label">PA</span>
						<span class="price-value cost"><?php echo esc_html( number_format( $buy_price, 0, ',', ' ' ) ); ?>€</span>
					</div>
					<div class="margin-indicator <?php echo esc_attr( $margin_level ); ?>">
						<span class="margin-value"><?php echo esc_html( number_format( $margin_percent, 0 ) ); ?>%</span>
						<span class="margin-label">marge</span>
					</div>
				</div>
			</div>
			<div class="price-editor">
				<label class="editor-label">Prix Vente HT</label>
				<div class="editor-control">
					<button class="editor-btn decrease" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-step="5">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
					</button>
					<div class="editor-input-wrap">
						<input type="number" class="editor-input" value="<?php echo esc_attr( round( $price ) ); ?>" 
							   data-product-id="<?php echo esc_attr( $product_id ); ?>"
							   data-buy-price="<?php echo esc_attr( $buy_price ); ?>" />
						<span class="editor-suffix">€</span>
					</div>
					<button class="editor-btn increase" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-step="5">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
					</button>
				</div>
			</div>

			<?php elseif ( $user_role === 'reseller' && $user_mode === 'achat' ) : ?>
			<!-- ═══════════════ RESELLER - ACHAT ═══════════════ -->
			<div class="card-pricing reseller-achat">
				<div class="buy-price">
					<span class="buy-label">Prix d'achat</span>
					<span class="buy-value"><?php echo esc_html( number_format( $buy_price, 2, ',', ' ' ) ); ?> <small>€ HT</small></span>
				</div>
				<?php if ( $in_stock && $stock_qty ) : ?>
				<span class="stock-info in-stock">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
					<?php echo esc_html( $stock_qty ); ?> en stock
				</span>
				<?php endif; ?>
			</div>
			<div class="card-actions">
				<div class="qty-selector">
					<button class="qty-btn minus" aria-label="Diminuer">−</button>
					<input type="number" class="qty-input" value="1" min="1" max="999" data-product-id="<?php echo esc_attr( $product_id ); ?>" />
					<button class="qty-btn plus" aria-label="Augmenter">+</button>
				</div>
				<button class="card-cta buy-cta" data-product-id="<?php echo esc_attr( $product_id ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
					</svg>
					<span>Commander</span>
				</button>
			</div>

			<?php elseif ( $user_role === 'reseller' && $user_mode === 'vitrine' ) : ?>
			<!-- ═══════════════ RESELLER - VITRINE ═══════════════ -->
			<div class="card-pricing vitrine-pricing">
				<?php if ( $has_discount ) : ?>
					<span class="price-old"><?php echo esc_html( number_format( $regular_price, 2, ',', ' ' ) ); ?> €</span>
				<?php endif; ?>
				<span class="price-main"><?php echo esc_html( number_format( $price, 2, ',', ' ' ) ); ?> <small>€ HT</small></span>
				<span class="vitrine-badge">Prix Public</span>
			</div>
			<a href="<?php echo esc_url( $product_url ); ?>" class="card-cta vitrine-cta">
				<span>Voir le produit</span>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M5 12h14M12 5l7 7-7 7"/>
				</svg>
			</a>

			<?php endif; ?>

		</div>
	</div>
</article>
