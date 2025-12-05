<?php
/**
 * Header Main Template Part
 *
 * @package SCW_Shop
 */

// Get user role and cart count
$user_role = scw_shop_get_user_role();
$cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

// Get product categories
$categories = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
    'parent'     => 0,
) );

// Get shop page URL
$shop_page = get_page_by_path( 'boutique' );
$shop_url = $shop_page ? get_permalink( $shop_page->ID ) : wc_get_page_permalink( 'shop' );
?>

<header class="header">
    <div class="header-container">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" id="mobile-menu-toggle" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'scw-shop' ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <!-- Logo -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-logo">
            SCW<span>SHOP</span>
        </a>

        <!-- Desktop Navigation -->
        <nav class="header-nav">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-link">
                <?php esc_html_e( 'Accueil', 'scw-shop' ); ?>
            </a>

            <!-- Categories Dropdown -->
            <div class="header-dropdown-container" id="categories-dropdown">
                <a href="<?php echo esc_url( $shop_url ); ?>" class="header-link">
                    <?php esc_html_e( 'Nos Produits', 'scw-shop' ); ?>
                    <svg class="chevron-down" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </a>

                <div class="dropdown-menu">
                    <div class="dropdown-grid">
                        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                            <?php foreach ( $categories as $category ) : ?>
                                <a href="<?php echo esc_url( add_query_arg( 'category', $category->slug, $shop_url ) ); ?>" class="dropdown-item">
                                    <?php echo esc_html( $category->name ); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <a href="<?php echo esc_url( $shop_url ); ?>" class="header-link">
                <?php esc_html_e( 'Boutique', 'scw-shop' ); ?>
            </a>
        </nav>

        <!-- Search Bar -->
        <div class="header-search">
            <div class="search-wrapper">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    placeholder="<?php esc_attr_e( 'Rechercher...', 'scw-shop' ); ?>"
                    class="search-input"
                    id="header-search-input"
                />
            </div>
        </div>

        <!-- Actions -->
        <div class="header-actions">
            <?php if ( $user_role === 'guest' ) : ?>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn-login-header">
                    <?php esc_html_e( 'Se connecter', 'scw-shop' ); ?>
                </a>
            <?php else : ?>
                <!-- Account -->
                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="icon-btn desktop-only" aria-label="<?php esc_attr_e( 'Mon compte', 'scw-shop' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>

                <!-- Favorites -->
                <a href="<?php echo esc_url( home_url( '/favoris' ) ); ?>" class="icon-btn desktop-only" aria-label="<?php esc_attr_e( 'Mes favoris', 'scw-shop' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                </a>

                <!-- Cart -->
                <a href="<?php echo esc_url( home_url( '/panier' ) ); ?>" class="icon-btn cart-wrapper" aria-label="<?php esc_attr_e( 'Panier', 'scw-shop' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <?php if ( $cart_count > 0 ) : ?>
                        <span class="cart-count"><?php echo esc_html( $cart_count ); ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>

    <!-- Mobile Sidebar -->
    <div class="mobile-sidebar" id="mobile-sidebar">
        <div class="mobile-header">
            <span class="mobile-title"><?php esc_html_e( 'Menu', 'scw-shop' ); ?></span>
            <button class="icon-btn" id="mobile-menu-close" aria-label="<?php esc_attr_e( 'Fermer le menu', 'scw-shop' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="mobile-content">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-link">
                <?php esc_html_e( 'Accueil', 'scw-shop' ); ?>
            </a>

            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="mobile-link">
                <?php echo $user_role === 'guest' ? esc_html__( 'Se connecter', 'scw-shop' ) : esc_html__( 'Mon Compte', 'scw-shop' ); ?>
            </a>

            <a href="<?php echo esc_url( home_url( '/favoris' ) ); ?>" class="mobile-link">
                <?php esc_html_e( 'Mes Favoris', 'scw-shop' ); ?>
            </a>

            <a href="<?php echo esc_url( $shop_url ); ?>" class="mobile-link">
                <?php esc_html_e( 'Boutique', 'scw-shop' ); ?>
            </a>

            <div class="mobile-divider"></div>

            <div class="mobile-section-title"><?php esc_html_e( 'Nos Produits', 'scw-shop' ); ?></div>

            <div class="mobile-categories">
                <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                    <?php foreach ( $categories as $category ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'category', $category->slug, $shop_url ) ); ?>" class="mobile-cat-link">
                            <?php echo esc_html( $category->name ); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
