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

<html <?php language_attributes(); ?>>

<head>
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
	
	<style>
		/* Styles pour la barre de catégories */
		.category-bar {
			background: #f8f9fa;
			border-bottom: 1px solid #dee2e6;
			padding: 0.5rem 0;
		}
		
		.category-list {
			display: flex;
			align-items: center;
			gap: 1.5rem;
			overflow-x: auto;
			white-space: nowrap;
			scrollbar-width: none;
			-ms-overflow-style: none;
			padding: 0 2rem;
		}
		
		.category-list::-webkit-scrollbar {
			display: none;
		}
		
		.category-item {
			display: flex;
			align-items: center;
			text-decoration: none;
			color: #495057;
			font-size: 0.9rem;
			font-weight: 500;
			padding: 0.25rem 0.75rem;
			border-radius: 20px;
			transition: all 0.3s ease;
			flex-shrink: 0;
		}
		
		.category-item:hover {
			background-color: #e9ecef;
			color: #0d6efd;
			text-decoration: none;
		}
		
		.category-item img {
			width: 20px;
			height: 20px;
			object-fit: cover;
			border-radius: 50%;
			margin-right: 0.5rem;
		}
		
		/* Améliorer l'espacement de la navbar principale */
		.main-navbar .container {
			display: grid;
			grid-template-columns: auto 1fr auto;
			gap: 1rem;
			align-items: center;
		}
		
		.search-container {
			max-width: 600px;
			width: 100%;
		}
		
		/* Styles pour le menu mobile moderne */
		.modern-mobile-menu {
			padding: 1rem;
		}
		
		.menu-section {
			margin-bottom: 2rem;
		}
		
		.menu-section-title {
			font-size: 1rem;
			font-weight: 600;
			color: #6c757d;
			margin-bottom: 1rem;
			padding-left: 0.5rem;
		}
		
		.menu-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 0.75rem;
		}
		
		.menu-card {
			background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
			border: 1px solid #dee2e6;
			border-radius: 12px;
			padding: 1rem;
			text-decoration: none;
			color: #495057;
			transition: all 0.3s ease;
			display: flex;
			flex-direction: column;
			align-items: center;
			text-align: center;
			min-height: 90px;
			position: relative;
			overflow: hidden;
		}
		
		.menu-card::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 3px;
			background: linear-gradient(90deg, #0d6efd, #6610f2);
			transform: scaleX(0);
			transition: transform 0.3s ease;
		}
		
		.menu-card:hover {
			background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
			color: #0d6efd;
			text-decoration: none;
			transform: translateY(-2px);
			box-shadow: 0 8px 25px rgba(0,0,0,0.15);
		}
		
		.menu-card:hover::before {
			transform: scaleX(1);
		}
		
		.menu-card-icon {
			width: 40px;
			height: 40px;
			background: linear-gradient(135deg, #0d6efd, #6610f2);
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 0.5rem;
			color: white;
			font-size: 1.2rem;
		}
		
		.menu-card img {
			width: 32px;
			height: 32px;
			object-fit: cover;
			border-radius: 50%;
			margin-bottom: 0.5rem;
		}
		
		.menu-card-title {
			font-size: 0.85rem;
			font-weight: 600;
			line-height: 1.2;
			margin: 0;
		}
		
		.menu-card-subtitle {
			font-size: 0.7rem;
			color: #6c757d;
			margin-top: 0.25rem;
		}
		
		/* Responsive pour mobile */
		@media (max-width: 768px) {
			.main-navbar .container {
				display: flex;
				justify-content: space-between;
			}
			
			.search-container {
				display: none;
			}
			
			.category-bar {
				display: none; /* Masquer la barre de catégories sur mobile */
			}
		}
		
		/* Ajuster le margin-top de la page pour compenser la hauteur supplémentaire */
		   #page.site {
			margin-top: 120px; /* Desktop : header + catégories */
		   }
       
		   @media (max-width: 768px) {
			   #page.site {
				margin-top: 140px; /* Mobile : header avec recherche intégrée */
			   }
		   }
	</style>
	
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php wp_body_open(); ?>

	<div id="page" class="site">
		<header id="masthead" class="site-header">
			<div class="fixed-top">
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
									<h6 class="menu-section-title">Nos Catégories</h6>
									<div class="menu-grid">
										<?php
										// Récupérer les mêmes catégories que la barre desktop
										$cat_args = array(
											'orderby'    => 'menu_order',
											'order'      => 'ASC',
											'hide_empty' => true,
											'parent'     => 0,
											'exclude'    => [16],
											'number'     => 8 // Limiter pour le menu mobile
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
												
												// Raccourcir le nom si nécessaire
												$category_name = esc_html($category->name);
												if (strlen($category_name) > 20) {
													$category_name = substr($category_name, 0, 20) . '...';
												}
												
												echo '<div class="menu-card-title">' . $category_name . '</div>';
												echo '<div class="menu-card-subtitle">' . $category->count . ' produits</div>';
												echo '</a>';
											}
										}
										?>
									</div>
								</div>

								<!-- Section Navigation -->
								<div class="menu-section">
									<h6 class="menu-section-title">Navigation</h6>
									<div class="menu-grid">
										<a href="<?php echo esc_url(home_url('mon-compte/')); ?>" class="menu-card">
											<div class="menu-card-icon"><i class="fa-solid fa-user"></i></div>
											<div class="menu-card-title">Mon Compte</div>
											<div class="menu-card-subtitle">Profil & commandes</div>
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
				</div>
			</div>



			<!-- Offcanvas cart -->
			<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-cart">
				<div class="offcanvas-header bg-light">
					<span class="h5 mb-0">Panier</span>
					<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body p-0 d-flex flex-column" style="min-height: 400px;">
					<div class="cart-list flex-grow-1 overflow-auto">
						<div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
					</div>
					<div class="cart-footer bg-light p-3 border-top mt-auto">
						<?php
						if (function_exists('WC')) {
							$cart = WC()->cart;
							echo '<div class="d-flex justify-content-between align-items-center mb-2">';
							echo '<span class="fw-bold">Total :</span>';
							echo '<span class="fw-bold">' . wc_price($cart->get_total('edit')) . '</span>';
							echo '</div>';
							echo '<a href="' . esc_url(wc_get_cart_url()) . '" class="btn btn-outline-primary w-100 mb-2">Voir le panier</a>';
							echo '<a href="' . esc_url(wc_get_checkout_url()) . '" class="btn btn-primary w-100">Commander</a>';
							echo '<p class="text-muted small mt-2 mb-0 text-center">Les frais de livraison seront calculés lors de la validation de la commande.</p>';
						}
						?>
					</div>
				</div>
			</div>

		</header>