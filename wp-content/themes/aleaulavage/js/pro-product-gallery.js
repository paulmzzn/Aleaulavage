/**
 * Système de galerie d'images professionnel pour e-commerce
 * Aleaulavage - Version Pro
 * 
 * Fonctionnalités :
 * - Zoom multi-niveaux fluide et responsive
 * - Navigation clavier et tactile
 * - Lightbox professionnel
 * - Lazy loading optimisé
 * - Support mobile complet
 * - Préchargement intelligent
 * - Gestion des images de différentes tailles
 */

class ProProductGallery {
    constructor() {
        this.gallery = null;
        this.mainImage = null;
        this.thumbnails = [];
        this.currentImageIndex = 0;
        this.isZooming = false;
        this.zoomLevel = 1;
        this.maxZoomLevel = 3;
        this.zoomStep = 0.5;
        this.isLightboxOpen = false;
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.images = [];
        
        this.init();
    }
    
    init() {
        this.findGalleryElements();
        if (!this.gallery) return;
        
        this.loadImages();
        this.setupMainImage();
        this.setupThumbnails();
        this.bindEvents();
        this.setupLightbox();
        this.optimizeForMobile();
    }
    
    findGalleryElements() {
        // Rechercher la galerie WooCommerce
        this.gallery = document.querySelector('.woocommerce-product-gallery, .product-gallery');
        if (this.gallery) {
            this.mainImage = this.gallery.querySelector('.woocommerce-product-gallery__image img, .main-product-image img');
            this.thumbnails = this.gallery.querySelectorAll('.flex-control-thumbs img, .thumbnails img');
        }
    }
    
    loadImages() {
        this.images = [];
        
        // Collecter toutes les images de la galerie
        const galleryImages = this.gallery.querySelectorAll('.woocommerce-product-gallery__image');
        galleryImages.forEach((container, index) => {
            const img = container.querySelector('img');
            const link = container.querySelector('a');
            
            if (img) {
                this.images.push({
                    thumb: img.src,
                    full: link ? link.href : img.src,
                    alt: img.alt || `Image produit ${index + 1}`,
                    title: img.title || '',
                    container: container
                });
            }
        });
        
        // Si pas d'images trouvées, essayer une approche alternative
        if (this.images.length === 0 && this.mainImage) {
            this.images.push({
                thumb: this.mainImage.src,
                full: this.mainImage.src,
                alt: this.mainImage.alt || 'Image produit',
                title: this.mainImage.title || '',
                container: this.mainImage.parentElement
            });
        }
    }
    
    setupMainImage() {
        if (!this.mainImage) return;
        
        // Créer le container principal moderne
        const mainContainer = document.createElement('div');
        mainContainer.className = 'pro-gallery-main';
        mainContainer.innerHTML = `
            <div class="pro-image-container">
                <div class="pro-image-wrapper">
                    <img class="pro-main-image" src="${this.images[0]?.full || ''}" alt="${this.images[0]?.alt || ''}">
                    <div class="zoom-overlay"></div>
                </div>
                <div class="gallery-controls">
                    <button class="gallery-btn prev-btn" title="Image précédente">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                    </button>
                    <button class="gallery-btn next-btn" title="Image suivante">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </button>
                    <button class="gallery-btn fullscreen-btn" title="Plein écran">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="m21,21l-6-6m6,6v-4.8m0,4.8h-4.8M3,3l6,6M3,3v4.8M3,3h4.8"></path>
                        </svg>
                    </button>
                </div>
                <div class="zoom-indicator">
                    <span class="zoom-level">1x</span>
                </div>
            </div>
        `;
        
        // Remplacer l'ancienne structure
        this.mainImage.parentElement.replaceWith(mainContainer);
        this.mainImage = mainContainer.querySelector('.pro-main-image');
        this.imageWrapper = mainContainer.querySelector('.pro-image-wrapper');
        this.zoomOverlay = mainContainer.querySelector('.zoom-overlay');
    }
    
    setupThumbnails() {
        if (this.images.length <= 1) return;
        
        const thumbContainer = document.createElement('div');
        thumbContainer.className = 'pro-thumbnails';
        thumbContainer.innerHTML = `
            <div class="thumbnails-scroll">
                ${this.images.map((img, index) => `
                    <button class="pro-thumb ${index === 0 ? 'active' : ''}" data-index="${index}">
                        <img src="${img.thumb}" alt="${img.alt}" loading="lazy">
                    </button>
                `).join('')}
            </div>
        `;
        
        // Insérer après l'image principale
        this.mainImage.closest('.pro-gallery-main').insertAdjacentElement('afterend', thumbContainer);
        this.thumbnailsContainer = thumbContainer;
    }
    
    bindEvents() {
        if (!this.mainImage) return;
        
        // Events de zoom sur l'image principale
        this.imageWrapper.addEventListener('mouseenter', () => this.startZoomMode());
        this.imageWrapper.addEventListener('mouseleave', () => this.endZoomMode());
        this.imageWrapper.addEventListener('mousemove', (e) => this.handleZoomMove(e));
        this.imageWrapper.addEventListener('click', (e) => this.handleImageClick(e));
        
        // Navigation par boutons
        const prevBtn = this.gallery.querySelector('.prev-btn');
        const nextBtn = this.gallery.querySelector('.next-btn');
        const fullscreenBtn = this.gallery.querySelector('.fullscreen-btn');
        
        if (prevBtn) prevBtn.addEventListener('click', () => this.previousImage());
        if (nextBtn) nextBtn.addEventListener('click', () => this.nextImage());
        if (fullscreenBtn) fullscreenBtn.addEventListener('click', () => this.openLightbox());
        
        // Thumbnails
        if (this.thumbnailsContainer) {
            this.thumbnailsContainer.addEventListener('click', (e) => {
                const thumb = e.target.closest('.pro-thumb');
                if (thumb) {
                    const index = parseInt(thumb.dataset.index);
                    this.goToImage(index);
                }
            });
        }
        
        // Navigation clavier
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
        
        // Touch events pour mobile
        this.setupTouchEvents();
        
        // Resize pour responsive
        window.addEventListener('resize', () => this.handleResize());
    }
    
    startZoomMode() {
        if (window.innerWidth < 768) return; // Pas de zoom hover sur mobile
        
        this.isZooming = true;
        this.imageWrapper.classList.add('zoom-mode');
        this.zoomOverlay.style.display = 'block';
        
        // Précharger l'image haute résolution
        if (this.images[this.currentImageIndex]) {
            const highRes = new Image();
            highRes.src = this.images[this.currentImageIndex].full;
        }
    }
    
    endZoomMode() {
        if (window.innerWidth < 768) return;
        
        this.isZooming = false;
        this.zoomLevel = 1;
        this.imageWrapper.classList.remove('zoom-mode');
        this.zoomOverlay.style.display = 'none';
        this.mainImage.style.transform = '';
        this.updateZoomIndicator();
    }
    
    handleZoomMove(e) {
        if (!this.isZooming || window.innerWidth < 768) return;
        
        const rect = this.imageWrapper.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width;
        const y = (e.clientY - rect.top) / rect.height;
        
        // Limiter aux bordures de l'image
        const clampedX = Math.max(0, Math.min(1, x));
        const clampedY = Math.max(0, Math.min(1, y));
        
        const zoomLevel = 2; // Zoom fixe pour le hover
        this.mainImage.style.transformOrigin = `${clampedX * 100}% ${clampedY * 100}%`;
        this.mainImage.style.transform = `scale(${zoomLevel})`;
        
        // Mise à jour de l'overlay
        this.updateZoomOverlay(clampedX, clampedY, zoomLevel);
    }
    
    handleImageClick(e) {
        e.preventDefault();
        
        if (window.innerWidth < 768) {
            // Sur mobile, clic = lightbox
            this.openLightbox();
        } else {
            // Sur desktop, zoom par niveaux
            this.zoomLevel = this.zoomLevel >= this.maxZoomLevel ? 1 : this.zoomLevel + this.zoomStep;
            
            if (this.zoomLevel === 1) {
                this.endZoomMode();
            } else {
                const rect = this.imageWrapper.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width;
                const y = (e.clientY - rect.top) / rect.height;
                
                this.mainImage.style.transformOrigin = `${x * 100}% ${y * 100}%`;
                this.mainImage.style.transform = `scale(${this.zoomLevel})`;
                this.imageWrapper.classList.add('zoom-active');
            }
        }
        
        this.updateZoomIndicator();
    }
    
    updateZoomOverlay(x, y, zoom) {
        const size = 100 / zoom;
        const left = Math.max(0, Math.min(100 - size, x * 100 - size / 2));
        const top = Math.max(0, Math.min(100 - size, y * 100 - size / 2));
        
        this.zoomOverlay.style.background = `
            linear-gradient(
                rgba(0,0,0,0.3),
                rgba(0,0,0,0.3)
            ),
            radial-gradient(
                circle at ${x * 100}% ${y * 100}%,
                transparent ${size/2}%,
                rgba(0,0,0,0.5) ${size/2}%
            )
        `;
    }
    
    updateZoomIndicator() {
        const indicator = this.gallery.querySelector('.zoom-level');
        if (indicator) {
            indicator.textContent = `${this.zoomLevel}x`;
            indicator.style.opacity = this.zoomLevel > 1 ? '1' : '0';
        }
    }
    
    goToImage(index) {
        if (index < 0 || index >= this.images.length || index === this.currentImageIndex) return;
        
        this.currentImageIndex = index;
        const newImage = this.images[index];
        
        // Animation de transition
        this.mainImage.style.opacity = '0.5';
        
        setTimeout(() => {
            this.mainImage.src = newImage.full;
            this.mainImage.alt = newImage.alt;
            this.mainImage.style.opacity = '1';
        }, 150);
        
        // Mise à jour des thumbnails
        this.updateThumbnails();
        
        // Reset du zoom
        this.endZoomMode();
    }
    
    previousImage() {
        const newIndex = this.currentImageIndex === 0 ? this.images.length - 1 : this.currentImageIndex - 1;
        this.goToImage(newIndex);
    }
    
    nextImage() {
        const newIndex = this.currentImageIndex === this.images.length - 1 ? 0 : this.currentImageIndex + 1;
        this.goToImage(newIndex);
    }
    
    updateThumbnails() {
        if (!this.thumbnailsContainer) return;
        
        const thumbs = this.thumbnailsContainer.querySelectorAll('.pro-thumb');
        thumbs.forEach((thumb, index) => {
            thumb.classList.toggle('active', index === this.currentImageIndex);
        });
        
        // Faire défiler pour montrer le thumbnail actif
        const activeThumb = thumbs[this.currentImageIndex];
        if (activeThumb) {
            activeThumb.scrollIntoView({ behavior: 'smooth', inline: 'center' });
        }
    }
    
    setupTouchEvents() {
        this.imageWrapper.addEventListener('touchstart', (e) => {
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
        }, { passive: true });
        
        this.imageWrapper.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            const deltaX = touchEndX - this.touchStartX;
            const deltaY = touchEndY - this.touchStartY;
            
            // Swipe horizontal pour navigation
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                if (deltaX > 0) {
                    this.previousImage();
                } else {
                    this.nextImage();
                }
            }
        }, { passive: true });
    }
    
    setupLightbox() {
        const lightbox = document.createElement('div');
        lightbox.className = 'pro-lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-backdrop"></div>
            <div class="lightbox-content">
                <button class="lightbox-close" title="Fermer">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                <img class="lightbox-image" src="" alt="">
                <div class="lightbox-nav">
                    <button class="lightbox-prev" title="Précédent">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                    </button>
                    <button class="lightbox-next" title="Suivant">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </button>
                </div>
                <div class="lightbox-counter">
                    <span class="current">1</span> / <span class="total">${this.images.length}</span>
                </div>
            </div>
        `;
        
        document.body.appendChild(lightbox);
        this.lightbox = lightbox;
        
        // Events lightbox
        lightbox.querySelector('.lightbox-close').addEventListener('click', () => this.closeLightbox());
        lightbox.querySelector('.lightbox-backdrop').addEventListener('click', () => this.closeLightbox());
        lightbox.querySelector('.lightbox-prev').addEventListener('click', () => this.lightboxPrev());
        lightbox.querySelector('.lightbox-next').addEventListener('click', () => this.lightboxNext());
    }
    
    openLightbox() {
        if (!this.lightbox) return;
        
        this.isLightboxOpen = true;
        const lightboxImage = this.lightbox.querySelector('.lightbox-image');
        const counter = this.lightbox.querySelector('.lightbox-counter .current');
        
        lightboxImage.src = this.images[this.currentImageIndex].full;
        lightboxImage.alt = this.images[this.currentImageIndex].alt;
        counter.textContent = this.currentImageIndex + 1;
        
        this.lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    closeLightbox() {
        if (!this.lightbox) return;
        
        this.isLightboxOpen = false;
        this.lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    lightboxPrev() {
        this.previousImage();
        this.openLightbox(); // Refresh lightbox avec nouvelle image
    }
    
    lightboxNext() {
        this.nextImage();
        this.openLightbox(); // Refresh lightbox avec nouvelle image
    }
    
    handleKeyDown(e) {
        if (this.isLightboxOpen) {
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
        } else if (this.gallery?.matches(':hover')) {
            switch(e.key) {
                case 'ArrowLeft':
                    this.previousImage();
                    break;
                case 'ArrowRight':
                    this.nextImage();
                    break;
            }
        }
    }
    
    optimizeForMobile() {
        if (window.innerWidth < 768) {
            // Optimisations mobile
            this.imageWrapper.style.cursor = 'pointer';
            
            // Masquer les contrôles hover
            const controls = this.gallery.querySelector('.gallery-controls');
            if (controls) {
                controls.style.opacity = '1';
            }
        }
    }
    
    handleResize() {
        this.optimizeForMobile();
        if (this.isZooming && window.innerWidth < 768) {
            this.endZoomMode();
        }
    }
    
    // Méthodes publiques pour l'API
    destroy() {
        // Nettoyer les event listeners et DOM
        if (this.lightbox) {
            this.lightbox.remove();
        }
        
        document.removeEventListener('keydown', this.handleKeyDown);
        window.removeEventListener('resize', this.handleResize);
    }
    
    refresh() {
        this.destroy();
        this.init();
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    // Attendre que WooCommerce soit chargé
    setTimeout(() => {
        window.proGallery = new ProProductGallery();
    }, 500);
});

// Réinitialiser après changement de variation
document.addEventListener('woocommerce_variation_has_changed', function() {
    setTimeout(() => {
        if (window.proGallery) {
            window.proGallery.refresh();
        }
    }, 200);
});

// Export pour utilisation externe
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProProductGallery;
}