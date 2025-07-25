document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.remove-from-wishlist').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const wishlistCard = this.closest('.wishlist-card');
            if (!wishlist_ajax || !wishlist_ajax.nonce) {
                alert('Erreur de configuration. Veuillez rafraîchir la page.');
                return;
            }
            // Animation de chargement
            this.style.pointerEvents = 'none';
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            const formData = new FormData();
            formData.append('action', 'remove_from_wishlist');
            formData.append('product_id', productId);
            formData.append('nonce', wishlist_ajax.nonce);
            fetch(wishlist_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animation fluide de suppression
                    wishlistCard.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    wishlistCard.style.opacity = '0';
                    wishlistCard.style.transform = 'scale(0.8) translateY(-20px)';
                    setTimeout(() => {
                        wishlistCard.remove();
                        const remainingItems = document.querySelectorAll('.wishlist-card');
                        if (remainingItems.length === 0) {
                            location.reload();
                        }
                    }, 500);
                } else {
                    alert(data.data ? data.data.message : 'Erreur lors de la suppression');
                    this.style.pointerEvents = 'auto';
                    this.innerHTML = '<i class="fa-solid fa-times"></i>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur de connexion');
                this.style.pointerEvents = 'auto';
                this.innerHTML = '<i class="fa-solid fa-times"></i>';
            });
        });
    });
});
