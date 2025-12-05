<?php
/**
 * Enqueue scripts and styles
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function aleaulavage_v2_scripts()
{
    $theme_version = wp_get_theme()->get('Version');
    $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

    // Bootstrap (Local)
    $bootstrap_css = get_template_directory() . '/assets/vendor/bootstrap/bootstrap.min.css';
    $bootstrap_js = get_template_directory() . '/assets/vendor/bootstrap/bootstrap.bundle.min.js';
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/vendor/bootstrap/bootstrap.min.css', array(), file_exists($bootstrap_css) ? filemtime($bootstrap_css) : $theme_version);
    wp_enqueue_script('bootstrap', get_template_directory_uri() . '/assets/vendor/bootstrap/bootstrap.bundle.min.js', array('jquery'), file_exists($bootstrap_js) ? filemtime($bootstrap_js) : $theme_version, true);

    // FontAwesome (Local)
    $fontawesome_css = get_template_directory() . '/assets/vendor/fontawesome/all.min.css';
    wp_enqueue_style('fontawesome', get_template_directory_uri() . '/assets/vendor/fontawesome/all.min.css', array(), file_exists($fontawesome_css) ? filemtime($fontawesome_css) : $theme_version);

    // Lucide Icons (Local)
    $lucide_js = get_template_directory() . '/assets/vendor/lucide/lucide.min.js';
    wp_enqueue_script('lucide', get_template_directory_uri() . '/assets/vendor/lucide/lucide.min.js', array(), file_exists($lucide_js) ? filemtime($lucide_js) : $theme_version, true);

    // Main Style
    $main_style = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style('aleaulavage-v2-style', get_stylesheet_uri(), array(), file_exists($main_style) ? filemtime($main_style) : $theme_version);

    // Gutenberg Styles (loaded globally for editor compatibility)
    $gutenberg_css = get_template_directory() . '/assets/css/gutenberg' . $suffix . '.css';
    wp_enqueue_style('aleaulavage-v2-gutenberg', get_template_directory_uri() . '/assets/css/gutenberg' . $suffix . '.css', array(), file_exists($gutenberg_css) ? filemtime($gutenberg_css) : $theme_version);

    // Header Assets
    $header_css = get_template_directory() . '/assets/css/header' . $suffix . '.css';
    $header_js = get_template_directory() . '/assets/js/header.js';
    wp_enqueue_style('aleaulavage-v2-header', get_template_directory_uri() . '/assets/css/header' . $suffix . '.css', array(), file_exists($header_css) ? filemtime($header_css) : $theme_version);
    wp_enqueue_script('aleaulavage-v2-header', get_template_directory_uri() . '/assets/js/header.js', array('jquery'), file_exists($header_js) ? filemtime($header_js) : $theme_version, true);

    // Homepage Assets
    if (is_front_page()) {
        $home_css = get_template_directory() . '/assets/css/home' . $suffix . '.css';
        wp_enqueue_style('aleaulavage-v2-home', get_template_directory_uri() . '/assets/css/home' . $suffix . '.css', array(), '2.2.25');
    }

    // Shop Page Assets
    if (is_shop() || is_product_category() || is_product_tag() || is_tax('product_brand') || is_page('favoris')) {
        $shop_css = get_template_directory() . '/assets/css/shop' . $suffix . '.css';
        wp_enqueue_style('aleaulavage-v2-shop', get_template_directory_uri() . '/assets/css/shop' . $suffix . '.css', array(), file_exists($shop_css) ? filemtime($shop_css) : $theme_version);

        // Shop AJAX filtering
        $shop_ajax_js = get_template_directory() . '/assets/js/shop-ajax.js';
        wp_enqueue_script('aleaulavage-v2-shop-ajax', get_template_directory_uri() . '/assets/js/shop-ajax.js', array('jquery'), file_exists($shop_ajax_js) ? filemtime($shop_ajax_js) : $theme_version, true);
    }

    // My Account Page Assets
    if (is_account_page()) {
        $account_css = get_template_directory() . '/assets/css/account.css';
        wp_enqueue_style('aleaulavage-v2-account', get_template_directory_uri() . '/assets/css/account.css', array(), file_exists($account_css) ? filemtime($account_css) : $theme_version);
    }

    // Product Page Assets
    if (is_product()) {
        $product_css = get_template_directory() . '/assets/css/product.css';
        wp_enqueue_style('aleaulavage-v2-product', get_template_directory_uri() . '/assets/css/product.css', array(), file_exists($product_css) ? filemtime($product_css) : $theme_version);
    }

    // Blog & Single Post Assets
    if (is_single() || is_home() || is_archive() || is_category() || is_tag()) {
        $blog_css = get_template_directory() . '/assets/css/blog' . $suffix . '.css';
        wp_enqueue_style('aleaulavage-v2-blog', get_template_directory_uri() . '/assets/css/blog' . $suffix . '.css', array(), file_exists($blog_css) ? filemtime($blog_css) : $theme_version);
    }

    // Product Cart Custom JS
    if (class_exists('WooCommerce')) {
        $product_cart_js = get_template_directory() . '/assets/js/product-cart.js';
        wp_enqueue_script('aleaulavage-v2-product-cart', get_template_directory_uri() . '/assets/js/product-cart.js', array('jquery'), file_exists($product_cart_js) ? filemtime($product_cart_js) : $theme_version, true);

        // Localize script with nonce for security
        wp_localize_script('aleaulavage-v2-product-cart', 'aleaulavage_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aleaulavage_cart_nonce')
        ));

        // Cart Offcanvas JS
        $cart_offcanvas_js = get_template_directory() . '/assets/js/cart-offcanvas.js';
        wp_enqueue_script('aleaulavage-v2-cart-offcanvas', get_template_directory_uri() . '/assets/js/cart-offcanvas.js', array('jquery'), file_exists($cart_offcanvas_js) ? filemtime($cart_offcanvas_js) : $theme_version, true);

        // Localize ajax params for cart offcanvas
        wp_localize_script('aleaulavage-v2-cart-offcanvas', 'aleaulavage_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aleaulavage_cart_nonce')
        ));

        // Wishlist JS
        $wishlist_js = get_template_directory() . '/assets/js/wishlist.js';
        wp_enqueue_script('aleaulavage-v2-wishlist', get_template_directory_uri() . '/assets/js/wishlist.js', array('jquery'), file_exists($wishlist_js) ? filemtime($wishlist_js) : $theme_version, true);

        wp_localize_script('aleaulavage-v2-wishlist', 'aleaulavage_wishlist_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aleaulavage_wishlist_nonce'),
            'is_logged_in' => is_user_logged_in()
        ));
    }

    // Cart Page Assets
    if (is_cart()) {
        $cart_css = get_template_directory() . '/assets/css/cart.css';
        wp_enqueue_style('aleaulavage-v2-cart', get_template_directory_uri() . '/assets/css/cart.css', array(), file_exists($cart_css) ? filemtime($cart_css) : $theme_version);

        // Ensure core WP packages some plugins rely on are available early
        // Prevents "wp is not defined" / "_ is not defined" issues
        wp_enqueue_script('underscore');
        wp_enqueue_script('wp-api-fetch');
        wp_enqueue_script('wp-url');
        wp_enqueue_script('wp-i18n');
    }

    // Checkout Page Assets
    if (is_checkout()) {
        $checkout_css = get_template_directory() . '/assets/css/checkout.css';
        wp_enqueue_style('aleaulavage-v2-checkout', get_template_directory_uri() . '/assets/css/checkout.css', array(), file_exists($checkout_css) ? filemtime($checkout_css) : $theme_version);

        $checkout_js = get_template_directory() . '/assets/js/checkout.js';
        wp_enqueue_script('aleaulavage-v2-checkout', get_template_directory_uri() . '/assets/js/checkout.js', array('jquery', 'wc-checkout'), file_exists($checkout_js) ? filemtime($checkout_js) : $theme_version, true);

        // Note: wc_checkout_params is already provided by WooCommerce
        // DO NOT override it or we'll lose critical nonces for update_order_review, apply_coupon, etc.
    }
}
add_action('wp_enqueue_scripts', 'aleaulavage_v2_scripts');

// Defer parsing of JavaScript (frontend only)
function aleaulavage_v2_defer_scripts($tag, $handle, $src)
{
    // Don't defer scripts in admin
    if (is_admin()) {
        return $tag;
    }

    // Don't defer critical or dependency-provider scripts
    // - jQuery and our own AJAX script
    // - WordPress package scripts (handles starting with wp-)
    // - WooCommerce core/blocks scripts (handles starting with wc-)
    // - Vendor globals many scripts expect synchronously: underscore/lodash, react, react-dom
    $no_defer = array('jquery', 'jquery-core', 'jquery-migrate', 'aleaulavage-v2-shop-ajax', 'underscore', 'lodash', 'react', 'react-dom');
    if (in_array($handle, $no_defer)) {
        return $tag;
    }
    if (strpos($handle, 'wp-') === 0 || strpos($handle, 'wc-') === 0) {
        return $tag;
    }

    // Defer other scripts
    if (strpos($tag, 'defer') === false) {
        $tag = str_replace(' src', ' defer src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'aleaulavage_v2_defer_scripts', 10, 3);
