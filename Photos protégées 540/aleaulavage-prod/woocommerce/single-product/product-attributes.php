<?php
/**
 * Product attributes
 *
 * Used by list_attributes() in the products class.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-attributes.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! $product_attributes ) {
	return;
}
?>
<table class="woocommerce-product-attributes shop_attributes" style="border:1.5px solid #e3e5e8;background:#fff;border-radius:8px;width:100%;border-collapse:collapse;table-layout:auto;overflow:hidden;">
	<?php foreach ( $product_attributes as $product_attribute_key => $product_attribute ) : ?>
		<tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--<?php echo esc_attr( $product_attribute_key ); ?>">
			<th class="woocommerce-product-attributes-item__label" style="color:#0E2141;background:#fff;font-weight:600;padding:12px 18px;text-align:left;white-space:nowrap;vertical-align:top;border:1px solid #e3e5e8;width:1%;">
				<?php echo wp_kses_post( $product_attribute['label'] ); ?>
			</th>
			<td class="woocommerce-product-attributes-item__value" style="color:#222;background:#fff;padding:12px 18px;text-align:left;vertical-align:top;border:1px solid #e3e5e8;">
				<div style="margin:0;padding:0;line-height:1.4;"><?php echo wp_kses_post( $product_attribute['value'] ); ?></div>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
