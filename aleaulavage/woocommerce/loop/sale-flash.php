<?php

/**
 * Product loop sale flash
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/sale-flash.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

global $post, $product;

?>
<?php 
// Utiliser la logique promo personnalisée
if (function_exists('calculate_product_promotion')) {
    $promo_data = calculate_product_promotion($product);
    
    if ($promo_data['has_promo']) {
        $discount_percent = $promo_data['discount_percent'];
        $is_quantity_based = $promo_data['is_quantity_based'];
        
        // Badge PROMO
        echo '<span class="wc-block-components-product-sale-badge alignright wc-block-components-product-sale-badge--align-right" style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                <span class="wc-block-components-product-sale-badge__text" aria-hidden="true">Promo</span>
                <span class="screen-reader-text">Produit en promotion</span>
              </span>';
        
        // Badge pourcentage de réduction
        $bubble_class = $is_quantity_based ? 'promo-bubble-quantity' : 'promo-bubble';
        $bubble_text = $is_quantity_based ? "jusqu'à<br>-{$discount_percent}%" : "-{$discount_percent}%";
        $bubble_size = $is_quantity_based ? 'width: 70px; height: 70px; font-size: 11.39px;' : 'width: 50px; height: 50px; font-size: 12px;';
        
        echo '<span class="' . $bubble_class . '" style="position: absolute; top: 15px; left: 15px; background-color: #5899E2; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; z-index: 10; box-shadow: 0 3px 6px rgba(0,0,0,0.3); line-height: 1.1; text-align: center; ' . $bubble_size . '">' . $bubble_text . '</span>';
    }
} elseif ($product->is_on_sale()) {
    // Fallback vers le badge standard si la fonction n'existe pas
    echo apply_filters('woocommerce_sale_flash', '<span class="badge bg-danger sale">%</span>', $post, $product);
}
?>

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
