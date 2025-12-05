/**
 * Main JavaScript
 * Global scripts and utilities
 *
 * @package SCW_Shop
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {

        /**
         * Smooth scroll to top
         */
        window.scrollToTop = function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        };

        /**
         * Add to favorites (placeholder)
         */
        window.addToFavorites = function(productId) {
            // This will be implemented later with AJAX
            console.log('Add to favorites:', productId);
        };

        /**
         * User mode switcher (for resellers)
         */
        const modeSwitcher = document.querySelectorAll('[data-mode-switch]');
        modeSwitcher.forEach(function(button) {
            button.addEventListener('click', function() {
                const mode = this.getAttribute('data-mode-switch');
                changeUserMode(mode);
            });
        });

        /**
         * Change user mode via AJAX
         */
        function changeUserMode(mode) {
            if (typeof scwShop === 'undefined') {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'scw_change_mode');
            formData.append('nonce', scwShop.nonce);
            formData.append('mode', mode);

            fetch(scwShop.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to apply new mode
                    window.location.reload();
                } else {
                    console.error('Error changing mode:', data.data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

    });

})();
