/**
 * Shop AJAX Filtering & Sorting
 * Reload products without page refresh
 */

jQuery(document).ready(function ($) {
    'use strict';

    console.log('Shop AJAX script loaded');

    // Function to load products via AJAX
    function loadProducts(url, updateSidebar) {
        const $productsContainer = $('.shop-products');
        const $sidebar = $('.shop-sidebar');

        console.log('Loading products from:', url);

        // Add loading class
        $productsContainer.addClass('loading');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'html',
            success: function (response) {
                console.log('AJAX success');

                try {
                    // Create a temporary container to parse the response
                    const $tempDiv = $('<div>').html(response);
                    const $newContent = $tempDiv.find('.shop-products').html();
                    const $newSidebar = $tempDiv.find('.shop-sidebar').html();

                    console.log('Found products content:', $newContent ? 'yes' : 'no');
                    console.log('Found sidebar content:', $newSidebar ? 'yes' : 'no');

                    if ($newContent) {
                        // Small delay for smooth transition
                        setTimeout(function () {
                            // Replace the products container content
                            $productsContainer.html($newContent);

                            // Update sidebar if needed (for category active states)
                            if (updateSidebar && $newSidebar) {
                                $sidebar.html($newSidebar);
                            }

                            // Update Breadcrumb
                            const $newBreadcrumb = $tempDiv.find('.breadcrumb-simple').html();
                            if ($newBreadcrumb) {
                                $('.breadcrumb-simple').html($newBreadcrumb);
                            }

                            // Update Hero Content (Title, Count, etc.)
                            const $newHero = $tempDiv.find('.shop-hero-content').html();
                            if ($newHero) {
                                $('.shop-hero-content').html($newHero);
                            }

                            // Remove loading state
                            $productsContainer.removeClass('loading');

                            // Scroll to products section smoothly
                            $('html, body').animate({
                                scrollTop: $productsContainer.offset().top - 100
                            }, 400);

                            // Re-initialize category accordions
                            initCategoryAccordions();

                            // Re-initialize wishlist buttons for new products
                            if (typeof window.aleaulavageInitWishlist === 'function') {
                                console.log('Reinitializing wishlist buttons after AJAX load');
                                window.aleaulavageInitWishlist();
                            }
                        }, 300);

                        // Update browser URL without reload
                        if (history.pushState) {
                            history.pushState(null, null, url);
                        }
                    } else {
                        console.log('No content found');
                        $productsContainer.removeClass('loading');
                        // Fallback to page reload
                        window.location.href = url;
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    $productsContainer.removeClass('loading');
                    window.location.href = url;
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX error:', status, error);
                console.log('XHR status:', xhr.status);
                console.log('Response text:', xhr.responseText ? xhr.responseText.substring(0, 200) : 'empty');

                // Remove loading state
                $productsContainer.removeClass('loading');

                // If AJAX fails, fall back to normal page load
                window.location.href = url;
            }
        });
    }

    // Initialize category accordions
    function initCategoryAccordions() {
        $('.toggle-children').off('click').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const parentLi = $(this).closest('.cat-item');
            const isOpen = parentLi.hasClass('open');

            // Toggle open class
            if (isOpen) {
                parentLi.removeClass('open');
            } else {
                parentLi.addClass('open');
            }
        });
    }

    // Prevent form submission
    $(document).on('submit', '.woocommerce-ordering', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        console.log('Form submit prevented');
        return false;
    });

    // Handle sorting change
    $(document).on('change', '.woocommerce-ordering select.orderby', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        const orderby = $(this).val();
        console.log('Sorting changed to:', orderby);

        // Get the form and prevent it from submitting
        const $form = $(this).closest('form');
        $form.off('submit').on('submit', function (e) {
            e.preventDefault();
            return false;
        });

        // Get current URL
        const currentUrl = new URL(window.location.href);
        const baseUrl = currentUrl.origin + currentUrl.pathname;

        // Build new URL with all parameters
        const params = new URLSearchParams();

        // Add all current query params except orderby and paged
        currentUrl.searchParams.forEach(function (value, key) {
            if (key !== 'orderby' && key !== 'paged' && key !== 'product-page') {
                params.set(key, value);
            }
        });

        // Add the orderby parameter
        params.set('orderby', orderby);

        const newUrl = baseUrl + '?' + params.toString();
        console.log('New URL:', newUrl);

        loadProducts(newUrl, false);

        return false;
    });

    // Handle category clicks
    $(document).on('click', '.product-categories a', function (e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('Category clicked');

        const categoryUrl = new URL($(this).attr('href'));

        // Get current orderby parameter
        const currentUrl = new URL(window.location.href);
        const currentOrderby = currentUrl.searchParams.get('orderby');

        // Preserve orderby in category URL if it exists
        if (currentOrderby) {
            categoryUrl.searchParams.set('orderby', currentOrderby);
        }

        const url = categoryUrl.toString();
        console.log('Category URL:', url);

        loadProducts(url, true);

        return false;
    });

    // Handle pagination clicks
    $(document).on('click', '.woocommerce-pagination a', function (e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('Pagination clicked');

        const paginationUrl = new URL($(this).attr('href'));

        // Get current orderby parameter
        const currentUrl = new URL(window.location.href);
        const currentOrderby = currentUrl.searchParams.get('orderby');

        // Preserve orderby in pagination URL if it exists
        if (currentOrderby) {
            paginationUrl.searchParams.set('orderby', currentOrderby);
        }

        const url = paginationUrl.toString();
        console.log('Pagination URL:', url);

        loadProducts(url, false);

        return false;
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function () {
        console.log('Popstate event');
        loadProducts(window.location.href, true);
    });

    // Initialize on page load
    initCategoryAccordions();

});
