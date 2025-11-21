<?php
/**
 * Template part for displaying the cart offcanvas
 */
?>
<div class="offcanvas offcanvas-end cart-offcanvas" tabindex="-1" id="offcanvas-cart">
    <!-- Header -->
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold mb-0">
            <i class="fa-solid fa-basket-shopping me-2"></i>
            Mon Panier
            <?php if (class_exists('WC') && WC()->cart): ?>
                <span class="badge bg-primary rounded-pill ms-2">
                    <?php echo WC()->cart->get_cart_contents_count(); ?>
                </span>
            <?php endif; ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>

    <!-- Body -->
    <div class="offcanvas-body p-0 d-flex flex-column">
        <?php if (class_exists('WC') && WC()->cart && WC()->cart->get_cart_contents_count() > 0): ?>

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
                    <p class="text-center mb-2 small text-muted">
                        Plus que <strong><?php echo $remaining_html; ?></strong> pour la livraison gratuite
                    </p>
                <?php else: ?>
                    <p class="text-center mb-2 small" style="color:#5899E2 !important;">
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
            <div class="cart-items flex-grow-1 overflow-auto p-3">
                <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
                    $_product = $cart_item['data'];
                    $product_id = $cart_item['product_id'];
                    $quantity = $cart_item['quantity'];

                    if (!$_product || !$_product->exists()) {
                        continue;
                    }
                    ?>

                    <div class="cart-item d-flex gap-3 mb-3 pb-3 border-bottom" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                        <!-- Product Image -->
                        <div class="cart-item-image flex-shrink-0">
                            <a href="<?php echo esc_url($_product->get_permalink($cart_item)); ?>">
                                <?php
                                $thumbnail = $_product->get_image('thumbnail', array(
                                    'class' => 'rounded',
                                    'style' => 'width: 80px; height: 80px; object-fit: cover;'
                                ));
                                echo $thumbnail;
                                ?>
                            </a>
                        </div>

                        <!-- Product Details -->
                        <div class="cart-item-details flex-grow-1">
                            <a href="<?php echo esc_url($_product->get_permalink($cart_item)); ?>"
                               class="product-name text-decoration-none text-dark fw-semibold d-block mb-1">
                                <?php echo wp_kses_post($_product->get_name()); ?>
                            </a>

                            <!-- Stock Status -->
                            <?php
                            $stock_status = $_product->get_stock_status();
                            $stock_quantity = $_product->get_stock_quantity();
                            $is_backorder = false;

                            if ($stock_status === 'outofstock' || $stock_status === 'onbackorder' ||
                                (!$_product->is_in_stock() && ($stock_quantity === 0 || $stock_quantity === null))) {
                                $is_backorder = true;
                            }

                            if ($is_backorder): ?>
                                <span class="badge bg-warning text-dark small mb-2">
                                    <i class="fa-solid fa-clock me-1"></i>
                                    Réapprovisionnement
                                </span>
                            <?php endif; ?>

                            <!-- Price -->
                            <div class="product-price text-dark fw-bold mb-2">
                                <?php $price_html = WC()->cart->get_product_price($_product);
                                      $price_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#1a1a1a !important; font-weight:700;">', $price_html);
                                      echo $price_html;
                                ?>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="quantity-controls d-flex align-items-center gap-2">
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary decrease-quantity"
                                        data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                        <?php echo ($quantity <= 1) ? 'disabled' : ''; ?>>
                                    <i class="fa-solid fa-minus"></i>
                                </button>

                                <input type="number"
                                       class="form-control form-control-sm text-center cart-quantity-input"
                                       value="<?php echo esc_attr($quantity); ?>"
                                       min="1"
                                       max="<?php echo $_product->get_max_purchase_quantity(); ?>"
                                       data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                       style="width: 60px;">

                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary increase-quantity"
                                        data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                    <i class="fa-solid fa-plus"></i>
                                </button>

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger ms-auto remove-item"
                                        data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                        title="Supprimer">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
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

                <!-- Total HT -->
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <span class="fs-6 fw-bold cart-quick-label text-dark">Total HT</span>
                    <?php $quick_html = wc_price( WC()->cart->get_cart_contents_total() );
                        $quick_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#1a1a1a !important; font-weight:700;">', $quick_html);
                    ?>
                    <span class="fs-5 fw-bold cart-quick-amount text-dark"><?php echo $quick_html; ?> <small class="text-dark">HT</small></span>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>"
                       class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-lock me-2"></i>
                        Passer commande
                    </a>
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>"
                       class="btn btn-outline-primary">
                        Voir le panier complet
                    </a>
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="offcanvas">
                        Continuer mes achats
                    </button>
                </div>
            </div>

        <?php else: ?>

            <!-- Empty Cart State -->
            <div class="empty-cart d-flex flex-column align-items-center justify-content-center p-5 text-center h-100">
                <i class="fa-solid fa-basket-shopping text-muted mb-4" style="font-size: 4rem; opacity: 0.3;"></i>
                <h5 class="mb-3">Votre panier est vide</h5>
                <p class="text-muted mb-4">Ajoutez des produits pour commencer vos achats</p>
                <a href="<?php echo esc_url(home_url('boutique/')); ?>"
                   class="btn btn-primary"
                   data-bs-dismiss="offcanvas">
                    <i class="fa-solid fa-store me-2"></i>
                    Découvrir la boutique
                </a>
            </div>

        <?php endif; ?>
    </div>
</div>
