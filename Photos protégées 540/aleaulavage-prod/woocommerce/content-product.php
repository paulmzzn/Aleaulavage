<?php

/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

// Ensure visibility.
if (empty($product) || !$product->is_visible()) {
  return;
}
?>

<div class="col-md-6 col-lg-4 col-xxl-3 mb-4 card_container">
  <div <?php wc_product_class('card h-100 d-flex', $product); ?>>
    <?php
    /**
     * Hook: woocommerce_before_shop_loop_item.
     *
     * @hooked woocommerce_template_loop_product_link_open - 10
     */
    do_action('woocommerce_before_shop_loop_item');

    /**
     * Hook: woocommerce_before_shop_loop_item_title.
     *
     * @hooked woocommerce_show_product_loop_sale_flash - 10
     * @hooked woocommerce_template_loop_product_thumbnail - 10
     */
    do_action('woocommerce_before_shop_loop_item_title');
    ?>
    <?php if ( !$product->is_in_stock() ) : ?>
      <!-- Indicateur rupture de stock -->
      <div class="out-of-stock-indicator"></div>
    <?php else: ?>
      <!-- Bouton d'ajout au panier flottant -->
      <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" 
         data-quantity="1" 
         class="add-to-cart-button add_to_cart_button ajax_add_to_cart" 
         data-product_id="<?php echo esc_attr($product->get_id()); ?>" 
         data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" 
         aria-label="<?php echo esc_attr__('Ajouter au panier', 'woocommerce'); ?>">
        <i class="fa-solid fa-cart-plus"></i>
      </a>
    <?php endif; ?>
    
    <div class="card-body d-flex flex-column">
		<a href="<?php the_permalink() ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
		<?php
		/**
		 * Hook: woocommerce_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_product_title - 10
		 */
      	do_action('woocommerce_shop_loop_item_title');
		?>
		</a>
		<?php
		if ( !$product->is_in_stock() ) {
		  echo '<div class="out-of-stock-notice">Rupture de stock</div>';
		} else {
		  do_action('woocommerce_after_shop_loop_item_title');
		}
		?>
    </div>

	<div class="d-flex align-items-center justify-content-between px-3 mb-4 cta_container">
		<?php
		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_close - 5
		 * @hooked woocommerce_template_loop_add_to_cart - 10
		 */
		do_action('woocommerce_after_shop_loop_item');
		?>
	</div>
  </div>
</div>