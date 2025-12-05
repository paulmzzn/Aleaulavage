<?php
/**
 * Enqueue scripts and styles
 *
 * @package SCW_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue scripts and styles
 */
function scw_shop_scripts() {
    // Main stylesheet - keep as blocking (contains critical CSS)
    wp_enqueue_style(
        'scw-shop-style',
        get_stylesheet_uri(),
        array(),
        SCW_SHOP_VERSION
    );

    // Component styles - combine critical ones
    wp_enqueue_style(
        'scw-shop-header',
        SCW_SHOP_URI . '/assets/css/header.css',
        array(),
        SCW_SHOP_VERSION
    );

    wp_enqueue_style(
        'scw-shop-components',
        SCW_SHOP_URI . '/assets/css/components.css',
        array(),
        SCW_SHOP_VERSION
    );

    // Non-critical styles - load asynchronously
    wp_enqueue_style(
        'scw-shop-footer',
        SCW_SHOP_URI . '/assets/css/footer.css',
        array(),
        SCW_SHOP_VERSION,
        'all'
    );
    wp_style_add_data( 'scw-shop-footer', 'async', true );

    // Product card styles
    wp_enqueue_style(
        'scw-shop-product-card',
        SCW_SHOP_URI . '/assets/css/product-card.css',
        array(),
        SCW_SHOP_VERSION
    );

    // Account page styles and scripts
    if ( is_account_page() ) {
        wp_enqueue_style(
            'scw-shop-account',
            SCW_SHOP_URI . '/assets/css/account.css',
            array(),
            SCW_SHOP_VERSION
        );

        wp_enqueue_script(
            'scw-shop-account',
            SCW_SHOP_URI . '/assets/js/account.js',
            array(),
            SCW_SHOP_VERSION,
            true
        );
    }

    // Shop page styles
    if ( is_page_template( 'template-shop.php' ) ) {
        wp_enqueue_style(
            'scw-shop-page',
            SCW_SHOP_URI . '/assets/css/shop.css',
            array(),
            SCW_SHOP_VERSION
        );
    }

    // Product detail page styles and scripts
    if ( is_product() ) {
        wp_enqueue_style(
            'scw-shop-product-detail',
            SCW_SHOP_URI . '/assets/css/product-detail.css',
            array(),
            SCW_SHOP_VERSION
        );

        wp_enqueue_script(
            'scw-shop-product-detail',
            SCW_SHOP_URI . '/assets/js/product-detail.js',
            array(),
            SCW_SHOP_VERSION,
            true
        );
    }

    // Favorites page styles and scripts
    if ( is_page_template( 'template-favorites.php' ) ) {
        wp_enqueue_style(
            'scw-shop-favorites',
            SCW_SHOP_URI . '/assets/css/favorites.css',
            array(),
            SCW_SHOP_VERSION
        );

        wp_enqueue_script(
            'scw-shop-favorites',
            SCW_SHOP_URI . '/assets/js/favorites.js',
            array(),
            SCW_SHOP_VERSION,
            true
        );
    }

    // Cart page styles and scripts
    if ( is_page_template( 'template-cart.php' ) ) {
        wp_enqueue_style(
            'scw-shop-cart',
            SCW_SHOP_URI . '/assets/css/cart.css',
            array(),
            SCW_SHOP_VERSION
        );

        wp_enqueue_script(
            'scw-shop-cart',
            SCW_SHOP_URI . '/assets/js/cart.js',
            array(),
            SCW_SHOP_VERSION,
            true
        );

        // Localize script for cart AJAX operations
        wp_localize_script( 'scw-shop-cart', 'scw_shop_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'scw-cart-nonce' ),
        ) );
    }

    // Checkout page styles and scripts
    $is_checkout_page = false;
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        $is_checkout_page = true;
    }
    // Also check URL as fallback
    if ( strpos( $_SERVER['REQUEST_URI'], '/commander' ) !== false ) {
        $is_checkout_page = true;
    }
    
    if ( is_page_template( 'template-checkout.php' ) || $is_checkout_page ) {
        wp_enqueue_style(
            'scw-shop-checkout',
            SCW_SHOP_URI . '/assets/css/checkout.css',
            array(),
            SCW_SHOP_VERSION
        );

        wp_enqueue_script(
            'scw-shop-checkout',
            SCW_SHOP_URI . '/assets/js/checkout.js',
            array(),
            SCW_SHOP_VERSION,
            true
        );
    }

    // Home page styles
    if ( is_front_page() ) {
        wp_enqueue_style(
            'scw-shop-home',
            SCW_SHOP_URI . '/assets/css/home.css',
            array(),
            SCW_SHOP_VERSION
        );

        wp_enqueue_style(
            'scw-shop-sliders',
            SCW_SHOP_URI . '/assets/css/sliders.css',
            array(),
            SCW_SHOP_VERSION
        );

        wp_enqueue_script(
            'scw-shop-carousel',
            SCW_SHOP_URI . '/assets/js/carousel.js',
            array(),
            SCW_SHOP_VERSION,
            true
        );
    }

    // Main JavaScript
    wp_enqueue_script(
        'scw-shop-main',
        SCW_SHOP_URI . '/assets/js/main.js',
        array(),
        SCW_SHOP_VERSION,
        true
    );

    // Header JavaScript
    wp_enqueue_script(
        'scw-shop-header',
        SCW_SHOP_URI . '/assets/js/header.js',
        array(),
        SCW_SHOP_VERSION,
        true
    );

    // Product card JavaScript
    wp_enqueue_script(
        'scw-shop-product-card',
        SCW_SHOP_URI . '/assets/js/product-card.js',
        array(),
        SCW_SHOP_VERSION,
        true
    );

    // Mode switcher for resellers
    if ( scw_shop_get_user_role() === 'reseller' ) {
        wp_enqueue_style(
            'scw-shop-mode-switcher',
            SCW_SHOP_URI . '/assets/css/mode-switcher.css',
            array(),
            SCW_SHOP_VERSION
        );

        wp_enqueue_script(
            'scw-shop-mode-switcher',
            SCW_SHOP_URI . '/assets/js/mode-switcher.js',
            array(),
            SCW_SHOP_VERSION,
            true
        );
    }

    // Cookie consent banner
    wp_enqueue_style(
        'scw-shop-cookie-consent',
        SCW_SHOP_URI . '/assets/css/cookie-consent.css',
        array(),
        SCW_SHOP_VERSION
    );

    wp_enqueue_script(
        'scw-shop-cookie-consent',
        SCW_SHOP_URI . '/assets/js/cookie-consent.js',
        array(),
        SCW_SHOP_VERSION,
        true
    );

    // Localize script for AJAX
    wp_localize_script( 'scw-shop-main', 'scwShop', array(
        'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
        'nonce'      => wp_create_nonce( 'scw-shop-nonce' ),
        'userRole'   => scw_shop_get_user_role(),
        'userMode'   => scw_shop_get_user_mode(),
        'accountUrl' => wc_get_page_permalink( 'myaccount' ),
    ) );

    // Comment reply script for threaded comments
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'scw_shop_scripts' );

/**
 * Enqueue admin scripts and styles
 */
function scw_shop_admin_scripts() {
    wp_enqueue_style(
        'scw-shop-admin',
        SCW_SHOP_URI . '/assets/css/admin.css',
        array(),
        SCW_SHOP_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'scw_shop_admin_scripts' );

/**
 * Add defer attribute to scripts for better performance
 */
function scw_shop_defer_scripts( $tag, $handle, $src ) {
    // List of scripts that should NOT be deferred (dependencies)
    $no_defer = array(
        'jquery',
        'jquery-core',
        'jquery-migrate',
    );

    // Don't defer these critical scripts
    if ( in_array( $handle, $no_defer ) ) {
        return $tag;
    }

    // Add defer to all other scripts
    if ( strpos( $tag, 'defer' ) === false ) {
        $tag = str_replace( ' src', ' defer src', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'scw_shop_defer_scripts', 10, 3 );

/**
 * Remove unnecessary WordPress features for better performance
 */
function scw_shop_performance_optimizations() {
    // Remove emoji detection script
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );

    // Remove WordPress version from head
    remove_action( 'wp_head', 'wp_generator' );

    // Remove WLW Manifest
    remove_action( 'wp_head', 'wlwmanifest_link' );

    // Remove RSD link
    remove_action( 'wp_head', 'rsd_link' );

    // Remove shortlink
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'init', 'scw_shop_performance_optimizations' );

/**
 * Add lazy loading to images
 */
function scw_shop_add_lazy_loading( $content ) {
    // Add loading="lazy" to all images
    $content = preg_replace(
        '/<img(.*?)>/i',
        '<img$1 loading="lazy">',
        $content
    );

    return $content;
}
add_filter( 'the_content', 'scw_shop_add_lazy_loading', 99 );
add_filter( 'post_thumbnail_html', 'scw_shop_add_lazy_loading', 99 );

/**
 * Enable native lazy loading for WordPress images
 */
add_filter( 'wp_lazy_loading_enabled', '__return_true' );

/**
 * Enable WebP support
 */
function scw_shop_enable_webp_upload( $mimes ) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter( 'mime_types', 'scw_shop_enable_webp_upload' );

/**
 * Display WebP in WordPress admin
 */
function scw_shop_webp_is_displayable( $result, $path ) {
    if ( $result === false ) {
        $displayable_image_types = array( IMAGETYPE_WEBP );
        $info = @getimagesize( $path );

        if ( empty( $info ) ) {
            $result = false;
        } elseif ( ! in_array( $info[2], $displayable_image_types ) ) {
            $result = false;
        } else {
            $result = true;
        }
    }

    return $result;
}
add_filter( 'file_is_displayable_image', 'scw_shop_webp_is_displayable', 10, 2 );

/**
 * Load non-critical CSS asynchronously
 */
function scw_shop_async_css( $html, $handle ) {
    // List of non-critical stylesheets to load asynchronously
    $async_styles = array(
        'scw-shop-footer',
        'scw-shop-cookie-consent',
        'woocommerce-smallscreen',
    );

    if ( in_array( $handle, $async_styles ) ) {
        $html = str_replace( "rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html );
        $html .= '<noscript><link rel="stylesheet" href="' . esc_url( wp_styles()->registered[$handle]->src ) . '"></noscript>';
    }

    return $html;
}
add_filter( 'style_loader_tag', 'scw_shop_async_css', 10, 2 );

/**
 * Add preconnect and dns-prefetch hints
 */
function scw_shop_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type ) {
        // Add your own domain for faster font/asset loading
        $urls[] = array(
            'href' => home_url(),
            'crossorigin',
        );
    }

    if ( 'dns-prefetch' === $relation_type ) {
        // Add common external resources
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = 'https://fonts.gstatic.com';
    }

    return $urls;
}
add_filter( 'wp_resource_hints', 'scw_shop_resource_hints', 10, 2 );

/**
 * Disable WooCommerce scripts on non-shop pages
 */
function scw_shop_disable_woocommerce_scripts() {
    if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
        // Disable WooCommerce default styles
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );
        wp_dequeue_style( 'woocommerce-general' );

        // Disable WooCommerce scripts
        wp_dequeue_script( 'wc-cart-fragments' );
        wp_dequeue_script( 'woocommerce' );
    }
}
add_action( 'wp_enqueue_scripts', 'scw_shop_disable_woocommerce_scripts', 99 );
