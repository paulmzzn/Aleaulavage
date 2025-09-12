/**
 * Correctif simple pour le système d'images - Version conservative
 * Préserve l'affichage existant et ajoute le zoom
 */

(function() {
    'use strict';
    
    // Attendre que le DOM soit chargé
    function initGalleryFix() {
        // Ne pas faire de modifications si les images sont déjà visibles
        const existingImages = document.querySelectorAll('.woocommerce-product-gallery img');
        let hasVisibleImages = false;
        
        existingImages.forEach(img => {
            const style = window.getComputedStyle(img);
            if (style.display !== 'none' && style.visibility !== 'hidden' && img.offsetWidth > 0) {
                hasVisibleImages = true;
            }
        });
        
        // Si les images sont déjà visibles, juste ajouter le zoom
        if (hasVisibleImages) {
            addZoomToExistingGallery();
        } else {
            // Sinon, essayer de restaurer l'affichage
            restoreImageDisplay();
            setTimeout(addZoomToExistingGallery, 500);
        }
    }
    
    function restoreImageDisplay() {
        // Restaurer l'affichage des images masquées
        const hiddenImages = document.querySelectorAll('.woocommerce-product-gallery img[style*="display: none"], .woocommerce-product-gallery img[style*="visibility: hidden"]');
        
        hiddenImages.forEach(img => {
            img.style.display = '';
            img.style.visibility = '';
            img.style.opacity = '';
            
            // Restaurer le container parent aussi
            const container = img.closest('.woocommerce-product-gallery__image');
            if (container) {
                container.style.display = '';
                container.style.visibility = '';
                container.style.opacity = '';
            }
        });
        
        // S'assurer que la galerie principale est visible
        const gallery = document.querySelector('.woocommerce-product-gallery');
        if (gallery) {
            gallery.style.display = '';
            gallery.style.visibility = '';
        }
        
        // Réactiver flexslider si nécessaire
        const flexslider = document.querySelector('.flexslider');
        if (flexslider) {
            flexslider.style.display = '';
            flexslider.style.visibility = '';
        }
    }
    
    function addZoomToExistingGallery() {
        const mainImages = document.querySelectorAll('.woocommerce-product-gallery .woocommerce-product-gallery__image img');
        
        mainImages.forEach((img, index) => {
            // Ne pas ajouter le zoom si déjà présent
            if (img.dataset.zoomAdded) return;
            
            const container = img.closest('.woocommerce-product-gallery__image');
            if (!container) return;
            
            // Styles de base pour le container
            container.style.position = 'relative';
            container.style.overflow = 'hidden';
            container.style.cursor = 'zoom-in';
            container.style.background = 'white';
            
            // Styles de base pour l'image
            img.style.transition = 'transform 0.3s ease';
            img.style.transformOrigin = 'center';
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.display = 'block';
            
            let isZooming = false;
            let zoomLevel = 1;
            
            // Zoom au survol (desktop seulement)
            container.addEventListener('mouseenter', function() {
                if (window.innerWidth >= 768) {
                    isZooming = true;
                    container.style.cursor = 'crosshair';
                }
            });
            
            container.addEventListener('mouseleave', function() {
                if (window.innerWidth >= 768) {
                    isZooming = false;
                    zoomLevel = 1;
                    img.style.transform = '';
                    container.style.cursor = 'zoom-in';
                }
            });
            
            container.addEventListener('mousemove', function(e) {
                if (!isZooming || window.innerWidth < 768) return;
                
                const rect = container.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width;
                const y = (e.clientY - rect.top) / rect.height;
                
                // Limiter aux bordures
                const clampedX = Math.max(0.1, Math.min(0.9, x));
                const clampedY = Math.max(0.1, Math.min(0.9, y));
                
                img.style.transformOrigin = `${clampedX * 100}% ${clampedY * 100}%`;
                img.style.transform = 'scale(1.8)';
            });
            
            // Zoom par clic
            container.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (window.innerWidth < 768) {
                    // Sur mobile, ouvrir l'image en grand
                    openImageModal(img);
                } else {
                    // Sur desktop, zoom par niveaux
                    zoomLevel = zoomLevel >= 3 ? 1 : zoomLevel + 0.5;
                    
                    if (zoomLevel === 1) {
                        img.style.transform = '';
                        container.style.cursor = 'zoom-in';
                    } else {
                        const rect = container.getBoundingClientRect();
                        const x = (e.clientX - rect.left) / rect.width;
                        const y = (e.clientY - rect.top) / rect.height;
                        
                        img.style.transformOrigin = `${x * 100}% ${y * 100}%`;
                        img.style.transform = `scale(${zoomLevel})`;
                        container.style.cursor = zoomLevel >= 3 ? 'zoom-out' : 'zoom-in';
                    }
                }
            });
            
            // Marquer comme traité
            img.dataset.zoomAdded = 'true';
        });
    }
    
    function openImageModal(img) {
        // Modal simple pour mobile
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
            box-sizing: border-box;
        `;
        
        const modalImg = document.createElement('img');
        modalImg.src = img.src;
        modalImg.alt = img.alt;
        modalImg.style.cssText = `
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
        `;
        
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '✕';
        closeBtn.style.cssText = `
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        modal.appendChild(modalImg);
        modal.appendChild(closeBtn);
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        
        // Fermeture
        const closeModal = () => {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
        };
        
        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }
    
    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initGalleryFix, 100);
        });
    } else {
        setTimeout(initGalleryFix, 100);
    }
    
    // Réinitialiser après changement de variation
    document.addEventListener('woocommerce_variation_has_changed', function() {
        setTimeout(initGalleryFix, 200);
    });
    
})();