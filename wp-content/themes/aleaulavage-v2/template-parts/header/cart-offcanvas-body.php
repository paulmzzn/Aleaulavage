<?php
/**
 * Cart Offcanvas Body Content (used for AJAX reload)
 */

// This file should only be included when cart has items
// The parent function already checks this
?>

<!-- Free Shipping Progress -->
<div class="shipping-progress p-3 bg-light">
    <?php
    $cart_total = WC()->cart->get_subtotal();
    $free_shipping_min = 550;
    $remaining = max(0, $free_shipping_min - $cart_total);
    $percentage = min(100, ($cart_total / $free_shipping_min) * 100);
    ?>

    <?php if ($remaining > 0): ?>
        <p class="text-center mb-2 small text-muted">
            Plus que <strong class="text-primary"><?php echo wc_price($remaining); ?></strong> pour la livraison gratuite
        </p>
    <?php else: ?>
        <p class="text-center mb-2 small text-success">
            <i class="fa-solid fa-check-circle me-1"></i>
            <strong>Livraison gratuite !</strong>
        </p>
    <?php endif; ?>

    <div class="progress" style="height: 6px;">
        <div class="progress-bar bg-primary" role="progressbar"
             style="width: <?php echo $percentage; ?>%;"
             aria-valuenow="<?php echo $percentage; ?>"
             aria-valuemin="0"
             aria-valuemax="100">
        </div>
    </div>
</div>

<!-- Cart Items -->
<div class="cart-items flex-grow-1 overflow-auto px-3 py-2">
    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
        $_product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];

        if (!$_product || !$_product->exists()) {
            continue;
        }

        // Stock status
        $stock_status = $_product->get_stock_status();
        $stock_quantity = $_product->get_stock_quantity();
        $is_backorder = ($stock_status === 'outofstock' || $stock_status === 'onbackorder' ||
                        (!$_product->is_in_stock() && ($stock_quantity === 0 || $stock_quantity === null)));
        ?>

        <div class="cart-item-compact mb-2" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
            <div class="d-flex gap-2 align-items-start p-2 rounded position-relative">
                <!-- Product Image -->
                <a href="<?php echo esc_url($_product->get_permalink($cart_item)); ?>" class="flex-shrink-0">
                    <?php
                    $thumbnail = $_product->get_image('thumbnail', array(
                        'class' => 'rounded',
                        'style' => 'width: 60px; height: 60px; object-fit: cover;'
                    ));
                    echo $thumbnail;
                    ?>
                </a>

                <!-- Product Info -->
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="d-flex align-items-center gap-1 flex-grow-1" style="min-width: 0;">
                            <a href="<?php echo esc_url($_product->get_permalink($cart_item)); ?>"
                               class="product-name text-decoration-none text-dark small fw-semibold text-truncate"
                               style="max-width: 160px;">
                                <?php echo wp_kses_post($_product->get_name()); ?>
                            </a>
                            <?php if ($is_backorder): ?>
                                <i class="fa-solid fa-clock text-warning" style="font-size: 0.75rem;" title="En réapprovisionnement"></i>
                            <?php endif; ?>
                        </div>
                        <button type="button"
                                class="btn btn-link text-muted p-0 remove-item"
                                data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                title="Supprimer"
                                style="font-size: 0.9rem; line-height: 1;">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Quantity Controls -->
                        <div class="cart-qty-selector d-flex align-items-center" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                            <button type="button" class="cart-qty-btn decrease-quantity" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" <?php echo ($quantity <= 1) ? 'disabled' : ''; ?>>
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number"
                                   class="cart-qty-value"
                                   value="<?php echo esc_attr($quantity); ?>"
                                   min="1"
                                   max="999"
                                   data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                            <button type="button" class="cart-qty-btn increase-quantity" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>

                        <!-- Price -->
                        <div class="text-primary fw-bold" style="font-size: 0.95rem;">
                            <?php echo WC()->cart->get_product_subtotal($_product, $quantity); ?>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-2" style="opacity: 0.1;">
        </div>

    <?php endforeach; ?>
</div>

<!-- Footer with Total and Actions -->
<div class="cart-footer border-top p-3 bg-white">
    <!-- Backorder Global Notice -->
    <?php
    $has_backorder_items = false;
    foreach (WC()->cart->get_cart() as $cart_item) {
        $_product = $cart_item['data'];
        $stock_status = $_product->get_stock_status();

        if ($stock_status === 'outofstock' || $stock_status === 'onbackorder' || !$_product->is_in_stock()) {
            $has_backorder_items = true;
            break;
        }
    }

    if ($has_backorder_items): ?>
        <div class="alert alert-warning d-flex align-items-start gap-2 py-2 px-3 mb-3" role="alert">
            <i class="fa-solid fa-clock mt-1"></i>
            <small>Certains articles sont en réapprovisionnement. Délais de livraison susceptibles d'être allongés.</small>
        </div>
    <?php endif; ?>

    <!-- Total -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
        <span class="fs-5 fw-bold">Total</span>
        <span class="fs-4 fw-bold text-primary"><?php echo WC()->cart->get_total(); ?></span>
    </div>

    <!-- Action Buttons -->
    <div class="d-grid gap-2">
        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>"
           class="btn btn-primary btn-lg">
            Commander
        </a>
        <button type="button"
                class="btn btn-outline-secondary"
                data-bs-dismiss="offcanvas">
            Continuer mes achats
        </button>
    </div>
</div>
