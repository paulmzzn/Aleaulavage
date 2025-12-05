<?php
/**
 * Performance Optimizations
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Clear product cache when a product is updated
function aleaulavage_v2_clear_product_cache($post_id)
{
    if (get_post_type($post_id) === 'product') {
        wp_cache_delete('aleaulavage_featured_products_v2');
    }
}
add_action('save_post', 'aleaulavage_v2_clear_product_cache');
add_action('woocommerce_update_product', 'aleaulavage_v2_clear_product_cache');

// Disable WooCommerce scripts on non-shop pages
function aleaulavage_v2_disable_woocommerce_scripts()
{
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page() && !is_front_page()) {
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_style('woocommerce-general');
    }
}
add_action('wp_enqueue_scripts', 'aleaulavage_v2_disable_woocommerce_scripts', 99);



// Remove unnecessary WP features (frontend only)
function aleaulavage_v2_disable_wp_features()
{
    if (!is_admin()) {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Disable embeds
        wp_dequeue_script('wp-embed');

        // Remove RSD link
        remove_action('wp_head', 'rsd_link');

        // Remove Windows Live Writer Manifest Link
        remove_action('wp_head', 'wlwmanifest_link');

        // Remove Shortlink
        remove_action('wp_head', 'wp_shortlink_wp_head');

        // Remove WP Generator Version
        remove_action('wp_head', 'wp_generator');
    }
}
add_action('init', 'aleaulavage_v2_disable_wp_features');

// Disable XML-RPC
add_filter('xmlrpc_enabled', '__return_false');

// Heartbeat Control
function aleaulavage_v2_heartbeat_settings($settings)
{
    $settings['interval'] = 60; // 60 seconds
    return $settings;
}
add_filter('heartbeat_settings', 'aleaulavage_v2_heartbeat_settings');

// Limit post revisions
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 3);
}

// Increase autosave interval
if (!defined('AUTOSAVE_INTERVAL')) {
    define('AUTOSAVE_INTERVAL', 300); // 5 minutes
}

// Prevent Express Checkout scripts on classic cart to avoid noisy console errors
function aleaulavage_v2_disable_express_checkout_on_cart()
{
    if (!function_exists('is_cart') || !is_cart()) {
        return;
    }

    global $wp_scripts;
    if (!$wp_scripts instanceof WP_Scripts) {
        return;
    }

    foreach ((array) $wp_scripts->queue as $handle) {
        if (strpos($handle, 'express-checkout') !== false) {
            wp_dequeue_script($handle);
        }
    }
}
add_action('wp_print_scripts', 'aleaulavage_v2_disable_express_checkout_on_cart', 100);
