<?php
/**
 * Page d'exports et rapports
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-exports">
    <h1>📋 Exports & Rapports</h1>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Export des Données</h3>
            <p class="comportement-card-subtitle">Exportez vos données comportementales dans différents formats</p>
        </div>
        
        <form id="exportForm" class="export-form">
            <div class="export-options">
                <div class="export-option" data-type="paniers">
                    <div class="export-icon">🛒</div>
                    <div class="export-title">Paniers</div>
                    <div class="export-description">Historique des paniers clients</div>
                </div>
                
                <div class="export-option" data-type="recherches">
                    <div class="export-icon">🔍</div>
                    <div class="export-title">Recherches</div>
                    <div class="export-description">Termes de recherche utilisés</div>
                </div>
                
                <div class="export-option" data-type="events">
                    <div class="export-icon">📊</div>
                    <div class="export-title">Événements</div>
                    <div class="export-description">Tous les événements trackés</div>
                </div>
                
                <div class="export-option" data-type="users_behavior">
                    <div class="export-icon">👤</div>
                    <div class="export-title">Comportements</div>
                    <div class="export-description">Analyse par utilisateur</div>
                </div>
            </div>
            
            <div class="export-formats">
                <h4>Format d'export :</h4>
                <label><input type="radio" name="format" value="csv" checked> CSV (Excel)</label>
                <label><input type="radio" name="format" value="json"> JSON</label>
                <label><input type="radio" name="format" value="pdf"> PDF</label>
            </div>
            
            <div class="export-filters">
                <h4>Filtres :</h4>
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Date de début :</label>
                        <input type="date" name="date_from" class="comportement-filter">
                    </div>
                    <div class="filter-group">
                        <label>Date de fin :</label>
                        <input type="date" name="date_to" class="comportement-filter">
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Type d'utilisateur :</label>
                        <select name="user_type" class="comportement-filter">
                            <option value="">Tous</option>
                            <option value="registered">Connectés</option>
                            <option value="anonymous">Anonymes</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Statut :</label>
                        <select name="status" class="comportement-filter">
                            <option value="">Tous</option>
                            <option value="active">Actif</option>
                            <option value="abandoned">Abandonné</option>
                            <option value="converted">Converti</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="export-actions">
                <button type="submit" class="comportement-btn comportement-btn-primary comportement-export-btn" data-type="" data-format="">
                    📥 Lancer l'Export
                </button>
            </div>
        </form>
    </div>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Exports Automatiques</h3>
            <p class="comportement-card-subtitle">Configuration des exports programmés</p>
        </div>
        
        <div class="auto-exports">
            <div class="auto-export-item">
                <div class="auto-export-info">
                    <h4>Export Quotidien</h4>
                    <p>Sauvegarde automatique des données critiques chaque jour</p>
                </div>
                <div class="auto-export-status">
                    <span class="status-badge status-active">Actif</span>
                </div>
            </div>
            
            <div class="auto-export-item">
                <div class="auto-export-info">
                    <h4>Rapport Hebdomadaire</h4>
                    <p>Compilation complète des métriques chaque semaine</p>
                </div>
                <div class="auto-export-status">
                    <span class="status-badge status-active">Actif</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Historique des Exports</h3>
        </div>
        
        <div class="export-history">
            <div class="export-history-item">
                <div class="export-file-info">
                    <strong>comportement_events_2024-08-14.csv</strong>
                    <small>Exporté le 14/08/2024 à 15:30</small>
                </div>
                <div class="export-file-size">2.3 MB</div>
                <div class="export-file-actions">
                    <a href="#" class="comportement-btn comportement-btn-sm">Télécharger</a>
                </div>
            </div>
            
            <div class="export-history-item">
                <div class="export-file-info">
                    <strong>comportement_paniers_2024-08-13.json</strong>
                    <small>Exporté le 13/08/2024 à 10:15</small>
                </div>
                <div class="export-file-size">856 KB</div>
                <div class="export-file-actions">
                    <a href="#" class="comportement-btn comportement-btn-sm">Télécharger</a>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
.export-form {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.export-formats {
    display: flex;
    gap: 20px;
    align-items: center;
}

.export-formats h4 {
    margin: 0;
    color: var(--dark-color);
}

.export-formats label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 500;
}

.export-filters h4 {
    margin: 0 0 15px 0;
    color: var(--dark-color);
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-weight: 600;
    color: var(--dark-color);
    font-size: 14px;
}

.filter-group input,
.filter-group select {
    padding: 10px 12px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 14px;
    transition: var(--transition);
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--primary-color);
}

.export-actions {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.auto-exports {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.auto-export-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid var(--info-color);
}

.auto-export-info h4 {
    margin: 0 0 5px 0;
    color: var(--dark-color);
}

.auto-export-info p {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.export-history {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.export-history-item {
    display: grid;
    grid-template-columns: 1fr auto auto;
    align-items: center;
    gap: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: var(--transition);
}

.export-history-item:hover {
    background: #e9ecef;
}

.export-file-info strong {
    display: block;
    color: var(--dark-color);
    margin-bottom: 4px;
}

.export-file-info small {
    color: #6c757d;
    font-size: 12px;
}

.export-file-size {
    font-weight: 600;
    color: var(--info-color);
}
</style>

<script>
$(document).ready(function() {
    // Gestion de la sélection du type d'export
    $('.export-option').click(function() {
        $('.export-option').removeClass('selected');
        $(this).addClass('selected');
        
        const type = $(this).data('type');
        $('.comportement-export-btn').attr('data-type', type);
        
        updateExportButton();
    });
    
    // Gestion du format
    $('input[name="format"]').change(function() {
        const format = $(this).val();
        $('.comportement-export-btn').attr('data-format', format);
        
        updateExportButton();
    });
    
    function updateExportButton() {
        const type = $('.export-option.selected').data('type') || '';
        const format = $('input[name="format"]:checked').val() || 'csv';
        
        if (type) {
            $('.comportement-export-btn').prop('disabled', false)
                .html(`📥 Exporter ${type.charAt(0).toUpperCase() + type.slice(1)} (${format.toUpperCase()})`);
        } else {
            $('.comportement-export-btn').prop('disabled', true)
                .html('📥 Sélectionnez un type de données');
        }
    }
    
    // Sélectionner le premier type par défaut
    $('.export-option').first().click();
});
</script>