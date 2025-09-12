/**
 * Système de Navigation SPA (Single Page Application)
 * Aleaulavage - Optimisation Performance
 * 
 * Fonctionnalités :
 * - Navigation AJAX sans rechargement complet
 * - Conservation du header/footer
 * - Gestion de l'historique navigateur
 * - Cache des pages visitées
 * - Loading states élégants
 */

class SPANavigation {
    constructor() {
        this.cache = new Map();
        this.currentUrl = window.location.href;
        this.isLoading = false;
        this.contentSelector = '#main, main, #primary, .site-main, #content';
        this.titleSelector = 'title';
        
        // Configuration
        this.config = {
            timeout: 10000,
            cacheLimit: 20,
            preloadDelay: 100,
            animationDuration: 300
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupLoadingIndicator();
        this.preloadCurrentPage();
    }
    
    bindEvents() {
        // Intercepter les clics sur les liens internes SEULEMENT
        document.addEventListener('click', (e) => {
            // IMPORTANT : Vérifier que c'est bien un clic sur un lien
            const link = e.target.closest('a');
            
            // Si ce n'est pas un lien, ne rien faire
            if (!link) {
                return;
            }
            
            // Si c'est un lien valide pour SPA, l'intercepter
            if (this.shouldInterceptLink(link, e)) {
                e.preventDefault();
                e.stopPropagation();
                //console.log('🔗 SPA intercepting link:', link.href);
                this.navigateTo(link.href);
            }
        });
        
        // Gérer les boutons retour/suivant du navigateur
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.spa) {
                this.loadPage(window.location.href, false);
            }
        });
        
        // Preload au hover sur les liens
        document.addEventListener('mouseover', (e) => {
            const link = e.target.closest('a');
            if (this.shouldPreloadLink(link)) {
                setTimeout(() => this.preloadPage(link.href), this.config.preloadDelay);
            }
        });
    }
    
    shouldInterceptLink(link, event) {
        // Vérifications de base strictes
        if (!link || !link.href || link.href === '') return false;
        if (!link.getAttribute('href')) return false; // Pas d'attribut href
        
        //console.log('🔍 Checking link:', link.href, 'target:', link.target);
        
        // Touches modificatrices
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return false;
        
        // Clic droit ou clic molette
        if (event.button !== 0) return false;
        
        // Target externe
        if (link.target && link.target !== '_self' && link.target !== '') return false;
        
        // Téléchargements
        if (link.download) return false;
        
        // Ancres sur la même page
        if (link.href.includes('#') && link.href.split('#')[0] === window.location.href.split('#')[0]) return false;
        
        // Exclure certains liens (plus restrictif)
        const excludeSelectors = [
            '.no-spa',
            '[href*="/wp-admin"]',
            '[href*="/wp-login"]',
            '[href*="wp-login.php"]',
            '[href*="mailto:"]',
            '[href*="tel:"]',
            '[href^="#"]',
            '.wp-admin',
            '.logout',
            '[href=""]',
            '[href="#"]',
            '.btn[data-bs-toggle]', // Boutons Bootstrap
            '[data-bs-toggle]',
            '.offcanvas',
            '[href*="javascript:"]'
        ];
        
        for (const selector of excludeSelectors) {
            if (link.matches(selector)) {
                //console.log('❌ Link excluded by selector:', selector);
                return false;
            }
        }
        
        try {
            // Vérifier si c'est un lien interne
            const linkUrl = new URL(link.href);
            const currentUrl = new URL(window.location.href);
            
            if (linkUrl.origin !== currentUrl.origin) {
                //console.log('❌ External link detected');
                return false;
            }
            
            //console.log('✅ Link approved for SPA');
            return true;
            
        } catch (error) {
            //console.log('❌ Invalid URL:', error);
            return false;
        }
    }
    
    shouldPreloadLink(link) {
        return this.shouldInterceptLink(link, {}) && !this.cache.has(link.href);
    }
    
    setupLoadingIndicator() {
        // Créer la barre de progression
        const progressBar = document.createElement('div');
        progressBar.id = 'spa-progress';
        progressBar.innerHTML = `
            <div class="spa-progress-bar"></div>
        `;
        
        const styles = `
            #spa-progress {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 3px;
                z-index: 9999;
                background: rgba(0,0,0,0.1);
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            #spa-progress.active {
                opacity: 1;
            }
            .spa-progress-bar {
                height: 100%;
                background: linear-gradient(90deg, #2A3E6A, #f1bb69);
                width: 0%;
                transition: width 0.3s ease;
            }
            .spa-loading {
                pointer-events: none;
                opacity: 0.7;
                transition: opacity 0.3s ease;
            }
        `;
        
        // Ajouter les styles
        if (!document.getElementById('spa-styles')) {
            const styleSheet = document.createElement('style');
            styleSheet.id = 'spa-styles';
            styleSheet.textContent = styles;
            document.head.appendChild(styleSheet);
        }
        
        document.body.appendChild(progressBar);
        this.progressBar = progressBar;
    }
    
    showLoading() {
        if (this.progressBar) {
            this.progressBar.classList.add('active');
            const bar = this.progressBar.querySelector('.spa-progress-bar');
            bar.style.width = '30%';
            
            // Animation progressive
            setTimeout(() => bar.style.width = '60%', 200);
            setTimeout(() => bar.style.width = '80%', 500);
        }
        
        // Ajouter classe loading au body
        document.body.classList.add('spa-loading');
    }
    
    hideLoading() {
        if (this.progressBar) {
            const bar = this.progressBar.querySelector('.spa-progress-bar');
            bar.style.width = '100%';
            
            setTimeout(() => {
                this.progressBar.classList.remove('active');
                bar.style.width = '0%';
            }, 200);
        }
        
        document.body.classList.remove('spa-loading');
    }
    
    async navigateTo(url) {
        if (this.isLoading || url === this.currentUrl) return;
        
        //console.log(`🧭 Navigating to: ${url}`);
        this.targetUrl = url; // Stocker l'URL cible pour les fallbacks
        
        try {
            await this.loadPage(url, true);
            this.currentUrl = url;
        } catch (error) {
            //console.error('Navigation error:', error);
            // Fallback vers navigation classique vers l'URL cible
            window.location.href = url;
        }
    }
    
    async loadPage(url, pushState = true) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.targetUrl = url; // Stocker l'URL cible pour les fallbacks
        this.showLoading();
        
        try {
            // Récupérer le contenu (cache ou réseau)
            const content = await this.fetchPageContent(url);
            
            // Vérifier que le contenu n'est pas vide
            if (!content || !content.content || content.content.trim().length < 100) {
                //console.warn('⚠️ Content seems empty or too short, forcing page reload');
                window.location.href = url;
                return;
            }
            
            // Mettre à jour la page
            await this.updatePage(content, url, pushState);
            
            // Déclencher les événements post-navigation
            this.triggerPostNavigationEvents(url);
            
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }
    
    async fetchPageContent(url) {
        // Vérifier le cache
        if (this.cache.has(url)) {
            //console.log(`📦 Loading from cache: ${url}`);
            return this.cache.get(url);
        }
        
        //console.log(`🌐 Fetching via AJAX: ${url}`);
        
        try {
            // Utiliser l'endpoint AJAX WordPress pour une meilleure compatibilité
            const formData = new FormData();
            formData.append('action', 'spa_get_page');
            formData.append('url', url);
            formData.append('nonce', spaConfig.nonce);
            
            const response = await fetch(spaConfig.ajaxUrl, {
                method: 'POST',
                body: formData,
                signal: AbortSignal.timeout(this.config.timeout)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.data || 'Erreur AJAX');
            }
            
            const content = {
                content: data.data.content,
                title: data.data.title,
                bodyClasses: document.body.className, // Conserver les classes actuelles
                metaTags: [] // Les meta tags seront gérés côté serveur
            };
            
            // Mettre en cache
            this.addToCache(url, content);
            
            return content;
            
        } catch (error) {
            // Fallback vers fetch classique si AJAX échoue
            //console.warn('🔄 AJAX failed, falling back to fetch:', error);
            return this.fetchPageContentFallback(url);
        }
    }
    
    async fetchPageContentFallback(url) {
        //console.log(`🌐 Fallback fetch: ${url}`);
        
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);
        
        try {
            const response = await fetch(url, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                }
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const html = await response.text();
            const content = this.parsePageContent(html);
            
            // Mettre en cache
            this.addToCache(url, content);
            
            return content;
            
        } catch (error) {
            clearTimeout(timeoutId);
            throw error;
        }
    }
    
    parsePageContent(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extraire les éléments nécessaires
        const content = doc.querySelector(this.contentSelector);
        const title = doc.querySelector(this.titleSelector);
        const bodyClasses = doc.body.className;
        
        // Extraire les meta tags importants
        const metaTags = Array.from(doc.querySelectorAll('meta[name="description"], meta[property^="og:"], meta[name^="twitter:"]'));
        
        return {
            content: content ? content.outerHTML : '',
            title: title ? title.textContent : '',
            bodyClasses,
            metaTags: metaTags.map(tag => tag.outerHTML)
        };
    }
    
    async updatePage(pageData, url, pushState) {
        // Mettre à jour le titre
        if (pageData.title) {
            document.title = pageData.title;
        }
        
        // Mettre à jour les classes du body
        if (pageData.bodyClasses) {
            document.body.className = pageData.bodyClasses;
        }
        
        // Mettre à jour les meta tags
        this.updateMetaTags(pageData.metaTags);
        
        // Mettre à jour le contenu principal avec animation
        await this.updateMainContent(pageData.content);
        
        // Mettre à jour l'URL
        if (pushState) {
            history.pushState({ spa: true, url }, pageData.title, url);
        }
        
        // Scroll vers le haut et restaurer la capacité de scroll
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    updateMetaTags(metaTags) {
        // Supprimer les anciens meta tags dynamiques
        const oldMetas = document.querySelectorAll('meta[data-spa-dynamic]');
        oldMetas.forEach(meta => meta.remove());
        
        // Ajouter les nouveaux
        metaTags.forEach(tagHTML => {
            const temp = document.createElement('div');
            temp.innerHTML = tagHTML;
            const meta = temp.firstElementChild;
            if (meta) {
                meta.setAttribute('data-spa-dynamic', 'true');
                document.head.appendChild(meta);
            }
        });
    }
    
    findMainContentElement() {
        // Sélecteurs spécifiques selon le type de page
        const selectors = [
            // WooCommerce (priorité haute)
            '#main.site-main',
            'main#main',
            '#main',
            'main',
            
            // Pages génériques
            '#primary',
            '.site-main',
            '#content',
            
            // Fallbacks
            '.main-content',
            '.content-area',
            'article',
            '.entry-content'
        ];
        
        for (const selector of selectors) {
            const element = document.querySelector(selector);
            if (element && element.offsetHeight > 0) { // Vérifier que l'élément est visible
                //console.log(`📍 Content found with selector: ${selector}`);
                return element;
            }
        }
        
        return null;
    }
    
    findMainContentInHTML(containerElement) {
        // Mêmes sélecteurs que pour la page actuelle
        const selectors = [
            '#main.site-main',
            'main#main', 
            '#main',
            'main',
            '#primary',
            '.site-main',
            '#content',
            '.main-content',
            '.content-area'
        ];
        
        for (const selector of selectors) {
            const element = containerElement.querySelector(selector);
            if (element) {
                //console.log(`📍 New content found with selector: ${selector}`);
                return element;
            }
        }
        
        return null;
    }

    async updateMainContent(newContent) {
        let mainElement = this.findMainContentElement();
        
        if (!mainElement) {
            //console.error('❌ No main content element found, falling back to page reload');
            // NE JAMAIS remplacer tout le body car cela supprime header/footer
            // Utiliser un rechargement complet vers l'URL cible stockée
            const fallbackUrl = this.targetUrl || window.location.href;
            //console.log('🔄 Redirecting to:', fallbackUrl);
            window.location.href = fallbackUrl;
            return;
        }
        
        // Animation de sortie
        mainElement.style.opacity = '0';
        mainElement.style.transform = 'translateY(20px)';
        mainElement.style.transition = `opacity ${this.config.animationDuration}ms ease, transform ${this.config.animationDuration}ms ease`;
        
        await new Promise(resolve => setTimeout(resolve, this.config.animationDuration));
        
        // Parser le nouveau contenu pour extraire seulement la partie principale
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newContent;
        
        // Utiliser la même fonction pour trouver le contenu dans la réponse
        let newMainContent = this.findMainContentInHTML(tempDiv);
        
        if (newMainContent) {
            // Remplacer le contenu avec le bon élément
            mainElement.outerHTML = newMainContent.outerHTML;
        } else {
            //console.warn('⚠️ Could not find main content in response, using innerHTML');
            // Dernier recours : remplacer seulement le innerHTML
            mainElement.innerHTML = newContent;
        }
        
        // Animation d'entrée
        const newMainElement = document.querySelector(this.contentSelector);
        if (newMainElement) {
            newMainElement.style.opacity = '0';
            newMainElement.style.transform = 'translateY(20px)';
            newMainElement.style.transition = `opacity ${this.config.animationDuration}ms ease, transform ${this.config.animationDuration}ms ease`;
            
            // Déclencher l'animation
            requestAnimationFrame(() => {
                newMainElement.style.opacity = '1';
                newMainElement.style.transform = 'translateY(0)';
                
                // Nettoyer les styles de transition après l'animation
                setTimeout(() => {
                    newMainElement.style.transition = '';
                }, this.config.animationDuration + 100);
            });
        }
    }
    
    triggerPostNavigationEvents(url) {
        // Réinitialiser les scripts spécifiques aux pages
        this.reinitializePageScripts();
        
        // Charger les styles spécifiques selon le type de page
        this.loadPageSpecificStyles(url);
        
        // S'assurer que le scroll fonctionne
        this.ensurePageScrollable();
        
        // Déclencher un événement personnalisé
        const event = new CustomEvent('spaNavigated', {
            detail: { url, timestamp: Date.now() }
        });
        document.dispatchEvent(event);
        
        // Google Analytics
        if (typeof gtag !== 'undefined') {
            gtag('config', 'AW-10813983195', {
                page_path: new URL(url).pathname
            });
        }
        
        //console.log(`✅ Page loaded: ${url}`);
    }
    
    loadPageSpecificStyles(url) {
        // Détecter le type de page à partir de l'URL et du contenu
        const isProductPage = this.isProductPageUrl(url) || document.querySelector('.product-container, .single-product, .woocommerce-product-gallery');
        
        if (isProductPage) {
            //console.log('🎨 Loading product page styles');
            this.loadProductPageStyles();
        } else {
            // Supprimer les styles produit si on quitte une page produit
            this.removeProductPageStyles();
        }
    }
    
    isProductPageUrl(url) {
        try {
            const urlObj = new URL(url);
            const path = urlObj.pathname;
            
            // Patterns courants pour les pages produits WooCommerce
            return path.includes('/product/') || 
                   path.includes('/produit/') ||
                   path.match(/\/[^\/]+\/?$/) && !path.match(/\/(category|cart|checkout|my-account|shop|contact)/);
        } catch (error) {
            return false;
        }
    }
    
    loadProductPageStyles() {
        // Vérifier si les styles produit sont déjà chargés
        if (document.getElementById('spa-product-styles')) {
            return;
        }
        
        // Charger le CSS produit
        const link = document.createElement('link');
        link.id = 'spa-product-styles';
        link.rel = 'stylesheet';
        link.href = spaConfig.themeUrl + '/css/single-product-style.css?v=' + Date.now();
        document.head.appendChild(link);
        
        // Ajouter les styles inline critiques pour les pages produits
        const inlineStyles = document.createElement('style');
        inlineStyles.id = 'spa-product-inline-styles';
        inlineStyles.textContent = `
            .woocommerce:where(body:not(.woocommerce-uses-block-theme)) div.product p.price,
            .woocommerce:where(body:not(.woocommerce-uses-block-theme)) div.product span.price {
                color: #0E2141 !important;
            }
            
            /* Force le chargement correct des styles produits */
            .product-container {
                display: grid !important;
                grid-template-columns: 1fr 360px !important;
                gap: 30px !important;
            }
            
            .product-gallery {
                background: #fff !important;
                border: 1px solid #e3e5e8 !important;
                border-radius: 8px !important;
                padding: 20px !important;
            }
            
            .product-purchase-card {
                background: #fff !important;
                border: 1px solid #e3e5e8 !important;
                border-radius: 8px !important;
                box-shadow: 0 2px 6px rgba(0,0,0,0.05) !important;
                padding: 20px 18px 18px 18px !important;
            }
            
            /* S'assurer que les images sont visibles */
            .woocommerce-product-gallery__image img {
                display: block !important;
                max-width: 100% !important;
                height: auto !important;
                margin: 0 auto !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* Forcer l'affichage de la galerie */
            .woocommerce-product-gallery {
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* S'assurer que les conteneurs d'images sont visibles */
            .woocommerce-product-gallery__wrapper,
            .woocommerce-product-gallery__image {
                opacity: 1 !important;
                visibility: visible !important;
                display: block !important;
            }
        `;
        document.head.appendChild(inlineStyles);
        
        //console.log('✅ Product styles loaded via SPA');
    }
    
    removeProductPageStyles() {
        // Supprimer les styles produit si présents
        const productCSS = document.getElementById('spa-product-styles');
        const productInlineCSS = document.getElementById('spa-product-inline-styles');
        
        if (productCSS) {
            productCSS.remove();
            //console.log('🗑️ Product styles removed');
        }
        
        if (productInlineCSS) {
            productInlineCSS.remove();
        }
    }
    
    ensurePageScrollable() {
        // S'assurer que la page est scrollable
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        document.body.style.height = '';
        document.documentElement.style.height = '';
        
        // Forcer un reflow pour actualiser le layout
        document.body.offsetHeight;
        
        // Vérifier que le contenu principal a une hauteur correcte
        const mainElement = this.findMainContentElement();
        if (mainElement) {
            mainElement.style.minHeight = '';
            mainElement.style.height = '';
        }
    }
    
    reinitializePageScripts() {
        // Réinitialiser WooCommerce si nécessaire
        if (typeof wc_add_to_cart_params !== 'undefined') {
            jQuery(document.body).trigger('wc_fragment_refresh');
        }
        
        // Réinitialiser les galeries de produits WooCommerce
        this.reinitializeProductGallery();
        
        // Réinitialiser le zoom personnalisé des images produits
        this.reinitializeProductZoom();
        
        // Réinitialiser les icônes Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Réinitialiser les tooltips Bootstrap
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }
    
    reinitializeProductGallery() {
        // Chercher les galeries de produits
        const galleries = document.querySelectorAll('.woocommerce-product-gallery');
        
        galleries.forEach(gallery => {
            // 1. Rendre la galerie visible (elle a opacity: 0 par défaut)
            gallery.style.opacity = '1';
            gallery.style.transition = 'opacity .25s ease-in-out';
            
            // 2. Réinitialiser les galeries WooCommerce si jQuery est disponible
            if (typeof jQuery !== 'undefined' && jQuery.fn.wc_product_gallery) {
                try {
                    jQuery(gallery).wc_product_gallery();
                    //console.log('✅ WooCommerce gallery reinitialized');
                } catch (error) {
                    //console.log('⚠️ WooCommerce gallery init failed:', error);
                }
            }
            
            // 3. S'assurer que les images sont visibles
            const images = gallery.querySelectorAll('.woocommerce-product-gallery__image img');
            images.forEach(img => {
                img.style.opacity = '1';
                img.style.display = 'block';
            });
        });
        
        // Déclencher l'événement de chargement d'images pour les plugins/scripts
        if (typeof jQuery !== 'undefined') {
            jQuery(document.body).trigger('wc-product-gallery-before-single-product-lightbox');
        }
    }
    
    reinitializeProductZoom() {
        // Réinitialiser le zoom personnalisé (copié de single-product-zoom.js)
        const gallery = document.querySelector('.product-gallery .woocommerce-product-gallery__image');
        if (!gallery) return;
        
        const img = gallery.querySelector('img');
        if (!img) return;
        
        // Nettoyer les anciens listeners
        gallery.classList.remove('zoom-follow');
        const oldListeners = gallery.cloneNode(true);
        gallery.parentNode.replaceChild(oldListeners, gallery);
        
        // Réappliquer le zoom
        const newGallery = document.querySelector('.product-gallery .woocommerce-product-gallery__image');
        const newImg = newGallery.querySelector('img');
        
        newGallery.classList.add('zoom-follow');
        const scale = 1.6;
        let isZooming = false;

        function onMouseMove(e) {
            const rect = newGallery.getBoundingClientRect();
            const imgRect = newImg.getBoundingClientRect();
            const x = Math.max(0, Math.min(1, (e.clientX - imgRect.left) / imgRect.width));
            const y = Math.max(0, Math.min(1, (e.clientY - imgRect.top) / imgRect.height));
            const tx = ((x - 0.5) * (imgRect.width * (scale - 1)));
            const ty = ((y - 0.5) * (imgRect.height * (scale - 1)));
            
            newImg.style.transform = 'scale(' + scale + ') translate(' + (-tx/scale) + 'px,' + (-ty/scale) + 'px)';
            newImg.classList.add('is-zooming');
            newGallery.classList.add('zooming');
            isZooming = true;
        }
        
        function onMouseLeave() {
            newImg.style.transform = '';
            newImg.classList.remove('is-zooming');
            newGallery.classList.remove('zooming');
            isZooming = false;
        }
        
        newGallery.addEventListener('mousemove', onMouseMove);
        newGallery.addEventListener('mouseleave', onMouseLeave);
        
        //console.log('✅ Product zoom reinitialized');
    }
    
    preloadCurrentPage() {
        const currentUrl = window.location.href;
        if (!this.cache.has(currentUrl)) {
            const content = this.parsePageContent(document.documentElement.outerHTML);
            this.addToCache(currentUrl, content);
        }
    }
    
    async preloadPage(url) {
        if (this.cache.has(url) || this.isLoading) return;
        
        try {
            await this.fetchPageContent(url);
            //console.log(`🔮 Preloaded: ${url}`);
        } catch (error) {
            //console.warn(`Failed to preload ${url}:`, error);
        }
    }
    
    addToCache(url, content) {
        // Limiter la taille du cache
        if (this.cache.size >= this.config.cacheLimit) {
            const firstKey = this.cache.keys().next().value;
            this.cache.delete(firstKey);
        }
        
        this.cache.set(url, {
            ...content,
            timestamp: Date.now()
        });
    }
    
    clearCache() {
        this.cache.clear();
        //console.log('🗑️ Cache cleared');
    }
    
    getCacheStats() {
        return {
            size: this.cache.size,
            urls: Array.from(this.cache.keys()),
            totalSize: JSON.stringify([...this.cache.values()]).length
        };
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si on doit activer la SPA
    const shouldActivate = !document.body.classList.contains('no-spa') && 
                          !window.location.pathname.includes('/wp-admin') &&
                          !window.location.search.includes('no-spa=1');
    
    if (shouldActivate) {
        window.spaNavigation = new SPANavigation();
        
        // Debug en développement
        if (window.location.hostname === 'localhost' || window.location.search.includes('debug=1') || true) {
            window.spaDebug = () => {
                //console.log('SPA Cache Stats:', window.spaNavigation.getCacheStats());
                //console.log('Current content selector:', window.spaNavigation.contentSelector);
                //console.log('Current main element:', document.querySelector(window.spaNavigation.contentSelector));
            };
            
            window.spaTest = (url) => {
                //console.log('🧪 Testing navigation to:', url);
                window.spaNavigation.navigateTo(url);
            };
            
            window.spaForceReload = () => {
                //console.log('🔄 Forcing page reload...');
                window.location.reload();
            };
            
            window.spaDisable = () => {
                //console.log('🚫 Disabling SPA temporarily...');
                window.location.href = window.location.href + (window.location.search ? '&' : '?') + 'no-spa=1';
            };
            
            // Ajouter un indicateur visuel du debug
            //console.log('🎯 SPA Debug Mode Active - Use spaDebug(), spaDisable(), etc.');
        }
    }
});