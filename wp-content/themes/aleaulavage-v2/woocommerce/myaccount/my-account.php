<?php
/**
 * My Account page
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_navigation');
?>

<nav class="woocommerce-MyAccount-navigation">
    <ul>
        <?php foreach (wc_get_account_menu_items() as $endpoint => $label): ?>
            <li class="<?php echo wc_get_account_menu_item_classes($endpoint); ?>">
                <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>">
                    <?php echo esc_html($label); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<div class="woocommerce-MyAccount-content">
    <?php
    /**
     * My Account content.
     */
    do_action('woocommerce_account_content');
    ?>
</div>