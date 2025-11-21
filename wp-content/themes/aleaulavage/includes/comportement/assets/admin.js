/**
 * JavaScript pour l'interface admin du système de comportement v2
 */

(function($) {
    'use strict';
    
    const ComportementAdminJS = {
        
        init: function() {
            this.bindEvents();
            this.initCharts();
            this.setupRealTimeUpdates();
            this.initTabs();
            this.initTooltips();
        },
        
        bindEvents: function() {
            // Gestion des exports
            $(document).on('click', '.comportement-export-btn', this.handleExport);
            
            // Gestion des filtres
            $(document).on('change', '.comportement-filter', this.handleFilterChange);
            
            // Actions sur les utilisateurs
            $(document).on('click', '.view-user-insights', this.viewUserInsights);
            
            // Rafraîchissement des données
            $(document).on('click', '.refresh-data-btn', this.refreshData);
            
            // Gestion des segments
            $(document).on('click', '.segment-filter', this.filterBySegment);
            
            // Configuration
            $(document).on('submit', '.comportement-config-form', this.saveConfiguration);
        },
        
        initCharts: function() {
            // Initialiser Chart.js avec les options par défaut
            if (typeof Chart !== 'undefined') {
                Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
                Chart.defaults.color = '#6c757d';
                Chart.defaults.plugins.legend.display = false;
                
                // Charger les graphiques existants
                this.loadFunnelChart();
                this.loadCohortChart();
                this.loadSegmentationChart();
                this.loadRealtimeChart();
            }
        },
        
        loadFunnelChart: function() {
            const ctx = document.getElementById('funnelChart');
            if (!ctx) return;
            
            this.showLoading(ctx);
            
            $.post(ComportementAdmin.ajax_url, {
                action: 'comportement_get_stats',
                type: 'funnel',
                period: 30,
                nonce: ComportementAdmin.nonce
            }).done((response) => {
                if (response.success) {
                    this.createFunnelChart(ctx, response.data);
                } else {
                    this.showError(ctx, 'Erreur de chargement du funnel');
                }
            }).fail(() => {
                this.showError(ctx, 'Erreur réseau');
            });
        },
        
        createFunnelChart: function(ctx, data) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Visiteurs', 'Vues Produit', 'Ajouts Panier', 'Checkout', 'Commandes'],
                    datasets: [{
                        data: [
                            data.visitors,
                            data.product_viewers,
                            data.cart_additions,
                            data.checkouts,
                            data.orders
                        ],
                        backgroundColor: [
                            '#667eea',
                            '#764ba2',
                            '#f093fb',
                            '#f5576c',
                            '#4facfe'
                        ],
                        borderRadius: 8,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const percentage = data.rates ? 
                                        Object.values(data.rates)[context.dataIndex] || 0 : 0;
                                    return `${context.parsed.y} (${percentage.toFixed(1)}%)`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        },
        
        loadCohortChart: function() {
            const ctx = document.getElementById('cohortChart');
            if (!ctx) return;
            
            this.showLoading(ctx);
            
            $.post(ComportementAdmin.ajax_url, {
                action: 'comportement_get_stats',
                type: 'cohort',
                months: 12,
                nonce: ComportementAdmin.nonce
            }).done((response) => {
                if (response.success) {
                    this.createCohortChart(ctx, response.data);
                }
            });
        },
        
        createCohortChart: function(ctx, data) {
            // Créer une heatmap pour l'analyse de cohortes
            const datasets = Object.keys(data).map((cohort, index) => {
                return {
                    label: cohort,
                    data: data[cohort].retention.map((retention, month) => ({
                        x: month,
                        y: index,
                        v: retention
                    })),
                    backgroundColor: function(context) {
                        const value = context.parsed.v;
                        const alpha = value / 100;
                        return `rgba(102, 126, 234, ${alpha})`;
                    }
                };
            });
            
            new Chart(ctx, {
                type: 'scatter',
                data: { datasets },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    const cohortIndex = context[0].parsed.y;
                                    return Object.keys(data)[cohortIndex];
                                },
                                label: function(context) {
                                    return `Mois ${context.parsed.x}: ${context.parsed.v}% rétention`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Mois depuis inscription' },
                            min: 0,
                            max: 12
                        },
                        y: {
                            title: { display: true, text: 'Cohorte' },
                            ticks: {
                                callback: function(value) {
                                    return Object.keys(data)[value] || '';
                                }
                            }
                        }
                    }
                }
            });
        },
        
        loadSegmentationChart: function() {
            const ctx = document.getElementById('segmentationChart');
            if (!ctx) return;
            
            // Compter les segments depuis les données de la page
            const segments = {};
            $('.user-segment').each(function() {
                const segment = $(this).data('segment');
                segments[segment] = (segments[segment] || 0) + 1;
            });
            
            this.createSegmentationChart(ctx, segments);
        },
        
        createSegmentationChart: function(ctx, segments) {
            const colors = {
                'nouveau_visiteur': '#28a745',
                'visiteur_regulier': '#17a2b8', 
                'client_potentiel': '#ffc107',
                'client_actif': '#5899E2',
                'client_fidele': '#6f42c1',
                'client_inactif': '#dc3545'
            };
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(segments).map(s => s.replace('_', ' ')),
                    datasets: [{
                        data: Object.values(segments),
                        backgroundColor: Object.keys(segments).map(s => colors[s] || '#6c757d'),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 1000
                    }
                }
            });
        },
        
        setupRealTimeUpdates: function() {
            // Mettre à jour les statistiques temps réel toutes les 30 secondes
            setInterval(() => {
                this.updateRealtimeStats();
            }, 30000);
            
            // Ajouter l'indicateur de mise à jour
            this.showRealtimeIndicator();
        },
        
        updateRealtimeStats: function() {
            $.post(ComportementAdmin.ajax_url, {
                action: 'comportement_get_stats',
                type: 'realtime',
                nonce: ComportementAdmin.nonce
            }).done((response) => {
                if (response.success) {
                    // Mettre à jour les métriques
                    $('.realtime-active-sessions .stat-number').text(response.data.active_sessions);
                    $('.realtime-page-views .stat-number').text(response.data.recent_page_views);
                    $('.realtime-product-views .stat-number').text(response.data.recent_product_views);
                    $('.realtime-cart-adds .stat-number').text(response.data.recent_cart_adds);
                    
                    // Animation de mise à jour
                    $('.realtime-stats .stat-card').addClass('updated');
                    setTimeout(() => {
                        $('.realtime-stats .stat-card').removeClass('updated');
                    }, 500);
                }
            });
        },
        
        showRealtimeIndicator: function() {
            if ($('.realtime-indicator').length === 0) {
                $('.realtime-stats').prepend(`
                    <div class="realtime-indicator">
                        <div class="realtime-dot"></div>
                        Temps réel - Mise à jour auto
                    </div>
                `);
            }
        },
        
        handleExport: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const type = $btn.data('type');
            const format = $btn.data('format') || 'csv';
            
            // Récupérer les filtres
            const filters = {};
            $('.comportement-filter').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (value) filters[name] = value;
            });
            
            // Afficher le loading
            $btn.addClass('loading').prop('disabled', true);
            $btn.html('<span class="comportement-spinner"></span> Export en cours...');
            
            // Créer un formulaire pour télécharger
            const $form = $('<form>', {
                method: 'POST',
                action: ComportementAdmin.ajax_url,
                style: 'display: none;'
            });
            
            $form.append($('<input>', { name: 'action', value: 'comportement_export' }));
            $form.append($('<input>', { name: 'type', value: type }));
            $form.append($('<input>', { name: 'format', value: format }));
            $form.append($('<input>', { name: 'filters', value: JSON.stringify(filters) }));
            $form.append($('<input>', { name: 'nonce', value: ComportementAdmin.nonce }));
            
            $('body').append($form);
            $form.submit();
            
            // Restaurer le bouton après 3 secondes
            setTimeout(() => {
                $btn.removeClass('loading').prop('disabled', false);
                $btn.html($btn.data('original-text') || 'Exporter');
                $form.remove();
            }, 3000);
        },
        
        handleFilterChange: function() {
            const $filter = $(this);
            const targetChart = $filter.data('target');
            
            if (targetChart) {
                ComportementAdminJS.refreshChart(targetChart);
            }
        },
        
        refreshChart: function(chartId) {
            const ctx = document.getElementById(chartId);
            if (!ctx) return;
            
            // Détruire le graphique existant
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
            
            // Recharger selon le type
            switch (chartId) {
                case 'funnelChart':
                    this.loadFunnelChart();
                    break;
                case 'cohortChart':
                    this.loadCohortChart();
                    break;
                case 'segmentationChart':
                    this.loadSegmentationChart();
                    break;
            }
        },
        
        viewUserInsights: function(e) {
            e.preventDefault();
            
            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');
            
            // Créer un modal pour afficher les insights
            const modal = this.createModal(`Insights - ${userName}`, 'Chargement...');
            
            $.post(ComportementAdmin.ajax_url, {
                action: 'comportement_get_user_insights',
                user_id: userId,
                nonce: ComportementAdmin.nonce
            }).done((response) => {
                if (response.success) {
                    modal.find('.modal-body').html(this.formatUserInsights(response.data));
                } else {
                    modal.find('.modal-body').html('<div class="comportement-alert comportement-alert-danger">Erreur de chargement</div>');
                }
            });
        },
        
        formatUserInsights: function(data) {
            const insights = data.insights;
            const segment = data.segment;
            
            return `
                <div class="user-insights">
                    <div class="insight-segment">
                        <h4>Segment utilisateur</h4>
                        <span class="comportement-user-segment segment-${segment}">${segment.replace('_', ' ')}</span>
                    </div>
                    
                    <div class="insight-metrics">
                        <h4>Métriques d'engagement</h4>
                        <div class="metrics-grid">
                            <div class="metric">
                                <div class="metric-value">${insights.navigation?.total_page_views || 0}</div>
                                <div class="metric-label">Pages vues</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value">${insights.products?.products_viewed || 0}</div>
                                <div class="metric-label">Produits vus</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value">${insights.products?.cart_additions || 0}</div>
                                <div class="metric-label">Ajouts panier</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value">${(insights.products?.conversion_rate || 0).toFixed(1)}%</div>
                                <div class="metric-label">Taux conversion</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="insight-engagement">
                        <h4>Score d'engagement</h4>
                        <div class="comportement-engagement-score">
                            <div class="engagement-score-bar">
                                <div class="engagement-score-fill engagement-score-${this.getEngagementLevel(insights.engagement_score)}" 
                                     style="width: ${insights.engagement_score || 0}%"></div>
                            </div>
                            <div class="engagement-score-value">${insights.engagement_score || 0}/100</div>
                        </div>
                    </div>
                    
                    ${insights.recommendations ? `
                        <div class="insight-recommendations">
                            <h4>Recommandations</h4>
                            ${insights.recommendations.map(rec => `
                                <div class="recommendation recommendation-${rec.priority}">
                                    <strong>${rec.type}:</strong> ${rec.message}
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            `;
        },
        
        getEngagementLevel: function(score) {
            if (score >= 70) return 'high';
            if (score >= 40) return 'medium';
            return 'low';
        },
        
        createModal: function(title, content) {
            const modalHtml = `
                <div class="comportement-modal-overlay">
                    <div class="comportement-modal">
                        <div class="modal-header">
                            <h3>${title}</h3>
                            <button class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">${content}</div>
                    </div>
                </div>
            `;
            
            const $modal = $(modalHtml);
            $('body').append($modal);
            
            // Fermer le modal
            $modal.on('click', '.modal-close, .comportement-modal-overlay', function(e) {
                if (e.target === this) {
                    $modal.remove();
                }
            });
            
            return $modal;
        },
        
        initTabs: function() {
            $('.comportement-tabs .comportement-tab').on('click', function(e) {
                e.preventDefault();
                
                const $tab = $(this);
                const target = $tab.data('target');
                
                // Activer l'onglet
                $tab.siblings().removeClass('active');
                $tab.addClass('active');
                
                // Afficher le contenu
                $('.comportement-tab-content').removeClass('active');
                $(target).addClass('active');
                
                // Rafraîchir les graphiques si nécessaire
                setTimeout(() => {
                    $(target).find('canvas').each(function() {
                        const chart = Chart.getChart(this);
                        if (chart) {
                            chart.resize();
                        }
                    });
                }, 100);
            });
        },
        
        initTooltips: function() {
            // Ajouter des tooltips aux éléments avec data-tooltip
            $('[data-tooltip]').hover(
                function() {
                    const tooltip = $('<div class="comportement-tooltip">' + $(this).data('tooltip') + '</div>');
                    $('body').append(tooltip);
                    
                    const pos = $(this).offset();
                    tooltip.css({
                        top: pos.top - tooltip.outerHeight() - 10,
                        left: pos.left + ($(this).outerWidth() / 2) - (tooltip.outerWidth() / 2)
                    });
                },
                function() {
                    $('.comportement-tooltip').remove();
                }
            );
        },
        
        refreshData: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const originalText = $btn.text();
            
            $btn.html('<span class="comportement-spinner"></span> Actualisation...').prop('disabled', true);
            
            // Simuler un délai puis recharger la page
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        },
        
        filterBySegment: function(e) {
            e.preventDefault();
            
            const segment = $(this).data('segment');
            
            // Afficher/masquer les utilisateurs selon le segment
            if (segment === 'all') {
                $('.user-row').show();
            } else {
                $('.user-row').hide();
                $(`.user-row[data-segment="${segment}"]`).show();
            }
            
            // Mettre à jour l'état actif du filtre
            $('.segment-filter').removeClass('active');
            $(this).addClass('active');
        },
        
        saveConfiguration: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            
            $submitBtn.prop('disabled', true).text('Sauvegarde...');
            
            // Soumettre le formulaire normalement
            // Le feedback sera géré par PHP
            setTimeout(() => {
                $submitBtn.prop('disabled', false).text('Sauvegarder');
            }, 2000);
        },
        
        showLoading: function(element) {
            $(element).parent().append('<div class="chart-loading">Chargement...</div>');
        },
        
        showError: function(element, message) {
            $(element).parent().find('.chart-loading').remove();
            $(element).parent().append(`<div class="chart-error">${message}</div>`);
        },
        
        // Utilitaires
        formatNumber: function(num) {
            return new Intl.NumberFormat('fr-FR').format(num);
        },
        
        formatCurrency: function(amount) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        },
        
        formatDate: function(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    };
    
    // Initialiser quand le DOM est prêt
    $(document).ready(function() {
        ComportementAdminJS.init();
    });
    
    // Rendre disponible globalement
    window.ComportementAdminJS = ComportementAdminJS;
    
})(jQuery);