/**
 * Système de Galerie d'Images Moderne
 * Aleaulavage - Qualité E-commerce Professionnel
 * 
 * Fonctionnalités :
 * - Zoom fluide et responsive 
 * - Navigation tactile et clavier
 * - Lightbox moderne avec drag-to-pan
 * - Support des variations produit
 * - Performance optimisée
 * - Compatible mobile
 */

class ModernProductGallery {
    constructor() {
        this.isEnabled = true; // Système activé par défaut
        this.gallery = null;
        this.images = [];
        this.currentIndex = 0;
        this.isZooming = false;
        this.zoomLevel = 1;
        this.maxZoom = 3;
        this.lightbox = null;
        this.animationFrame = null;
        
        // Configuration
        this.config = {
            zoomStep: 0.5,
            hoverZoom: 1.8,
            animationDuration: 300,
            touchSensitivity: 50
        };
        
        this.init();
    }
    
    init() {
        // Vérifier si le système doit être activé
        if (!this.shouldActivate()) return;
        
        // Marquer le body pour le CSS
        document.body.classList.add('gallery-v2-active');
        
        this.createGalleryContainer();
        this.loadImages();
        this.setupEvents();
        this.createLightbox();
        this.setupKeyboardNavigation();
        this.setupVariationWatcher();
        this.updateColorDotClass();
        this.repositionPromoBadges();
    }
    
    shouldActivate() {
        // Système activé par défaut
        return true;
    }
    
    createGalleryContainer() {
        // Récupérer l'ancien slider pour extraire les images
        const oldGallery = document.querySelector('.woocommerce-product-gallery');
        if (!oldGallery) return;
        
        // Sauvegarder l'ancien système pour restauration ET récupérer les images AVANT de supprimer
        this.originalGallery = oldGallery.cloneNode(true);
        this.extractImages(oldGallery);
        
        const newGallery = document.createElement('div');
        newGallery.className = 'modern-gallery-v2';
        newGallery.innerHTML = `
            <div class="gallery-v2-main">
                <div class="v2-image-container">
                    <img class="v2-main-image" src="" alt="" loading="lazy">
                    <div class="v2-zoom-overlay"></div>
                </div>
                <div class="v2-thumbnails-container">
                    <div class="v2-thumbnails"></div>
                    <div class="v2-counter">1 / 1</div>
                </div>
                <button class="v2-fullscreen-btn" title="Plein écran">⛶</button>
            </div>
        `;
        
        // Masquer l'ancienne galerie au lieu de la supprimer (pour les variations)
        oldGallery.style.display = 'none';
        
        // Insérer la nouvelle galerie
        oldGallery.parentNode.insertBefore(newGallery, oldGallery.nextSibling);
        
        this.gallery = newGallery;
        this.mainImage = newGallery.querySelector('.v2-main-image');
        this.imageContainer = newGallery.querySelector('.v2-image-container');
        this.thumbnailsContainer = newGallery.querySelector('.v2-thumbnails');
        this.zoomOverlay = newGallery.querySelector('.v2-zoom-overlay');
        this.counter = newGallery.querySelector('.v2-counter');
    }
    
    extractImages(oldGallery) {
        // Récupérer les images de l'ancienne galerie AVANT suppression
        const oldImages = oldGallery.querySelectorAll('.woocommerce-product-gallery__image');
        
        this.images = [];
        oldImages.forEach((container, index) => {
            const img = container.querySelector('img');
            const link = container.querySelector('a');
            
            if (img) {
                this.images.push({
                    thumb: img.src,
                    full: link ? link.href : img.src,
                    alt: img.alt || `Image ${index + 1}`,
                    title: img.title || ''
                });
            }
        });
    }
    
    loadImages() {
        // Les images sont déjà extraites dans createGalleryContainer()
        if (this.images && this.images.length > 0) {
            this.displayImage(0);
            this.createThumbnails();
            this.updateCounter();
        }
    }
    
    displayImage(index) {
        if (index < 0 || index >= this.images.length) return;
        
        this.currentIndex = index;
        const image = this.images[index];
        
        // Animation de transition
        this.mainImage.style.opacity = '0.7';
        
        setTimeout(() => {
            this.mainImage.src = image.full;
            this.mainImage.alt = image.alt;
            this.mainImage.title = image.title;
            this.mainImage.style.opacity = '1';
        }, 150);
        
        this.updateThumbnails();
        this.updateCounter();
    }
    
    createThumbnails() {
        if (this.images.length <= 1) {
            this.thumbnailsContainer.style.display = 'none';
            return;
        }
        
        this.thumbnailsContainer.innerHTML = '';
        
        this.images.forEach((image, index) => {
            const thumb = document.createElement('button');
            thumb.className = `v2-thumb ${index === 0 ? 'active' : ''}`;
            thumb.innerHTML = `<img src="${image.thumb}" alt="${image.alt}" loading="lazy">`;
            thumb.addEventListener('click', () => this.displayImage(index));
            
            this.thumbnailsContainer.appendChild(thumb);
        });
    }
    
    updateThumbnails() {
        const thumbs = this.thumbnailsContainer.querySelectorAll('.v2-thumb');
        thumbs.forEach((thumb, index) => {
            thumb.classList.toggle('active', index === this.currentIndex);
        });
    }
    
    updateCounter() {
        if (this.counter) {
            this.counter.textContent = `${this.currentIndex + 1} / ${this.images.length}`;
        }
    }
    
    setupEvents() {
        // Navigation
        const fullscreenBtn = this.gallery.querySelector('.v2-fullscreen-btn');
        
        if (fullscreenBtn) fullscreenBtn.addEventListener('click', () => this.openLightbox());
        
        // Zoom sur l'image principale
        this.setupZoomEvents();
        
        // Touch events
        this.setupTouchEvents();
    }
    
    setupZoomEvents() {
        if (!this.imageContainer) return;
        
        // Hover zoom (desktop uniquement)
        this.imageContainer.addEventListener('mouseenter', () => {
            if (window.innerWidth >= 768) {
                this.isZooming = true;
                this.imageContainer.classList.add('zooming');
            }
        });
        
        this.imageContainer.addEventListener('mouseleave', () => {
            if (window.innerWidth >= 768) {
                this.isZooming = false;
                this.imageContainer.classList.remove('zooming');
                
                // Annuler les animations en cours
                if (this.animationFrame) {
                    cancelAnimationFrame(this.animationFrame);
                    this.animationFrame = null;
                }
                
                // Réinitialiser le transform au survol
                this.mainImage.style.transform = '';
                this.mainImage.style.transformOrigin = '';
            }
        });
        
        this.imageContainer.addEventListener('mousemove', (e) => {
            if (!this.isZooming || window.innerWidth < 768) return;
            
            // Utiliser requestAnimationFrame pour fluidité maximale
            if (this.animationFrame) {
                cancelAnimationFrame(this.animationFrame);
            }
            
            this.animationFrame = requestAnimationFrame(() => {
                const rect = this.imageContainer.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width;
                const y = (e.clientY - rect.top) / rect.height;
                
                const clampedX = Math.max(0, Math.min(1, x));
                const clampedY = Math.max(0, Math.min(1, y));
                
                this.mainImage.style.transformOrigin = `${clampedX * 100}% ${clampedY * 100}%`;
                this.mainImage.style.transform = `scale(${this.config.hoverZoom})`;
            });
        });
        
        // Click pour ouvrir le lightbox
        this.imageContainer.addEventListener('click', (e) => {
            this.openLightbox();
        });
    }
    
    updateZoomOverlay(x, y) {
        // Supprimer complètement l'overlay de zoom
        return;
    }
    
    setupTouchEvents() {
        let touchStartX = 0;
        let touchStartY = 0;
        
        this.imageContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }, { passive: true });
        
        this.imageContainer.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;
            
            // Swipe horizontal
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > this.config.touchSensitivity) {
                if (deltaX > 0) {
                    this.previousImage();
                } else {
                    this.nextImage();
                }
            }
        }, { passive: true });
    }
    
    previousImage() {
        const newIndex = this.currentIndex === 0 ? this.images.length - 1 : this.currentIndex - 1;
        this.displayImage(newIndex);
    }
    
    nextImage() {
        const newIndex = this.currentIndex === this.images.length - 1 ? 0 : this.currentIndex + 1;
        this.displayImage(newIndex);
    }
    
    createLightbox() {
        const lightbox = document.createElement('div');
        lightbox.className = 'modern-lightbox-v2';
        lightbox.innerHTML = `
            <div class="lightbox-v2-backdrop"></div>
            <div class="lightbox-v2-content">
                <div class="lightbox-v2-image-container">
                    <img class="lightbox-v2-image" src="" alt="">
                </div>
                <button class="lightbox-v2-close" title="Fermer">×</button>
                <div class="lightbox-v2-nav">
                    <button class="lightbox-v2-prev" title="Précédent">‹</button>
                    <button class="lightbox-v2-next" title="Suivant">›</button>
                </div>
                <div class="lightbox-v2-zoom-controls">
                    <button class="lightbox-v2-zoom-out" title="Dézoomer">−</button>
                    <span class="lightbox-v2-zoom-level">100%</span>
                    <button class="lightbox-v2-zoom-in" title="Zoomer">+</button>
                </div>
                <div class="lightbox-v2-counter">1 / 1</div>
            </div>
        `;
        
        document.body.appendChild(lightbox);
        this.lightbox = lightbox;
        this.lightboxZoom = 1;
        this.lightboxPanX = 0;
        this.lightboxPanY = 0;
        
        // Events lightbox
        lightbox.querySelector('.lightbox-v2-close').addEventListener('click', () => this.closeLightbox());
        lightbox.querySelector('.lightbox-v2-backdrop').addEventListener('click', () => this.closeLightbox());
        lightbox.querySelector('.lightbox-v2-prev').addEventListener('click', () => this.lightboxPrev());
        lightbox.querySelector('.lightbox-v2-next').addEventListener('click', () => this.lightboxNext());
        lightbox.querySelector('.lightbox-v2-zoom-in').addEventListener('click', () => this.lightboxZoomIn());
        lightbox.querySelector('.lightbox-v2-zoom-out').addEventListener('click', () => this.lightboxZoomOut());
        
        // Double clic pour zoom
        const lightboxImage = lightbox.querySelector('.lightbox-v2-image');
        lightboxImage.addEventListener('dblclick', () => this.toggleLightboxZoom());
        
        // Déplacement dans l'image zoomée
        this.setupLightboxDrag();
    }
    
    openLightbox() {
        if (!this.lightbox) return;
        
        const lightboxImage = this.lightbox.querySelector('.lightbox-v2-image');
        const lightboxCounter = this.lightbox.querySelector('.lightbox-v2-counter');
        
        lightboxImage.src = this.images[this.currentIndex].full;
        lightboxImage.alt = this.images[this.currentIndex].alt;
        lightboxCounter.textContent = `${this.currentIndex + 1} / ${this.images.length}`;
        
        // Reset zoom et position
        this.lightboxZoom = 1;
        this.lightboxPanX = 0;
        this.lightboxPanY = 0;
        this.updateLightboxZoom();
        
        this.lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    closeLightbox() {
        if (!this.lightbox) return;
        
        this.lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    lightboxPrev() {
        this.previousImage();
        this.openLightbox(); // Refresh avec nouvelle image
    }
    
    lightboxNext() {
        this.nextImage();
        this.openLightbox(); // Refresh avec nouvelle image
    }
    
    lightboxZoomIn() {
        this.lightboxZoom = Math.min(3, this.lightboxZoom + 0.5);
        this.updateLightboxZoom();
    }
    
    lightboxZoomOut() {
        this.lightboxZoom = Math.max(0.5, this.lightboxZoom - 0.5);
        this.updateLightboxZoom();
    }
    
    toggleLightboxZoom() {
        this.lightboxZoom = this.lightboxZoom === 1 ? 2 : 1;
        this.updateLightboxZoom();
    }
    
    updateLightboxZoom() {
        const lightboxImage = this.lightbox.querySelector('.lightbox-v2-image');
        const zoomLevel = this.lightbox.querySelector('.lightbox-v2-zoom-level');
        
        // Appliquer le zoom et le déplacement
        lightboxImage.style.transform = `scale(${this.lightboxZoom}) translate(${this.lightboxPanX}px, ${this.lightboxPanY}px)`;
        lightboxImage.style.cursor = this.lightboxZoom > 1 ? 'move' : 'zoom-in';
        
        if (zoomLevel) {
            zoomLevel.textContent = `${Math.round(this.lightboxZoom * 100)}%`;
        }
        
        // Activer/désactiver les boutons
        const zoomIn = this.lightbox.querySelector('.lightbox-v2-zoom-in');
        const zoomOut = this.lightbox.querySelector('.lightbox-v2-zoom-out');
        
        if (zoomIn) zoomIn.disabled = this.lightboxZoom >= 3;
        if (zoomOut) zoomOut.disabled = this.lightboxZoom <= 0.5;
        
        // Réinitialiser la position si dézoom complet
        if (this.lightboxZoom <= 1) {
            this.lightboxPanX = 0;
            this.lightboxPanY = 0;
        }
    }
    
    setupLightboxDrag() {
        const lightboxImage = this.lightbox.querySelector('.lightbox-v2-image');
        let isDragging = false;
        let startX = 0;
        let startY = 0;
        let initialPanX = 0;
        let initialPanY = 0;
        
        // Mouse events
        lightboxImage.addEventListener('mousedown', (e) => {
            if (this.lightboxZoom <= 1) return;
            
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            initialPanX = this.lightboxPanX;
            initialPanY = this.lightboxPanY;
            
            e.preventDefault();
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!isDragging || this.lightboxZoom <= 1) return;
            
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            
            this.lightboxPanX = initialPanX + deltaX;
            this.lightboxPanY = initialPanY + deltaY;
            
            this.updateLightboxZoom();
        });
        
        document.addEventListener('mouseup', () => {
            isDragging = false;
        });
        
        // Touch events pour mobile
        lightboxImage.addEventListener('touchstart', (e) => {
            if (this.lightboxZoom <= 1 || e.touches.length !== 1) return;
            
            isDragging = true;
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            initialPanX = this.lightboxPanX;
            initialPanY = this.lightboxPanY;
            
            e.preventDefault();
        }, { passive: false });
        
        lightboxImage.addEventListener('touchmove', (e) => {
            if (!isDragging || this.lightboxZoom <= 1 || e.touches.length !== 1) return;
            
            const deltaX = e.touches[0].clientX - startX;
            const deltaY = e.touches[0].clientY - startY;
            
            this.lightboxPanX = initialPanX + deltaX;
            this.lightboxPanY = initialPanY + deltaY;
            
            this.updateLightboxZoom();
            e.preventDefault();
        }, { passive: false });
        
        lightboxImage.addEventListener('touchend', () => {
            isDragging = false;
        });
    }
    
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            if (!this.isEnabled) return;
            
            if (this.lightbox && this.lightbox.classList.contains('active')) {
                switch(e.key) {
                    case 'Escape':
                        this.closeLightbox();
                        break;
                    case 'ArrowLeft':
                        this.lightboxPrev();
                        break;
                    case 'ArrowRight':
                        this.lightboxNext();
                        break;
                }
            } else if (this.gallery && this.gallery.matches(':hover')) {
                switch(e.key) {
                    case 'ArrowLeft':
                        this.previousImage();
                        break;
                    case 'ArrowRight':
                        this.nextImage();
                        break;
                }
            }
        });
    }
    
    setupVariationWatcher() {
        // Référence à l'ancienne galerie (maintenant cachée)
        const originalGallery = document.querySelector('.woocommerce-product-gallery');
        if (!originalGallery) return;
        
        // Créer un observer pour détecter les changements d'images
        const observer = new MutationObserver(() => {
            this.updateImagesFromOriginal();
        });
        
        // Observer les changements dans la galerie originale
        observer.observe(originalGallery, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['src', 'href']
        });
        
        // Écouter aussi les événements de changement de variation WooCommerce
        document.addEventListener('woocommerce_variation_select_change', () => {
            setTimeout(() => {
                this.updateImagesFromOriginal();
            }, 100);
        });
        
        // Écouter l'événement found_variation de WooCommerce
        document.addEventListener('found_variation', () => {
            setTimeout(() => {
                this.updateImagesFromOriginal();
            }, 100);
        });
    }
    
    updateImagesFromOriginal() {
        // Récupérer les nouvelles images de la galerie originale
        const oldImages = document.querySelectorAll('.woocommerce-product-gallery .woocommerce-product-gallery__image');
        
        const newImages = [];
        oldImages.forEach((container, index) => {
            const img = container.querySelector('img');
            const link = container.querySelector('a');
            
            if (img && img.src && !img.src.includes('woocommerce-placeholder')) {
                newImages.push({
                    thumb: img.src,
                    full: link ? link.href : img.src,
                    alt: img.alt || `Image ${index + 1}`,
                    title: img.title || ''
                });
            }
        });
        
        // Mettre à jour seulement si les images ont changé
        if (JSON.stringify(newImages) !== JSON.stringify(this.images)) {
            this.images = newImages;
            this.currentIndex = 0;
            this.displayImage(0);
            this.createThumbnails();
            this.updateCounter();
        }
    }
    
    checkColorDot() {
        // Observer les changements pour détecter les badges couleur
        const observer = new MutationObserver(() => {
            this.updateColorDotClass();
        });
        
        // Observer le document pour les badges couleur qui peuvent apparaître
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Vérification initiale
        this.updateColorDotClass();
    }
    
    updateColorDotClass() {
        const colorDot = document.querySelector('.color-dot');
        if (colorDot && this.imageContainer) {
            this.imageContainer.classList.add('has-color-dot');
        } else if (this.imageContainer) {
            this.imageContainer.classList.remove('has-color-dot');
        }
    }
    
    repositionPromoBadges() {
        // Rechercher tous les badges promo
        const promoBadges = document.querySelectorAll('.onsale, .woocommerce-onsale, .sale-badge, .promo-badge, .promo-bubble-single.promo-bubble-quantity');
        
        promoBadges.forEach(badge => {
            if (badge && this.imageContainer) {
                // Forcer le repositionnement via JavaScript
                badge.style.setProperty('position', 'absolute', 'important');
                badge.style.setProperty('top', '10px', 'important');
                badge.style.setProperty('right', '60px', 'important');
                badge.style.setProperty('left', 'auto', 'important');
                badge.style.setProperty('z-index', '15', 'important');
                badge.style.setProperty('transform', 'none', 'important');
            }
        });
    }
    
    // Méthode de nettoyage
    destroy() {
        if (this.lightbox) {
            this.lightbox.remove();
        }
        document.body.style.overflow = '';
    }
}

// Ajouter la classe immédiatement pour masquer l'ancien système
if (document.querySelector('.woocommerce-product-gallery')) {
    document.body.classList.add('gallery-v2-active');
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.woocommerce-product-gallery')) {
        window.modernGallery = new ModernProductGallery();
    }
});


// Export pour les modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernProductGallery;
}