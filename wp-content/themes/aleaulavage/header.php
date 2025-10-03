<?php
$promo_banner = get_theme_mod('promo_banner_message');
?>
<!DOCTYPE html>
<!-- Hotjar Tracking Code for https://aleaulavage.com -->
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:3224976,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
<!-- Hotjar Tracking Code for https://aleaulavage.com -->
<!-- Script déplacé dans js/custom-header.js -->

<html <?php language_attributes(); ?>>

<head>
	<style>
	.promo-banner {
		background: #5899E2;
		color: #fff;
		text-align: center;
		padding: 6px 0;
		font-weight: 600;
		font-size: 1rem;
		position: relative;
		z-index: 100;
	}
	</style>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<!-- Favicons -->
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/favicon-16x16.png">
	<link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/site.webmanifest">
	<link rel="mask-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/safari-pinned-tab.svg" color="#0d6efd">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="theme-color" content="#ffffff">
	<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/custom-header.css?v=20250725">
	<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
	<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/custom-header.js?v=20250725" defer></script>
	<?php if (!empty($promo_banner)) : ?>
	<style>
	body .site-header {
		margin-top: 48px;
	}
	.promo-banner {
		position: relative;
		z-index: 100;
	}
	</style>
	<?php endif; ?>

		<!-- Google tag (gtag.js) -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=AW-10813983195"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());

			gtag('config', 'AW-10813983195');
		</script>
	
	
	
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php wp_body_open(); ?>

	<!-- Bandeau promo déplacé dans .fixed-top -->

	<div id="page" class="site">
		<header id="masthead" class="site-header">
			<div class="fixed-top">
				<?php if (!empty($promo_banner)) {
					echo '<div class="promo-banner">' . wp_kses_post($promo_banner) . '</div>';
				} ?>
				<!-- Navbar principale -->
				<nav id="nav-main" class="navbar main-navbar outline-gray box-shadow-gray bg-white">
					<div class="container">
						<!-- Première ligne : Logo + Actions -->
						<div class="w-100 d-flex justify-content-between align-items-center">
							<!-- Logo -->
							<div class="navbar-brand-container">
								<a class="navbar-brand xs d-md-none" href="<?php echo esc_url(home_url()); ?>">
									<img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/img/logo/logo-sm.svg" alt="logo" class="logo xs">
								</a>
								<a class="navbar-brand md d-none d-md-block" href="<?php echo esc_url(home_url()); ?>">
									<img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/img/logo/logo.svg" alt="logo" class="logo md">
								</a>
							</div>

							<!-- Barre de recherche élargie (desktop seulement) -->
							<div class="search-container d-none d-md-block">
								<form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url(home_url('/')); ?>">
									<div class="input-group" style="height:48px; border:1.5px solid #444; border-radius:15px; overflow:hidden; background:#fff;">
										<span class="input-group-text bg-white border-0 pe-0" style="height:100%; border-radius:0; display:flex; align-items:center; color:#444; background:#fff;">
											<i class="fa-solid fa-magnifying-glass"></i>
										</span>
										<input type="search" id="woocommerce-product-search-field-<?php echo isset($index) ? absint($index) : 0; ?>" class="form-control border-0 ps-2" placeholder="Que recherchez-vous ?" value="<?php echo get_search_query(); ?>" name="s" style="box-shadow:none; height:100%; border-radius:0 24px 24px 0; background:#fff; color:#222;">
										<input type="hidden" name="post_type" value="product" />
									</div>
								</form>
							</div>

							<!-- Actions header -->
							<div class="header-actions d-flex align-items-center">
								<a class="btn bg-secondary me-2 d-none d-sm-inline-flex" href="<?php echo esc_url(home_url('boutique/')); ?>">
									<span>Boutique</span>
								</a>
								<a class="btn ms-1" href="<?php echo esc_url(home_url('mon-compte/')); ?>" title="Mon compte">
									<i class="fa-solid fa-user"></i>
									<span class="visually-hidden-focusable">Account</span>
								</a>
								<a class="btn ms-1 position-relative" href="<?php echo esc_url(home_url('favoris/')); ?>" title="Mes favoris">
									<i class="fa-<?php echo is_user_logged_in() ? 'solid' : 'regular'; ?> fa-heart" style="color: #333; font-size: 1.2rem;"></i>
									<span class="visually-hidden-focusable">Favoris</span>
									<?php if (is_user_logged_in()) {
										$user_id = get_current_user_id();
										$wishlist = get_user_meta($user_id, 'user_wishlist', true);
										$count = is_array($wishlist) ? count($wishlist) : 0;
										if ($count > 0) : ?>
											<span class="wishlist-count position-absolute badge rounded-pill" style="background: #f1bb69; color: #0E2141; font-size: 0.7rem; padding: 2px 6px; top: 0px; right: 0px;">
												<?php echo esc_html($count); ?>
											</span>
										<?php endif;
									} ?>
								</a>
								<button class="btn ms-1 position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-cart" aria-controls="offcanvas-cart" title="Panier">
									<i class="fa-solid fa-basket-shopping"></i>
									<span class="visually-hidden-focusable">Cart</span>
									<?php if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
										$count = WC()->cart->cart_contents_count;
									?>
										<span class="cart-content">
											<?php if ($count > 0) { ?>
												<?php echo esc_html($count); ?>
											<?php } ?>
										</span>
									<?php } ?>
								</button>
								<button class="btn btn-outline-secondary d-lg-none ms-1 ms-md-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-navbar" aria-controls="offcanvas-navbar" title="Menu">
									<i class="fa-solid fa-bars"></i>
									<span class="visually-hidden-focusable">Menu</span>
								</button>
							</div>
						</div>

						<!-- Deuxième ligne : Barre de recherche mobile -->
						<div class="w-100 d-block d-md-none">
							<form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url(home_url('/')); ?>">
								<div class="input-group" style="height:44px; border:1.5px solid #444; border-radius:12px; overflow:hidden; background:#fff;">
									<span class="input-group-text bg-white border-0 pe-0" style="height:100%; border-radius:0; display:flex; align-items:center; color:#444; background:#fff;">
										<i class="fa-solid fa-magnifying-glass"></i>
									</span>
									<input type="search" id="mobile-product-search-field" class="form-control border-0 ps-2" placeholder="Rechercher un produit..." value="<?php echo get_search_query(); ?>" name="s" style="box-shadow:none; height:100%; border-radius:0 12px 12px 0; background:#fff; color:#222;">
									<input type="hidden" name="post_type" value="product" />
								</div>
							</form>
						</div>
					</div>
					
					<!-- Offcanvas mobile menu -->
					<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-navbar">
						<div class="offcanvas-header bg-light">
							<span class="h5 mb-0">Menu</span>
							<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
						</div>
						<div class="offcanvas-body p-0">
							<div class="modern-mobile-menu">
								
								<!-- Section Catégories -->
								<div class="menu-section">
									<p class="menu-section-title">Nos Catégories</p>
									<div class="menu-grid">
										<?php
										// Récupérer les mêmes catégories que la barre desktop
										$cat_args = array(
											'orderby'    => 'menu_order',
											'order'      => 'ASC',
											'hide_empty' => true,
											'parent'     => 0,
											'exclude'    => [16]
										);

										$product_categories = get_terms('product_cat', $cat_args);
										
										// Ajouter "Toutes les catégories" en premier
										echo '<a href="' . esc_url(home_url('boutique/')) . '" class="menu-card">';
										echo '<div class="menu-card-icon"><i class="fa-solid fa-grid-2"></i></div>';
										echo '<div class="menu-card-title">Toutes les catégories</div>';
										echo '<div class="menu-card-subtitle">Voir tout</div>';
										echo '</a>';
										
										if (!empty($product_categories)) {
											foreach ($product_categories as $index => $category) {
												$thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
												$category_link = get_term_link($category);
												
												echo '<a href="' . esc_url($category_link) . '" class="menu-card">';
												
												if ($thumbnail_id) {
													echo '<img src="' . esc_url(wp_get_attachment_url($thumbnail_id)) . '" alt="' . esc_attr($category->name) . '">';
												} else {
													// Icônes différentes selon la catégorie
													$icons = [
														'fa-solid fa-microchip', 'fa-solid fa-wrench', 'fa-solid fa-cog',
														'fa-solid fa-bolt', 'fa-solid fa-tools', 'fa-solid fa-plug',
														'fa-solid fa-gear', 'fa-solid fa-hammer'
													];
													$icon = $icons[$index % count($icons)];
													echo '<div class="menu-card-icon"><i class="' . $icon . '"></i></div>';
												}
												
												// Afficher le nom complet sans le couper
												$category_name = esc_html($category->name);

												echo '<div class="menu-card-title">' . $category_name . '</div>';
												echo '</a>';
											}
										}
										?>
									</div>
								</div>

								<!-- Section Navigation -->
								<div class="menu-section">
									<p class="menu-section-title">Navigation</p>
									<div class="menu-grid">
										<a href="<?php echo esc_url(home_url('mon-compte/')); ?>" class="menu-card">
											<div class="menu-card-icon"><i class="fa-solid fa-user"></i></div>
											<div class="menu-card-title">Mon Compte</div>
											<div class="menu-card-subtitle">Profil & commandes</div>
										</a>
										
										<a href="<?php echo esc_url(home_url('favoris/')); ?>" class="menu-card">
											<div class="menu-card-icon"><i class="fa-solid fa-heart" style="color: #f1bb69;"></i></div>
											<div class="menu-card-title">Mes Favoris</div>
											<div class="menu-card-subtitle">Produits préférés</div>
										</a>
										
										<a href="<?php echo esc_url(home_url('boutique/')); ?>" class="menu-card">
											<div class="menu-card-icon"><i class="fa-solid fa-store"></i></div>
											<div class="menu-card-title">Boutique</div>
											<div class="menu-card-subtitle">Tous nos produits</div>
										</a>
										
										<a href="<?php echo esc_url(home_url('contact/')); ?>" class="menu-card">
											<div class="menu-card-icon"><i class="fa-solid fa-envelope"></i></div>
											<div class="menu-card-title">Contact</div>
											<div class="menu-card-subtitle">Nous contacter</div>
										</a>
										
										<a href="<?php echo esc_url(home_url('service/')); ?>" class="menu-card">
											<div class="menu-card-icon"><i class="fa-solid fa-handshake"></i></div>
											<div class="menu-card-title">Services</div>
											<div class="menu-card-subtitle">Nos services</div>
										</a>
									</div>
								</div>

							</div>
						</div>
					</div>
				</nav>

				<!-- Nouvelle barre de catégories (desktop uniquement) -->
				<div class="category-bar bg-light d-none d-md-block">
					<div class="category-scroll-container position-relative">
						<button class="category-scroll-btn category-scroll-left" id="scrollLeft">
							<i class="fa-solid fa-chevron-left"></i>
						</button>
						<div class="category-list">
						<?php
						$cat_args = array(
							'orderby'    => 'menu_order',
							'order'      => 'ASC',
							'hide_empty' => true,
							'parent'     => 0,
							'exclude'    => [16],
							'number'     => 20
						);

						$product_categories = get_terms('product_cat', $cat_args);
						
						// Ajouter un lien "Toutes les catégories" en premier
						echo '<a href="' . esc_url(home_url('boutique/')) . '" class="category-item">';
						echo '<i class="fa-solid fa-grid-2 me-2"></i>Toutes les catégories';
						echo '</a>';
						
						if (!empty($product_categories)) {
							foreach ($product_categories as $category) {
								$thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
								$category_link = get_term_link($category);
								
								echo '<a href="' . esc_url($category_link) . '" class="category-item">';
								
								if ($thumbnail_id) {
									echo '<img src="' . esc_url(wp_get_attachment_url($thumbnail_id)) . '" alt="' . esc_attr($category->name) . '">';
								}
								
								echo esc_html($category->name);
								echo '</a>';
							}
						}
						?>
						</div>
						<button class="category-scroll-btn category-scroll-right" id="scrollRight">
							<i class="fa-solid fa-chevron-right"></i>
						</button>
					</div>
				</div>
			</div>



			<!-- Offcanvas cart -->
			<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-cart">
				   <div class="offcanvas-header bg-light flex-row align-items-center justify-content-between">
					   <span class="h5 mb-0">Panier</span>
					   <button type="button" class="btn-close text-reset ms-2" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				   </div>
				   <hr class="m-0" style="border-top:1.5px solid #d1d7e0;">
				   <div class="offcanvas-body p-0 d-flex flex-column" style="min-height: 400px;">
					   <div class="free-shipping-progress mt-3 px-3">
						   <div class="fsp-label text-center mb-2" style="font-size:1.05rem;"></div>
						   <div class="fsp-bar-bg" style="width:100%;height:18px;background:#e8f0ed;border-radius:9px;position:relative;border:1.5px solid #bfc8d6;">
							   <div class="fsp-bar-fill" style="height:100%;background:#6fa298;border-radius:9px 0 0 9px;width:0%;transition:width 0.4s;"></div>
						   </div>
						   <div class="mt-1 text-center mb-3" style="font-size:0.98rem;color:#23443b;">
							   <span style="display:inline-block;font-weight:600;">550&nbsp;€ : Livraison offerte</span>
						   </div>
					   </div>
					   <hr class="m-0" style="border-top:1.5px solid #d1d7e0;">
					   <div class="cart-list flex-grow-1 overflow-auto">
						   <div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
					   </div>
					   <script>
					   // Forcer le rafraîchissement du mini-cart à l'ouverture
					   document.addEventListener('DOMContentLoaded', function() {
						   var offcanvasCart = document.getElementById('offcanvas-cart');
						   if (offcanvasCart) {
							   offcanvasCart.addEventListener('shown.bs.offcanvas', function () {
								   // Trigger le refresh du mini-cart
								   jQuery(document.body).trigger('wc_fragment_refresh');
							   });
						   }
					   });
					   </script>
					   <div class="cart-footer bg-light p-3 border-top mt-auto">
						   <?php
						   if (function_exists('WC')) {
							   $cart = WC()->cart;

							   // Conteneur pour le message de réapprovisionnement (mis à jour via AJAX)
							   ?>
							   <div class="mini-cart-backorder-notice">
								   <?php
								   // Vérifier s'il y a des produits en réapprovisionnement dans le panier
								   $has_backorder_items = false;
								   foreach ($cart->get_cart() as $cart_item) {
									   $_product = $cart_item['data'];
									   $stock_status = $_product->get_stock_status();
									   $stock_quantity = $_product->get_stock_quantity();
									   $backorders = $_product->get_backorders();
									   $current_qty = $cart_item['quantity'];

									   // Vérifier si produit en réapprovisionnement
									   if ($stock_status === 'outofstock' || $stock_status === 'onbackorder' ||
										   (!$_product->is_in_stock() && ($stock_quantity === 0 || $stock_quantity === null))) {
										   $has_backorder_items = true;
										   break;
									   }

									   // Vérifier si quantité dépasse le stock avec backorders
									   if (($backorders === 'yes' || $backorders === 'notify') &&
										   $stock_quantity !== null && $current_qty > $stock_quantity) {
										   $has_backorder_items = true;
										   break;
									   }
								   }

								   // Afficher le message de réapprovisionnement si nécessaire
								   if ($has_backorder_items) {
									   echo '<div style="background: #FFF8E7; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; font-size: 0.8rem; color: #8B6914; display: flex; align-items: start; gap: 8px;">';
									   echo '<i class="fa-solid fa-clock" style="color: #E9A825; font-size: 0.85rem; margin-top: 2px;"></i>';
									   echo '<span style="line-height: 1.4;">Certains articles sont en réapprovisionnement. Délais de livraison susceptibles d\'être allongés.</span>';
									   echo '</div>';
								   }
								   ?>
							   </div>
							   <?php

							   echo '<div class="d-flex justify-content-between align-items-center mb-2">';
							   echo '<span class="fw-bold">Total :</span>';
							   echo '<span class="fw-bold">' . wc_price($cart->get_total('edit')) . '</span>';
							   echo '</div>';
							   echo '<button type="button" class="btn btn-outline-primary w-100 mb-2" data-bs-dismiss="offcanvas">Continuer mes achats</button>';
							   echo '<a href="' . esc_url(wc_get_cart_url()) . '" class="btn btn-primary w-100">Commander</a>';
						   }
						   ?>
					   </div>
				   </div>
			</div>

				</header>

	<!-- Style livraison offerte déplacé dans custom-header.css -->
	<!-- Scripts déplacés dans custom-header.js -->