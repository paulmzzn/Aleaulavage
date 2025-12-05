<?php
/**
 * Template part for displaying the main navigation
 */
?>
<nav id="nav-main" class="navbar main-navbar outline-gray box-shadow-gray bg-white">
    <div class="container">
        <!-- First Row: Logo + Actions -->
        <div class="w-100 d-flex align-items-center justify-content-md-between">
            <!-- Menu Burger (Mobile - Left Side) -->
            <button class="btn btn-outline-secondary d-lg-none me-1" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#offcanvas-navbar" aria-controls="offcanvas-navbar"
                aria-label="Ouvrir le menu de navigation">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
                <span class="visually-hidden">Menu</span>
            </button>

            <!-- Logo -->
            <div class="navbar-brand-container">
                <a class="navbar-brand xs d-md-none" href="<?php echo esc_url(home_url()); ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo/logo-sm.svg"
                        alt="logo" class="logo xs">
                </a>
                <a class="navbar-brand md d-none d-md-block" href="<?php echo esc_url(home_url()); ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo/logo.svg"
                        alt="logo" class="logo md">
                </a>
            </div>

            <!-- Search Bar (Desktop) -->
            <?php
            get_template_part('template-parts/header/search-form', null, array(
                'classes' => 'd-none d-md-block',
                'input_classes' => 'search-input-group',
                'placeholder' => 'Que recherchez-vous ?',
                'aria_label' => 'Recherche de produits'
            ));
            ?>

            <!-- Header Actions -->
            <div class="header-actions d-flex align-items-center ms-auto ms-md-0">
                <a class="btn bg-secondary me-2 d-none d-sm-inline-flex text-white"
                    href="<?php echo esc_url(home_url('boutique/')); ?>" aria-label="Accéder à la boutique">
                    <span>Boutique</span>
                </a>
                <a class="btn ms-1" href="<?php echo esc_url(home_url('mon-compte/')); ?>"
                    aria-label="Accéder à mon compte">
                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                    <span class="visually-hidden">Mon compte</span>
                </a>
                <a class="btn ms-1 position-relative" href="<?php echo esc_url(home_url('favoris/')); ?>" aria-label="Accéder à mes favoris<?php if (is_user_logged_in()) {
                       $user_id = get_current_user_id();
                       $wishlist = get_user_meta($user_id, 'user_wishlist', true);
                       $count = is_array($wishlist) ? count($wishlist) : 0;
                       if ($count > 0) {
                           echo ' (' . $count . ' articles)';
                       }
                   } ?>">
                    <i class="fa-<?php echo is_user_logged_in() ? 'solid' : 'regular'; ?> fa-heart text-dark"
                        aria-hidden="true"></i>
                    <span class="visually-hidden">Favoris</span>
                    <?php if (is_user_logged_in()) {
                        $user_id = get_current_user_id();
                        $wishlist = get_user_meta($user_id, 'user_wishlist', true);
                        $count = is_array($wishlist) ? count($wishlist) : 0;
                        if ($count > 0): ?>
                            <span class="wishlist-count position-absolute badge rounded-pill" aria-hidden="true">
                                <?php echo esc_html($count); ?>
                            </span>
                        <?php endif;
                    } ?>
                </a>
                <button class="btn ms-1 position-relative" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvas-cart" aria-controls="offcanvas-cart" aria-label="Ouvrir le panier<?php if (class_exists('WooCommerce') && WC()->cart) {
                        $count = WC()->cart->get_cart_contents_count();
                        if ($count > 0) {
                            echo ' (' . $count . ' articles)';
                        }
                    } ?>">
                    <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
                    <span class="visually-hidden">Panier</span>
                    <?php if (class_exists('WooCommerce') && WC()->cart) {
                        $count = WC()->cart->get_cart_contents_count();
                        ?>
                        <span class="cart-content">
                            <?php if ($count > 0) { ?>
                                <span class="badge bg-primary rounded-pill position-absolute top-0 start-100 translate-middle"
                                    aria-hidden="true"><?php echo esc_html($count); ?></span>
                            <?php } ?>
                        </span>
                    <?php } ?>
                </button>
            </div>
        </div>

        <!-- Second Row: Mobile Search -->
        <div class="w-100 d-block d-md-none mt-2">
            <?php
            get_template_part('template-parts/header/search-form', null, array(
                'classes' => '',
                'input_classes' => 'search-input-group mobile',
                'placeholder' => 'Rechercher un produit...',
                'aria_label' => 'Recherche de produits mobile'
            ));
            ?>
        </div>
    </div>
</nav>