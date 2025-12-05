/**
 * Shop Page JavaScript
 * Handles category accordion and infinite scroll
 *
 * @package SCW_Shop
 */

(function() {
    'use strict';

    let isLoading = false;
    let hasMoreProducts = true;

    document.addEventListener('DOMContentLoaded', function() {
        initCategoryAccordion();
        initInfiniteScroll();
    });

    /**
     * Initialize category accordion behavior
     */
    function initCategoryAccordion() {
        const parentGroups = document.querySelectorAll('.category-parent-group');

        parentGroups.forEach(function(group) {
            const parentLink = group.querySelector('.category-item.parent');
            const toggleIcon = parentLink?.querySelector('.toggle-icon');

            if (toggleIcon) {
                // Add click handler on the toggle icon only
                toggleIcon.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Toggle expanded state
                    group.classList.toggle('expanded');
                });
            }
        });
    }

    /**
     * Initialize infinite scroll
     */
    function initInfiniteScroll() {
        const shopGrid = document.querySelector('.shop-grid');
        const spinner = document.querySelector('.load-more-spinner');

        if (!shopGrid || !spinner) {
            return;
        }

        // Get initial max pages
        const maxPages = parseInt(shopGrid.dataset.maxPages || '1');
        if (maxPages <= 1) {
            return; // No need for infinite scroll
        }

        // Intersection Observer for detecting when user scrolls near bottom
        const observerTarget = document.querySelector('.load-more-container');
        if (!observerTarget) {
            return;
        }

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting && !isLoading && hasMoreProducts) {
                    loadMoreProducts();
                }
            });
        }, {
            rootMargin: '200px' // Start loading 200px before reaching the target
        });

        observer.observe(observerTarget);
    }

    /**
     * Load more products via AJAX
     */
    function loadMoreProducts() {
        const shopGrid = document.querySelector('.shop-grid');
        const spinner = document.querySelector('.load-more-spinner');

        if (!shopGrid || isLoading) {
            return;
        }

        isLoading = true;
        spinner.style.display = 'block';

        // Get current page and increment
        let currentPage = parseInt(shopGrid.dataset.currentPage || '1');
        const nextPage = currentPage + 1;

        // Get filter parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category') || 'all';
        const brand = urlParams.get('brand') || '';
        const search = urlParams.get('search') || '';
        const orderby = urlParams.get('orderby') || 'default';

        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'scw_load_more_products');
        formData.append('paged', nextPage);
        formData.append('category', category);
        formData.append('brand', brand);
        formData.append('search', search);
        formData.append('orderby', orderby);

        // Make AJAX request
        const ajaxUrl = (typeof scwShopPage !== 'undefined' && scwShopPage.ajaxUrl)
            ? scwShopPage.ajaxUrl
            : '/wp-admin/admin-ajax.php';

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success && data.data.html) {
                // Append new products to grid
                shopGrid.insertAdjacentHTML('beforeend', data.data.html);

                // Update current page
                shopGrid.dataset.currentPage = nextPage;

                // Update hasMoreProducts flag
                hasMoreProducts = data.data.has_more;

                // Hide spinner if no more products
                if (!hasMoreProducts) {
                    spinner.style.display = 'none';
                }
            }
        })
        .catch(function(error) {
            console.error('Error loading products:', error);
        })
        .finally(function() {
            isLoading = false;
            if (hasMoreProducts) {
                spinner.style.display = 'none';
            }
        });
    }
})();
