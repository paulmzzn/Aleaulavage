<?php
/**
 * The template for displaying product content within loops
 *
 * Custom template for Aleaulavage V2
 * Modern & Optimized Design
 *
 * @package Aleaulavage_V2
 * @version 9.4.0
 */

defined('ABSPATH') || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if (!is_a($product, WC_Product::class) || !$product->is_visible()) {
    return;
}

// Get stock status
$stock_status = $product->get_stock_status();
$is_in_stock = $product->is_in_stock();
$is_on_backorder = $product->is_on_backorder();

// Add custom classes
$classes = array();
if (!$is_in_stock) {
    $classes[] = 'outofstock';
}
if ($is_on_backorder) {
    $classes[] = 'on-backorder';
}
?>
<li <?php wc_product_class($classes, $product); ?>>

    <?php
    /**
     * Wishlist Button
     */
    ?>
    <button class="aleaulavage-wishlist-btn" data-product-id="<?php echo esc_attr($product->get_id()); ?>"
        aria-label="Ajouter aux favoris">
        <i class="fa-regular fa-heart"></i>
    </button>

    <a href="<?php echo esc_url($product->get_permalink()); ?>"
        class="woocommerce-LoopProduct-link woocommerce-loop-product__link">

        <?php
        /**
         * Sale Flash Badge
         */
        if ($product->is_on_sale()) {
            echo '<span class="onsale">Promo</span>';
        }
        ?>

        <?php
        /**
         * Stock Status Badge (Out of Stock / Backorder)
         */
        if (!$is_in_stock) {
            echo '<span class="stock-badge out-of-stock"><i class="fa-solid fa-circle-xmark me-1"></i>Rupture</span>';
        } elseif ($is_on_backorder) {
            echo '<span class="stock-badge backorder"><i class="fa-solid fa-clock me-1"></i>Réappro.</span>';
        }
        ?>

        <?php
        /**
         * Product Image
         */
        echo woocommerce_get_product_thumbnail('woocommerce_thumbnail');
        ?>

        <?php
        /**
         * Product Title
         */
        ?>
        <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product->get_name()); ?></h2>

        <?php
        /**
         * Product Price
         */
        ?>
        <div class="price"><?php echo $product->get_price_html(); ?></div>

    </a>

    <?php
    /**
     * Add to Cart Button
     */
    if ($is_in_stock || $is_on_backorder) {
        woocommerce_template_loop_add_to_cart();
    } else {
        echo '<button class="button disabled" disabled><i class="fa-solid fa-ban me-2"></i>Indisponible</button>';
    }
    ?>

</li>