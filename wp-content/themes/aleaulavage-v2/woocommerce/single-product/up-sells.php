<?php
/**
 * Single Product Up-Sells - Horizontal Scroll Version
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.0.0
 */

if (! defined('ABSPATH')) {
    exit;
}

if ($upsells) : ?>

    <section class="up-sells upsells products product-slider-section">
        <h2 class="modern-title"><?php esc_html_e('Produits similaires...', 'aleaulavage'); ?></h2>

        <ul class="products products-slider">
            <?php foreach ($upsells as $upsell) : ?>
                <?php
                $post_object = get_post($upsell->get_id());
                setup_postdata($GLOBALS['post'] =& $post_object); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
                wc_get_template_part('content', 'product');
                ?>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php
endif;

wp_reset_postdata();
