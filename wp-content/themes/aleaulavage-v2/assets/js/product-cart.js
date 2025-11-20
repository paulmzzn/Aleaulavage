/**
 * Product Add to Cart - Custom behavior
 * Shows quantity in cart and allows adding more
 */

(function ($) {
    'use strict';

    // Store product quantities in cart
    let productQuantities = {};

    // Initialize on document ready
    $(document).ready(function () {

        // Load initial quantities from cart
        loadCartQuantities();

        // Handle add to cart button clicks
        $(document).on('click', '.products-glass-grid .ajax_add_to_cart, .products-glass-grid .button.product_type_simple, .products-glass-grid a.button, .woocommerce ul.products .ajax_add_to_cart, .woocommerce ul.products .button.product_type_simple, .woocommerce ul.products a.button', function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const $button = $(this);
            const productId = $button.data('product_id');

            // Disable button during AJAX
            $button.prop('disabled', true).addClass('loading');

            // Add to cart via AJAX
            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'woocommerce_ajax_add_to_cart',
                    product_id: productId,
                    quantity: 1,
                    nonce: aleaulavage_ajax.nonce
                },
                success: function (response) {
                    if (response.error) {
                        console.error('Error adding to cart:', response);
                        $button.prop('disabled', false).removeClass('loading');
                        return;
                    }

                    // Update product quantity
                    if (!productQuantities[productId]) {
                        productQuantities[productId] = 0;
                    }
                    productQuantities[productId]++;

                    // Update button text
                    updateButtonText($button, productId);

                    // Update cart count in header
                    $(document.body).trigger('wc_fragment_refresh');

                    // Force update cart footer visibility
                    setTimeout(function () {
                        $('.cart-footer').show();
                    }, 500);

                    // Re-enable button
                    $button.prop('disabled', false).removeClass('loading').addClass('added');
                },
                error: function () {
                    console.error('AJAX error');
                    $button.prop('disabled', false).removeClass('loading');
                }
            });

            return false;
        });

        // Load cart quantities from server
        function loadCartQuantities() {
            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_cart_quantities',
                    nonce: aleaulavage_ajax.nonce
                },
                success: function (response) {
                    if (response.success && response.data) {
                        productQuantities = response.data;

                        // Update all buttons with quantities
                        $('.products-glass-grid .button, .woocommerce ul.products .button').each(function () {
                            const $btn = $(this);
                            const productId = $btn.data('product_id');
                            if (productId && productQuantities[productId]) {
                                updateButtonText($btn, productId);
                                $btn.addClass('added');
                            }
                        });
                    }
                }
            });
        }

        // Update button text based on quantity
        function updateButtonText($button, productId) {
            const quantity = productQuantities[productId] || 0;
            if (quantity > 0) {
                $button.text(quantity + ' dans le panier');
            } else {
                $button.text('Ajouter au panier');
            }
        }

        // Listen for cart updates to refresh quantities
        $(document.body).on('updated_cart_totals wc_fragments_refreshed', function () {
            // Reload quantities when cart is updated externally
            loadCartQuantities();
        });

        // Refresh cart fragments when offcanvas is opened
        $('#offcanvas-cart').on('show.bs.offcanvas', function () {
            // Trigger WooCommerce fragment refresh to update cart footer
            $(document.body).trigger('wc_fragment_refresh');
        });
    });

})(jQuery);
