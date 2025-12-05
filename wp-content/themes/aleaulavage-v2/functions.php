<?php
/**
 * Aleaulavage V2 functions and definitions
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Include required files
 */
require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/scripts.php';
require get_template_directory() . '/inc/widgets.php';
require get_template_directory() . '/inc/woocommerce.php';
require get_template_directory() . '/inc/performance.php';
require get_template_directory() . '/inc/shortcodes.php';
require get_template_directory() . '/inc/brands.php';

/**
 * Show all products on shop page (disable pagination)
 */
add_filter('loop_shop_per_page', 'aleaulavage_v2_loop_shop_per_page', 20);
function aleaulavage_v2_loop_shop_per_page($cols)
{
    return 12;
}

/**
 * Add product images to order items in order details table (front-end only)
 */
add_filter('woocommerce_order_item_name', 'aleaulavage_add_product_image_to_order_items', 10, 2);
function aleaulavage_add_product_image_to_order_items($item_name, $item)
{
    // Only apply on front-end, not in admin
    if (is_admin()) {
        return $item_name;
    }

    $product = $item->get_product();
    if ($product) {
        $thumbnail = $product->get_image(array(60, 60));
        $item_name = $thumbnail . $item_name;
    }
    return $item_name;
}

