/**
 * Header JavaScript
 * Manages mobile menu, dropdown, and other header interactions
 *
 * @package SCW_Shop
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {

        // Mobile Menu
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const mobileOverlay = document.getElementById('mobile-menu-overlay');

        // Categories Dropdown
        const categoriesDropdown = document.getElementById('categories-dropdown');

        /**
         * Open mobile menu
         */
        function openMobileMenu() {
            if (mobileSidebar && mobileOverlay) {
                mobileSidebar.classList.add('open');
                mobileOverlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        /**
         * Close mobile menu
         */
        function closeMobileMenu() {
            if (mobileSidebar && mobileOverlay) {
                mobileSidebar.classList.remove('open');
                mobileOverlay.classList.remove('open');
                document.body.style.overflow = 'unset';
            }
        }

        /**
         * Show categories dropdown
         */
        function showDropdown() {
            if (categoriesDropdown) {
                const dropdownMenu = categoriesDropdown.querySelector('.dropdown-menu');
                const headerLink = categoriesDropdown.querySelector('.header-link');
                if (dropdownMenu) {
                    dropdownMenu.classList.add('show');
                }
                if (headerLink) {
                    headerLink.classList.add('active');
                }
            }
        }

        /**
         * Hide categories dropdown
         */
        function hideDropdown() {
            if (categoriesDropdown) {
                const dropdownMenu = categoriesDropdown.querySelector('.dropdown-menu');
                const headerLink = categoriesDropdown.querySelector('.header-link');
                if (dropdownMenu) {
                    dropdownMenu.classList.remove('show');
                }
                if (headerLink) {
                    headerLink.classList.remove('active');
                }
            }
        }

        // Mobile menu event listeners
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', openMobileMenu);
        }

        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileMenu);
        }

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', closeMobileMenu);
        }

        // Close mobile menu when clicking on mobile links
        const mobileLinks = document.querySelectorAll('.mobile-link, .mobile-cat-link');
        mobileLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                closeMobileMenu();
            });
        });

        // Categories dropdown event listeners
        if (categoriesDropdown) {
            categoriesDropdown.addEventListener('mouseenter', showDropdown);
            categoriesDropdown.addEventListener('mouseleave', hideDropdown);
        }

        // Close mobile menu on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });

        // Search functionality (basic implementation)
        const searchInput = document.getElementById('header-search-input');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value.trim();
                    if (searchTerm) {
                        // Redirect to boutique page with search query
                        window.location.href = window.location.origin + '/boutique/?search=' + encodeURIComponent(searchTerm);
                    }
                }
            });
        }

        // Update cart count dynamically (if WooCommerce fragments are available)
        if (typeof jQuery !== 'undefined') {
            jQuery(document.body).on('wc_fragments_refreshed updated_wc_div', function() {
                // Cart count will be updated by WooCommerce fragments
            });
        }

    });

})();
