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
    $cart_total = WC()->cart->get_cart_contents_total();

    // Get franco de port based on customer type
    $free_shipping_min = 550; // Default value
    if (class_exists('Aleaulavage_Customer_Types')) {
        $customer_type = Aleaulavage_Customer_Types::get_current_customer_type();
        $franco = Aleaulavage_Customer_Types::get_franco_de_port($customer_type);
        if ($franco !== null && $franco > 0) {
            $free_shipping_min = $franco;
        }
    }

    $remaining = max(0, $free_shipping_min - $cart_total);
    $percentage = min(100, ($cart_total / $free_shipping_min) * 100);
    ?>

    <?php if ($remaining > 0): ?>
        <?php $remaining_html = wc_price($remaining);
            $remaining_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#5899E2 !important;">', $remaining_html);
        ?>
        <p class="text-center mb-2 small" style="color: #666; line-height: 1.5;">
            Plus que <strong style="color:#5899E2;"><?php echo $remaining_html; ?> HT</strong> pour la livraison gratuite
        </p>
    <?php else: ?>
        <p class="text-center mb-2 small" style="color:#5899E2 !important;">
            <i class="fa-solid fa-check-circle me-1" style="color:#5899E2 !important;"></i>
            <strong>Livraison gratuite !</strong>
        </p>
    <?php endif; ?>

    <div class="progress" style="height: 6px;">
           <div class="progress-bar bg-primary" role="progressbar"
               style="width: <?php echo $percentage; ?>%; background: #5899E2 !important; border-color: #5899E2 !important;"
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

        <div class="cart-item-compact py-3 border-bottom position-relative" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
            <!-- Remove Button (Absolute Top Right of Item) -->
            <button type="button"
                    class="btn btn-link text-muted p-0 remove-item position-absolute top-0 end-0 mt-2"
                    data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                    title="Supprimer"
                    style="font-size: 0.85rem; line-height: 1; z-index: 10;">
                <i class="fa-solid fa-trash-can"></i>
            </button>

            <div class="d-flex align-items-start">
                <!-- Product Image -->
                <a href="<?php echo esc_url($_product->get_permalink($cart_item)); ?>" class="flex-shrink-0 me-3">
                    <?php
                    $thumbnail = $_product->get_image('thumbnail', array(
                        'class' => 'rounded',
                        'style' => 'width: 60px; height: 60px; object-fit: cover; border: 1px solid #f0f0f0;'
                    ));
                    echo $thumbnail;
                    ?>
                </a>

                <!-- Product Info -->
                <div class="flex-grow-1" style="min-width: 0;">
                    <!-- Row 1: Name -->
                    <div class="mb-2 pe-4">
                        <a href="<?php echo esc_url($_product->get_permalink($cart_item)); ?>"
                           class="product-name text-decoration-none fw-semibold text-dark lh-sm d-block"
                           style="font-size: 0.9rem;">
                            <?php echo wp_kses_post($_product->get_name()); ?>
                        </a>
                    </div>

                    <!-- Row 2: Qty + Price (In Flow, Just Below Name) -->
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <!-- Quantity Controls (Pill Style) -->
                        <div class="cart-qty-selector d-flex align-items-center border rounded-pill bg-white" 
                             style="height: 32px; padding: 0 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                            <button type="button" class="cart-qty-btn decrease-quantity btn btn-sm btn-link text-dark p-0 text-decoration-none rounded-circle" 
                                    data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" 
                                    <?php echo ($quantity <= 1) ? 'disabled' : ''; ?>
                                    style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-minus" style="font-size: 0.6rem;"></i>
                            </button>
                            <input type="number"
                                   class="cart-qty-value border-0 text-center p-0 fw-bold bg-transparent"
                                   value="<?php echo esc_attr($quantity); ?>"
                                   min="1"
                                   max="999"
                                   data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                   style="width: 30px; height: 24px; font-size: 0.85rem; -moz-appearance: textfield;">
                            <button type="button" class="cart-qty-btn increase-quantity btn btn-sm btn-link text-dark p-0 text-decoration-none rounded-circle" 
                                    data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                    style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-plus" style="font-size: 0.6rem;"></i>
                            </button>
                        </div>

                        <!-- Price -->
                        <div class="fw-bold text-end" style="font-size: 0.95rem; color: #2c3e50;">
                            <?php $subtotal_html = WC()->cart->get_product_subtotal($_product, $quantity);
                                  $subtotal_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#2c3e50 !important; font-weight:700;">', $subtotal_html);
                                  echo $subtotal_html;
                            ?>
                            <small style="font-size: 0.7rem; color: #6c757d; font-weight: normal;">HT</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endforeach; ?>
</div>

<!-- Footer with Total and Actions -->
<div class="cart-footer bg-white px-3 pb-3">
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

    <!-- Total HT -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-top" style="padding: 1rem; background: #f8f9fa; margin: 0 -1rem 1rem -1rem;">
        <span class="fw-bold" style="font-size: 1rem; color: #2c3e50;">Total HT</span>
        <?php $quick_html = wc_price( WC()->cart->get_cart_contents_total() );
            $quick_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#2c3e50 !important; font-weight:700;">', $quick_html);
        ?>
        <span class="fw-bold" style="font-size: 1.25rem; color: #2c3e50;"><?php echo $quick_html; ?> <small style="font-size: 0.85rem; color: #666;">HT</small></span>
    </div>

    <!-- Action Buttons -->
    <div class="d-grid gap-2">
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>"
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
