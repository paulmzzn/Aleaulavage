<?php
/**
 * Page d'administration des recherches clients
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientAdminRecherches {
    
    /**
     * Afficher la page des recherches
     */
    public static function afficher_page() {
        global $wpdb;
        
        ?>
        <div class="comportement-client-wrapper">
            <div class="comportement-client-container">
                
                <!-- Header Premium -->
                <div class="cc-page-header">
                    <div>
                        <h1 class="cc-page-title">🔍 Analyse des Recherches</h1>
                        <p class="cc-page-subtitle">Insights sur les comportements de recherche par appareil</p>
                    </div>
                    <div class="cc-actions-bar">
                        <button class="cc-btn cc-btn-refresh" id="refresh-recherches">
                            <span class="cc-refresh-icon">🔄</span>
                            Actualiser
                        </button>
                        <button class="cc-btn cc-btn-export" data-export-type="recherches">
                            📊 Export CSV
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=comportement-clients'); ?>" class="cc-btn cc-btn-secondary">
                            ← Dashboard
                        </a>
                    </div>
                </div>

                <!-- Statistiques des recherches -->
                <?php self::afficher_stats_recherches_premium(); ?>
                
                <!-- Filtres modernes -->
                <?php self::afficher_filtres_premium(); ?>
                
                <!-- Termes les plus recherchés -->
                <div class="cc-card">
                    <div class="cc-card-header">
                        <h2 class="cc-card-title">🏆 Termes les Plus Recherchés</h2>
                        <div class="cc-actions-bar">
                            <button class="cc-btn cc-btn-secondary" onclick="toggleTopTerms()">
                                <span id="toggle-terms-text">Masquer</span>
                            </button>
                        </div>
                    </div>
                    <div class="cc-card-body" id="top-terms-section">
                        <?php self::afficher_termes_populaires_premium(); ?>
                    </div>
                </div>
                
                <!-- Recherches récentes -->
                <div class="cc-card">
                    <div class="cc-card-header">
                        <h2 class="cc-card-title">⏰ Recherches Récentes</h2>
                        <div class="cc-actions-bar">
                            <button class="cc-btn cc-btn-secondary" onclick="toggleRecentSearches()">
                                <span id="toggle-recent-text">Masquer</span>
                            </button>
                        </div>
                    </div>
                    <div class="cc-card-body" id="recent-searches-section">
                        <?php self::afficher_recherches_recentes_premium(); ?>
                    </div>
                </div>

            </div>
        </div>

        <script>
        function toggleTopTerms() {
            jQuery('#top-terms-section').slideToggle();
            var text = jQuery('#toggle-terms-text');
            text.text(text.text() === 'Masquer' ? 'Afficher' : 'Masquer');
        }
        
        function toggleRecentSearches() {
            jQuery('#recent-searches-section').slideToggle();
            var text = jQuery('#toggle-recent-text');
            text.text(text.text() === 'Masquer' ? 'Afficher' : 'Masquer');
        }
        </script>
        <?php
    }
    
    /**
     * Afficher les boutons d'export
     */
    private static function afficher_boutons_export() {
        echo '<div class="comportement-client-export-buttons">';
        echo '<a href="#" class="button export-csv-btn" data-export-type="recherches">📊 Export CSV</a>';
        echo '</div>';
    }
    
    /**
     * Afficher les styles CSS
     */
    private static function afficher_styles() {
        echo '<style>
            .recherche-stats { display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap; }
            .recherche-stat-box { flex: 1; min-width: 200px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .recherche-stat-number { font-size: 24px; font-weight: bold; color: #0073aa; }
            .recherche-stat-label { font-size: 14px; color: #666; margin-top: 5px; }
            .terme-populaire { display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee; align-items: center; }
            .terme-populaire:hover { background-color: #f8f9fa; }
            .recherche-item { display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee; align-items: center; }
            .recherche-item:hover { background-color: #f8f9fa; }
            .device-filter-container { margin: 20px 0; }
            .device-filter-btn { margin-right: 10px; }
            .search-container { margin: 20px 0; }
            .search-container input { width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        </style>';
    }
    
    /**
     * Afficher les statistiques premium des recherches
     */
    private static function afficher_stats_recherches_premium() {
        $stats = ComportementClientRechercheTracker::obtenir_stats_recherche_par_device(30);
        $total_recherches = array_sum(array_column($stats, 'total_recherches'));
        $total_utilisateurs = array_sum(array_column($stats, 'utilisateurs_uniques'));
        $termes_uniques = array_sum(array_column($stats, 'termes_uniques'));
        
        // Calculer le device le plus utilisé
        $device_principal = !empty($stats) ? $stats[0] : null;
        
        ?>
        <div class="cc-stats-grid">
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">🔍</div>
                </div>
                <div class="cc-stat-number"><?php echo $total_recherches; ?></div>
                <div class="cc-stat-label">Total Recherches</div>
                <div class="cc-stat-change positive">↗ 30 derniers jours</div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">👥</div>
                </div>
                <div class="cc-stat-number"><?php echo $total_utilisateurs; ?></div>
                <div class="cc-stat-label">Utilisateurs Uniques</div>
                <div class="cc-stat-change neutral">→ Rechercheurs actifs</div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">📝</div>
                </div>
                <div class="cc-stat-number"><?php echo $termes_uniques; ?></div>
                <div class="cc-stat-label">Termes Uniques</div>
                <div class="cc-stat-change positive">↗ Diversité des recherches</div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">📱</div>
                </div>
                <div class="cc-stat-number"><?php echo $device_principal ? $device_principal->total_recherches : 0; ?></div>
                <div class="cc-stat-label">Device Principal</div>
                <div class="cc-stat-change neutral">
                    <?php 
                    if ($device_principal) {
                        echo self::get_device_icon($device_principal->device_type) . ' ' . ucfirst($device_principal->device_type);
                    } else {
                        echo '❓ Aucun';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Afficher les filtres premium
     */
    private static function afficher_filtres_premium() {
        ?>
        <div class="cc-filters-bar">
            <div class="cc-filter-group">
                <span class="cc-filter-label">Filtrer par appareil :</span>
                <button class="cc-filter-btn active" data-device="all">Tous</button>
                <button class="cc-filter-btn" data-device="mobile">📱 Mobile</button>
                <button class="cc-filter-btn" data-device="pc">💻 PC</button>
                <button class="cc-filter-btn" data-device="tablette">📱 Tablette</button>
                <button class="cc-filter-btn" data-device="inconnu">❓ Inconnu</button>
            </div>
            
            <div class="cc-filter-group">
                <span class="cc-filter-label">Rechercher :</span>
                <div class="cc-search-container">
                    <input type="text" class="cc-search-input" placeholder="Terme de recherche..." id="recherche-search">
                </div>
            </div>
            
            <div class="cc-filter-group">
                <span class="cc-filter-label">Période :</span>
                <button class="cc-filter-btn" data-period="all">Tout</button>
                <button class="cc-filter-btn active" data-period="30">30j</button>
                <button class="cc-filter-btn" data-period="7">7j</button>
                <button class="cc-filter-btn" data-period="1">24h</button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Afficher les termes les plus populaires (version premium)
     */
    private static function afficher_termes_populaires_premium() {
        $termes_populaires = ComportementClientRechercheTracker::obtenir_termes_populaires_par_device(null, 20);
        
        if (empty($termes_populaires)) {
            echo '<div style="text-align: center; padding: 60px 20px; color: #6c757d;">';
            echo '<div style="font-size: 48px; margin-bottom: 20px;">🔍</div>';
            echo '<h3 style="margin: 0 0 10px 0;">Aucun terme populaire</h3>';
            echo '<p style="margin: 0;">Les termes les plus recherchés apparaîtront ici</p>';
            echo '</div>';
            return;
        }
        
        // Grille de termes populaires
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">';
        
        foreach ($termes_populaires as $index => $terme) {
            $popularite = min(100, ($terme->total_recherches / max(1, $termes_populaires[0]->total_recherches)) * 100);
            ?>
            <div class="cc-card cc-animate-in" 
                 style="border-left: 4px solid var(--cc-primary); animation-delay: <?php echo $index * 0.1; ?>s;" 
                 data-device="<?php echo esc_attr($terme->device_type); ?>">
                
                <div class="cc-card-header" style="background: linear-gradient(135deg, #f0f8ff, #ffffff);">
                    <div>
                        <h3 style="margin: 0; color: var(--cc-primary);">
                            🔍 <?php echo esc_html($terme->terme_recherche); ?>
                        </h3>
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #6c757d;">
                            <?php echo $terme->utilisateurs_uniques; ?> utilisateur(s) unique(s)
                        </p>
                    </div>
                    <div class="cc-actions-bar">
                        <span class="cc-device-badge <?php echo $terme->device_type; ?>">
                            <?php echo self::get_device_icon($terme->device_type); ?> <?php echo ucfirst($terme->device_type); ?>
                        </span>
                    </div>
                </div>
                
                <div class="cc-card-body">
                    <!-- Métriques -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 8px;">
                            <div style="font-size: 20px; font-weight: bold; color: var(--cc-primary);">
                                <?php echo $terme->total_recherches; ?>
                            </div>
                            <div style="font-size: 11px; color: #6c757d;">Total recherches</div>
                        </div>
                        <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 8px;">
                            <div style="font-size: 20px; font-weight: bold; color: var(--cc-success);">
                                <?php echo round($popularite, 1); ?>%
                            </div>
                            <div style="font-size: 11px; color: #6c757d;">Popularité</div>
                        </div>
                    </div>
                    
                    <!-- Barre de popularité -->
                    <div class="cc-progress-container">
                        <div class="cc-progress-label">
                            <span style="font-size: 12px;">Niveau de popularité</span>
                            <span style="font-size: 12px;"><?php echo round($popularite, 1); ?>%</span>
                        </div>
                        <div class="cc-progress-bar">
                            <div class="cc-progress-fill" 
                                 style="background: linear-gradient(90deg, var(--cc-primary), var(--cc-secondary)); width: <?php echo $popularite; ?>%;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        
        echo '</div>';
    }
    
    /**
     * Afficher les recherches récentes (version premium)
     */
    private static function afficher_recherches_recentes_premium() {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $recherches_recentes = $wpdb->get_results("
            SELECT 
                terme_recherche, 
                COALESCE(device_type, 'inconnu') as device_type,
                CASE WHEN r.user_id IS NOT NULL THEN u.display_name ELSE r.session_id END as identifiant,
                CASE WHEN r.user_id IS NOT NULL THEN 'connecté' ELSE 'anonyme' END as type_utilisateur,
                date_recherche
            FROM $table_recherches r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            ORDER BY date_recherche DESC 
            LIMIT 50
        ");
        
        if (empty($recherches_recentes)) {
            echo '<div style="text-align: center; padding: 60px 20px; color: #6c757d;">';
            echo '<div style="font-size: 48px; margin-bottom: 20px;">⏰</div>';
            echo '<h3 style="margin: 0 0 10px 0;">Aucune recherche récente</h3>';
            echo '<p style="margin: 0;">Les recherches récentes apparaîtront ici</p>';
            echo '</div>';
            return;
        }
        
        ?>
        <div class="cc-table-container">
            <table class="cc-table" id="recherches-table">
                <thead>
                    <tr>
                        <th>Terme recherché</th>
                        <th>Appareil</th>
                        <th>Utilisateur</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Temps écoulé</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recherches_recentes as $index => $recherche): ?>
                        <tr class="cc-animate-in" 
                            style="animation-delay: <?php echo $index * 0.05; ?>s;" 
                            data-device="<?php echo esc_attr($recherche->device_type); ?>">
                            
                            <td>
                                <strong style="color: var(--cc-primary);">
                                    🔍 <?php echo esc_html($recherche->terme_recherche); ?>
                                </strong>
                            </td>
                            
                            <td>
                                <span class="cc-device-badge <?php echo $recherche->device_type; ?>">
                                    <?php echo self::get_device_icon($recherche->device_type); ?> <?php echo ucfirst($recherche->device_type); ?>
                                </span>
                            </td>
                            
                            <td>
                                <?php if ($recherche->type_utilisateur === 'connecté'): ?>
                                    <strong style="color: var(--cc-primary); font-size: 13px;">
                                        <?php echo esc_html($recherche->identifiant); ?>
                                    </strong>
                                <?php else: ?>
                                    <span style="font-family: monospace; font-size: 12px; color: #6c757d;">
                                        <?php echo esc_html(substr($recherche->identifiant, 0, 12)) . '...'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php if ($recherche->type_utilisateur === 'connecté'): ?>
                                    <span class="cc-device-badge" style="background: linear-gradient(135deg, #28a745, #20c997); font-size: 11px;">
                                        👤 Connecté
                                    </span>
                                <?php else: ?>
                                    <span class="cc-device-badge" style="background: linear-gradient(135deg, #ffc107, #ffb300); font-size: 11px;">
                                        👥 Anonyme
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td style="font-size: 12px; color: #6c757d;">
                                <?php echo date('d/m/Y H:i', strtotime($recherche->date_recherche)); ?>
                            </td>
                            
                            <td style="font-size: 12px; color: #6c757d;">
                                <?php
                                $temps_ecoule = time() - strtotime($recherche->date_recherche);
                                if ($temps_ecoule < 60) {
                                    echo 'À l\'instant';
                                } elseif ($temps_ecoule < 3600) {
                                    echo floor($temps_ecoule / 60) . ' min';
                                } elseif ($temps_ecoule < 86400) {
                                    echo floor($temps_ecoule / 3600) . ' h';
                                } else {
                                    echo floor($temps_ecoule / 86400) . ' j';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (count($recherches_recentes) >= 50): ?>
            <div style="text-align: center; margin-top: 20px;">
                <button class="cc-btn cc-btn-secondary" onclick="loadMoreSearches()">
                    📄 Charger plus de recherches
                </button>
            </div>
        <?php endif; ?>
        
        <script>
        function loadMoreSearches() {
            ComportementClientAdmin.showToast('Chargement de plus de recherches...', 'info');
            setTimeout(function() {
                ComportementClientAdmin.showToast('Fonctionnalité à implémenter', 'warning');
            }, 1000);
        }
        </script>
        <?php
    }
    
    /**
     * Obtenir l'icône pour un type de device
     */
    private static function get_device_icon($device_type) {
        $icons = array(
            'mobile' => '📱',
            'pc' => '💻',
            'tablette' => '📱',
            'inconnu' => '❓'
        );
        
        return isset($icons[$device_type]) ? $icons[$device_type] : '❓';
    }
}