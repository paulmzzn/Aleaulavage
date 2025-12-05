<?php
/**
 * Single Product Template
 *
 * @package SCW_Shop
 */

get_header();

// Get current product properly
global $post;
$product = wc_get_product( $post->ID );

if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
	echo '<div class="product-detail-container"><p>Produit introuvable.</p></div>';
	get_footer();
	exit;
}

$user_role = scw_shop_get_user_role();
$user_mode = scw_shop_get_user_mode();

// Get product data
$product_id = $product->get_id();
$product_name = $product->get_name();
$product_ref = $product->get_sku();
$product_description = $product->get_description();
$product_short_desc = $product->get_short_description();

// Get all product images (main + gallery)
$main_image_id = $product->get_image_id();
$gallery_image_ids = $product->get_gallery_image_ids();
$all_image_ids = array_merge( array( $main_image_id ), $gallery_image_ids );

// Get prices
$price = scw_shop_get_product_price( $product );
$buy_price = (float) get_post_meta( $product_id, '_buy_price', true );

// Calculate margin
$margin_value = $price - $buy_price;
$margin_percent = $price > 0 ? ( $margin_value / $price * 100 ) : 0;

// Get margin color
$margin_color = '#10b981'; // green
if ( $margin_percent < 15 ) {
	$margin_color = '#ef4444'; // red
} elseif ( $margin_percent < 30 ) {
	$margin_color = '#f59e0b'; // orange
}

// Get category
$categories = wp_get_post_terms( $product_id, 'product_cat' );
$category_name = ! empty( $categories ) ? $categories[0]->name : 'Produits';
$category_slug = ! empty( $categories ) ? $categories[0]->slug : '';

// Get related products
$related_ids = wc_get_related_products( $product_id, 4 );

// Check what tabs have content
$has_description = ! empty( $product_description );
$attributes = $product->get_attributes();
$has_attributes = ! empty( $attributes );
$has_docs = false; // Set to true when you have actual documentation

// Determine which tab should be active by default
$first_tab = $has_description ? 'desc' : ( $has_attributes ? 'specs' : 'docs' );
?>

<main id="main" class="site-main">
	<div class="product-detail-container">

		<!-- BREADCRUMB & BACK -->
		<div class="detail-nav">
			<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'boutique' ) ) ); ?>" class="back-btn">
				← Retour au catalogue
			</a>
			<div class="breadcrumb">
				<a href="<?php echo esc_url( home_url() ); ?>">Accueil</a> /
				<a href="<?php echo esc_url( add_query_arg( 'category', $category_slug, get_permalink( get_page_by_path( 'boutique' ) ) ) ); ?>"><?php echo esc_html( $category_name ); ?></a> /
				<span class="current"><?php echo esc_html( $product_name ); ?></span>
			</div>
		</div>

		<div class="detail-main">
			<!-- GAUCHE : IMAGES + DESCRIPTION -->
			<div class="detail-left-column">
				<!-- GALLERY -->
				<div class="detail-gallery">
					<div class="gallery-container">
						<div class="main-image" id="main-product-image">
							<?php
							$main_image_url = $main_image_id ? wp_get_attachment_image_url( $main_image_id, 'full' ) : wc_placeholder_img_src();
							?>
							<img src="<?php echo esc_url( $main_image_url ); ?>" alt="<?php echo esc_attr( $product_name ); ?>" id="main-image-src" />
							<?php if ( $product->is_on_sale() ) : ?>
								<span class="detail-badge promo">Promo</span>
							<?php endif; ?>
						</div>

						<!-- Thumbnails -->
						<?php if ( count( $all_image_ids ) > 1 || $main_image_id ) : ?>
							<div class="thumbnails">
								<?php
								$thumb_index = 0;
								foreach ( $all_image_ids as $image_id ) :
									if ( ! $image_id ) continue;
									$thumb_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
									$full_url = wp_get_attachment_image_url( $image_id, 'full' );
									?>
									<div class="thumb <?php echo $thumb_index === 0 ? 'active' : ''; ?>" data-full="<?php echo esc_url( $full_url ); ?>">
										<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" />
									</div>
									<?php
									$thumb_index++;
								endforeach;
								?>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- TABS DESCRIPTION -->
				<?php if ( $has_description || $has_attributes || $has_docs ) : ?>
				<div class="detail-tabs-section">
					<div class="tabs-header">
						<?php if ( $has_description ) : ?>
							<button class="tab-btn <?php echo $first_tab === 'desc' ? 'active' : ''; ?>" data-tab="desc">Description</button>
						<?php endif; ?>
						<?php if ( $has_attributes ) : ?>
							<button class="tab-btn <?php echo $first_tab === 'specs' ? 'active' : ''; ?>" data-tab="specs">Caractéristiques</button>
						<?php endif; ?>
						<?php if ( $has_docs ) : ?>
							<button class="tab-btn <?php echo $first_tab === 'docs' ? 'active' : ''; ?>" data-tab="docs">Documentation</button>
						<?php endif; ?>
					</div>
					<div class="tab-content">
						<?php if ( $has_description ) : ?>
							<div class="tab-panel <?php echo $first_tab === 'desc' ? 'active' : ''; ?>" data-panel="desc">
								<div class="text-content">
									<h3>Description détaillée</h3>
									<?php echo wp_kses_post( wpautop( $product_description ) ); ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $has_attributes ) : ?>
							<div class="tab-panel <?php echo $first_tab === 'specs' ? 'active' : ''; ?>" data-panel="specs">
								<table class="specs-table">
									<tbody>
										<?php
										foreach ( $attributes as $attribute ) :
											?>
											<tr>
												<td><?php echo esc_html( wc_attribute_label( $attribute->get_name() ) ); ?></td>
												<td><?php echo wp_kses_post( $product->get_attribute( $attribute->get_name() ) ); ?></td>
											</tr>
											<?php
										endforeach;
										?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>

						<?php if ( $has_docs ) : ?>
							<div class="tab-panel <?php echo $first_tab === 'docs' ? 'active' : ''; ?>" data-panel="docs">
								<div class="docs-list">
									<a href="#" class="doc-link">📄 Fiche Technique (PDF)</a>
									<a href="#" class="doc-link">📄 Manuel d'installation (PDF)</a>
									<a href="#" class="doc-link">📄 Certificat de conformité</a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>
			</div><!-- .detail-left-column -->

			<!-- DROITE : INFOS & ACTIONS -->
			<div class="detail-info">
				<!-- Product Info Card with Border -->
				<div class="product-info-card">
					<div class="product-header-row">
						<h1 class="product-name"><?php echo esc_html( $product_name ); ?></h1>
						<button class="favorite-btn <?php echo scw_shop_is_in_favorites( $product_id ) ? 'active' : ''; ?>"
						        data-product-id="<?php echo esc_attr( $product_id ); ?>"
						        title="Ajouter aux favoris">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="<?php echo scw_shop_is_in_favorites( $product_id ) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
								<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
							</svg>
						</button>
					</div>

					<div class="product-meta">
						<div class="product-meta-row">
							<span class="ref">Réf: <strong><?php echo esc_html( $product_ref ?: 'N/A' ); ?></strong></span>
							<span class="brand">Marque: <strong>SCW PRO</strong></span>
						</div>
						<?php if ( $product->is_in_stock() ) : ?>
							<span class="stock-status in-stock">
								<span class="dot"></span> En stock
							</span>
						<?php else : ?>
							<span class="stock-status out-of-stock">
								<span class="dot"></span> Rupture de stock
							</span>
						<?php endif; ?>
					</div>

					<!-- ZONE PRIX & ACTIONS (DYNAMIQUE) -->
					<div class="detail-pricing-card">

						<?php if ( $user_role === 'guest' ) : ?>
							<!-- CAS INVITÉ -->
							<div class="guest-block">
								<p class="login-tease">Connectez-vous pour voir les tarifs professionnels et commander.</p>
								<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn-primary">Se connecter</a>
							</div>

						<?php elseif ( $user_role === 'reseller' ) : ?>
							<!-- CAS REVENDEUR -->
							<div class="reseller-block">

								<?php if ( $user_mode === 'gestion' ) : ?>
									<!-- MODE ÉDITION -->
									<div class="edit-mode-panel">
										<div class="kpi-row">
											<div class="kpi">
												<span class="label">Prix Achat (HT)</span>
												<span class="value"><?php echo esc_html( number_format( $buy_price, 2 ) ); ?> €</span>
											</div>
											<div class="kpi">
												<span class="label">Marge</span>
												<span class="value" style="color: <?php echo esc_attr( $margin_color ); ?>;"><?php echo esc_html( number_format( $margin_percent, 0 ) ); ?>%</span>
											</div>
										</div>

										<div class="price-editor">
											<label>Votre Prix de Vente (HT)</label>
											<div class="input-group">
												<input
													type="number"
													class="price-input-detail"
													value="<?php echo esc_attr( $price ); ?>"
													data-product-id="<?php echo esc_attr( $product_id ); ?>"
													data-buy-price="<?php echo esc_attr( $buy_price ); ?>"
												/>
												<span class="currency">€</span>
											</div>
										</div>
										<div class="margin-bar">
											<div class="fill margin-fill" style="width: <?php echo esc_attr( min( $margin_percent, 100 ) ); ?>%; background: <?php echo esc_attr( $margin_color ); ?>;"></div>
										</div>
									</div>

								<?php elseif ( $user_mode === 'achat' ) : ?>
									<!-- MODE ACHAT STOCK -->
									<div class="buy-mode-panel">
										<div class="price-display">
											<span class="main-price"><?php echo esc_html( number_format( $buy_price, 2, ',', ' ' ) ); ?> € <span class="tax">HT</span></span>
										</div>
										<div class="actions-row">
											<div class="qty-selector">
												<button type="button" class="qty-btn minus">−</button>
												<input type="number" class="qty-input" value="1" min="1" data-product-id="<?php echo esc_attr( $product_id ); ?>" />
												<button type="button" class="qty-btn plus">+</button>
											</div>
											<button type="button" class="btn-primary add-to-cart-ajax" data-product-id="<?php echo esc_attr( $product_id ); ?>">
												Ajouter au stock
											</button>
										</div>
									</div>

								<?php else : // vitrine ?>
									<!-- MODE VITRINE -->
									<div class="client-mode-panel">
										<div class="price-display">
											<span class="main-price"><?php echo esc_html( number_format( $price, 2, ',', ' ' ) ); ?> € <span class="tax">HT</span></span>
										</div>
										<div class="actions-row">
											<div class="qty-selector">
												<button type="button" class="qty-btn minus">−</button>
												<input type="number" class="qty-input" value="1" min="1" data-product-id="<?php echo esc_attr( $product_id ); ?>" />
												<button type="button" class="qty-btn plus">+</button>
											</div>
											<button type="button" class="btn-primary add-to-cart-ajax" data-product-id="<?php echo esc_attr( $product_id ); ?>">
												<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
												Ajouter au panier
											</button>
										</div>
									</div>
								<?php endif; ?>
							</div>

						<?php else : // client ?>
							<!-- CAS CLIENT FINAL -->
							<div class="client-mode-panel">
								<div class="price-display">
									<span class="main-price"><?php echo esc_html( number_format( $price, 2, ',', ' ' ) ); ?> € <span class="tax">HT</span></span>
								</div>
								<div class="actions-row">
									<div class="qty-selector">
										<button type="button" class="qty-btn minus">−</button>
										<input type="number" class="qty-input" value="1" min="1" data-product-id="<?php echo esc_attr( $product_id ); ?>" />
										<button type="button" class="qty-btn plus">+</button>
									</div>
									<button type="button" class="btn-primary add-to-cart-ajax" data-product-id="<?php echo esc_attr( $product_id ); ?>">
										<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
										Ajouter au panier
									</button>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( $product_short_desc ) : ?>
						<div class="product-description-short">
							<div class="description-text">
								<?php echo wp_kses_post( wpautop( $product_short_desc ) ); ?>
							</div>
							<button class="read-more-btn" style="display: none;">Voir plus</button>
						</div>
					<?php endif; ?>
				</div><!-- .product-info-card -->
			</div><!-- .detail-info -->
		</div><!-- .detail-main -->

		<!-- RELATED PRODUCTS -->
		<?php if ( ! empty( $related_ids ) ) : ?>
			<div class="related-section">
				<h2 class="section-title">Produits similaires</h2>
				<div class="related-grid">
					<?php
					foreach ( $related_ids as $related_id ) :
						$related_product = wc_get_product( $related_id );
						if ( ! $related_product ) {
							continue;
						}

						// Setup product data for the card
						global $product;
						$product = $related_product;
						wc_get_template_part( 'content', 'product' );
					endforeach;
					?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
