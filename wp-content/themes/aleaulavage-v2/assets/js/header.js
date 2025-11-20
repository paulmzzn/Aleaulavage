document.addEventListener('DOMContentLoaded', function () {
    // Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Category Scroll Logic
    const scrollContainer = document.querySelector('.category-list');
    const scrollLeftBtn = document.getElementById('scrollLeft');
    const scrollRightBtn = document.getElementById('scrollRight');

    if (scrollContainer && scrollLeftBtn && scrollRightBtn) {
        scrollLeftBtn.addEventListener('click', () => {
            scrollContainer.scrollBy({ left: -200, behavior: 'smooth' });
        });

        scrollRightBtn.addEventListener('click', () => {
            scrollContainer.scrollBy({ left: 200, behavior: 'smooth' });
        });
    }

    // Offcanvas Cart Refresh
    const offcanvasCart = document.getElementById('offcanvas-cart');
    if (offcanvasCart) {
        offcanvasCart.addEventListener('shown.bs.offcanvas', function () {
            // Trigger WC fragment refresh
            if (typeof jQuery !== 'undefined') {
                jQuery(document.body).trigger('wc_fragment_refresh');
            }
        });
    }

    // Part Finder Custom Dropdown
    const partFinderSelect = document.querySelector('.part-finder-box select');
    if (partFinderSelect) {
        // Hide native select
        partFinderSelect.style.display = 'none';

        // Create Custom Select Container
        const customSelect = document.createElement('div');
        customSelect.className = 'custom-select-container';

        // Create Selected Item Display
        const selectedDisplay = document.createElement('div');
        selectedDisplay.className = 'custom-select-trigger';
        selectedDisplay.innerHTML = '<span>Sélectionnez une catégorie...</span> <div class="arrow"></div>';

        // Create Options List
        const optionsList = document.createElement('div');
        optionsList.className = 'custom-options';

        // Populate Options
        Array.from(partFinderSelect.options).forEach(option => {
            if (option.value === '' || option.value === 'false') return; // Skip placeholder

            const item = document.createElement('div');
            item.className = 'custom-option';

            let text = option.text;
            // Handle Hierarchy
            // Detect prefix (dashes, spaces, nbsp) including U+2212 Minus Sign
            const prefixMatch = text.match(/^[\s\u00A0\u2212\u2013\u2014-]+/);

            if (prefixMatch) {
                const prefix = prefixMatch[0];
                // Count dash-like characters to determine depth
                const dashCount = (prefix.match(/[\u2212\u2013\u2014-]/g) || []).length;

                // Remove prefix
                text = text.replace(/^[\s\u00A0\u2212\u2013\u2014-]+/, '').trim();

                if (dashCount >= 2) {
                    // Grandchild (Level 2)
                    item.classList.add('is-grandchild');
                    item.innerHTML = `<span class="child-arrow" style="opacity:0.3">↳</span> ${text}`;
                } else {
                    // Child (Level 1)
                    item.classList.add('is-child');
                    item.innerHTML = `<span class="child-arrow">↳</span> ${text}`;
                }
            } else {
                // Parent (Level 0)
                item.classList.add('is-parent');
                item.textContent = text;
            }

            item.dataset.value = option.value;

            // Click Handler
            item.addEventListener('click', function () {
                const url = this.dataset.value;
                if (url && url !== 'false') {
                    window.location.href = url;
                }
            });

            optionsList.appendChild(item);
        });

        // Toggle Dropdown
        selectedDisplay.addEventListener('click', function (e) {
            e.stopPropagation();
            customSelect.classList.toggle('open');
        });

        // Close on Click Outside
        document.addEventListener('click', function (e) {
            if (!customSelect.contains(e.target)) {
                customSelect.classList.remove('open');
            }
        });

        // Assemble
        customSelect.appendChild(selectedDisplay);
        customSelect.appendChild(optionsList);
        partFinderSelect.parentNode.insertBefore(customSelect, partFinderSelect);
    }

});
