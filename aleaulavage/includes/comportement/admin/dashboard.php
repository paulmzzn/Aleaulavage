<?php
/**
 * Dashboard principal du système de comportement v2
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-dashboard">
    <h1>🚀 Comportement Client v2.0 - Dashboard</h1>
    
    <div class="comportement-version-badge">
        <span class="badge-v2">v2.0</span>
        <span class="badge-status">Système Avancé Activé</span>
    </div>
    
    <!-- Statistiques en temps réel -->
    <div class="dashboard-stats-grid">
        <div class="stat-card active-sessions">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $stats['active_sessions']; ?></div>
                <div class="stat-label">Sessions Actives</div>
                <div class="stat-sublabel">Dernières 30 min</div>
            </div>
        </div>
        
        <div class="stat-card page-views">
            <div class="stat-icon">👁️</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $stats['recent_page_views']; ?></div>
                <div class="stat-label">Pages Vues</div>
                <div class="stat-sublabel">Dernière heure</div>
            </div>
        </div>
        
        <div class="stat-card product-views">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $stats['recent_product_views']; ?></div>
                <div class="stat-label">Produits Vus</div>
                <div class="stat-sublabel">Dernière heure</div>
            </div>
        </div>
        
        <div class="stat-card cart-adds">
            <div class="stat-icon">🛒</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $stats['recent_cart_adds']; ?></div>
                <div class="stat-label">Ajouts Panier</div>
                <div class="stat-sublabel">Dernière heure</div>
            </div>
        </div>
    </div>
    
    <!-- Graphiques principaux -->
    <div class="dashboard-charts-grid">
        
        <!-- Funnel de conversion -->
        <div class="chart-container">
            <div class="chart-header">
                <h3>🎯 Funnel de Conversion (30 jours)</h3>
                <div class="chart-controls">
                    <select id="funnel-period">
                        <option value="7">7 jours</option>
                        <option value="30" selected>30 jours</option>
                        <option value="90">90 jours</option>
                    </select>
                </div>
            </div>
            <div class="chart-content">
                <canvas id="funnelChart"></canvas>
                <div class="funnel-stats">
                    <div class="funnel-step">
                        <span class="step-name">Visiteurs</span>
                        <span class="step-count"><?php echo $funnel_data['visitors']; ?></span>
                        <span class="step-rate">100%</span>
                    </div>
                    <div class="funnel-step">
                        <span class="step-name">Vues Produit</span>
                        <span class="step-count"><?php echo $funnel_data['product_viewers']; ?></span>
                        <span class="step-rate"><?php echo round($funnel_data['rates']['visitor_to_product'], 1); ?>%</span>
                    </div>
                    <div class="funnel-step">
                        <span class="step-name">Ajouts Panier</span>
                        <span class="step-count"><?php echo $funnel_data['cart_additions']; ?></span>
                        <span class="step-rate"><?php echo round($funnel_data['rates']['product_to_cart'], 1); ?>%</span>
                    </div>
                    <div class="funnel-step">
                        <span class="step-name">Commandes</span>
                        <span class="step-count"><?php echo $funnel_data['orders']; ?></span>
                        <span class="step-rate"><?php echo round($funnel_data['rates']['overall_conversion'], 1); ?>%</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- État de la base de données -->
        <div class="chart-container">
            <div class="chart-header">
                <h3>💾 État de la Base de Données</h3>
            </div>
            <div class="chart-content">
                <div class="db-stats">
                    <?php foreach ($db_stats as $table => $stats_data): ?>
                        <div class="db-table-stat">
                            <div class="table-name"><?php echo ucfirst($table); ?></div>
                            <div class="table-metrics">
                                <span class="metric">
                                    <strong><?php echo number_format($stats_data['count']); ?></strong>
                                    <small>enregistrements</small>
                                </span>
                                <span class="metric">
                                    <strong><?php echo $stats_data['size_mb']; ?> MB</strong>
                                    <small>taille</small>
                                </span>
                            </div>
                            <div class="table-health">
                                <?php 
                                $health_class = $stats_data['size_mb'] > 50 ? 'warning' : 'good';
                                $health_text = $stats_data['size_mb'] > 50 ? 'Volumineuse' : 'Optimale';
                                ?>
                                <span class="health-indicator <?php echo $health_class; ?>">
                                    <?php echo $health_text; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pages les plus visitées en temps réel -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>🔥 Pages Populaires (Temps Réel)</h3>
            <button class="refresh-button" onclick="refreshPopularPages()">🔄 Actualiser</button>
        </div>
        <div class="popular-pages" id="popularPages">
            <?php if (!empty($stats['top_current_pages'])): ?>
                <?php foreach ($stats['top_current_pages'] as $page): ?>
                    <div class="page-item">
                        <div class="page-url"><?php echo esc_html($page->page_url ?? 'Page inconnue'); ?></div>
                        <div class="page-views"><?php echo intval($page->views ?? 0); ?> vues</div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📊</div>
                    <h4>Aucune activité récente</h4>
                    <p>Les pages populaires s'afficheront ici une fois que des visiteurs navigueront sur le site.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Fonctionnalités avancées -->
    <div class="advanced-features-grid">
        
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <div class="feature-content">
                <h4>Analytics Avancées</h4>
                <p>Analyse de cohortes, segmentation utilisateur, métriques personnalisées</p>
                <a href="<?php echo admin_url('admin.php?page=comportement-v2-analytics'); ?>" class="button button-primary">
                    Explorer →
                </a>
            </div>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">🎯</div>
            <div class="feature-content">
                <h4>Segmentation Intelligente</h4>
                <p>Classification automatique des utilisateurs par comportement</p>
                <a href="<?php echo admin_url('admin.php?page=comportement-v2-segmentation'); ?>" class="button button-primary">
                    Segmenter →
                </a>
            </div>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <div class="feature-content">
                <h4>Monitoring Temps Réel</h4>
                <p>Suivi en direct de l'activité sur votre site</p>
                <a href="<?php echo admin_url('admin.php?page=comportement-v2-realtime'); ?>" class="button button-primary">
                    Surveiller →
                </a>
            </div>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">📋</div>
            <div class="feature-content">
                <h4>Exports Avancés</h4>
                <p>Rapports détaillés CSV, JSON, PDF avec filtres personnalisés</p>
                <a href="<?php echo admin_url('admin.php?page=comportement-v2-exports'); ?>" class="button button-primary">
                    Exporter →
                </a>
            </div>
        </div>
        
    </div>
    
    <!-- Alertes et notifications -->
    <?php
    $abandoned_carts = ComportementAnalyzer::detect_abandoned_carts(24);
    if (!empty($abandoned_carts)):
    ?>
    <div class="dashboard-alerts">
        <div class="alert alert-warning">
            <div class="alert-icon">⚠️</div>
            <div class="alert-content">
                <h4>Paniers Abandonnés Détectés</h4>
                <p><?php echo count($abandoned_carts); ?> paniers ont été abandonnés dans les dernières 24h</p>
                <a href="<?php echo admin_url('admin.php?page=comportement-v2-analytics'); ?>" class="alert-action">
                    Voir les détails →
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<style>
/* Styles spécifiques au dashboard v2 */
.comportement-v2-dashboard {
    max-width: 1400px;
}

.comportement-version-badge {
    margin: 10px 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.badge-v2 {
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-status {
    background: #28a745;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 11px;
}

.dashboard-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0 30px 0;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.stat-icon {
    font-size: 24px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    color: white;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    font-weight: 600;
    color: #34495e;
    margin: 5px 0 2px 0;
}

.stat-sublabel {
    font-size: 12px;
    color: #7f8c8d;
}

.dashboard-charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin: 30px 0;
}

.chart-container {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.chart-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 18px;
}

.chart-controls select {
    padding: 5px 10px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.funnel-stats {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

.funnel-step {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #667eea;
}

.step-name {
    font-weight: 600;
    color: #2c3e50;
}

.step-count {
    font-weight: 700;
    color: #667eea;
}

.step-rate {
    font-size: 14px;
    color: #27ae60;
    font-weight: 600;
}

.db-stats {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.db-table-stat {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #17a2b8;
}

.table-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    text-transform: capitalize;
}

.table-metrics {
    display: flex;
    gap: 15px;
    margin-bottom: 8px;
}

.metric {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.metric strong {
    color: #17a2b8;
    font-size: 16px;
}

.metric small {
    color: #6c757d;
    font-size: 11px;
}

.health-indicator {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.health-indicator.good {
    background: #d4edda;
    color: #155724;
}

.health-indicator.warning {
    background: #fff3cd;
    color: #856404;
}

.dashboard-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #f1f1f1;
    padding-bottom: 10px;
}

.section-header h3 {
    margin: 0;
    color: #2c3e50;
}

.refresh-button {
    background: #667eea;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
}

.popular-pages {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.page-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.page-url {
    color: #667eea;
    font-weight: 500;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.page-views {
    color: #27ae60;
    font-weight: 600;
    font-size: 14px;
}

.advanced-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.feature-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.2s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    font-size: 40px;
    margin-bottom: 15px;
}

.feature-content h4 {
    color: #2c3e50;
    margin: 0 0 10px 0;
}

.feature-content p {
    color: #6c757d;
    margin-bottom: 20px;
    font-size: 14px;
}

.dashboard-alerts {
    margin: 30px 0;
}

.alert {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 15px;
}

.alert-warning {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
}

.alert-icon {
    font-size: 24px;
}

.alert-content h4 {
    margin: 0 0 5px 0;
    color: #856404;
}

.alert-content p {
    margin: 0 0 10px 0;
    color: #856404;
    font-size: 14px;
}

.alert-action {
    color: #856404;
    text-decoration: none;
    font-weight: 600;
}

.alert-action:hover {
    text-decoration: underline;
}

.no-data {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 20px;
}
</style>

<script>
// Initialiser les graphiques
document.addEventListener('DOMContentLoaded', function() {
    // Graphique du funnel de conversion
    const funnelCtx = document.getElementById('funnelChart').getContext('2d');
    new Chart(funnelCtx, {
        type: 'bar',
        data: {
            labels: ['Visiteurs', 'Vues Produit', 'Ajouts Panier', 'Commandes'],
            datasets: [{
                label: 'Conversions',
                data: [
                    <?php echo $funnel_data['visitors']; ?>,
                    <?php echo $funnel_data['product_viewers']; ?>,
                    <?php echo $funnel_data['cart_additions']; ?>,
                    <?php echo $funnel_data['orders']; ?>
                ],
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});

function refreshPopularPages() {
    // Actualiser les pages populaires via AJAX
    jQuery.post(ajaxurl, {
        action: 'comportement_get_stats',
        type: 'realtime',
        nonce: ComportementAdmin.nonce
    }, function(response) {
        if (response.success) {
            // Mettre à jour l'affichage
            location.reload();
        }
    });
}
</script>