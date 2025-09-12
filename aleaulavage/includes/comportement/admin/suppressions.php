<?php
/**
 * Page d'historique des suppressions de panier
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-suppressions">
    <h1>🗑️ Historique des Suppressions de Panier</h1>
    
    <div class="comportement-stats-bar">
        <div class="stat-item">
            <span class="stat-number"><?php echo count($suppressions_data); ?></span>
            <span class="stat-label">Suppressions totales</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count(array_filter($suppressions_data, function($s) { return $s->user_id; })); ?></span>
            <span class="stat-label">Utilisateurs connectés</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count(array_filter($suppressions_data, function($s) { return !$s->user_id; })); ?></span>
            <span class="stat-label">Sessions anonymes</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">
                <?php 
                $total_supprimees = array_sum(array_map(function($s) { return $s->quantity; }, $suppressions_data));
                echo $total_supprimees;
                ?>
            </span>
            <span class="stat-label">Quantités supprimées</span>
        </div>
    </div>
    
    <?php if (!empty($suppressions_data)): ?>
        <div class="suppressions-table">
            <table class="comportement-table">
                <thead>
                    <tr>
                        <th>Date/Heure</th>
                        <th>Utilisateur</th>
                        <th>Produit Supprimé</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total ligne</th>
                        <th>Total panier avant</th>
                        <th>Raison</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppressions_data as $suppression): ?>
                        <tr>
                            <td>
                                <div class="suppression-date">
                                    <strong><?php echo date('d/m/Y H:i:s', strtotime($suppression->removed_at)); ?></strong>
                                </div>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php if ($suppression->user_id): ?>
                                            👤
                                        <?php else: ?>
                                            👻
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-details">
                                        <?php if ($suppression->display_name): ?>
                                            <div class="user-name"><?php echo esc_html($suppression->display_name); ?></div>
                                            <?php if ($suppression->user_email): ?>
                                                <div class="user-email"><?php echo esc_html($suppression->user_email); ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="session-info">
                                                Session: <?php echo substr($suppression->session_id, -12); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="product-info">
                                    <strong><?php echo esc_html($suppression->product_name); ?></strong>
                                    <div class="product-meta">
                                        ID: <?php echo $suppression->product_id; ?>
                                        <?php if ($suppression->variation_id): ?>
                                            | Variation: <?php echo $suppression->variation_id; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="quantity-badge">
                                    ×<?php echo intval($suppression->quantity); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($suppression->product_price): ?>
                                    <span class="price"><?php echo number_format($suppression->product_price, 2); ?>€</span>
                                <?php else: ?>
                                    <span class="price-na">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($suppression->line_total): ?>
                                    <span class="line-total"><?php echo number_format($suppression->line_total, 2); ?>€</span>
                                <?php else: ?>
                                    <span class="price-na">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($suppression->cart_total_before): ?>
                                    <span class="cart-total"><?php echo number_format($suppression->cart_total_before, 2); ?>€</span>
                                <?php else: ?>
                                    <span class="price-na">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="reason-badge reason-<?php echo esc_attr($suppression->reason); ?>">
                                    <?php 
                                    $reasons = [
                                        'user_action' => 'Action utilisateur',
                                        'quantity_change' => 'Changement quantité',
                                        'cart_clear' => 'Vidage panier',
                                        'session_expire' => 'Session expirée'
                                    ];
                                    echo $reasons[$suppression->reason] ?? ucfirst($suppression->reason);
                                    ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-suppressions">
            <div class="no-data-icon">🗑️</div>
            <h3>Aucune suppression trouvée</h3>
            <p>L'historique des suppressions de panier s'affichera ici une fois que des utilisateurs supprimeront des produits de leur panier.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.comportement-stats-bar {
    display: flex;
    gap: 20px;
    margin: 20px 0;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stat-item {
    text-align: center;
    flex: 1;
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #dc3545;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.suppressions-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(45deg, #dc3545, #c82333);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
}

.user-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.user-email {
    font-size: 11px;
    color: #6c757d;
}

.session-info {
    font-size: 12px;
    color: #6c757d;
    font-family: monospace;
}

.product-info {
    max-width: 200px;
}

.product-meta {
    font-size: 11px;
    color: #6c757d;
    margin-top: 2px;
}

.quantity-badge {
    background: #dc3545;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.price, .line-total, .cart-total {
    font-weight: 600;
    color: #dc3545;
}

.price-na {
    color: #6c757d;
    font-style: italic;
    font-size: 12px;
}

.reason-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.reason-user_action {
    background: #f8d7da;
    color: #721c24;
}

.reason-quantity_change {
    background: #fff3cd;
    color: #856404;
}

.reason-cart_clear {
    background: #d1ecf1;
    color: #0c5460;
}

.reason-session_expire {
    background: #e2e3ff;
    color: #383d41;
}

.suppression-date strong {
    color: #2c3e50;
}

.no-suppressions {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-data-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.no-suppressions h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.no-suppressions p {
    margin: 0;
    color: #6c757d;
}

@media (max-width: 768px) {
    .comportement-stats-bar {
        flex-wrap: wrap;
    }
    
    .user-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .product-info {
        max-width: none;
    }
}
</style>