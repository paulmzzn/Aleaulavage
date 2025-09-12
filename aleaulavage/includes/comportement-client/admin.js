/**
 * JavaScript Premium pour Comportement Client
 * Interface moderne et interactive
 */

(function($) {
    'use strict';

    // Namespace pour éviter les conflits
    window.ComportementClientAdmin = {
        init: function() {
            this.bindEvents();
            this.initAnimations();
            this.setupAutoRefresh();
            this.initTooltips();
            this.initCounters();
        },

        bindEvents: function() {
            // Boutons de refresh premium
            $(document).on('click', '.cc-btn-refresh', this.handleRefresh);
            
            // Exports CSV améliorés
            $(document).on('click', '.cc-btn-export', this.handleExport);
            
            // Filtres interactifs
            $(document).on('click', '.cc-filter-btn', this.handleFilter);
            
            // Recherche en temps réel
            $(document).on('input', '.cc-search-input', this.handleSearch);
            
            // Actions diverses
            $(document).on('click', '.copy-to-clipboard', this.copyToClipboard);
            $(document).on('click', '.delete-action', this.confirmDelete);
        },

        handleRefresh: function(e) {
            e.preventDefault();
            var $btn = $(this);
            var originalText = $btn.text();
            var originalHtml = $btn.html();
            
            // Animation de loading
            $btn.addClass('cc-btn-loading')
                .html('<span class="cc-refresh-icon">🔄</span> Actualisation...');
            
            // Simuler le refresh avec transition
            setTimeout(function() {
                ComportementClientAdmin.refreshPageData($btn, originalHtml);
            }, 1000);
        },

        refreshPageData: function($btn, originalHtml) {
            // Déterminer le type de page pour le refresh approprié
            var currentPage = window.location.href;
            var action = 'refresh_dashboard_stats';
            
            if (currentPage.includes('paniers')) {
                action = 'refresh_paniers_data';
            } else if (currentPage.includes('recherches')) {
                action = 'refresh_recherches_data';
            } else if (currentPage.includes('historique')) {
                action = 'refresh_historique_data';
            }
            
            $.post(comportementClientAjax.ajax_url, {
                action: action,
                nonce: comportementClientAjax.nonce
            })
            .done(function(response) {
                if (response.success) {
                    ComportementClientAdmin.updatePageContent(response.data);
                    ComportementClientAdmin.showToast('Données actualisées avec succès!', 'success');
                } else {
                    ComportementClientAdmin.showToast('Erreur lors de l\'actualisation', 'error');
                }
            })
            .fail(function() {
                ComportementClientAdmin.showToast('Erreur de connexion', 'error');
            })
            .always(function() {
                $btn.removeClass('cc-btn-loading').html(originalHtml);
            });
        },

        updatePageContent: function(data) {
            // Mettre à jour les compteurs avec animation
            Object.keys(data).forEach(function(key) {
                var $counter = $('#' + key + '-counter');
                if ($counter.length) {
                    ComportementClientAdmin.animateCounter($counter, data[key]);
                }
            });
            
            // Recharger la page pour les données complexes
            if (data.reload) {
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        },

        animateCounter: function($element, targetValue) {
            // Animation désactivée - affichage direct de la valeur
            $element.text(targetValue);
        },

        handleExport: function(e) {
            e.preventDefault();
            var $btn = $(this);
            var exportType = $btn.data('export-type');
            var originalHtml = $btn.html();
            
            $btn.html('⏳ Export...').prop('disabled', true);
            
            // Créer un lien de téléchargement temporaire
            var downloadUrl = comportementClientAjax.ajax_url + 
                '?action=export_' + exportType + '_csv&nonce=' + comportementClientAjax.nonce;
            
            var $link = $('<a>', {
                href: downloadUrl,
                download: exportType + '_export.csv',
                style: 'display: none'
            }).appendTo('body');
            
            $link[0].click();
            $link.remove();
            
            setTimeout(function() {
                $btn.html(originalHtml).prop('disabled', false);
                ComportementClientAdmin.showToast('Export terminé!', 'success');
            }, 2000);
        },

        handleFilter: function(e) {
            e.preventDefault();
            var $btn = $(this);
            var filterValue = $btn.data('device');
            
            // Animation de transition
            $btn.siblings().removeClass('active');
            $btn.addClass('active');
            
            // Filtrage avec animation
            var $items = $('.cc-table tr[data-device], [data-device]');
            
            $items.fadeOut(200, function() {
                if (filterValue === 'all') {
                    $items.fadeIn(300);
                } else {
                    $items.filter('[data-device="' + filterValue + '"]').fadeIn(300);
                }
            });
            
            ComportementClientAdmin.showToast('Filtre appliqué: ' + $btn.text(), 'info');
        },

        handleSearch: function(e) {
            var searchTerm = $(this).val().toLowerCase();
            var $searchContainer = $(this).closest('.cc-search-container');
            
            // Animation de l'icône de recherche
            if (searchTerm.length > 0) {
                $searchContainer.addClass('searching');
            } else {
                $searchContainer.removeClass('searching');
            }
            
            // Recherche en temps réel avec debounce
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(function() {
                ComportementClientAdmin.performSearch(searchTerm);
            }, 300);
        },

        performSearch: function(searchTerm) {
            var $rows = $('.cc-table tbody tr, .cc-card[data-searchable]');
            
            if (!searchTerm) {
                $rows.show();
                return;
            }
            
            $rows.each(function() {
                var $row = $(this);
                var text = $row.text().toLowerCase();
                
                if (text.includes(searchTerm)) {
                    $row.fadeIn(200);
                } else {
                    $row.fadeOut(200);
                }
            });
        },

        initAnimations: function() {
            // Animations désactivées pour éviter les erreurs
            // Seules les animations CSS de base sont conservées
            return;
        },

        initTooltips: function() {
            // Tooltips désactivés pour éviter les erreurs
            return;
        },

        getDeviceTooltip: function(deviceText) {
            var tooltips = {
                '📱 Mobile': 'Smartphones et appareils mobiles',
                '💻 PC': 'Ordinateurs de bureau et portables',
                '📱 Tablette': 'Tablettes et iPads',
                '❓ Inconnu': 'Type d\'appareil non détecté'
            };
            return tooltips[deviceText] || 'Appareil non identifié';
        },

        initCounters: function() {
            // Animation des compteurs désactivée
            return;
        },

        setupAutoRefresh: function() {
            // Auto-refresh désactivé pour éviter les erreurs JavaScript
            // Seul le refresh manuel est disponible via les boutons
            return;
        },

        silentRefresh: function() {
            // Fonction désactivée complètement
            return;
        },

        updateLiveIndicators: function(data) {
            // Fonction désactivée complètement
            return;
        },

        refreshRealTimeActivity: function() {
            // Fonction désactivée pour éviter les erreurs JavaScript
            // L'actualisation se fait uniquement via le bouton manuel
            return;
        },

        copyToClipboard: function(e) {
            e.preventDefault();
            var text = $(this).data('copy-text');
            
            navigator.clipboard.writeText(text).then(function() {
                ComportementClientAdmin.showToast('Copié dans le presse-papiers!', 'success');
            }).catch(function() {
                ComportementClientAdmin.showToast('Erreur lors de la copie', 'error');
            });
        },

        confirmDelete: function(e) {
            if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet élément ?\n\nCette action est irréversible.')) {
                e.preventDefault();
                return false;
            }
        },

        showToast: function(message, type) {
            type = type || 'info';
            
            var $toast = $('<div class="cc-toast ' + type + '">')
                .html('<strong>' + message + '</strong>')
                .appendTo('body');
            
            // Animation d'entrée
            setTimeout(function() {
                $toast.addClass('show');
            }, 100);
            
            // Auto-hide après 4 secondes
            setTimeout(function() {
                $toast.removeClass('show');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }, 4000);
            
            // Fermeture au clic
            $toast.on('click', function() {
                $(this).removeClass('show');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            });
        },

        // Utilitaires pour l'éasing
        easing: {
            easeOutCubic: function(t) {
                return 1 - Math.pow(1 - t, 3);
            }
        }
    };

    // Extensions jQuery pour les animations personnalisées
    $.fn.extend({
        animateCSS: function(animationName, callback) {
            var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
            this.addClass('animated ' + animationName).one(animationEnd, function() {
                $(this).removeClass('animated ' + animationName);
                if (callback) callback();
            });
            return this;
        }
    });

    // Initialisation au chargement de la page
    $(document).ready(function() {
        ComportementClientAdmin.init();
        
        // Ajouter des classes CSS pour les animations
        $('body').addClass('cc-premium-interface');
    });

})(jQuery);