<?php
/**
 * The template for displaying product search form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/product-searchform.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form role="search" method="get" class="woocommerce-product-search w-100" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <div class="input-group" style="height:48px; border:1.5px solid #444; border-radius:15px; overflow:hidden; background:#fff;">
        <span class="input-group-text bg-white border-0 pe-0" style="height:100%; border-radius:0; display:flex; align-items:center; color:#444; background:#fff;"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input type="search" id="woocommerce-product-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>" class="form-control border-0 ps-2" placeholder="Rechercher" value="<?php echo get_search_query(); ?>" name="s" style="box-shadow:none; height:100%; border-radius:0 24px 24px 0; background:#fff; color:#222;">
        <input type="hidden" name="post_type" value="product" />
    </div>
</form>
