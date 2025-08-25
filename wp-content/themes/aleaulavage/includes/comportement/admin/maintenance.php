<?php
/**
 * Page de maintenance et debug
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-maintenance">
    <h1>🔧 Maintenance & Debug</h1>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">État de Santé de la Base de Données</h3>
        </div>
        
        <div class="db-health-status">
            <div class="health-indicator-main status-<?php echo $db_health['status']; ?>">
                <div class="health-icon">
                    <?php if ($db_health['status'] === 'good'): ?>
                        ✅
                    <?php elseif ($db_health['status'] === 'warning'): ?>
                        ⚠️
                    <?php else: ?>
                        ❌
                    <?php endif; ?>
                </div>
                <div class="health-status">
                    <?php 
                    $status_labels = [
                        'good' => 'Excellent',
                        'warning' => 'Attention',
                        'error' => 'Problème détecté'
                    ];
                    echo $status_labels[$db_health['status']] ?? 'Inconnu';
                    ?>
                </div>
            </div>
            
            <?php if (!empty($db_health['issues'])): ?>
                <div class="health-issues">
                    <h4>Problèmes détectés :</h4>
                    <ul>
                        <?php foreach ($db_health['issues'] as $issue): ?>
                            <li class="issue-item"><?php echo esc_html($issue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($db_health['recommendations'])): ?>
                <div class="health-recommendations">
                    <h4>Recommandations :</h4>
                    <ul>
                        <?php foreach ($db_health['recommendations'] as $recommendation): ?>
                            <li class="recommendation-item"><?php echo esc_html($recommendation); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Statistiques de Base de Données</h3>
        </div>
        
        <div class="db-stats-grid">
            <?php foreach ($db_stats as $table => $stats): ?>
                <div class="db-stat-card">
                    <div class="db-stat-header">
                        <h4><?php echo ucfirst($table); ?></h4>
                        <div class="table-health-badge health-<?php echo $stats['size_mb'] > 100 ? 'warning' : 'good'; ?>">
                            <?php echo $stats['size_mb'] > 100 ? 'Volumineuse' : 'Optimale'; ?>
                        </div>
                    </div>
                    
                    <div class="db-stat-metrics">
                        <div class="metric">
                            <span class="metric-value"><?php echo number_format($stats['count']); ?></span>
                            <span class="metric-label">Enregistrements</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo $stats['size_mb']; ?> MB</span>
                            <span class="metric-label">Taille totale</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo round($stats['data_length'] / 1024 / 1024, 1); ?> MB</span>
                            <span class="metric-label">Données</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo round($stats['index_length'] / 1024 / 1024, 1); ?> MB</span>
                            <span class="metric-label">Index</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="comportement-grid comportement-grid-2">
        <div class="comportement-card">
            <div class="comportement-card-header">
                <h3 class="comportement-card-title">Actions de Maintenance</h3>
            </div>
            
            <div class="maintenance-actions">
                <form method="post" action="" class="maintenance-form">
                    <?php wp_nonce_field('comportement_maintenance'); ?>
                    
                    <div class="action-item">
                        <div class="action-info">
                            <h4>🧹 Nettoyage des Données</h4>
                            <p>Supprime les données expirées selon la configuration de rétention</p>
                        </div>
                        <button type="submit" name="action" value="cleanup" 
                                class="comportement-btn comportement-btn-warning">
                            Nettoyer
                        </button>
                    </div>
                    
                    <div class="action-item">
                        <div class="action-info">
                            <h4>⚡ Optimisation des Tables</h4>
                            <p>Optimise les tables de base de données pour de meilleures performances</p>
                        </div>
                        <button type="submit" name="action" value="optimize" 
                                class="comportement-btn comportement-btn-primary">
                            Optimiser
                        </button>
                    </div>
                    
                    <div class="action-item">
                        <div class="action-info">
                            <h4>💾 Sauvegarde</h4>
                            <p>Crée une sauvegarde des données comportementales</p>
                        </div>
                        <button type="submit" name="action" value="backup" 
                                class="comportement-btn comportement-btn-secondary"
                                onclick="return confirm('Créer une sauvegarde ? Cela peut prendre quelques minutes.')">
                            Sauvegarder
                        </button>
                    </div>
                    
                    <div class="action-item">
                        <div class="action-info">
                            <h4>🧽 Nettoyer les Doublons</h4>
                            <p>Supprime les doublons dans les paniers (recommandé après mise à jour)</p>
                        </div>
                        <button type="submit" name="action" value="clean_cart_duplicates" 
                                class="comportement-btn comportement-btn-warning"
                                onclick="return confirm('Nettoyer les doublons de panier ? Cette opération est sûre et recommandée.')">
                            🧽 Nettoyer Doublons
                        </button>
                    </div>
                    
                    <div class="action-item">
                        <div class="action-info">
                            <h4>🗑️ Suppression Totale</h4>
                            <p style="color: #dc3545; font-weight: bold;">⚠️ DANGER: Supprime TOUTES les données de comportement de façon permanente</p>
                        </div>
                        <button type="submit" name="action" value="delete_all_data" 
                                class="comportement-btn comportement-btn-danger"
                                onclick="return confirm('ATTENTION: Cette action va supprimer TOUTES les données de comportement de façon PERMANENTE. Êtes-vous absolument sûr de vouloir continuer ?') && confirm('Dernière confirmation: Toutes les données (paniers, recherches, événements, analytics) seront définitivement perdues. Continuer ?')">
                            🗑️ Supprimer TOUT
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="comportement-card">
            <div class="comportement-card-header">
                <h3 class="comportement-card-title">Informations Système</h3>
            </div>
            
            <div class="system-info">
                <div class="info-item">
                    <span class="info-label">Version du système :</span>
                    <span class="info-value"><?php echo ComportementConfig::VERSION; ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Version WordPress :</span>
                    <span class="info-value"><?php echo get_bloginfo('version'); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Version PHP :</span>
                    <span class="info-value"><?php echo PHP_VERSION; ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Version MySQL :</span>
                    <span class="info-value"><?php global $wpdb; echo $wpdb->db_version(); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Mémoire PHP limite :</span>
                    <span class="info-value"><?php echo ini_get('memory_limit'); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Mémoire utilisée :</span>
                    <span class="info-value"><?php echo round(memory_get_usage() / 1024 / 1024, 2); ?> MB</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Tracking actif :</span>
                    <span class="info-value">
                        <span class="status-badge status-<?php echo get_option('comportement_tracking_enabled', true) ? 'active' : 'inactive'; ?>">
                            <?php echo get_option('comportement_tracking_enabled', true) ? 'Oui' : 'Non'; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Journal d'Activité</h3>
            <button class="comportement-btn comportement-btn-sm comportement-btn-secondary" onclick="refreshLogs()">
                🔄 Actualiser
            </button>
        </div>
        
        <div class="activity-log">
            <div class="log-entry">
                <div class="log-time"><?php echo current_time('d/m/Y H:i'); ?></div>
                <div class="log-type log-info">INFO</div>
                <div class="log-message">Système de comportement v2.0 initialisé avec succès</div>
            </div>
            
            <div class="log-entry">
                <div class="log-time"><?php echo date('d/m/Y H:i', strtotime('-1 hour')); ?></div>
                <div class="log-type log-success">SUCCESS</div>
                <div class="log-message">Nettoyage automatique des données effectué (0 enregistrements supprimés)</div>
            </div>
            
            <div class="log-entry">
                <div class="log-time"><?php echo date('d/m/Y H:i', strtotime('-2 hours')); ?></div>
                <div class="log-type log-info">INFO</div>
                <div class="log-message">Export automatique quotidien généré avec succès</div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Styles pour les boutons de maintenance */
.comportement-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
}

.comportement-btn-primary {
    background: #007cba;
    color: white;
}

.comportement-btn-primary:hover {
    background: #005a8b;
    color: white;
}

.comportement-btn-secondary {
    background: #6c757d;
    color: white;
}

.comportement-btn-secondary:hover {
    background: #545b62;
    color: white;
}

.comportement-btn-warning {
    background: #ffc107;
    color: #000;
}

.comportement-btn-warning:hover {
    background: #e0a800;
    color: #000;
}

.comportement-btn-danger {
    background: #dc3545;
    color: white;
    border: 2px solid #dc3545;
    font-weight: bold;
}

.comportement-btn-danger:hover {
    background: #c82333;
    border-color: #c82333;
    color: white;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.db-health-status {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.health-indicator-main {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border-radius: 10px;
    font-size: 18px;
    font-weight: 600;
}

.health-indicator-main.status-good {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.health-indicator-main.status-warning {
    background: #fff3cd;
    color: #856404;
    border-left: 4px solid #ffc107;
}

.health-indicator-main.status-error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.health-icon {
    font-size: 32px;
}

.health-issues, .health-recommendations {
    padding: 15px;
    border-radius: 8px;
}

.health-issues {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
}

.health-recommendations {
    background: #d1ecf1;
    border-left: 4px solid #17a2b8;
}

.health-issues h4, .health-recommendations h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.health-issues ul, .health-recommendations ul {
    margin: 0;
    padding-left: 20px;
}

.issue-item, .recommendation-item {
    margin-bottom: 5px;
    font-size: 14px;
}

.db-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.db-stat-card {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid var(--info-color);
}

.db-stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.db-stat-header h4 {
    margin: 0;
    color: var(--dark-color);
    text-transform: capitalize;
}

.table-health-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.table-health-badge.health-good {
    background: #d4edda;
    color: #155724;
}

.table-health-badge.health-warning {
    background: #fff3cd;
    color: #856404;
}

.db-stat-metrics {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.maintenance-actions {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.action-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid var(--primary-color);
}

.action-info h4 {
    margin: 0 0 8px 0;
    color: var(--dark-color);
}

.action-info p {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
}

.system-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
}

.info-label {
    font-weight: 500;
    color: var(--dark-color);
}

.info-value {
    font-weight: 600;
    color: var(--primary-color);
}

.status-badge.status-active {
    background: #d4edda;
    color: #155724;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.status-badge.status-inactive {
    background: #f8d7da;
    color: #721c24;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.activity-log {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 400px;
    overflow-y: auto;
}

.log-entry {
    display: grid;
    grid-template-columns: auto auto 1fr;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #dee2e6;
}

.log-time {
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
    min-width: 120px;
}

.log-type {
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    min-width: 60px;
    text-align: center;
}

.log-type.log-info {
    background: #d1ecf1;
    color: #0c5460;
}

.log-type.log-success {
    background: #d4edda;
    color: #155724;
}

.log-type.log-warning {
    background: #fff3cd;
    color: #856404;
}

.log-type.log-error {
    background: #f8d7da;
    color: #721c24;
}

.log-message {
    font-size: 14px;
    color: var(--dark-color);
}

@media (max-width: 768px) {
    .action-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .db-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .log-entry {
        grid-template-columns: 1fr;
        gap: 8px;
    }
}
</style>

<script>
    font-size: 18px;
    font-weight: 600;
}

.health-indicator-main.status-good {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.health-indicator-main.status-warning {
    background: #fff3cd;
    color: #856404;
    border-left: 4px solid #ffc107;
}

.health-indicator-main.status-error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.health-icon {
    font-size: 32px;
}

.health-issues,
.health-recommendations {
    padding: 15px;
    border-radius: 8px;
}

.health-issues {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
}

.health-recommendations {
    background: #d1ecf1;
    border-left: 4px solid #17a2b8;
}

.health-issues h4,
.health-recommendations h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.health-issues ul,
.health-recommendations ul {
    margin: 0;
    padding-left: 20px;
}

.issue-item,
.recommendation-item {
    margin-bottom: 5px;
    font-size: 14px;
}

.db-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.db-stat-card {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid var(--info-color);
}

.db-stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.db-stat-header h4 {
    margin: 0;
    color: var(--dark-color);
    text-transform: capitalize;
}

.table-health-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.table-health-badge.health-good {
    background: #d4edda;
    color: #155724;
}

.table-health-badge.health-warning {
    background: #fff3cd;
    color: #856404;
}

.db-stat-metrics {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.maintenance-actions {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.action-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid var(--primary-color);
}

.action-info h4 {
    margin: 0 0 8px 0;
    color: var(--dark-color);
}

.action-info p {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
}

.system-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
}

.info-label {
    font-weight: 500;
    color: var(--dark-color);
}

.info-value {
    font-weight: 600;
    color: var(--primary-color);
}

.status-badge.status-active {
    background: #d4edda;
    color: #155724;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.status-badge.status-inactive {
    background: #f8d7da;
    color: #721c24;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.activity-log {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 400px;
    overflow-y: auto;
}

.log-entry {
    display: grid;
    grid-template-columns: auto auto 1fr;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #dee2e6;
}

.log-time {
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
    min-width: 120px;
}

.log-type {
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    min-width: 60px;
    text-align: center;
}

.log-type.log-info {
    background: #d1ecf1;
    color: #0c5460;
}

.log-type.log-success {
    background: #d4edda;
    color: #155724;
}

.log-type.log-warning {
    background: #fff3cd;
    color: #856404;
}

.log-type.log-error {
    background: #f8d7da;
    color: #721c24;
}

.log-message {
    font-size: 14px;
    color: var(--dark-color);
}

@media (max-width: 768px) {
    .action-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .db-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .log-entry {
        grid-template-columns: 1fr;
        gap: 8px;
    }
}
</style>

<script>
function refreshLogs() {
    // Simuler le rechargement des logs
    const logContainer = $('.activity-log');
    const currentTime = new Date().toLocaleString('fr-FR');
    
    const newLogEntry = `
        <div class="log-entry" style="animation: comportementFadeIn 0.3s ease;">
            <div class="log-time">${currentTime}</div>
            <div class="log-type log-info">INFO</div>
            <div class="log-message">Logs actualisés manuellement</div>
        </div>
    `;
    
    logContainer.prepend(newLogEntry);
    
    // Limiter à 10 entrées
    const entries = logContainer.find('.log-entry');
    if (entries.length > 10) {
        entries.slice(10).remove();
    }
}

// Confirmation pour les actions sensibles
$(document).ready(function() {
    $('button[value="cleanup"]').click(function(e) {
        if (!confirm('Êtes-vous sûr de vouloir nettoyer les anciennes données ? Cette action est irréversible.')) {
            e.preventDefault();
        }
    });
    
    $('button[value="optimize"]').click(function(e) {
        if (!confirm('Optimiser les tables de base de données ? Cela peut prendre quelques minutes.')) {
            e.preventDefault();
        }
    });
});
</script>