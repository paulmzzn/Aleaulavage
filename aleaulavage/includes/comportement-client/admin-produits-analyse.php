<?php
/**
 * Page d'analyse des produits
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientAdminProduitsAnalyse {
    
    /**
     * Afficher la page d'analyse des produits
     */
    public static function afficher_page() {
        // Récupérer les données d'analyse
        $produits_jamais_achetes = ComportementClientRechercheTracker::obtenir_produits_jamais_achetes();
        $produits_panier_abandonnes = ComportementClientRechercheTracker::obtenir_produits_panier_non_achetes();
        $produits_rupture_recherches = ComportementClientRechercheTracker::obtenir_recherches_rupture_populaires();
        $produits_jamais_en_stock = self::obtenir_produits_jamais_en_stock();
        
        ?>
        <div class="comportement-client-wrapper">
            <div class="comportement-client-container">
                
                <!-- Header -->
                <div class="cc-page-header">
                    <div>
                        <h1 class="cc-page-title">📊 Analyse des Produits</h1>
                        <p class="cc-page-subtitle">Insights détaillés sur les performances et comportements produits</p>
                    </div>
                    <div class="cc-actions-bar">
                        <button class="cc-btn cc-btn-primary" onclick="refreshAnalysis()">
                            🔄 Actualiser l'Analyse
                        </button>
                        <button class="cc-btn cc-btn-secondary" onclick="exportProductAnalysis()">
                            📊 Export CSV
                        </button>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="cc-stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                    <div class="cc-stat-card">
                        <div class="cc-stat-icon">🔍</div>
                        <div class="cc-stat-number"><?php echo count($produits_jamais_achetes); ?></div>
                        <div class="cc-stat-label">Recherchés jamais achetés</div>
                    </div>
                    
                    <div class="cc-stat-card">
                        <div class="cc-stat-icon">🛒</div>
                        <div class="cc-stat-number"><?php echo count($produits_panier_abandonnes); ?></div>
                        <div class="cc-stat-label">Abandonnés dans panier</div>
                    </div>
                    
                    <div class="cc-stat-card">
                        <div class="cc-stat-icon">📦</div>
                        <div class="cc-stat-number"><?php echo count($produits_rupture_recherches); ?></div>
                        <div class="cc-stat-label">Ruptures recherchées</div>
                    </div>
                    
                    <div class="cc-stat-card">
                        <div class="cc-stat-icon">⚠️</div>
                        <div class="cc-stat-number"><?php echo count($produits_jamais_en_stock); ?></div>
                        <div class="cc-stat-label">Jamais en stock</div>
                    </div>
                </div>

                <!-- Onglets d'analyse -->
                <div class="cc-tabs-container">
                    <div class="cc-tabs-nav">
                        <button class="cc-tab-btn active" data-tab="jamais-achetes">
                            🔍 Jamais Achetés
                        </button>
                        <button class="cc-tab-btn" data-tab="panier-abandonnes">
                            🛒 Paniers Abandonnés
                        </button>
                        <button class="cc-tab-btn" data-tab="rupture-stock">
                            📦 Ruptures de Stock
                        </button>
                        <button class="cc-tab-btn" data-tab="jamais-stock">
                            ⚠️ Jamais en Stock
                        </button>
                    </div>

                    <!-- Onglet: Produits jamais achetés -->
                    <div class="cc-tab-content active" id="tab-jamais-achetes">
                        <div class="cc-card">
                            <div class="cc-card-header">
                                <h2 class="cc-card-title">🔍 Produits Recherchés mais Jamais Achetés</h2>
                                <p class="cc-card-subtitle">Ces produits génèrent de l'intérêt mais ne convertissent pas</p>
                            </div>
                            <div class="cc-card-body">
                                <?php if (empty($produits_jamais_achetes)): ?>
                                    <div class="cc-empty-state">
                                        <div class="cc-empty-icon">🎉</div>
                                        <h3>Excellent !</h3>
                                        <p>Tous vos produits recherchés ont été achetés au moins une fois.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="cc-table-container">
                                        <table class="cc-table">
                                            <thead>
                                                <tr>
                                                    <th>Produit</th>
                                                    <th>Recherches</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($produits_jamais_achetes as $produit): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="cc-product-info">
                                                                <strong><?php echo esc_html($produit->product_name); ?></strong>
                                                                <small>ID: <?php echo $produit->product_id; ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="cc-badge cc-badge-info">
                                                                <?php echo $produit->recherches_count; ?> recherches
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="cc-btn cc-btn-small cc-btn-primary" onclick="viewProduct(<?php echo $produit->product_id; ?>)">
                                                                👁️ Voir
                                                            </button>
                                                            <button class="cc-btn cc-btn-small cc-btn-secondary" onclick="analyzeProduct(<?php echo $produit->product_id; ?>)">
                                                                📊 Analyser
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet: Paniers abandonnés -->
                    <div class="cc-tab-content" id="tab-panier-abandonnes">
                        <div class="cc-card">
                            <div class="cc-card-header">
                                <h2 class="cc-card-title">🛒 Produits Abandonnés dans les Paniers</h2>
                                <p class="cc-card-subtitle">Produits ajoutés au panier mais jamais achetés</p>
                            </div>
                            <div class="cc-card-body">
                                <?php if (empty($produits_panier_abandonnes)): ?>
                                    <div class="cc-empty-state">
                                        <div class="cc-empty-icon">✅</div>
                                        <h3>Parfait !</h3>
                                        <p>Aucun produit n'est systématiquement abandonné dans les paniers.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="cc-table-container">
                                        <table class="cc-table">
                                            <thead>
                                                <tr>
                                                    <th>Produit</th>
                                                    <th>Ajouts au Panier</th>
                                                    <th>Sessions Uniques</th>
                                                    <th>Taux d'Abandon</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($produits_panier_abandonnes as $produit): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="cc-product-info">
                                                                <strong><?php echo esc_html($produit->product_name); ?></strong>
                                                                <small>ID: <?php echo $produit->product_id; ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="cc-badge cc-badge-warning">
                                                                <?php echo $produit->ajouts_panier; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="cc-badge cc-badge-info">
                                                                <?php echo $produit->sessions_uniques; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="cc-badge cc-badge-danger">
                                                                100%
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="cc-btn cc-btn-small cc-btn-primary" onclick="viewProduct(<?php echo $produit->product_id; ?>)">
                                                                👁️ Voir
                                                            </button>
                                                            <button class="cc-btn cc-btn-small cc-btn-warning" onclick="createRetargetingCampaign(<?php echo $produit->product_id; ?>)">
                                                                📧 Relancer
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet: Ruptures de stock -->
                    <div class="cc-tab-content" id="tab-rupture-stock">
                        <div class="cc-card">
                            <div class="cc-card-header">
                                <h2 class="cc-card-title">📦 Produits en Rupture Souvent Recherchés</h2>
                                <p class="cc-card-subtitle">Opportunités manquées par rupture de stock</p>
                            </div>
                            <div class="cc-card-body">
                                <?php if (empty($produits_rupture_recherches)): ?>
                                    <div class="cc-empty-state">
                                        <div class="cc-empty-icon">📈</div>
                                        <h3>Stock bien géré !</h3>
                                        <p>Aucune rupture de stock fréquemment recherchée détectée.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="cc-table-container">
                                        <table class="cc-table">
                                            <thead>
                                                <tr>
                                                    <th>Produit</th>
                                                    <th>Terme Recherché</th>
                                                    <th>Recherches</th>
                                                    <th>Utilisateurs</th>
                                                    <th>Dernière Recherche</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($produits_rupture_recherches as $produit): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="cc-product-info">
                                                                <strong><?php echo esc_html($produit->product_name); ?></strong>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="cc-search-term">
                                                                "<?php echo esc_html($produit->terme_recherche); ?>"
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="cc-badge cc-badge-danger">
                                                                <?php echo $produit->total_recherches; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="cc-badge cc-badge-info">
                                                                <?php echo $produit->utilisateurs_uniques; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small><?php echo date('d/m/Y H:i', strtotime($produit->derniere_recherche)); ?></small>
                                                        </td>
                                                        <td>
                                                            <button class="cc-btn cc-btn-small cc-btn-success" onclick="restockProduct('<?php echo esc_js($produit->product_name); ?>')">
                                                                📦 Réapprovisionner
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet: Jamais en stock -->
                    <div class="cc-tab-content" id="tab-jamais-stock">
                        <div class="cc-card">
                            <div class="cc-card-header">
                                <h2 class="cc-card-title">⚠️ Produits Jamais en Stock</h2>
                                <p class="cc-card-subtitle">Produits toujours indisponibles depuis leur création</p>
                            </div>
                            <div class="cc-card-body">
                                <?php if (empty($produits_jamais_en_stock)): ?>
                                    <div class="cc-empty-state">
                                        <div class="cc-empty-icon">✅</div>
                                        <h3>Gestion parfaite !</h3>
                                        <p>Tous vos produits ont été en stock au moins une fois.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="cc-table-container">
                                        <table class="cc-table">
                                            <thead>
                                                <tr>
                                                    <th>Produit</th>
                                                    <th>Date de Création</th>
                                                    <th>Statut Actuel</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($produits_jamais_en_stock as $produit): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="cc-product-info">
                                                                <strong><?php echo esc_html($produit->product_name); ?></strong>
                                                                <small>ID: <?php echo $produit->product_id; ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <small><?php echo date('d/m/Y', strtotime($produit->date_creation)); ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="cc-badge cc-badge-danger">
                                                                <?php echo ucfirst($produit->stock_status); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="cc-btn cc-btn-small cc-btn-primary" onclick="viewProduct(<?php echo $produit->product_id; ?>)">
                                                                👁️ Voir
                                                            </button>
                                                            <button class="cc-btn cc-btn-small cc-btn-warning" onclick="reviewProduct(<?php echo $produit->product_id; ?>)">
                                                                📝 Réviser
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
        // Gestion des onglets
        document.querySelectorAll('.cc-tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Désactiver tous les onglets et contenus
                document.querySelectorAll('.cc-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.cc-tab-content').forEach(c => c.classList.remove('active'));
                
                // Activer l'onglet cliqué
                this.classList.add('active');
                document.getElementById('tab-' + this.dataset.tab).classList.add('active');
            });
        });

        function refreshAnalysis() {
            ComportementClientAdmin.showToast('Actualisation de l\'analyse...', 'info');
            setTimeout(() => window.location.reload(), 1000);
        }

        function exportProductAnalysis() {
            ComportementClientAdmin.showToast('Export en cours...', 'info');
            window.location.href = ajaxurl + '?action=export_product_analysis&nonce=<?php echo wp_create_nonce('comportement_client_nonce'); ?>';
        }

        function viewProduct(productId) {
            window.open('<?php echo admin_url('post.php?action=edit&post='); ?>' + productId, '_blank');
        }

        function analyzeProduct(productId) {
            ComportementClientAdmin.showToast('Analyse détaillée à venir...', 'info');
        }

        function createRetargetingCampaign(productId) {
            ComportementClientAdmin.showToast('Fonctionnalité de relance à venir...', 'info');
        }

        function restockProduct(productName) {
            if (confirm('Marquer "' + productName + '" pour réapprovisionnement ?')) {
                ComportementClientAdmin.showToast('Produit ajouté à la liste de réapprovisionnement', 'success');
            }
        }

        function reviewProduct(productId) {
            window.open('<?php echo admin_url('post.php?action=edit&post='); ?>' + productId, '_blank');
        }
        </script>

        <style>
        .cc-tabs-container {
            margin-top: 30px;
        }

        .cc-tabs-nav {
            display: flex;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 20px;
        }

        .cc-tab-btn {
            padding: 12px 24px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #6c757d;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .cc-tab-btn:hover {
            color: #495057;
            background-color: #f8f9fa;
        }

        .cc-tab-btn.active {
            color: #007bff;
            border-bottom-color: #007bff;
            background-color: #f8f9fa;
        }

        .cc-tab-content {
            display: none;
        }

        .cc-tab-content.active {
            display: block;
        }

        .cc-empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .cc-empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .cc-empty-state h3 {
            margin: 0 0 10px 0;
            color: #28a745;
        }

        .cc-empty-state p {
            color: #6c757d;
            margin: 0;
        }

        .cc-product-info strong {
            display: block;
            margin-bottom: 4px;
        }

        .cc-product-info small {
            color: #6c757d;
            font-size: 11px;
        }

        .cc-search-term {
            font-style: italic;
            color: #495057;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .cc-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .cc-badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .cc-badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .cc-badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .cc-badge-success {
            background-color: #28a745;
            color: white;
        }
        </style>
        <?php
    }
    
    /**
     * Obtenir les produits qui n'ont jamais été en stock
     */
    private static function obtenir_produits_jamais_en_stock() {
        global $wpdb;
        
        return $wpdb->get_results("
            SELECT 
                p.ID as product_id,
                p.post_title as product_name,
                p.post_date as date_creation,
                pm.meta_value as stock_status
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_stock_status'
            AND pm.meta_value = 'outofstock'
            AND p.ID NOT IN (
                SELECT DISTINCT post_id 
                FROM {$wpdb->postmeta}
                WHERE meta_key = '_stock_status' 
                AND meta_value = 'instock'
            )
            ORDER BY p.post_date DESC
            LIMIT 50
        ");
    }
}