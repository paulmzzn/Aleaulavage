/**
 * Script de tracking côté client pour le système de comportement
 */

(function($) {
    'use strict';
    
    const ComportementClientTracker = {
        
        config: window.ComportementTracker || {},
        sessionStart: Date.now(),
        pageLoadTime: null,
        scrollDepth: 0,
        maxScrollDepth: 0,
        timeOnPage: 0,
        interactions: 0,
        
        init: function() {
            if (!this.config.events_enabled) return;
            
            this.trackPageLoad();
            this.bindEvents();
            this.startSessionTimer();
            this.trackScrollDepth();
        },
        
        trackPageLoad: function() {
            $(window).on('load', () => {
                this.pageLoadTime = Date.now() - this.sessionStart;
                
                this.trackEvent('page_load_complete', {
                    load_time_ms: this.pageLoadTime,
                    page_url: window.location.href,
                    referrer: document.referrer,
                    viewport: {
                        width: $(window).width(),
                        height: $(window).height()
                    }
                });
            });
        },
        
        bindEvents: function() {
            // Tracking des clics sur les liens
            $('a').on('click', (e) => {
                const $link = $(e.currentTarget);
                const href = $link.attr('href');
                const text = $link.text().trim();
                
                this.trackEvent('link_click', {
                    href: href,
                    text: text,
                    is_external: href && !href.startsWith(window.location.origin),
                    position: this.getElementPosition($link)
                });
            });
            
            // Tracking des clics sur les boutons
            $('button, .button, input[type="submit"]').on('click', (e) => {
                const $button = $(e.currentTarget);
                
                this.trackEvent('button_click', {
                    text: $button.text() || $button.val(),
                    class: $button.attr('class'),
                    id: $button.attr('id'),
                    type: $button.attr('type'),
                    position: this.getElementPosition($button)
                });
                
                this.interactions++;
            });
            
            // Tracking des interactions avec les formulaires
            $('form').on('submit', (e) => {
                const $form = $(e.currentTarget);
                
                this.trackEvent('form_submit', {
                    form_id: $form.attr('id'),
                    form_class: $form.attr('class'),
                    fields_count: $form.find('input, textarea, select').length,
                    action: $form.attr('action')
                });
            });
            
            // Tracking des champs de recherche
            $('input[type="search"], .search-field').on('focus', (e) => {
                this.trackEvent('search_focus', {
                    field_id: $(e.target).attr('id'),
                    placeholder: $(e.target).attr('placeholder')
                });
            });
            
            // Tracking du temps passé sur les champs
            let fieldFocusTime = {};
            $('input, textarea, select').on('focus', function() {
                const fieldId = $(this).attr('id') || $(this).attr('name') || 'unnamed';
                fieldFocusTime[fieldId] = Date.now();
            }).on('blur', function() {
                const fieldId = $(this).attr('id') || $(this).attr('name') || 'unnamed';
                if (fieldFocusTime[fieldId]) {
                    const timeSpent = Date.now() - fieldFocusTime[fieldId];
                    if (timeSpent > 1000) { // Plus d'1 seconde
                        ComportementClientTracker.trackEvent('field_interaction', {
                            field_id: fieldId,
                            time_spent_ms: timeSpent,
                            field_type: $(this).attr('type') || $(this).prop('tagName').toLowerCase()
                        });
                    }
                }
            });
            
            // Tracking des erreurs JavaScript
            window.addEventListener('error', (e) => {
                this.trackEvent('javascript_error', {
                    message: e.message,
                    filename: e.filename,
                    line: e.lineno,
                    column: e.colno,
                    stack: e.error ? e.error.stack : null
                });
            });
            
            // Tracking de la fermeture/quitter la page
            $(window).on('beforeunload', () => {
                this.trackPageExit();
            });
            
            // Tracking des événements WooCommerce spécifiques
            this.bindWooCommerceEvents();
        },
        
        bindWooCommerceEvents: function() {
            // Tracking des variations de produit
            $('.variations select').on('change', function() {
                const variation = $(this).val();
                const attribute = $(this).attr('name');
                
                ComportementClientTracker.trackEvent('product_variation_change', {
                    attribute: attribute,
                    value: variation,
                    product_id: $('[name="product_id"]').val()
                });
            });
            
            // Tracking des images produit
            $('.woocommerce-product-gallery__image').on('click', function() {
                const imageUrl = $(this).find('img').attr('src');
                const imageAlt = $(this).find('img').attr('alt');
                
                ComportementClientTracker.trackEvent('product_image_click', {
                    image_url: imageUrl,
                    image_alt: imageAlt,
                    product_id: $('[name="product_id"]').val()
                });
            });
            
            // Tracking des onglets produit
            $('.wc-tabs li a').on('click', function() {
                const tabId = $(this).attr('href');
                
                ComportementClientTracker.trackEvent('product_tab_click', {
                    tab_id: tabId,
                    tab_text: $(this).text(),
                    product_id: $('[name="product_id"]').val()
                });
            });
            
            // Tracking des filtres de boutique
            $('.widget_price_filter form').on('submit', function() {
                const minPrice = $(this).find('[name="min_price"]').val();
                const maxPrice = $(this).find('[name="max_price"]').val();
                
                ComportementClientTracker.trackEvent('price_filter_applied', {
                    min_price: minPrice,
                    max_price: maxPrice
                });
            });
        },
        
        trackScrollDepth: function() {
            $(window).on('scroll', $.throttle(250, () => {
                const scrollTop = $(window).scrollTop();
                const windowHeight = $(window).height();
                const documentHeight = $(document).height();
                
                this.scrollDepth = Math.round(((scrollTop + windowHeight) / documentHeight) * 100);
                
                if (this.scrollDepth > this.maxScrollDepth) {
                    this.maxScrollDepth = this.scrollDepth;
                    
                    // Tracker les jalons de scroll
                    if (this.maxScrollDepth >= 25 && !this.scrollMilestones?.quarter) {
                        this.trackEvent('scroll_milestone', { depth: 25 });
                        this.scrollMilestones = this.scrollMilestones || {};
                        this.scrollMilestones.quarter = true;
                    }
                    if (this.maxScrollDepth >= 50 && !this.scrollMilestones?.half) {
                        this.trackEvent('scroll_milestone', { depth: 50 });
                        this.scrollMilestones = this.scrollMilestones || {};
                        this.scrollMilestones.half = true;
                    }
                    if (this.maxScrollDepth >= 75 && !this.scrollMilestones?.threeQuarters) {
                        this.trackEvent('scroll_milestone', { depth: 75 });
                        this.scrollMilestones = this.scrollMilestones || {};
                        this.scrollMilestones.threeQuarters = true;
                    }
                    if (this.maxScrollDepth >= 90 && !this.scrollMilestones?.almost) {
                        this.trackEvent('scroll_milestone', { depth: 90 });
                        this.scrollMilestones = this.scrollMilestones || {};
                        this.scrollMilestones.almost = true;
                    }
                }
            }));
        },
        
        startSessionTimer: function() {
            // Mettre à jour le temps passé toutes les 10 secondes
            setInterval(() => {
                this.timeOnPage = Math.floor((Date.now() - this.sessionStart) / 1000);
            }, 10000);
            
            // Tracker l'engagement toutes les 30 secondes
            setInterval(() => {
                if (this.timeOnPage > 0) {
                    this.trackEvent('engagement_ping', {
                        time_on_page: this.timeOnPage,
                        interactions: this.interactions,
                        max_scroll_depth: this.maxScrollDepth
                    });
                }
            }, 30000);
        },
        
        trackPageExit: function() {
            this.trackEvent('page_exit', {
                time_on_page: Math.floor((Date.now() - this.sessionStart) / 1000),
                max_scroll_depth: this.maxScrollDepth,
                interactions: this.interactions,
                exit_type: 'beforeunload'
            }, false); // Synchronous call
        },
        
        trackEvent: function(eventType, eventData, async = true) {
            if (!this.config.ajax_url) return;
            
            const data = {
                action: 'track_comportement',
                nonce: this.config.nonce,
                event_type: eventType,
                event_data: JSON.stringify($.extend({
                    session_id: this.config.session_id,
                    user_id: this.config.user_id,
                    timestamp: Date.now(),
                    page_url: window.location.href,
                    user_agent: navigator.userAgent
                }, eventData))
            };
            
            $.ajax({
                url: this.config.ajax_url,
                type: 'POST',
                data: data,
                async: async,
                success: function(response) {
                    if (window.console && response && !response.success) {
                        console.warn('Tracking error:', response);
                    }
                },
                error: function(xhr, status, error) {
                    if (window.console) {
                        console.warn('Tracking failed:', error);
                    }
                }
            });
        },
        
        getElementPosition: function($element) {
            const offset = $element.offset();
            return {
                x: Math.round(offset.left),
                y: Math.round(offset.top),
                width: $element.outerWidth(),
                height: $element.outerHeight()
            };
        },
        
        // Méthodes publiques pour tracking manuel
        
        trackCustomEvent: function(eventType, eventData) {
            this.trackEvent(eventType, eventData);
        },
        
        trackConversion: function(conversionType, value, currency) {
            this.trackEvent('conversion', {
                conversion_type: conversionType,
                value: value,
                currency: currency || 'EUR'
            });
        },
        
        trackProductInteraction: function(productId, interactionType, details) {
            this.trackEvent('product_interaction', $.extend({
                product_id: productId,
                interaction_type: interactionType
            }, details));
        }
    };
    
    // Initialiser le tracking quand le DOM est prêt
    $(document).ready(function() {
        ComportementClientTracker.init();
        
        // Rendre le tracker disponible globalement
        window.ComportementClientTracker = ComportementClientTracker;
    });
    
    // Plugin jQuery throttle pour limiter les événements
    $.throttle = function(delay, fn) {
        let lastCall = 0;
        return function(...args) {
            const now = Date.now();
            if (now - lastCall >= delay) {
                lastCall = now;
                return fn.apply(this, args);
            }
        };
    };
    
})(jQuery);