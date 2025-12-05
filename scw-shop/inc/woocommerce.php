<?php
/**
 * WooCommerce Compatibility
 *
 * @package SCW_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Remove default WooCommerce wrappers
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

/**
 * Add custom WooCommerce wrappers
 */
function scw_shop_woocommerce_wrapper_start() {
    echo '<main id="main" class="site-main woocommerce-main"><div class="container">';
}
add_action( 'woocommerce_before_main_content', 'scw_shop_woocommerce_wrapper_start', 10 );

function scw_shop_woocommerce_wrapper_end() {
    echo '</div></main>';
}
add_action( 'woocommerce_after_main_content', 'scw_shop_woocommerce_wrapper_end', 10 );

/**
 * Customize WooCommerce based on user role
 */
function scw_shop_woocommerce_price_display( $price, $product ) {
    $user_role = scw_shop_get_user_role();

    // Hide prices for guests
    if ( $user_role === 'guest' ) {
        return '<span class="price-blurred">' . __( 'Prix sur demande', 'scw-shop' ) . '</span>';
    }

    return $price;
}
add_filter( 'woocommerce_get_price_html', 'scw_shop_woocommerce_price_display', 10, 2 );

/**
 * Modify add to cart button for guests
 */
function scw_shop_woocommerce_add_to_cart_text( $text, $product ) {
    $user_role = scw_shop_get_user_role();

    if ( $user_role === 'guest' ) {
        return __( 'Connectez-vous pour commander', 'scw-shop' );
    }

    return $text;
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'scw_shop_woocommerce_add_to_cart_text', 10, 2 );
add_filter( 'woocommerce_product_add_to_cart_text', 'scw_shop_woocommerce_add_to_cart_text', 10, 2 );

/**
 * Disable add to cart for guests
 */
function scw_shop_woocommerce_disable_cart_for_guests() {
    $user_role = scw_shop_get_user_role();

    if ( $user_role === 'guest' ) {
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
    }
}
add_action( 'wp', 'scw_shop_woocommerce_disable_cart_for_guests' );

/**
 * Customize product columns
 */
function scw_shop_woocommerce_loop_columns() {
    return 3;
}
add_filter( 'loop_shop_columns', 'scw_shop_woocommerce_loop_columns' );

/**
 * Customize products per page
 */
function scw_shop_woocommerce_products_per_page() {
    return 12;
}
add_filter( 'loop_shop_per_page', 'scw_shop_woocommerce_products_per_page' );

/**
 * Disable default WooCommerce my-account content
 */
function scw_shop_disable_default_account_content() {
    // Remove default account navigation
    remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' );

    // Remove default account content
    remove_action( 'woocommerce_account_content', 'woocommerce_account_content' );
}
add_action( 'init', 'scw_shop_disable_default_account_content' );

/**
 * Remove WooCommerce default account menu items
 */
function scw_shop_remove_my_account_links( $items ) {
    // Remove all default menu items for custom interface
    return array();
}
add_filter( 'woocommerce_account_menu_items', 'scw_shop_remove_my_account_links', 999 );

/**
 * Replace WooCommerce my-account shortcode with custom template
 */
function scw_shop_custom_my_account_shortcode() {
    ob_start();

    $user_role = scw_shop_get_user_role();
    $user_mode = scw_shop_get_user_mode();

    echo '<div class="scw-account-page">';

    if ( $user_role === 'guest' ) {
        get_template_part( 'template-parts/account/login-form' );
    } elseif ( $user_role === 'reseller' ) {
        get_template_part( 'template-parts/account/reseller-dashboard' );
    } elseif ( $user_role === 'client' ) {
        get_template_part( 'template-parts/account/client-account' );
    }

    echo '</div>';

    return ob_get_clean();
}

/**
 * Override the default WooCommerce my account shortcode
 */
function scw_shop_override_woocommerce_shortcode() {
    remove_shortcode( 'woocommerce_my_account' );
    add_shortcode( 'woocommerce_my_account', 'scw_shop_custom_my_account_shortcode' );
}
add_action( 'init', 'scw_shop_override_woocommerce_shortcode', 20 );

/**
 * Filter my-account page content to only show our custom template
 */
function scw_shop_filter_my_account_content( $content ) {
    // Check if we're on the my-account page
    if ( is_page() && has_shortcode( $content, 'woocommerce_my_account' ) ) {
        // Replace all content with just the shortcode
        return do_shortcode( '[woocommerce_my_account]' );
    }
    return $content;
}
add_filter( 'the_content', 'scw_shop_filter_my_account_content', 999 );

/**
 * Remove all WooCommerce checkout hooks to use our custom template
 */
function scw_shop_remove_checkout_hooks() {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        // Remove coupon form
        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
        // Remove login form
        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
    }
}
add_action( 'wp', 'scw_shop_remove_checkout_hooks' );

/**
 * Use custom template for WooCommerce checkout page
 */
function scw_shop_checkout_template( $template ) {
    if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url() ) {
        $custom_template = get_template_directory() . '/template-checkout.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'scw_shop_checkout_template', 99 );
