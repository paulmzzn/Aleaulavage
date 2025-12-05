/**
 * Cart Offcanvas - Interactive functionality
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // Increase quantity
        $(document).on('click', '.increase-quantity', function (e) {
            e.preventDefault();
            const $button = $(this);
            const cartItemKey = $button.data('cart-item-key');
            const $qtyValue = $button.siblings('.cart-qty-value');

            if ($qtyValue.length) {
                const currentQty = parseInt($qtyValue.val());
                if (!isNaN(currentQty)) {
                    updateCartQuantity(cartItemKey, currentQty + 1);
                }
            }
        });

        // Decrease quantity
        $(document).on('click', '.decrease-quantity', function (e) {
            e.preventDefault();
            const $button = $(this);
            const cartItemKey = $button.data('cart-item-key');
            const $qtyValue = $button.siblings('.cart-qty-value');

            if ($qtyValue.length) {
                const currentQty = parseInt($qtyValue.val());
                if (!isNaN(currentQty) && currentQty > 1) {
                    updateCartQuantity(cartItemKey, currentQty - 1);
                }
            }
        });

        // Manual quantity input change
        $(document).on('change', '.cart-qty-value', function () {
            const $input = $(this);
            const cartItemKey = $input.data('cart-item-key');
            let newQty = parseInt($input.val());
            const minQty = parseInt($input.attr('min')) || 1;
            const maxQty = parseInt($input.attr('max')) || 999;

            // Validate quantity
            if (isNaN(newQty) || newQty < minQty) {
                newQty = minQty;
            }
            if (newQty > maxQty) {
                newQty = maxQty;
            }

            $input.val(newQty);
            updateCartQuantity(cartItemKey, newQty);
        });

        // Select input value on focus for easy editing
        $(document).on('focus', '.cart-qty-value', function () {
            $(this).select();
        });

        // Remove item
        $(document).on('click', '.remove-item', function () {
            const $button = $(this);
            const cartItemKey = $button.data('cart-item-key');
            removeCartItem(cartItemKey);
        });

        /**
         * Update cart item quantity via AJAX
         */
        function updateCartQuantity(cartItemKey, quantity) {
            const $cartItem = $(`.cart-item[data-cart-item-key="${cartItemKey}"]`);
            $cartItem.addClass('updating');

            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_cart_item_quantity',
                    cart_item_key: cartItemKey,
                    quantity: quantity,
                    nonce: aleaulavage_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        // Reload offcanvas content immediately
                        reloadOffcanvasContent();

                        // Update cart count in header badge
                        if (response.data && response.data.cart_count !== undefined) {
                            updateHeaderCartBadge(response.data.cart_count);
                        }
                    } else {
                        alert('Erreur lors de la mise à jour du panier');
                        $cartItem.removeClass('updating');
                    }
                },
                error: function () {
                    alert('Erreur lors de la mise à jour du panier');
                    $cartItem.removeClass('updating');
                }
            });
        }

        /**
         * Remove cart item via AJAX
         */
        function removeCartItem(cartItemKey) {
            const $cartItem = $(`.cart-item[data-cart-item-key="${cartItemKey}"]`);
            $cartItem.addClass('removing');

            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'remove_cart_item',
                    cart_item_key: cartItemKey,
                    nonce: aleaulavage_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        // Reload offcanvas content immediately
                        reloadOffcanvasContent();

                        // Update cart count in header badge
                        if (response.data && response.data.cart_count !== undefined) {
                            updateHeaderCartBadge(response.data.cart_count);
                        }
                    } else {
                        alert('Erreur lors de la suppression de l\'article');
                        $cartItem.removeClass('removing');
                    }
                },
                error: function () {
                    alert('Erreur lors de la suppression de l\'article');
                    $cartItem.removeClass('removing');
                }
            });
        }

        /**
         * Reload offcanvas content
         */
        function reloadOffcanvasContent() {
            const $offcanvas = $('#offcanvas-cart');
            const $offcanvasBody = $offcanvas.find('.offcanvas-body');

            // Only reload if offcanvas exists
            if ($offcanvas.length === 0) {
                console.log('Offcanvas not found');
                return;
            }

            console.log('Reloading offcanvas content...');

            // Add loading overlay to hide old values during reload
            $offcanvasBody.css('opacity', '0.5').css('pointer-events', 'none');

            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_cart_offcanvas_content',
                    nonce: aleaulavage_ajax.nonce
                },
                success: function (response) {
                    console.log('Offcanvas reload response:', response);
                    if (response.success && response.data) {
                        console.log('Cart count:', response.data.cart_count);
                        console.log('Debug:', response.data.debug);
                        if (response.data.html) {
                            $offcanvasBody.html(response.data.html);
                            console.log('Offcanvas content updated');

                            // Remove loading overlay
                            $offcanvasBody.css('opacity', '1').css('pointer-events', 'auto');
                            
                            // Trigger custom event to notify other parts of the page (e.g. product page quantity sync) without causing infinite loop
                            $(document.body).trigger('aleaulavage_cart_updated');
                        }
                    } else {
                        console.error('Failed to reload cart content:', response);
                        // Remove loading overlay even on error
                        $offcanvasBody.css('opacity', '1').css('pointer-events', 'auto');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error reloading cart:', error, xhr.responseText);
                    // Remove loading overlay on error
                    $offcanvasBody.css('opacity', '1').css('pointer-events', 'auto');
                }
            });
        }

        // Refresh offcanvas content when it's opened
        $('#offcanvas-cart').on('show.bs.offcanvas', function () {
            console.log('Offcanvas opened, reloading content');
            reloadOffcanvasContent();
        });

        // Update cart when fragments are refreshed
        $(document.body).on('wc_fragments_refreshed wc_fragment_refresh added_to_cart', function (e) {
            console.log('Cart event triggered:', e.type);
            // Always reload when cart changes
            reloadOffcanvasContent();
        });

        // Also listen for the custom WooCommerce add to cart event
        $(document.body).on('added_to_cart', function (event, fragments, cart_hash, $button) {
            console.log('Added to cart event');
            reloadOffcanvasContent();
        });

        /**
         * Helper to update header cart badge
         */
        function updateHeaderCartBadge(count) {
            var $cartContent = $('.cart-content');
            var $badge = $cartContent.find('.badge');
            
            // Update existing badge or create new one
            if (count > 0) {
                if ($badge.length) {
                    $badge.text(count);
                } else {
                    $cartContent.html('<span class="badge bg-primary rounded-pill position-absolute top-0 start-100 translate-middle" aria-hidden="true">' + count + '</span>');
                }
            } else {
                $badge.remove();
            }
            
            // Also update any other badges with standard class
            $('.cart-count-badge').text(count);
        }
    });

})(jQuery);
