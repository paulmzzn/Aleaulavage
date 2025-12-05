/**
 * Product Card JavaScript
 * Gère les interactions des cartes produit modernes
 *
 * @package SCW_Shop
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {

        // ═══════════════════════════════════════════════════════════
        // QUANTITY SELECTORS (only for product cards, NOT cart)
        // ═══════════════════════════════════════════════════════════
        document.querySelectorAll('.product-card .qty-selector').forEach(selector => {
            const minusBtn = selector.querySelector('.qty-btn.minus');
            const plusBtn = selector.querySelector('.qty-btn.plus');
            const input = selector.querySelector('.qty-input');

            if (!input) return;

            minusBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const current = parseInt(input.value) || 1;
                input.value = Math.max(1, current - 1);
            });

            plusBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const current = parseInt(input.value) || 1;
                const max = parseInt(input.max) || 999;
                input.value = Math.min(max, current + 1);
            });

            input.addEventListener('click', (e) => e.stopPropagation());
        });

        // ═══════════════════════════════════════════════════════════
        // PRICE EDITOR (Reseller Gestion Mode)
        // ═══════════════════════════════════════════════════════════
        document.querySelectorAll('.price-editor').forEach(editor => {
            const decreaseBtn = editor.querySelector('.editor-btn.decrease');
            const increaseBtn = editor.querySelector('.editor-btn.increase');
            const input = editor.querySelector('.editor-input');
            const card = editor.closest('.product-card');

            if (!input) return;

            const step = parseFloat(decreaseBtn?.dataset.step) || 5;
            const buyPrice = parseFloat(input.dataset.buyPrice) || 0;
            const productId = input.dataset.productId;

            // Decrease price
            decreaseBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const current = parseFloat(input.value) || 0;
                const newPrice = Math.max(buyPrice, current - step);
                input.value = Math.round(newPrice);
                updateMarginIndicator(card, newPrice, buyPrice);
                savePriceDebounced(productId, newPrice);
            });

            // Increase price
            increaseBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const current = parseFloat(input.value) || 0;
                const newPrice = current + step;
                input.value = Math.round(newPrice);
                updateMarginIndicator(card, newPrice, buyPrice);
                savePriceDebounced(productId, newPrice);
            });

            // Direct input change
            input.addEventListener('change', function() {
                const newPrice = Math.max(buyPrice, parseFloat(this.value) || 0);
                this.value = Math.round(newPrice);
                updateMarginIndicator(card, newPrice, buyPrice);
                savePriceDebounced(productId, newPrice);
            });

            input.addEventListener('click', (e) => e.stopPropagation());
        });

        // Update margin indicator display
        function updateMarginIndicator(card, sellingPrice, buyPrice) {
            const indicator = card.querySelector('.margin-indicator');
            const marginValue = indicator?.querySelector('.margin-value');
            if (!indicator || !marginValue) return;

            const marginPercent = sellingPrice > 0 ? ((sellingPrice - buyPrice) / sellingPrice * 100) : 0;
            marginValue.textContent = Math.round(marginPercent) + '%';

            // Update level class
            indicator.classList.remove('good', 'medium', 'low');
            if (marginPercent >= 30) {
                indicator.classList.add('good');
            } else if (marginPercent >= 15) {
                indicator.classList.add('medium');
            } else {
                indicator.classList.add('low');
            }
        }

        // Debounced save price function
        let priceTimeout = {};
        function savePriceDebounced(productId, price) {
            if (priceTimeout[productId]) {
                clearTimeout(priceTimeout[productId]);
            }
            priceTimeout[productId] = setTimeout(() => {
                savePrice(productId, price);
            }, 500);
        }

        // ═══════════════════════════════════════════════════════════
        // FAVORITES
        // ═══════════════════════════════════════════════════════════
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const productId = this.dataset.productId;
                const isFavorite = this.classList.contains('is-favorite');

                this.classList.toggle('is-favorite');
                this.setAttribute('aria-label', isFavorite ? 'Ajouter aux favoris' : 'Retirer des favoris');

                toggleFavorite(productId, !isFavorite);
            });
        });

        function toggleFavorite(productId, add) {
            if (!window.scwShop) {
                // Fallback: save to localStorage/cookie
                let favorites = JSON.parse(localStorage.getItem('scw_favorites') || '[]');
                if (add) {
                    if (!favorites.includes(productId)) favorites.push(productId);
                } else {
                    favorites = favorites.filter(id => id !== productId);
                }
                localStorage.setItem('scw_favorites', JSON.stringify(favorites));
                return;
            }

            fetch(scwShop.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'scw_toggle_favorite',
                    nonce: scwShop.nonce,
                    product_id: productId,
                    add: add ? '1' : '0'
                })
            }).catch(console.error);
        }

        // ═══════════════════════════════════════════════════════════
        // ADD TO CART (Client Mode)
        // ═══════════════════════════════════════════════════════════
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (this.classList.contains('adding') || this.classList.contains('added')) return;

                const productId = this.dataset.productId;
                const card = this.closest('.product-card');
                const qtyInput = card?.querySelector('.qty-input');
                const quantity = parseInt(qtyInput?.value) || 1;

                // Visual feedback
                this.classList.add('adding');
                const ctaText = this.querySelector('.cta-text');
                const originalText = ctaText?.textContent;
                if (ctaText) ctaText.textContent = '...';

                addToCart(productId, quantity).then(success => {
                    this.classList.remove('adding');
                    if (success) {
                        this.classList.add('added');
                        if (ctaText) ctaText.textContent = 'Ajouté !';
                        updateCartCount();
                        
                        setTimeout(() => {
                            this.classList.remove('added');
                            if (ctaText) ctaText.textContent = originalText;
                        }, 2000);
                    } else {
                        if (ctaText) ctaText.textContent = originalText;
                    }
                });
            });
        });

        // ═══════════════════════════════════════════════════════════
        // BUY/ORDER (Reseller Achat Mode)
        // ═══════════════════════════════════════════════════════════
        document.querySelectorAll('.buy-cta').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const productId = this.dataset.productId;
                const card = this.closest('.product-card');
                const qtyInput = card?.querySelector('.qty-input');
                const quantity = parseInt(qtyInput?.value) || 1;

                // Add to wholesale cart/order
                addToWholesaleCart(productId, quantity);
            });
        });

        // ═══════════════════════════════════════════════════════════
        // NOTIFY (Out of stock)
        // ═══════════════════════════════════════════════════════════
        document.querySelectorAll('.notify-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const productId = this.dataset.productId;
                
                // Show notification modal or form
                if (window.scwShop?.showNotifyModal) {
                    window.scwShop.showNotifyModal(productId);
                } else {
                    alert('Vous serez notifié(e) lorsque ce produit sera de nouveau disponible.');
                }
            });
        });

        // ═══════════════════════════════════════════════════════════
        // AJAX FUNCTIONS
        // ═══════════════════════════════════════════════════════════

        async function addToCart(productId, quantity = 1) {
            if (!window.scwShop) return false;

            try {
                const response = await fetch(scwShop.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'scw_add_to_cart',
                        nonce: scwShop.nonce,
                        product_id: productId,
                        quantity: quantity
                    })
                });
                const data = await response.json();
                return data.success;
            } catch (error) {
                console.error('Add to cart error:', error);
                return false;
            }
        }

        async function addToWholesaleCart(productId, quantity = 1) {
            if (!window.scwShop) return false;

            try {
                const response = await fetch(scwShop.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'scw_add_to_wholesale_cart',
                        nonce: scwShop.nonce,
                        product_id: productId,
                        quantity: quantity
                    })
                });
                const data = await response.json();
                if (data.success) {
                    updateWholesaleCartCount();
                }
                return data.success;
            } catch (error) {
                console.error('Wholesale cart error:', error);
                return false;
            }
        }

        function savePrice(productId, newPrice) {
            if (!window.scwShop) return;

            fetch(scwShop.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'scw_update_product_price',
                    nonce: scwShop.nonce,
                    product_id: productId,
                    price: newPrice
                })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) console.error('Price update failed');
            })
            .catch(console.error);
        }

        function updateCartCount() {
            const badge = document.querySelector('.cart-count');
            if (!badge || !window.scwShop) return;

            fetch(scwShop.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'scw_get_cart_count',
                    nonce: scwShop.nonce
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data?.count !== undefined) {
                    badge.textContent = data.data.count;
                    badge.style.display = data.data.count > 0 ? 'flex' : 'none';
                }
            })
            .catch(console.error);
        }

        function updateWholesaleCartCount() {
            const badge = document.querySelector('.wholesale-cart-badge');
            if (!badge || !window.scwShop) return;

            fetch(scwShop.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'scw_get_wholesale_cart_count',
                    nonce: scwShop.nonce
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data?.count !== undefined) {
                    badge.textContent = data.data.count;
                    badge.style.display = data.data.count > 0 ? 'flex' : 'none';
                }
            })
            .catch(console.error);
        }

    });

})();
