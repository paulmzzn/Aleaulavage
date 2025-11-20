jQuery(document).ready(function ($) {
    // Check if params exist
    if (typeof aleaulavage_wishlist_params === 'undefined') {
        return;
    }

    // Helper: Toggle Heart Icon
    function toggleHeartIcon(btn, isAdded) {
        const icon = btn.find('i');
        if (isAdded) {
            btn.addClass('active');
            icon.removeClass('fa-regular').addClass('fa-solid');
        } else {
            btn.removeClass('active');
            icon.removeClass('fa-solid').addClass('fa-regular');
        }
    }

    // 1. Handle "Add to Wishlist" Click (Global)
    $(document).on('click', '.aleaulavage-wishlist-btn', function (e) {
        e.preventDefault();
        const btn = $(this);
        const productId = btn.data('product-id');

        // If not logged in, show modal
        if (!aleaulavage_wishlist_params.is_logged_in) {
            $('#login-modal').css('display', 'flex');
            return;
        }

        // Prevent double clicks
        if (btn.hasClass('loading')) return;
        btn.addClass('loading');

        const isActive = btn.hasClass('active');
        const action = isActive ? 'aleaulavage_remove_from_wishlist' : 'aleaulavage_add_to_wishlist';

        $.ajax({
            url: aleaulavage_wishlist_params.ajax_url,
            type: 'POST',
            data: {
                action: action,
                product_id: productId,
                nonce: aleaulavage_wishlist_params.nonce
            },
            success: function (response) {
                btn.removeClass('loading');
                if (response.success) {
                    // Update Counter
                    const newCount = response.data.count;
                    const wishlistLink = $('a[href*="favoris"]');
                    let countBadge = wishlistLink.find('.wishlist-count');

                    if (newCount > 0) {
                        if (countBadge.length) {
                            countBadge.text(newCount);
                        } else {
                            wishlistLink.append('<span class="wishlist-count position-absolute badge rounded-pill" aria-hidden="true">' + newCount + '</span>');
                        }
                        // Update title attribute text if present
                        const currentLabel = wishlistLink.attr('aria-label');
                        if (currentLabel) {
                            wishlistLink.attr('aria-label', 'Accéder à mes favoris (' + newCount + ' articles)');
                        }
                    } else {
                        countBadge.remove();
                        const currentLabel = wishlistLink.attr('aria-label');
                        if (currentLabel) {
                            wishlistLink.attr('aria-label', 'Accéder à mes favoris');
                        }
                    }

                    if (response.data.action === 'added') {
                        toggleHeartIcon(btn, true);
                    } else if (response.data.action === 'removed') {
                        toggleHeartIcon(btn, false);

                        // If we are on the wishlist page, remove the item from the grid
                        if ($('#aleaulavage-wishlist-container').length) {
                            const productCard = btn.closest('.product');
                            productCard.fadeOut(300, function () {
                                $(this).remove();
                                if ($('.wishlist-grid .product').length === 0) {
                                    location.reload(); // Reload to show empty state
                                }
                            });
                        }
                    }
                } else {
                    alert(response.data.message || 'Une erreur est survenue');
                }
            },
            error: function () {
                btn.removeClass('loading');
                alert('Erreur de connexion');
            }
        });
    });

    // 2. Check Wishlist Status on Page Load (for products in loop)
    if (aleaulavage_wishlist_params.is_logged_in) {
        $('.aleaulavage-wishlist-btn').each(function () {
            const btn = $(this);
            const productId = btn.data('product-id');

            // Optimization: could batch this, but for now single requests or relying on server-side rendering if possible
            // Actually, for better performance, we should rely on a localized array of IDs if possible, 
            // or check individually. Let's check individually for now as it's simpler to implement without modifying global localized data.

            // Only check if not already marked (server-side rendering might handle this in future)
            if (!btn.hasClass('checked-status')) {
                $.ajax({
                    url: aleaulavage_wishlist_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aleaulavage_check_wishlist_status',
                        product_id: productId,
                        nonce: aleaulavage_wishlist_params.nonce
                    },
                    success: function (response) {
                        btn.addClass('checked-status');
                        if (response.success && response.data.in_wishlist) {
                            toggleHeartIcon(btn, true);
                        }
                    }
                });
            }
        });
    }

    // 3. Login Modal Logic
    // Close modal on outside click
    $(window).on('click', function (e) {
        if ($(e.target).is('#login-modal')) {
            $('#login-modal').css('display', 'none');
        }
    });

    // Handle Login Form Submit
    $('#wishlist-login-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('#submit-login');
        const errorDiv = form.find('#login-error');

        submitBtn.prop('disabled', true).text('Connexion...');
        errorDiv.hide();

        $.ajax({
            url: aleaulavage_wishlist_params.ajax_url,
            type: 'POST',
            data: {
                action: 'aleaulavage_ajax_login',
                username: form.find('#login-username').val(),
                password: form.find('#login-password').val(),
                security: aleaulavage_wishlist_params.nonce
            },
            success: function (response) {
                if (response.success) {
                    submitBtn.text('Connecté !').css('background-color', '#27ae60');
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    submitBtn.prop('disabled', false).text('Se connecter');
                    errorDiv.text(response.data.message).show();
                }
            },
            error: function () {
                submitBtn.prop('disabled', false).text('Se connecter');
                errorDiv.text('Erreur de connexion.').show();
            }
        });
    });
});
