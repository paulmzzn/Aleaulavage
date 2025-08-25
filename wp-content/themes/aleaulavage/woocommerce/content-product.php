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
  <div <?php wc_product_class('card h-100 d-flex', $product); ?> style="position: relative;">
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
    <?php 
    // Ajouter les badges promo directement dans le template
    if (function_exists('calculate_product_promotion')) {
        $promo_data = calculate_product_promotion($product);
        
        // Afficher les badges promo seulement si le produit est en stock
        if ($promo_data['has_promo'] && $product->is_in_stock()) {
            $discount_percent = $promo_data['discount_percent'];
            $is_quantity_based = $promo_data['is_quantity_based'];
            
            // Badge PROMO - agrandi
            echo '<div class="wc-block-components-product-sale-badge" style="position: absolute; top: 15px; right: 15px; z-index: 10; background: #fff; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <span>Promo</span>
                  </div>';
            
            // Badge pourcentage de réduction - réduit
            $bubble_size = $is_quantity_based ? 'width: 60px; height: 60px; font-size: 10px;' : 'width: 45px; height: 45px; font-size: 11px;';
            $bubble_text = $is_quantity_based ? "jusqu'à<br>-{$discount_percent}%" : "-{$discount_percent}%";
            
            echo '<div class="promo-bubble" style="position: absolute; top: 15px; left: 15px; background-color: #5899E2; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; z-index: 10; box-shadow: 0 3px 6px rgba(0,0,0,0.3); line-height: 1.1; text-align: center; ' . $bubble_size . '">' . $bubble_text . '</div>';
        }
    }
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
		  // Toujours utiliser le hook standard ici, on gérera le prix dans la section CTA
		  do_action('woocommerce_after_shop_loop_item_title');
		}
		?>
    </div>

	<div class="d-flex align-items-center justify-content-between px-3 mb-4 cta_container">
		<?php
		// Fermer le lien produit seulement
		woocommerce_template_loop_product_link_close();
		
		// Affichage personnalisé du prix avec gestion des promos
		if (function_exists('calculate_product_promotion')) {
		    $promo_data = calculate_product_promotion($product);
		    
		    // Afficher le prix promo seulement si le produit est en stock
		    if ($promo_data['has_promo'] && $product->is_in_stock()) {
		        $regular_price = floatval($product->get_regular_price());
		        $lowest_price = $promo_data['lowest_price'];
		        
		        // Formater les prix
		        $regular_price_formatted = number_format($regular_price, 2, ',', '') . '&nbsp;€';
		        $lowest_price_formatted = number_format($lowest_price, 2, ',', '') . '&nbsp;€';
		        
		        if ($promo_data['is_quantity_based']) {
		            echo '<div class="price order-1" style="display: flex; flex-direction: column; align-items: flex-start;">' .
		                 '<del style="color:#999;text-decoration:line-through;">' . $regular_price_formatted . '</del>' .
		                 '<span style="color:#5899E2;font-weight:bold;">À partir de ' . $lowest_price_formatted . '</span>' .
		                 '</div>';
		        } else {
		            echo '<div class="price order-1" style="display: flex; flex-direction: column; align-items: flex-start;">' .
		                 '<del style="color:#999;text-decoration:line-through;">' . $regular_price_formatted . '</del>' .
		                 '<span style="color:#5899E2;font-weight:bold;">' . $lowest_price_formatted . '</span>' .
		                 '</div>';
		        }
		    } else {
		        // Prix normal pour les produits sans promo ou en rupture de stock
		        echo '<div class="price order-1">' . $product->get_price_html() . '</div>';
		    }
		} else {
		    // Fallback : prix normal
		    echo '<div class="price order-1">' . $product->get_price_html() . '</div>';
		}
		?>
	</div>
  </div>
</div>