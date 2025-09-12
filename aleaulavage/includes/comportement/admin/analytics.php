<?php
/**
 * Page d'analytics avancées
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-analytics">
    <h1>📊 Analytics Avancées</h1>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Analyse de Cohortes</h3>
            <p class="comportement-card-subtitle">Suivi de la rétention des utilisateurs par mois d'inscription</p>
        </div>
        
        <div class="chart-container">
            <canvas id="cohortChart" height="400"></canvas>
        </div>
        
        <?php if (!empty($cohort_data)): ?>
        <div class="cohort-summary">
            <h4>Résumé des Cohortes</h4>
            <div class="cohort-list">
                <?php foreach ($cohort_data as $month => $data): ?>
                    <div class="cohort-item">
                        <strong><?php echo $month; ?></strong>
                        <span><?php echo $data['size']; ?> utilisateurs</span>
                        <span>Rétention 1 mois: <?php echo $data['retention'][1] ?? 0; ?>%</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Segmentation des Utilisateurs</h3>
            <p class="comportement-card-subtitle">Répartition automatique des utilisateurs par comportement</p>
        </div>
        
        <div class="segments-overview">
            <?php foreach ($segments as $segment_key => $segment_config): ?>
                <div class="segment-card">
                    <div class="segment-color" style="background-color: <?php echo $segment_config['color']; ?>"></div>
                    <div class="segment-info">
                        <h4><?php echo $segment_config['label']; ?></h4>
                        <p><?php echo $segment_config['description']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
</div>

<style>
.cohort-summary {
    margin-top: 20px;
}

.cohort-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.cohort-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid var(--primary-color);
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.segments-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.segment-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: var(--transition);
}

.segment-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.segment-color {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.segment-info h4 {
    margin: 0 0 5px 0;
    color: var(--dark-color);
}

.segment-info p {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
}
</style>