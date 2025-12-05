<?php
/**
 * Related Products - Horizontal Scroll Version
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.9.0
 */

if (! defined('ABSPATH')) {
    exit;
}

if ($related_products) : ?>

    <section class="related products product-slider-section">
        <h2 class="modern-title"><?php esc_html_e('Vous aimerez peut-être aussi...', 'aleaulavage'); ?></h2>

        <ul class="products products-slider">
            <?php foreach ($related_products as $related_product) : ?>
                <?php
                $post_object = get_post($related_product->get_id());
                setup_postdata($GLOBALS['post'] =& $post_object); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
                wc_get_template_part('content', 'product');
                ?>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php
endif;

wp_reset_postdata();
