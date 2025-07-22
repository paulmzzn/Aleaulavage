<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

$regular_price = (float) $product->get_regular_price();
$sale_price = (float) $product->get_sale_price();
$solde = !empty($sale_price) && $regular_price > 0 ? round((($regular_price - $sale_price) * 100) / $regular_price) : '';
$is_on_sale = $product->is_on_sale() && $sale_price > 0 && $sale_price < $regular_price;
?>
<p class="fw-normal <?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ); ?>">
    <?php if($is_on_sale) { ?><del class="me-2"><?php echo wc_price($regular_price); ?></del><?php } ?>
    <?php if($is_on_sale) { ?>
        <?php if(!empty($solde)) {?><span class="me-2 fs-6 py-1 px-3 text-white rounded mx-2" style="background-color: #5899E2;"><?php echo $solde; ?>%</span><?php } ?>
        <?php echo wc_price($sale_price); ?>
    <?php } else { ?>
        <?php echo wc_price($regular_price); ?>
    <?php } ?>
</p>
