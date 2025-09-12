<?php
/**
 * Page de segmentation utilisateur
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-segmentation">
    <h1>🎯 Segmentation Intelligente</h1>
    
    <div class="segment-filters">
        <button class="segment-filter active" data-segment="all">Tous</button>
        <?php foreach ($segments_config as $segment_key => $segment_config): ?>
            <button class="segment-filter" data-segment="<?php echo $segment_key; ?>" 
                    style="border-color: <?php echo $segment_config['color']; ?>">
                <?php echo $segment_config['label']; ?>
            </button>
        <?php endforeach; ?>
    </div>
    
    <div class="segmented-users-grid">
        <?php foreach ($segmented_users as $user_data): ?>
            <div class="user-card user-row" data-segment="<?php echo $user_data['segment']; ?>">
                <div class="user-header">
                    <div class="user-info">
                        <strong><?php echo $user_data['user']->display_name; ?></strong>
                        <small><?php echo $user_data['user']->user_email; ?></small>
                    </div>
                    <span class="comportement-user-segment segment-<?php echo $user_data['segment']; ?> user-segment" 
                          data-segment="<?php echo $user_data['segment']; ?>">
                        <?php echo $segments_config[$user_data['segment']]['label'] ?? ucfirst($user_data['segment']); ?>
                    </span>
                </div>
                
                <div class="user-metrics">
                    <div class="metric">
                        <span class="metric-value"><?php echo $user_data['insights']['navigation']['total_page_views'] ?? 0; ?></span>
                        <span class="metric-label">Pages vues</span>
                    </div>
                    <div class="metric">
                        <span class="metric-value"><?php echo $user_data['insights']['products']['products_viewed'] ?? 0; ?></span>
                        <span class="metric-label">Produits vus</span>
                    </div>
                    <div class="metric">
                        <span class="metric-value"><?php echo $user_data['insights']['products']['cart_additions'] ?? 0; ?></span>
                        <span class="metric-label">Ajouts panier</span>
                    </div>
                    <div class="metric">
                        <span class="metric-value"><?php echo number_format($user_data['insights']['engagement_score'] ?? 0); ?>%</span>
                        <span class="metric-label">Engagement</span>
                    </div>
                </div>
                
                <div class="user-actions">
                    <button class="comportement-btn comportement-btn-primary comportement-btn-sm view-user-insights" 
                            data-user-id="<?php echo $user_data['user']->ID; ?>" 
                            data-user-name="<?php echo esc_attr($user_data['user']->display_name); ?>">
                        📊 Voir détails
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
</div>

<style>
.segment-filters {
    display: flex;
    gap: 10px;
    margin: 20px 0;
    flex-wrap: wrap;
}

.segment-filter {
    padding: 8px 16px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 20px;
    cursor: pointer;
    transition: var(--transition);
    font-weight: 500;
}

.segment-filter.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.segment-filter:hover {
    border-color: var(--primary-color);
}

.segmented-users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.user-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.user-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.user-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.user-info strong {
    display: block;
    color: var(--dark-color);
    margin-bottom: 2px;
}

.user-info small {
    color: #6c757d;
    font-size: 12px;
}

.user-metrics {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 15px;
}

.metric {
    text-align: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.metric-value {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
}

.metric-label {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 5px;
}

.user-actions {
    text-align: center;
}
</style>