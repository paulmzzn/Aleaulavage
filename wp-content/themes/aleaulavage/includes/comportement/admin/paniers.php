<?php
/**
 * Page des paniers clients v2
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-paniers">
    <h1>🛒 Paniers Clients v2</h1>
    
    <div class="comportement-stats-bar">
        <div class="stat-item">
            <span class="stat-number"><?php echo count($paniers_data); ?></span>
            <span class="stat-label">Paniers total</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count(array_filter($paniers_data, function($p) { return $p['type'] === 'connecte'; })); ?></span>
            <span class="stat-label">Utilisateurs connectés</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count(array_filter($paniers_data, function($p) { return $p['type'] === 'anonyme'; })); ?></span>
            <span class="stat-label">Sessions anonymes</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count(array_filter($paniers_data, function($p) { return $p['status'] === 'active'; })); ?></span>
            <span class="stat-label">Paniers actifs</span>
        </div>
    </div>
    
    <div class="comportement-filters-bar">
        <div class="filter-group">
            <label>Filtrer par type :</label>
            <select class="comportement-filter" data-filter="type">
                <option value="">Tous</option>
                <option value="connecte">Utilisateurs connectés</option>
                <option value="anonyme">Sessions anonymes</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Filtrer par statut :</label>
            <select class="comportement-filter" data-filter="status">
                <option value="">Tous</option>
                <option value="active">Actif</option>
                <option value="abandoned">Abandonné</option>
                <option value="converted">Converti</option>
            </select>
        </div>
        
        <div class="filter-group">
            <button class="comportement-btn comportement-btn-secondary refresh-data-btn">
                🔄 Actualiser
            </button>
        </div>
    </div>
    
    <div class="paniers-grid">
        <?php foreach ($paniers_data as $panier): ?>
            <div class="panier-card" 
                 data-type="<?php echo $panier['type']; ?>" 
                 data-status="<?php echo $panier['status']; ?>">
                
                <div class="panier-header">
                    <div class="panier-user-info">
                        <div class="user-avatar">
                            <?php if ($panier['type'] === 'connecte'): ?>
                                👤
                            <?php else: ?>
                                👻
                            <?php endif; ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo esc_html($panier['display_name']); ?></div>
                            <?php if ($panier['user_email']): ?>
                                <div class="user-email"><?php echo esc_html($panier['user_email']); ?></div>
                            <?php endif; ?>
                            <div class="user-id">
                                <?php if ($panier['type'] === 'connecte'): ?>
                                    ID: <?php echo $panier['user_id']; ?>
                                <?php else: ?>
                                    Session: <?php echo substr($panier['session_id'], -12); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panier-meta">
                        <div class="panier-status">
                            <span class="status-badge status-<?php echo $panier['status']; ?>">
                                <?php
                                $status_labels = [
                                    'active' => 'Actif',
                                    'abandoned' => 'Abandonné',
                                    'converted' => 'Converti'
                                ];
                                echo $status_labels[$panier['status']] ?? ucfirst($panier['status']);
                                ?>
                            </span>
                        </div>
                        <div class="panier-date">
                            <?php echo date('d/m/Y H:i', strtotime($panier['derniere_modif'])); ?>
                        </div>
                    </div>
                </div>
                
                <div class="panier-content">
                    <div class="panier-summary">
                        <span class="items-count"><?php echo $panier['total_items']; ?> article<?php echo $panier['total_items'] > 1 ? 's' : ''; ?></span>
                        <?php if (!empty($panier['items'])): ?>
                            <span class="total-value">
                                <?php 
                                $total = array_sum(array_map(function($item) { 
                                    return ($item['price'] ?: 0) * $item['quantity']; 
                                }, $panier['items']));
                                if ($total > 0) {
                                    echo number_format($total, 2) . '€';
                                } else {
                                    echo 'Prix N/A';
                                }
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="panier-items">
                        <?php 
                        $items_to_show = array_slice($panier['items'], 0, 3);
                        foreach ($items_to_show as $item): 
                        ?>
                            <div class="panier-item">
                                <div class="item-name"><?php echo esc_html($item['product_name']); ?></div>
                                <div class="item-details">
                                    <span class="item-quantity">×<?php echo $item['quantity']; ?></span>
                                    <?php if ($item['price']): ?>
                                        <span class="item-price"><?php echo number_format($item['price'], 2); ?>€</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($panier['items']) > 3): ?>
                            <div class="items-more">
                                + <?php echo count($panier['items']) - 3; ?> autre<?php echo count($panier['items']) - 3 > 1 ? 's' : ''; ?> article<?php echo count($panier['items']) - 3 > 1 ? 's' : ''; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="panier-actions">
                    <button class="comportement-btn comportement-btn-primary comportement-btn-sm view-panier-details" 
                            data-panier="<?php echo esc_attr(json_encode($panier)); ?>">
                        👁️ Voir détails
                    </button>
                    
                    <?php if ($panier['type'] === 'connecte' && $panier['user_id']): ?>
                        <a href="<?php echo admin_url('user-edit.php?user_id=' . $panier['user_id']); ?>" 
                           class="comportement-btn comportement-btn-secondary comportement-btn-sm" target="_blank">
                            👤 Profil utilisateur
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($paniers_data)): ?>
            <div class="no-paniers">
                <div class="no-data-icon">🛒</div>
                <h3>Aucun panier trouvé</h3>
                <p>Il n'y a pas de paniers actifs dans les 30 derniers jours.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.comportement-stats-bar {
    display: flex;
    gap: 20px;
    margin: 20px 0;
    padding: 20px;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.stat-item {
    text-align: center;
    flex: 1;
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.comportement-filters-bar {
    display: flex;
    gap: 20px;
    align-items: end;
    margin: 20px 0;
    padding: 15px 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 14px;
    font-weight: 600;
    color: var(--dark-color);
}

.filter-group select {
    padding: 8px 12px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 14px;
    min-width: 150px;
}

.paniers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.panier-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-left: 4px solid #e9ecef;
}

.panier-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.panier-card[data-type="connecte"] {
    border-left-color: #28a745;
}

.panier-card[data-type="anonyme"] {
    border-left-color: #ffc107;
}

.panier-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.panier-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
}

.user-name {
    font-weight: 600;
    color: var(--dark-color);
    font-size: 16px;
}

.user-email {
    font-size: 12px;
    color: #6c757d;
}

.user-id {
    font-size: 11px;
    color: #6c757d;
    font-family: monospace;
}

.panier-meta {
    text-align: right;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 5px;
    display: inline-block;
}

.status-badge.status-active {
    background: #d1ecf1;
    color: #0c5460;
}

.status-badge.status-abandoned {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.status-converted {
    background: #d4edda;
    color: #155724;
}

.panier-date {
    font-size: 12px;
    color: #6c757d;
}

.panier-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.items-count {
    font-weight: 600;
    color: var(--dark-color);
}

.total-value {
    font-weight: 700;
    color: var(--primary-color);
    font-size: 16px;
}

.panier-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
}

.panier-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.item-name {
    font-size: 14px;
    color: var(--dark-color);
    flex: 1;
    margin-right: 10px;
}

.item-details {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.item-quantity {
    color: #6c757d;
    font-weight: 600;
}

.item-price {
    color: var(--primary-color);
    font-weight: 600;
}

.items-more {
    font-size: 12px;
    color: #6c757d;
    font-style: italic;
    text-align: center;
    padding: 5px;
}

.panier-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.no-paniers {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.no-data-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.no-paniers h3 {
    margin: 0 0 10px 0;
    color: var(--dark-color);
}

.no-paniers p {
    margin: 0;
    color: #6c757d;
}

@media (max-width: 768px) {
    .paniers-grid {
        grid-template-columns: 1fr;
    }
    
    .comportement-stats-bar {
        flex-wrap: wrap;
    }
    
    .comportement-filters-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .panier-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .panier-actions {
        justify-content: center;
    }
}

/* Styles pour les boutons supplémentaires */
.comportement-btn-success {
    background: #28a745;
    color: white;
}

.comportement-btn-success:hover {
    background: #218838;
    color: white;
}

.comportement-btn-danger {
    background: #dc3545;
    color: white;
}

.comportement-btn-danger:hover {
    background: #c82333;
    color: white;
}

/* Styles pour la modal simplifiée */
.comportement-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease;
}

.comportement-modal {
    background: white;
    border-radius: 8px;
    max-width: 900px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    position: relative;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    animation: slideIn 0.3s ease;
}

.modal-close {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #f5f5f5;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 4px;
    cursor: pointer;
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s ease;
    color: #666;
}

.modal-close:hover {
    background: #e5e5e5;
}

.modal-close svg {
    width: 16px;
    height: 16px;
}

.panier-details-modal {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.modal-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 20px 24px;
    min-height: auto;
}

.modal-header h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #212529;
}

.header-info {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: #6c757d;
}

.user-type {
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.last-update {
    font-size: 11px;
}

.modal-tabs {
    background: #fff;
    display: flex;
    border-bottom: 1px solid #dee2e6;
    padding: 0 24px;
}

.tab-button {
    background: none;
    border: none;
    padding: 12px 16px;
    color: #6c757d;
    font-weight: 500;
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
    font-size: 14px;
    border-bottom: 2px solid transparent;
}

.tab-button:hover {
    color: #495057;
}

.tab-button.active {
    color: #007cba;
    border-bottom-color: #007cba;
}

.modal-content {
    flex: 1;
    overflow: hidden;
    background: white;
    display: flex;
    flex-direction: column;
}

.tab-content {
    display: none;
    flex: 1;
    overflow-y: auto;
    padding: 20px 24px;
    max-height: calc(80vh - 140px);
}

.tab-content.active {
    display: block;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.item-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background 0.2s ease;
}

.item-card:hover {
    background: #f1f3f4;
}

.item-main {
    flex: 1;
    margin-right: 16px;
}

.item-name {
    font-weight: 500;
    color: #212529;
    font-size: 14px;
    margin-bottom: 2px;
}

.item-meta {
    color: #6c757d;
    font-size: 11px;
    font-family: monospace;
}

.item-quantity {
    background: #007cba;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 500;
    font-size: 12px;
    margin-right: 12px;
}

.item-price {
    font-weight: 600;
    color: #28a745;
    font-size: 14px;
    text-align: right;
    min-width: 60px;
}

.total-card {
    background: #e8f5e8;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 16px;
    border-radius: 6px;
    text-align: center;
    margin-top: 16px;
}

.total-label {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 4px;
}

.total-value {
    font-size: 18px;
    font-weight: 700;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #64748b;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
    font-size: 16px;
    font-style: italic;
}

@keyframes fadeIn {
    from { 
        opacity: 0; 
    }
    to { 
        opacity: 1; 
    }
}

@keyframes slideIn {
    from { 
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to { 
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@media (max-width: 768px) {
    .comportement-modal {
        width: 95%;
        max-height: 85vh;
    }
    
    .modal-tabs {
        padding: 0 16px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .tab-content {
        padding: 16px;
    }
    
    .item-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 12px;
    }
    
    .item-main {
        margin-right: 0;
        width: 100%;
    }
    
    .item-quantity, .item-price {
        align-self: flex-end;
        margin: 0;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    console.log('Script paniers chargé');
    console.log('Nombre de filtres trouvés:', $('.comportement-filter').length);
    console.log('Nombre de cartes paniers trouvées:', $('.panier-card').length);
    
    // Filtrage des paniers
    $('.comportement-filter').on('change', function() {
        console.log('Filtre changé');
        applyAllFilters();
        updateStats();
    });
    
    function applyAllFilters() {
        const typeFilter = $('.comportement-filter[data-filter="type"]').val();
        const statusFilter = $('.comportement-filter[data-filter="status"]').val();
        
        console.log('Applying filters - Type:', typeFilter, 'Status:', statusFilter);
        
        $('.panier-card').each(function() {
            const $card = $(this);
            const cardType = $card.attr('data-type');
            const cardStatus = $card.attr('data-status');
            
            console.log('Card - Type:', cardType, 'Status:', cardStatus);
            
            let showCard = true;
            
            // Apply type filter
            if (typeFilter !== '' && cardType !== typeFilter) {
                showCard = false;
            }
            
            // Apply status filter
            if (statusFilter !== '' && cardStatus !== statusFilter) {
                showCard = false;
            }
            
            if (showCard) {
                $card.show();
            } else {
                $card.hide();
            }
        });
    }
    
    // Voir les détails d'un panier
    $('.view-panier-details').on('click', function() {
        console.log('Voir détails cliqué');
        const panierDataRaw = $(this).attr('data-panier');
        console.log('Données panier brutes:', panierDataRaw);
        
        try {
            const panierData = JSON.parse(panierDataRaw);
            console.log('Données panier parsées:', panierData);
            
            // Récupérer les données additionnelles via AJAX
            const userId = panierData.user_id;
            const sessionId = panierData.session_id;
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_user_additional_data',
                    user_id: userId,
                    session_id: sessionId,
                    nonce: '<?php echo wp_create_nonce("comportement_details"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        panierData.additionalData = response.data;
                        showPanierDetails(panierData);
                    } else {
                        console.error('Erreur récupération données:', response.data);
                        // Afficher quand même la modal sans les données additionnelles
                        panierData.additionalData = {
                            searches: [],
                            removals: [],
                            product_views: []
                        };
                        showPanierDetails(panierData);
                    }
                },
                error: function() {
                    console.error('Erreur AJAX');
                    // Afficher quand même la modal sans les données additionnelles
                    panierData.additionalData = {
                        searches: [],
                        removals: [],
                        product_views: []
                    };
                    showPanierDetails(panierData);
                }
            });
            
        } catch (e) {
            console.error('Erreur parsing JSON panier:', e);
            alert('Erreur lors de l\'affichage des détails du panier');
        }
    });
    
    
    // Bouton refresh
    $('.refresh-data-btn').on('click', function() {
        // Reset filters
        $('.comportement-filter').val('');
        applyAllFilters();
        updateStats();
    });
    
    function updateStats() {
        const visibleCards = $('.panier-card:visible');
        const totalVisible = visibleCards.length;
        const connectes = visibleCards.filter('[data-type="connecte"]').length;
        const anonymes = visibleCards.filter('[data-type="anonyme"]').length;
        const actifs = visibleCards.filter('[data-status="active"]').length;
        
        $('.stat-item:eq(0) .stat-number').text(totalVisible);
        $('.stat-item:eq(1) .stat-number').text(connectes);
        $('.stat-item:eq(2) .stat-number').text(anonymes);
        $('.stat-item:eq(3) .stat-number').text(actifs);
    }
    
    function showPanierDetails(panierData) {
        console.log('showPanierDetails appelée avec:', panierData);
        
        // Vérifications de sécurité
        if (!panierData || !panierData.items) {
            console.error('Données panier invalides:', panierData);
            alert('Données de panier invalides');
            return;
        }
        
        const additionalData = panierData.additionalData || {searches: [], removals: [], product_views: []};
        
        let modalContent = `
            <div class="panier-details-modal">
                <div class="modal-header">
                    <h3>${panierData.display_name || 'Utilisateur inconnu'}</h3>
                    <div class="header-info">
                        <span class="user-type">${panierData.type === 'connecte' ? 'Connecté' : 'Anonyme'}</span>
                        ${panierData.user_email ? `• ${panierData.user_email}` : ''}
                        <span class="last-update">Modifié: ${panierData.derniere_modif ? new Date(panierData.derniere_modif).toLocaleString('fr-FR') : 'N/A'}</span>
                    </div>
                </div>
                
                <div class="modal-tabs">
                    <button class="tab-button active" data-tab="panier">Panier (${panierData.items ? panierData.items.length : 0})</button>
                    <button class="tab-button" data-tab="recherches">Recherches (${additionalData.searches.length})</button>
                    <button class="tab-button" data-tab="suppressions">Supprimés (${additionalData.removals.length})</button>
                    <button class="tab-button" data-tab="vues">Consultés (${additionalData.product_views.length})</button>
                </div>
                
                <div class="modal-content">
                    <!-- Onglet Panier -->
                    <div class="tab-content active" id="tab-panier">
                        <div class="items-list">
                            ${panierData.items && panierData.items.length > 0 ? 
                                panierData.items.map(item => `
                                    <div class="item-card">
                                        <div class="item-main">
                                            <div class="item-name">${item.product_name || 'Produit inconnu'}</div>
                                            <div class="item-meta">ID: ${item.product_id || 'N/A'}${item.variation_id ? ` • Variation: ${item.variation_id}` : ''}</div>
                                        </div>
                                        <div class="item-quantity">×${item.quantity || 1}</div>
                                        <div class="item-price">${item.price ? (parseFloat(item.price) * (item.quantity || 1)).toFixed(2) + '€' : 'N/A'}</div>
                                    </div>
                                `).join('')
                                : '<div class="empty-state"><div class="empty-icon">🛒</div><p>Aucun produit dans ce panier</p></div>'
                            }
                        </div>
                        ${panierData.items && panierData.items.length > 0 ? `
                            <div class="total-card">
                                <div class="total-label">Total du panier</div>
                                <div class="total-value">${panierData.items.reduce((sum, item) => sum + (parseFloat(item.price || 0) * (item.quantity || 1)), 0).toFixed(2)}€</div>
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- Onglet Recherches -->
                    <div class="tab-content" id="tab-recherches">
                        <div class="items-list">
                            ${additionalData.searches && additionalData.searches.length > 0 ?
                                additionalData.searches.map(search => `
                                    <div class="item-card">
                                        <div class="item-main">
                                            <div class="item-name">🔍 "${search.terme_recherche}"</div>
                                            <div class="item-meta">${search.resultats_count || 0} résultats • ${new Date(search.date_recherche).toLocaleString('fr-FR')}</div>
                                        </div>
                                    </div>
                                `).join('')
                                : '<div class="empty-state"><div class="empty-icon">🔍</div><p>Aucune recherche effectuée</p></div>'
                            }
                        </div>
                    </div>
                    
                    <!-- Onglet Suppressions -->
                    <div class="tab-content" id="tab-suppressions">
                        <div class="items-list">
                            ${additionalData.removals && additionalData.removals.length > 0 ?
                                additionalData.removals.map(removal => `
                                    <div class="item-card">
                                        <div class="item-main">
                                            <div class="item-name">🗑️ ${removal.product_name || 'Produit inconnu'}</div>
                                            <div class="item-meta">Supprimé le ${new Date(removal.removed_at).toLocaleString('fr-FR')}</div>
                                        </div>
                                        <div class="item-quantity">×${removal.quantity || 1}</div>
                                        <div class="item-price">${removal.product_price ? (parseFloat(removal.product_price) * (removal.quantity || 1)).toFixed(2) + '€' : 'N/A'}</div>
                                    </div>
                                `).join('')
                                : '<div class="empty-state"><div class="empty-icon">🗑️</div><p>Aucun produit supprimé</p></div>'
                            }
                        </div>
                    </div>
                    
                    <!-- Onglet Vues produits -->
                    <div class="tab-content" id="tab-vues">
                        <div class="items-list">
                            ${additionalData.product_views && additionalData.product_views.length > 0 ?
                                additionalData.product_views.map(view => {
                                    const eventData = JSON.parse(view.event_data || '{}');
                                    return `
                                        <div class="item-card">
                                            <div class="item-main">
                                                <div class="item-name">👁️ ${eventData.product_name || 'Produit inconnu'}</div>
                                                <div class="item-meta">Vu le ${new Date(view.timestamp).toLocaleString('fr-FR')}</div>
                                            </div>
                                            <div class="item-price">${eventData.price ? parseFloat(eventData.price).toFixed(2) + '€' : 'N/A'}</div>
                                        </div>
                                    `;
                                }).join('')
                                : '<div class="empty-state"><div class="empty-icon">👁️</div><p>Aucun produit consulté</p></div>'
                            }
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Créer une modal moderne
        const modal = $(`
            <div class="comportement-modal-overlay">
                <div class="comportement-modal">
                    <button class="modal-close">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                    ${modalContent}
                </div>
            </div>
        `);
        
        $('body').append(modal);
        
        // Gestion des onglets
        modal.find('.tab-button').on('click', function() {
            const tabName = $(this).data('tab');
            
            // Activer l'onglet cliqué
            modal.find('.tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Afficher le contenu correspondant
            modal.find('.tab-content').removeClass('active');
            modal.find('#tab-' + tabName).addClass('active');
        });
        
        // Events pour fermer la modal
        modal.find('.modal-close').on('click', function() {
            modal.remove();
            $(document).off('keydown.modal');
        });
        
        modal.on('click', function(e) {
            if (e.target === this) {
                modal.remove();
                $(document).off('keydown.modal');
            }
        });
        
        // Fermer avec Escape
        $(document).on('keydown.modal', function(e) {
            if (e.keyCode === 27) {
                modal.remove();
                $(document).off('keydown.modal');
            }
        });
    }
});
</script>