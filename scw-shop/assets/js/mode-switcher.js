/**
 * Reseller Mode Switcher JavaScript
 * Gère le bouton flottant de sélection de mode
 *
 * @package SCW_Shop
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const modeSwitcher = document.querySelector('.reseller-mode-toggle');
        if (!modeSwitcher) return;

        const triggerBtn = modeSwitcher.querySelector('.mode-trigger-btn');
        const menu = modeSwitcher.querySelector('.mode-menu');
        const menuButtons = modeSwitcher.querySelectorAll('.mode-menu-btn');
        const modeIcon = modeSwitcher.querySelector('.mode-icon');

        let isOpen = false;

        // Mode icons
        const modeIcons = {
            gestion: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
            achat: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
            vitrine: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
        };

        /**
         * Toggle menu open/close
         */
        function toggleMenu() {
            isOpen = !isOpen;
            if (isOpen) {
                menu.classList.add('open');
            } else {
                menu.classList.remove('open');
            }
        }

        /**
         * Close menu
         */
        function closeMenu() {
            isOpen = false;
            menu.classList.remove('open');
        }

        /**
         * Change user mode
         */
        function changeMode(mode) {
            if (!window.scwShop) return;

            // Update UI immediately
            menuButtons.forEach(btn => {
                if (btn.dataset.mode === mode) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // Update icon
            if (modeIcons[mode]) {
                modeIcon.innerHTML = modeIcons[mode];
                modeIcon.dataset.currentMode = mode;
            }

            // Close menu
            closeMenu();

            // Send AJAX request
            fetch(scwShop.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'scw_change_mode',
                    nonce: scwShop.nonce,
                    mode: mode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Mode changé:', mode);
                    // Reload page to update product cards
                    window.location.reload();
                } else {
                    console.error('Erreur changement mode');
                }
            })
            .catch(error => {
                console.error('Erreur AJAX:', error);
            });
        }

        // Toggle menu on trigger button click
        triggerBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
        });

        // Handle mode button clicks
        menuButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const mode = this.dataset.mode;
                changeMode(mode);
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!modeSwitcher.contains(e.target)) {
                closeMenu();
            }
        });

        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isOpen) {
                closeMenu();
            }
        });
    });

})();
