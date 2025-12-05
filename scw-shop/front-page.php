<?php
/**
 * Front Page Template
 * Page d'accueil du site
 *
 * @package SCW_Shop
 */

get_header();

$user_role = scw_shop_get_user_role();
$user_mode = scw_shop_get_user_mode();

// Récupérer les données de la page de configuration
$homepage_data = scw_shop_parse_homepage_content();

// Get products for sliders
$best_sellers_args = array(
    'post_type'      => 'product',
    'posts_per_page' => 8,
    'meta_key'       => 'total_sales',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
);
$best_sellers = new WP_Query( $best_sellers_args );

$new_arrivals_args = array(
    'post_type'      => 'product',
    'posts_per_page' => 8,
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$new_arrivals = new WP_Query( $new_arrivals_args );

$promo_args = array(
    'post_type'      => 'product',
    'posts_per_page' => 8,
    'meta_query'     => array(
        array(
            'key'     => '_sale_price',
            'value'   => '',
            'compare' => '!=',
        ),
    ),
);
$promo_products = new WP_Query( $promo_args );

// Get product categories
$categories = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
    'parent'     => 0,
    'number'     => 9,
) );

// Get brands from WooCommerce product attributes
// First try to get brands from product_brand taxonomy (if exists)
$brands_terms = get_terms( array(
    'taxonomy'   => 'product_brand',
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
) );

// If no product_brand taxonomy, try pa_brand or pa_marque attributes
if ( empty( $brands_terms ) || is_wp_error( $brands_terms ) ) {
    $brands_terms = get_terms( array(
        'taxonomy'   => 'pa_brand',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );
}

if ( empty( $brands_terms ) || is_wp_error( $brands_terms ) ) {
    $brands_terms = get_terms( array(
        'taxonomy'   => 'pa_marque',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );
}

// If still no brands found, fallback to config
if ( empty( $brands_terms ) || is_wp_error( $brands_terms ) ) {
    $brands = $homepage_data['brands'];
    $brands_with_links = array();
    foreach ( $brands as $brand ) {
        $brands_with_links[] = array(
            'name' => strtoupper( $brand ),
            'url'  => '',
        );
    }
} else {
    $brands_with_links = array();

    // Get the shop page URL - try to find page with template-shop.php
    $shop_pages = get_pages( array(
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'template-shop.php',
    ) );

    if ( ! empty( $shop_pages ) ) {
        $shop_url = get_permalink( $shop_pages[0]->ID );
    } else {
        // Fallback to home_url/boutique
        $shop_url = home_url( '/boutique' );
    }

    foreach ( $brands_terms as $brand_term ) {
        $brands_with_links[] = array(
            'name' => strtoupper( $brand_term->name ),
            'url'  => add_query_arg( 'brand', $brand_term->slug, $shop_url ),
        );
    }
}
?>

<main id="main" class="site-main">

    <!-- Hero Section -->
    <section class="hero-section <?php echo esc_attr( $user_role ); ?> <?php echo $user_mode ? 'mode-' . esc_attr( $user_mode ) : ''; ?>">
        <div class="hero-content">
            <?php if ( $user_role === 'guest' ) : ?>
                <span class="hero-pill"><?php echo esc_html( $homepage_data['hero_pill'] ); ?></span>
                <h1><?php echo wp_kses_post( $homepage_data['hero_title'] ); ?></h1>
                <p class="subtitle"><?php echo esc_html( $homepage_data['hero_subtitle'] ); ?></p>
                <div class="hero-buttons">
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) . '?action=register' ); ?>" class="hero-cta primary">
                        <?php echo esc_html( $homepage_data['hero_cta_guest'] ); ?>
                    </a>
                </div>
            <?php elseif ( $user_role === 'reseller' ) : ?>
                <?php if ( $user_mode === 'gestion' ) : ?>
                    <span class="hero-pill admin"><?php echo esc_html( $homepage_data['reseller_gestion_pill'] ); ?></span>
                    <h1><?php echo esc_html( $homepage_data['reseller_gestion_title'] ); ?></h1>
                    <div class="dashboard-stats">
                        <div class="stat-item">
                            <div class="stat-icon margin-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="20" x2="18" y2="10"></line>
                                    <line x1="12" y1="20" x2="12" y2="4"></line>
                                    <line x1="6" y1="20" x2="6" y2="14"></line>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label"><?php echo esc_html( $homepage_data['reseller_gestion_stat1_label'] ); ?></span>
                                <strong class="stat-value text-green">38%</strong>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon revenue-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label"><?php echo esc_html( $homepage_data['reseller_gestion_stat2_label'] ); ?></span>
                                <strong class="stat-value text-blue">12.4k€</strong>
                            </div>
                        </div>
                    </div>
                <?php elseif ( $user_mode === 'achat' ) : ?>
                    <span class="hero-pill admin"><?php echo esc_html( $homepage_data['reseller_achat_pill'] ); ?></span>
                    <h1><?php echo esc_html( $homepage_data['reseller_achat_title'] ); ?></h1>
                    <p class="subtitle"><?php echo esc_html( $homepage_data['reseller_achat_subtitle'] ); ?></p>
                <?php elseif ( $user_mode === 'vitrine' ) : ?>
                    <span class="hero-pill client"><?php echo esc_html( $homepage_data['reseller_vitrine_pill'] ); ?></span>
                    <h1><?php echo esc_html( $homepage_data['reseller_vitrine_title'] ); ?></h1>
                    <p class="subtitle"><?php echo esc_html( $homepage_data['reseller_vitrine_subtitle'] ); ?></p>
                <?php endif; ?>
            <?php elseif ( $user_role === 'client' ) : ?>
                <span class="hero-pill"><?php echo esc_html( $homepage_data['hero_pill'] ); ?></span>
                <h1><?php echo wp_kses_post( $homepage_data['hero_title'] ); ?></h1>
                <p class="subtitle"><?php echo esc_html( $homepage_data['hero_subtitle'] ); ?></p>
                <div class="hero-buttons">
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="hero-cta client">
                        <?php echo esc_html( $homepage_data['hero_cta_client'] ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Brand Marquee -->
    <div class="brand-marquee">
        <div class="brand-track">
            <?php
            // Duplicate brands array for seamless loop
            $brands_loop = array_merge( $brands_with_links, $brands_with_links, $brands_with_links, $brands_with_links );
            foreach ( $brands_loop as $brand ) :
            ?>
                <?php if ( ! empty( $brand['url'] ) ) : ?>
                    <a href="<?php echo esc_url( $brand['url'] ); ?>" class="brand-item">
                        <?php echo esc_html( $brand['name'] ); ?>
                    </a>
                <?php else : ?>
                    <div class="brand-item"><?php echo esc_html( $brand['name'] ); ?></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Best Sellers Slider -->
    <?php if ( $best_sellers->have_posts() ) : ?>
        <section class="product-slider-section products-section">
            <div class="slider-header-wrapper">
                <div class="slider-texts">
                    <h2><?php echo esc_html( $homepage_data['slider_bestsellers_title'] ); ?></h2>
                </div>
                <div class="slider-nav-buttons">
                    <button class="nav-btn prev" aria-label="Précédent">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <button class="nav-btn next" aria-label="Suivant">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="slider-viewport">
                <?php while ( $best_sellers->have_posts() ) : $best_sellers->the_post(); ?>
                    <div class="slider-item">
                        <?php wc_get_template_part( 'content', 'product' ); ?>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
                <div class="slider-item see-more-card">
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="see-more-link">
                        <span><?php echo esc_html( $homepage_data['slider_see_more'] ); ?></span>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Promo Carousel -->
    <?php if ( ! empty( $homepage_data['promo_slides'] ) ) : ?>
    <section class="promo-carousel theme-<?php echo esc_attr( $homepage_data['promo_slides'][0]['theme'] ?? 'winter' ); ?>">
        <div class="carousel-timer">
            <svg width="48" height="48" viewBox="0 0 48 48">
                <circle class="timer-bg" cx="24" cy="24" r="18" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3"/>
                <circle class="timer-progress" cx="24" cy="24" r="18" fill="none" stroke="white" stroke-width="3"
                    stroke-linecap="round" transform="rotate(-90 24 24)"/>
                <g class="pause-icon" transform="translate(18, 18) scale(0.5)" style="opacity: 0; transition: opacity 0.3s;">
                    <rect x="6" y="4" width="4" height="16" fill="white"/>
                    <rect x="14" y="4" width="4" height="16" fill="white"/>
                </g>
            </svg>
        </div>

        <?php foreach ( $homepage_data['promo_slides'] as $index => $slide ) : ?>
        <div class="promo-slide-content <?php echo $index > 0 ? 'hidden' : ''; ?>" data-theme="<?php echo esc_attr( $slide['theme'] ); ?>">
            <div class="promo-content">
                <span class="promo-label"><?php echo esc_html( $slide['label'] ); ?></span>
                <h2><?php echo esc_html( $slide['title'] ); ?></h2>
                <p><?php echo esc_html( $slide['description'] ); ?></p>
                <a href="<?php echo esc_url( home_url( $slide['url'] ) ); ?>" class="promo-btn"><?php echo esc_html( $slide['button_text'] ); ?></a>
            </div>
            <div class="promo-icon">
                <?php echo scw_shop_get_promo_icon( $slide['theme'] ); ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if ( count( $homepage_data['promo_slides'] ) > 1 ) : ?>
        <div class="carousel-dots">
            <?php foreach ( $homepage_data['promo_slides'] as $index => $slide ) : ?>
            <button class="dot <?php echo $index === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- New Arrivals Slider -->
    <?php if ( $new_arrivals->have_posts() ) : ?>
        <section class="product-slider-section products-section">
            <div class="slider-header-wrapper">
                <div class="slider-texts">
                    <h2><?php echo esc_html( $homepage_data['slider_newarrivals_title'] ); ?></h2>
                </div>
                <div class="slider-nav-buttons">
                    <button class="nav-btn prev" aria-label="Précédent">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <button class="nav-btn next" aria-label="Suivant">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="slider-viewport">
                <?php while ( $new_arrivals->have_posts() ) : $new_arrivals->the_post(); ?>
                    <div class="slider-item">
                        <?php wc_get_template_part( 'content', 'product' ); ?>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
                <div class="slider-item see-more-card">
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="see-more-link">
                        <span><?php echo esc_html( $homepage_data['slider_see_more'] ); ?></span>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Features Bar -->
    <section class="features-bar">
        <?php foreach ( $homepage_data['features'] as $feature ) : ?>
        <div class="feature-item">
            <span class="feature-icon">
                <?php echo scw_shop_get_feature_icon( $feature['icon'] ); ?>
            </span>
            <div>
                <strong><?php echo esc_html( $feature['title'] ); ?></strong>
                <span><?php echo esc_html( $feature['subtitle'] ); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

    <!-- Promo Products Slider -->
    <?php if ( $promo_products->have_posts() ) : ?>
        <div class="promo-slider-wrapper">
            <section class="product-slider-section products-section">
                <div class="slider-header-wrapper">
                    <div class="slider-texts">
                        <h2><?php echo esc_html( $homepage_data['slider_promos_title'] ); ?></h2>
                    </div>
                </div>
                <div class="slider-viewport">
                    <?php while ( $promo_products->have_posts() ) : $promo_products->the_post(); ?>
                        <div class="slider-item">
                            <?php wc_get_template_part( 'content', 'product' ); ?>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
        </div>
    <?php endif; ?>

    <!-- Categories Section -->
    <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
        <section class="categories-section">
            <div class="section-header">
                <h2><?php echo esc_html( $homepage_data['categories_title'] ); ?></h2>
            </div>
            <div class="categories-grid">
                <?php
                $shop_page = get_page_by_path( 'boutique' );
                $shop_url = $shop_page ? get_permalink( $shop_page->ID ) : home_url( '/boutique/' );
                foreach ( $categories as $index => $category ) :
                    $category_url = add_query_arg( 'category', $category->slug, $shop_url );
                    ?>
                    <a href="<?php echo esc_url( $category_url ); ?>" class="category-card">
                        <div class="category-image-placeholder">
                            <div class="category-icon-wrapper">
                                <?php echo scw_shop_get_category_icon( $category ); ?>
                            </div>
                        </div>
                        <h3><?php echo esc_html( $category->name ); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Trust Section -->
    <section class="trust-section">
        <h2><?php echo esc_html( $homepage_data['trust_title'] ); ?></h2>
        <div class="trust-grid">
            <?php foreach ( $homepage_data['trust_cards'] as $card ) : ?>
            <div class="trust-card">
                <div class="trust-icon">
                    <?php echo scw_shop_get_trust_icon( $card['icon'] ); ?>
                </div>
                <h3><?php echo esc_html( $card['title'] ); ?></h3>
                <p><?php echo esc_html( $card['description'] ); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<?php get_footer(); ?>
