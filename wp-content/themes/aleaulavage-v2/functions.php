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

/**
 * Show all products on shop page (disable pagination)
 */
add_filter('loop_shop_per_page', 'aleaulavage_v2_loop_shop_per_page', 20);
function aleaulavage_v2_loop_shop_per_page($cols)
{
    return 12;
}
