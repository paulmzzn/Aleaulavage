/**
 * Account Page JavaScript
 * Gère les interactions des pages de compte
 *
 * @package SCW_Shop
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {

        // Gestion du sélecteur de couleur (Mode Revendeur - Settings)
        const colorSwatches = document.querySelectorAll('.color-swatch');
        const previewButton = document.getElementById('preview-button');

        colorSwatches.forEach(swatch => {
            swatch.addEventListener('click', function() {
                const selectedColor = this.dataset.color;

                // Mettre à jour l'état sélectionné
                colorSwatches.forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');

                // Mettre à jour l'aperçu du bouton
                if (previewButton) {
                    previewButton.style.backgroundColor = selectedColor;
                }

                // Sauvegarder via AJAX
                saveStoreColor(selectedColor);
            });
        });

        // Gestion de la marge globale
        const globalMarginInput = document.getElementById('global-margin');

        if (globalMarginInput) {
            globalMarginInput.addEventListener('change', function() {
                const margin = this.value;
                saveGlobalMargin(margin);
            });
        }

        /**
         * Sauvegarder la couleur de la boutique
         */
        function saveStoreColor(color) {
            if (!window.scwShop) return;

            fetch(scwShop.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'scw_save_store_color',
                    nonce: scwShop.nonce,
                    color: color
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Couleur sauvegardée:', color);
                } else {
                    console.error('Erreur sauvegarde couleur');
                }
            })
            .catch(error => {
                console.error('Erreur AJAX:', error);
            });
        }

        /**
         * Sauvegarder la marge globale
         */
        function saveGlobalMargin(margin) {
            if (!window.scwShop) return;

            fetch(scwShop.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'scw_save_global_margin',
                    nonce: scwShop.nonce,
                    margin: margin
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Marge sauvegardée:', margin);
                } else {
                    console.error('Erreur sauvegarde marge');
                }
            })
            .catch(error => {
                console.error('Erreur AJAX:', error);
            });
        }

        // Gestion des onglets client (Commandes, Adresses, Infos personnelles)
        const tabButtons = document.querySelectorAll('.menu-item[data-tab]');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');

                // Remove active class from all buttons and tabs
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to clicked button and corresponding tab
                this.classList.add('active');
                const targetContent = document.getElementById('tab-' + targetTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });

        // Handle address edit buttons (open modals)
        const editAddressButtons = document.querySelectorAll('.btn-edit');
        editAddressButtons.forEach(button => {
            button.addEventListener('click', function() {
                const addressType = this.getAttribute('data-address-type');
                if (addressType) {
                    openModal(addressType);
                }
            });
        });

        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById('modal-' + modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById('modal-' + modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        // Close modal buttons
        const modalCloseButtons = document.querySelectorAll('.modal-close');
        modalCloseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal');
                if (modalId) {
                    closeModal(modalId);
                }
            });
        });

        // Close modal when clicking overlay
        const modalOverlays = document.querySelectorAll('.modal-overlay');
        modalOverlays.forEach(overlay => {
            overlay.addEventListener('click', function() {
                const modal = this.closest('.address-modal');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        });

        // Handle address form submissions
        const addressForms = document.querySelectorAll('.address-form');
        addressForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('action', 'scw_save_address');

                // Disable submit button
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Enregistrement...';

                fetch(scwShop.ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal and reload page
                        const addressType = formData.get('address_type');
                        closeModal(addressType);
                        location.reload();
                    } else {
                        alert(data.data.message || 'Erreur lors de la sauvegarde');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors de la sauvegarde');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            });
        });

    });

})();
