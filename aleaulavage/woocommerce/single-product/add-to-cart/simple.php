<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

// Vérifier le stock disponible et la quantité déjà dans le panier
$stock_quantity = $product->get_stock_quantity();
$is_manage_stock = $product->managing_stock();
$cart_qty = 0;

// Calculer la quantité déjà dans le panier
if (WC()->cart) {
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $product->get_id()) {
            $cart_qty += $cart_item['quantity'];
        }
    }
}

// Déterminer s'il y a du stock disponible
$available_stock = null;
$is_out_of_stock = false;

if ($is_manage_stock && $stock_quantity !== null) {
    $available_stock = $stock_quantity - $cart_qty;
    $is_out_of_stock = $available_stock <= 0;
} else {
    $is_out_of_stock = !$product->is_in_stock();
}

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		// Paramètres pour le champ quantité
		$quantity_args = array(
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(),
		);

		// Ajuster max_value si on gère le stock
		if ($is_manage_stock && $available_stock !== null && $available_stock > 0) {
			$quantity_args['max_value'] = min($quantity_args['max_value'], $available_stock);
		}

		// Classe et attributs pour les éléments grisés
		$disabled_class = $is_out_of_stock ? ' disabled-out-of-stock' : '';
		$disabled_attr = $is_out_of_stock ? ' disabled' : '';
		$tooltip_attr = $is_out_of_stock ? ' title="Plus de stock disponible" data-toggle="tooltip"' : '';
		?>

		<div class="quantity-wrapper purchase-qty<?php echo $disabled_class; ?>"<?php echo $tooltip_attr; ?>>
			<?php woocommerce_quantity_input( $quantity_args ); ?>
		</div>

		<?php do_action( 'woocommerce_after_add_to_cart_quantity' ); ?>

		<button type="submit" 
				name="add-to-cart" 
				value="<?php echo esc_attr( $product->get_id() ); ?><?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" 
				class="single_add_to_cart_button alt btn bg-secondary mt-3<?php echo $disabled_class; ?>"
				<?php echo $disabled_attr . $tooltip_attr; ?>>
			<i class="fa-solid fa-basket-shopping me-1"></i><?php echo esc_html( $product->single_add_to_cart_text() ); ?>
		</button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
