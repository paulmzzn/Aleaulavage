<?php
/**
 * Page de configuration
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-config">
    <h1>⚙️ Configuration du Système</h1>
    
    <form method="post" action="" class="comportement-config-form">
        <?php wp_nonce_field('comportement_config'); ?>
        
        <div class="comportement-card">
            <div class="comportement-card-header">
                <h3 class="comportement-card-title">Paramètres Généraux</h3>
            </div>
            
            <div class="config-section">
                <div class="comportement-form-group">
                    <label class="comportement-form-label">
                        <input type="checkbox" name="tracking_enabled" value="1" 
                               <?php checked($current_config['tracking_enabled']); ?>>
                        Activer le tracking comportemental
                    </label>
                    <div class="comportement-form-help">
                        Désactiver cette option arrêtera complètement la collecte de données comportementales.
                    </div>
                </div>
                
                <div class="comportement-form-group">
                    <label class="comportement-form-label">Durée de rétention des données (jours)</label>
                    <input type="number" 
                           name="retention_days" 
                           value="<?php echo $current_config['retention_days']; ?>"
                           min="7" 
                           max="365" 
                           class="comportement-form-input" 
                           style="max-width: 200px;">
                    <div class="comportement-form-help">
                        Les données plus anciennes que cette période seront automatiquement supprimées.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="comportement-card">
            <div class="comportement-card-header">
                <h3 class="comportement-card-title">Événements Trackés</h3>
                <p class="comportement-card-subtitle">Choisissez quels événements suivre</p>
            </div>
            
            <div class="tracked-events">
                <?php foreach (ComportementConfig::get_tracked_events() as $event_key => $event_config): ?>
                    <div class="event-config-item">
                        <div class="event-info">
                            <label class="event-checkbox">
                                <input type="checkbox" 
                                       name="tracked_events[]" 
                                       value="<?php echo $event_key; ?>" 
                                       checked>
                                <strong><?php echo $event_config['label']; ?></strong>
                            </label>
                            <p><?php echo $event_config['description']; ?></p>
                            <small>Rétention: <?php echo $event_config['retention_days']; ?> jours</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="comportement-card">
            <div class="comportement-card-header">
                <h3 class="comportement-card-title">Alertes Automatiques</h3>
                <p class="comportement-card-subtitle">Configuration des notifications automatiques</p>
            </div>
            
            <div class="alert-configs">
                <?php foreach ($current_config['alert_configs'] as $alert_key => $alert_config): ?>
                    <div class="alert-config-item">
                        <div class="alert-header">
                            <label class="alert-checkbox">
                                <input type="checkbox" 
                                       name="alerts[<?php echo $alert_key; ?>][enabled]" 
                                       value="1" 
                                       <?php checked($alert_config['enabled']); ?>>
                                <strong><?php echo ucwords(str_replace('_', ' ', $alert_key)); ?></strong>
                            </label>
                        </div>
                        
                        <div class="alert-settings">
                            <?php if (isset($alert_config['threshold_hours'])): ?>
                                <div class="alert-setting">
                                    <label>Seuil (heures):</label>
                                    <input type="number" 
                                           name="alerts[<?php echo $alert_key; ?>][threshold_hours]" 
                                           value="<?php echo $alert_config['threshold_hours']; ?>"
                                           min="1" 
                                           max="168">
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($alert_config['threshold_days'])): ?>
                                <div class="alert-setting">
                                    <label>Seuil (jours):</label>
                                    <input type="number" 
                                           name="alerts[<?php echo $alert_key; ?>][threshold_days]" 
                                           value="<?php echo $alert_config['threshold_days']; ?>"
                                           min="1" 
                                           max="365">
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($alert_config['max_reminders'])): ?>
                                <div class="alert-setting">
                                    <label>Max rappels:</label>
                                    <input type="number" 
                                           name="alerts[<?php echo $alert_key; ?>][max_reminders]" 
                                           value="<?php echo $alert_config['max_reminders']; ?>"
                                           min="1" 
                                           max="10">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="comportement-card">
            <div class="comportement-card-header">
                <h3 class="comportement-card-title">Performance & Maintenance</h3>
            </div>
            
            <div class="performance-settings">
                <div class="comportement-form-group">
                    <label class="comportement-form-label">
                        <input type="checkbox" name="auto_cleanup" value="1" checked>
                        Nettoyage automatique des données
                    </label>
                    <div class="comportement-form-help">
                        Supprime automatiquement les anciennes données selon la durée de rétention.
                    </div>
                </div>
                
                <div class="comportement-form-group">
                    <label class="comportement-form-label">
                        <input type="checkbox" name="optimize_tables" value="1" checked>
                        Optimisation automatique des tables
                    </label>
                    <div class="comportement-form-help">
                        Optimise les tables de base de données pour de meilleures performances.
                    </div>
                </div>
                
                <div class="comportement-form-group">
                    <label class="comportement-form-label">Limite d'événements par heure</label>
                    <input type="number" 
                           name="events_per_hour_limit" 
                           value="1000" 
                           min="100" 
                           max="10000"
                           class="comportement-form-input" 
                           style="max-width: 200px;">
                    <div class="comportement-form-help">
                        Limite le nombre d'événements trackés par heure pour éviter la surcharge.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="config-actions">
            <button type="submit" name="save_config" class="comportement-btn comportement-btn-primary">
                💾 Sauvegarder la Configuration
            </button>
            <button type="button" class="comportement-btn comportement-btn-secondary" onclick="resetToDefaults()">
                🔄 Rétablir par Défaut
            </button>
        </div>
    </form>
    
</div>

<style>
.config-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.tracked-events {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.event-config-item {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid var(--primary-color);
}

.event-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    margin-bottom: 8px;
}

.event-info p {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #6c757d;
}

.event-info small {
    color: #6c757d;
    font-size: 12px;
    background: #dee2e6;
    padding: 2px 6px;
    border-radius: 10px;
}

.alert-configs {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.alert-config-item {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid var(--warning-color);
}

.alert-header {
    margin-bottom: 15px;
}

.alert-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.alert-settings {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-left: 30px;
}

.alert-setting {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.alert-setting label {
    font-size: 14px;
    font-weight: 500;
    color: var(--dark-color);
}

.alert-setting input {
    padding: 8px 10px;
    border: 2px solid #e9ecef;
    border-radius: 4px;
    font-size: 14px;
}

.performance-settings {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.config-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    padding-top: 30px;
    border-top: 2px solid #e9ecef;
    margin-top: 30px;
}

@media (max-width: 768px) {
    .tracked-events,
    .alert-settings {
        grid-template-columns: 1fr;
    }
    
    .config-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<script>
function resetToDefaults() {
    if (confirm('Êtes-vous sûr de vouloir rétablir la configuration par défaut ? Cette action est irréversible.')) {
        // Rétablir les valeurs par défaut
        $('input[name="tracking_enabled"]').prop('checked', true);
        $('input[name="retention_days"]').val(90);
        $('input[name="tracked_events[]"]').prop('checked', true);
        $('input[name*="[enabled]"]').prop('checked', true);
        $('input[name="auto_cleanup"]').prop('checked', true);
        $('input[name="optimize_tables"]').prop('checked', true);
        $('input[name="events_per_hour_limit"]').val(1000);
        
        // Réinitialiser les seuils d'alerte
        $('input[name*="threshold_hours"]').each(function() {
            $(this).val(24);
        });
        
        $('input[name*="threshold_days"]').each(function() {
            $(this).val(60);
        });
        
        $('input[name*="max_reminders"]').each(function() {
            $(this).val(3);
        });
    }
}
</script>