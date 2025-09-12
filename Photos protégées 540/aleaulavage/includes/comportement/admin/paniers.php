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
                    
                    <?php if ($panier['status'] === 'active'): ?>
                        <button class="comportement-btn comportement-btn-warning comportement-btn-sm send-reminder" 
                                data-user-id="<?php echo $panier['user_id']; ?>" 
                                data-session-id="<?php echo $panier['session_id']; ?>">
                            ✉️ Rappel
                        </button>
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
</style>

<script>
$(document).ready(function() {
    // Filtrage des paniers
    $('.comportement-filter').on('change', function() {
        const filterType = $(this).data('filter');
        const filterValue = $(this).val();
        
        $('.panier-card').each(function() {
            const cardValue = $(this).data(filterType);
            
            if (filterValue === '' || cardValue === filterValue) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        updateStats();
    });
    
    // Voir les détails d'un panier
    $('.view-panier-details').on('click', function() {
        const panierData = $(this).data('panier');
        showPanierDetails(panierData);
    });
    
    // Envoyer un rappel
    $('.send-reminder').on('click', function() {
        const userId = $(this).data('user-id');
        const sessionId = $(this).data('session-id');
        
        if (confirm('Envoyer un rappel de panier abandonné ?')) {
            // Simuler l'envoi de rappel
            $(this).prop('disabled', true).text('✉️ Envoyé !');
            setTimeout(() => {
                $(this).prop('disabled', false).text('✉️ Rappel');
            }, 3000);
        }
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
        let modalContent = `
            <div class="panier-details-modal">
                <h3>Détails du panier - ${panierData.display_name}</h3>
                
                <div class="modal-user-info">
                    <strong>Type:</strong> ${panierData.type === 'connecte' ? 'Utilisateur connecté' : 'Session anonyme'}<br>
                    ${panierData.user_email ? `<strong>Email:</strong> ${panierData.user_email}<br>` : ''}
                    <strong>Dernière modification:</strong> ${new Date(panierData.derniere_modif).toLocaleString('fr-FR')}<br>
                    <strong>Statut:</strong> <span class="status-badge status-${panierData.status}">${panierData.status}</span>
                </div>
                
                <h4>Produits (${panierData.items.length})</h4>
                <div class="modal-items-list">
                    ${panierData.items.map(item => `
                        <div class="modal-item">
                            <div class="item-info">
                                <strong>${item.product_name}</strong><br>
                                <small>ID: ${item.product_id}</small>
                            </div>
                            <div class="item-quantity">×${item.quantity}</div>
                            <div class="item-price">${item.price ? (parseFloat(item.price) * item.quantity).toFixed(2) + '€' : 'N/A'}</div>
                        </div>
                    `).join('')}
                </div>
                
                ${panierData.items.length > 0 ? `
                    <div class="modal-total">
                        <strong>Total: ${panierData.items.reduce((sum, item) => sum + (parseFloat(item.price || 0) * item.quantity), 0).toFixed(2)}€</strong>
                    </div>
                ` : ''}
            </div>
        `;
        
        ComportementAdminJS.createModal('Détails du panier', modalContent);
    }
});
</script>