<?php
/**
 * Page d'administration de l'historique complet
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientAdminHistorique {
    
    /**
     * Afficher la page d'historique complet
     */
    public static function afficher_page() {
        global $wpdb;
        
        ?>
        <div class="comportement-client-wrapper">
            <div class="comportement-client-container">
                
                <!-- Header Premium -->
                <div class="cc-page-header">
                    <div>
                        <h1 class="cc-page-title">📈 Historique Complet</h1>
                        <p class="cc-page-subtitle">Analyse temporelle des activités utilisateurs par appareil</p>
                    </div>
                    <div class="cc-actions-bar">
                        <button class="cc-btn cc-btn-refresh" id="refresh-historique">
                            <span class="cc-refresh-icon">🔄</span>
                            Actualiser
                        </button>
                        <button class="cc-btn cc-btn-export" data-export-type="historique">
                            📊 Export CSV
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=comportement-clients'); ?>" class="cc-btn cc-btn-secondary">
                            ← Dashboard
                        </a>
                    </div>
                </div>

                <!-- Statistiques générales -->
                <?php self::afficher_statistiques_generales_premium(); ?>
                
                <!-- Filtres modernes -->
                <?php self::afficher_filtres_premium(); ?>
                
                <!-- Graphique d'activité -->
                <div class="cc-card">
                    <div class="cc-card-header">
                        <h2 class="cc-card-title">📈 Évolution de l'Activité</h2>
                        <div class="cc-actions-bar">
                            <button class="cc-btn cc-btn-secondary" onclick="toggleChartView()">
                                <span id="chart-view-text">Vue Détaillée</span>
                            </button>
                        </div>
                    </div>
                    <div class="cc-card-body">
                        <?php self::afficher_graphique_activite_premium(); ?>
                    </div>
                </div>
                
                <!-- Historique détaillé -->
                <div class="cc-card">
                    <div class="cc-card-header">
                        <h2 class="cc-card-title">📋 Activités Détaillées</h2>
                        <div class="cc-actions-bar">
                            <button class="cc-btn cc-btn-secondary" onclick="toggleHistoryDetails()">
                                <span id="toggle-history-text">Masquer</span>
                            </button>
                        </div>
                    </div>
                    <div class="cc-card-body" id="history-details-section">
                        <?php self::afficher_historique_detaille_premium(); ?>
                    </div>
                </div>

            </div>
        </div>

        <script>
        function toggleChartView() {
            var text = jQuery('#chart-view-text');
            // Ici on pourrait implémenter différentes vues du graphique
            ComportementClientAdmin.showToast('Fonctionnalité à implémenter', 'info');
        }
        
        function toggleHistoryDetails() {
            jQuery('#history-details-section').slideToggle();
            var text = jQuery('#toggle-history-text');
            text.text(text.text() === 'Masquer' ? 'Afficher' : 'Masquer');
        }
        </script>
        <?php
    }
    
    /**
     * Afficher les filtres premium
     */
    private static function afficher_filtres_premium() {
        $periode = isset($_GET['periode']) ? sanitize_text_field($_GET['periode']) : '30';
        
        ?>
        <div class="cc-filters-bar">
            <div class="cc-filter-group">
                <span class="cc-filter-label">Période d'analyse :</span>
                <?php
                $periodes = array(
                    '7' => '7 jours',
                    '30' => '30 jours', 
                    '90' => '90 jours',
                    '365' => '1 année'
                );
                
                foreach ($periodes as $jours => $label) {
                    $class = ($periode === $jours) ? 'cc-filter-btn active' : 'cc-filter-btn';
                    $url = add_query_arg('periode', $jours);
                    echo '<a href="' . esc_url($url) . '" class="' . $class . '">' . $label . '</a>';
                }
                ?>
            </div>
            
            <div class="cc-filter-group">
                <span class="cc-filter-label">Type d'activité :</span>
                <button class="cc-filter-btn active" data-activity="all">Toutes</button>
                <button class="cc-filter-btn" data-activity="recherche">🔍 Recherches</button>
                <button class="cc-filter-btn" data-activity="panier">🛒 Paniers</button>
            </div>
            
            <div class="cc-filter-group">
                <span class="cc-filter-label">Appareil :</span>
                <button class="cc-filter-btn active" data-device="all">Tous</button>
                <button class="cc-filter-btn" data-device="mobile">📱 Mobile</button>
                <button class="cc-filter-btn" data-device="pc">💻 PC</button>
                <button class="cc-filter-btn" data-device="tablette">📱 Tablette</button>
            </div>
            
            <div class="cc-filter-group">
                <span class="cc-filter-label">Rechercher :</span>
                <div class="cc-search-container">
                    <input type="text" class="cc-search-input" placeholder="Rechercher dans l'historique..." id="historique-search">
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Afficher les styles CSS
     */
    private static function afficher_styles() {
        echo '<style>
            .historique-stats { display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap; }
            .historique-stat-box { flex: 1; min-width: 200px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
            .historique-stat-number { font-size: 28px; font-weight: bold; color: #0073aa; }
            .historique-stat-label { font-size: 14px; color: #666; margin-top: 5px; }
            .activite-item { display: flex; justify-content: space-between; padding: 12px; border-bottom: 1px solid #eee; align-items: center; }
            .activite-item:hover { background-color: #f8f9fa; }
            .activite-type-recherche { border-left: 4px solid #0073aa; }
            .activite-type-panier { border-left: 4px solid #28a745; }
            .graphique-container { background: #fff; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .graphique-barre { display: flex; align-items: end; gap: 5px; height: 200px; margin: 20px 0; }
            .graphique-jour { flex: 1; background: #0073aa; border-radius: 3px 3px 0 0; position: relative; min-height: 2px; }
            .graphique-jour:hover { background: #005a87; cursor: pointer; }
            .graphique-tooltip { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 5px 10px; border-radius: 3px; font-size: 12px; white-space: nowrap; opacity: 0; transition: opacity 0.3s; }
            .graphique-jour:hover .graphique-tooltip { opacity: 1; }
            .graphique-labels { display: flex; justify-content: space-between; font-size: 12px; color: #666; }
        </style>';
    }
    
    /**
     * Afficher les statistiques générales (version premium)
     */
    private static function afficher_statistiques_generales_premium() {
        $periode = isset($_GET['periode']) ? intval($_GET['periode']) : 30;
        $stats = self::obtenir_statistiques_periode($periode);
        $total_activites = $stats['total_recherches'] + $stats['total_paniers'];
        $pct_mobile = $total_activites > 0 ? round(($stats['activites_mobile'] / $total_activites) * 100, 1) : 0;
        
        ?>
        <div class="cc-stats-grid">
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">📊</div>
                </div>
                <div class="cc-stat-number"><?php echo $total_activites; ?></div>
                <div class="cc-stat-label">Total Activités</div>
                <div class="cc-stat-change positive">📈 Sur <?php echo $periode; ?> jours</div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">🔍</div>
                </div>
                <div class="cc-stat-number"><?php echo $stats['total_recherches']; ?></div>
                <div class="cc-stat-label">Recherches</div>
                <div class="cc-stat-change positive">
                    <?php echo $total_activites > 0 ? round(($stats['total_recherches'] / $total_activites) * 100, 1) : 0; ?>% du total
                </div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">🛒</div>
                </div>
                <div class="cc-stat-number"><?php echo $stats['total_paniers']; ?></div>
                <div class="cc-stat-label">Actions Panier</div>
                <div class="cc-stat-change positive">
                    <?php echo $total_activites > 0 ? round(($stats['total_paniers'] / $total_activites) * 100, 1) : 0; ?>% du total
                </div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">👥</div>
                </div>
                <div class="cc-stat-number"><?php echo $stats['utilisateurs_uniques']; ?></div>
                <div class="cc-stat-label">Utilisateurs Uniques</div>
                <div class="cc-stat-change neutral">→ Sessions distinctes</div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">📱</div>
                </div>
                <div class="cc-stat-number"><?php echo $pct_mobile; ?>%</div>
                <div class="cc-stat-label">Activité Mobile</div>
                <div class="cc-stat-change <?php echo $pct_mobile > 50 ? 'positive' : 'neutral'; ?>">
                    <?php echo $stats['activites_mobile']; ?> actions mobiles
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Afficher le graphique d'activité par jour (version premium)
     */
    private static function afficher_graphique_activite_premium() {
        $periode = isset($_GET['periode']) ? intval($_GET['periode']) : 30;
        $activites_par_jour = self::obtenir_activites_par_jour($periode);
        
        if (empty($activites_par_jour)) {
            echo '<div style="text-align: center; padding: 60px 20px; color: #6c757d;">';
            echo '<div style="font-size: 48px; margin-bottom: 20px;">📈</div>';
            echo '<h3 style="margin: 0 0 10px 0;">Aucune donnée disponible</h3>';
            echo '<p style="margin: 0;">Les données d\'activité pour cette période apparaîtront ici</p>';
            echo '</div>';
            return;
        }
        
        $max_activites = max(array_column($activites_par_jour, 'total'));
        
        ?>
        <div class="cc-chart-container">
            <div class="cc-chart-header">
                <h3 class="cc-chart-title">📈 Évolution Quotidienne</h3>
                <div class="cc-actions-bar">
                    <div style="display: flex; gap: 15px; align-items: center; font-size: 12px;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <div style="width: 12px; height: 12px; background: var(--cc-primary); border-radius: 2px;"></div>
                            <span>Recherches</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <div style="width: 12px; height: 12px; background: var(--cc-success); border-radius: 2px;"></div>
                            <span>Paniers</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Graphique moderne -->
            <div style="position: relative; height: 250px; margin: 20px 0;">
                <div style="display: flex; align-items: end; gap: 3px; height: 200px; padding: 20px 0;">
                    <?php foreach ($activites_par_jour as $index => $jour): 
                        $hauteur_total = $max_activites > 0 ? ($jour['total'] / $max_activites) * 100 : 0;
                        $hauteur_recherches = $jour['total'] > 0 ? ($jour['recherches'] / $jour['total']) * $hauteur_total : 0;
                        $hauteur_paniers = $jour['total'] > 0 ? ($jour['paniers'] / $jour['total']) * $hauteur_total : 0;
                    ?>
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; height: 100%;">
                            <!-- Barres empilées -->
                            <div style="display: flex; flex-direction: column-reverse; height: 100%; width: 100%; position: relative;">
                                <?php if ($jour['recherches'] > 0): ?>
                                    <div style="background: linear-gradient(135deg, var(--cc-primary), var(--cc-secondary)); 
                                               height: <?php echo $hauteur_recherches; ?>%; 
                                               border-radius: 3px 3px 0 0; 
                                               transition: all 0.3s ease;"
                                         onmouseover="this.style.transform='scaleX(1.1)'"
                                         onmouseout="this.style.transform='scaleX(1)'"
                                         title="<?php echo $jour['recherches']; ?> recherches le <?php echo date('d/m', strtotime($jour['date'])); ?>">
                                    </div>
                                <?php endif; ?>
                                <?php if ($jour['paniers'] > 0): ?>
                                    <div style="background: linear-gradient(135deg, var(--cc-success), #20c997); 
                                               height: <?php echo $hauteur_paniers; ?>%; 
                                               border-radius: <?php echo $jour['recherches'] > 0 ? '0' : '3px 3px 0 0'; ?>; 
                                               transition: all 0.3s ease;"
                                         onmouseover="this.style.transform='scaleX(1.1)'"
                                         onmouseout="this.style.transform='scaleX(1)'"
                                         title="<?php echo $jour['paniers']; ?> actions panier le <?php echo date('d/m', strtotime($jour['date'])); ?>">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Tooltip au hover -->
                            <div style="position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); 
                                       background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; 
                                       border-radius: 6px; font-size: 11px; white-space: nowrap; 
                                       opacity: 0; transition: opacity 0.3s; pointer-events: none; z-index: 1000;"
                                 class="chart-tooltip">
                                <strong><?php echo date('d/m', strtotime($jour['date'])); ?></strong><br>
                                Total: <?php echo $jour['total']; ?><br>
                                🔍 <?php echo $jour['recherches']; ?> - 🛒 <?php echo $jour['paniers']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Labels des dates -->
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #6c757d; margin-top: 10px;">
                    <?php
                    $nb_labels = min(7, count($activites_par_jour));
                    $step = max(1, floor(count($activites_par_jour) / $nb_labels));
                    for ($i = 0; $i < count($activites_par_jour); $i += $step) {
                        echo '<span>' . date('d/m', strtotime($activites_par_jour[$i]['date'])) . '</span>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Statistiques du graphique -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--cc-border);">
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: var(--cc-primary);"><?php echo $max_activites; ?></div>
                    <div style="font-size: 12px; color: #6c757d;">Pic d'activité</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: var(--cc-success);"><?php echo round(array_sum(array_column($activites_par_jour, 'total')) / count($activites_par_jour), 1); ?></div>
                    <div style="font-size: 12px; color: #6c757d;">Moyenne / jour</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: var(--cc-info);"><?php echo count($activites_par_jour); ?></div>
                    <div style="font-size: 12px; color: #6c757d;">Jours actifs</div>
                </div>
            </div>
        </div>
        
        <style>
        .cc-chart-container [style*="position: relative"] > div:hover .chart-tooltip {
            opacity: 1 !important;
        }
        </style>
        <?php
    }
    
    /**
     * Afficher l'historique détaillé
     */
    private static function afficher_historique_detaille() {
        global $wpdb;
        
        $periode = isset($_GET['periode']) ? intval($_GET['periode']) : 30;
        $date_limite = date('Y-m-d H:i:s', strtotime("-{$periode} days"));
        
        echo '<h2>📋 Historique détaillé</h2>';
        
        // Filtres
        echo '<div style="margin: 20px 0;">';
        echo '<input type="text" id="historique-search" placeholder="Rechercher..." style="width: 300px; padding: 8px; margin-right: 10px;">';
        echo '<select id="historique-filter-type" style="padding: 8px;">';
        echo '<option value="all">Tous les types</option>';
        echo '<option value="recherche">Recherches</option>';
        echo '<option value="panier">Paniers</option>';
        echo '</select>';
        echo '</div>';
        
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        $historique = $wpdb->get_results($wpdb->prepare("
            SELECT 
                'recherche' as type,
                terme_recherche as details,
                COALESCE(device_type, 'inconnu') as device_type,
                CASE WHEN r.user_id IS NOT NULL THEN u.display_name ELSE r.session_id END as identifiant,
                CASE WHEN r.user_id IS NOT NULL THEN 'connecté' ELSE 'anonyme' END as type_utilisateur,
                date_recherche as date_action
            FROM $table_recherches r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            WHERE date_recherche >= %s
            
            UNION ALL
            
            SELECT 
                'panier' as type,
                CONCAT('Produit ID: ', product_id, ' (Qté: ', quantity, ')') as details,
                COALESCE(device_type, 'inconnu') as device_type,
                session_id as identifiant,
                'anonyme' as type_utilisateur,
                date_modif as date_action
            FROM $table_paniers
            WHERE date_modif >= %s
            
            ORDER BY date_action DESC
            LIMIT 100
        ", $date_limite, $date_limite));
        
        if (empty($historique)) {
            echo '<div class="notice notice-info"><p>Aucune activité trouvée pour cette période.</p></div>';
            return;
        }
        
        echo '<div class="postbox">';
        echo '<div class="inside">';
        echo '<table class="wp-list-table widefat fixed striped" id="historique-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Type</th>';
        echo '<th>Détails</th>';
        echo '<th>Device</th>';
        echo '<th>Utilisateur</th>';
        echo '<th>Date</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($historique as $activite) {
            $type_class = 'activite-type-' . $activite->type;
            
            echo '<tr class="activite-item ' . $type_class . '" data-type="' . $activite->type . '">';
            echo '<td>';
            if ($activite->type === 'recherche') {
                echo '<span style="color: #0073aa;">🔍 Recherche</span>';
            } else {
                echo '<span style="color: #28a745;">🛒 Panier</span>';
            }
            echo '</td>';
            echo '<td>' . esc_html($activite->details) . '</td>';
            echo '<td>';
            echo '<span class="comportement-client-device-badge ' . $activite->device_type . '">';
            echo self::get_device_icon($activite->device_type) . ' ' . ucfirst($activite->device_type);
            echo '</span>';
            echo '</td>';
            echo '<td>';
            if ($activite->type_utilisateur === 'connecté') {
                echo '<strong style="color: var(--cc-primary);">' . esc_html($activite->identifiant) . '</strong>';
                echo ' <span style="color: #28a745; font-size: 12px;">(connecté)</span>';
            } else {
                echo '<span style="font-family: monospace; color: #6c757d;">' . esc_html(substr($activite->identifiant, 0, 12)) . '...</span>';
                echo ' <span style="color: #ffc107; font-size: 12px;">(anonyme)</span>';
            }
            echo '</td>';
            echo '<td>' . date('d/m/Y H:i', strtotime($activite->date_action)) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        // Script de filtrage
        echo '<script>
        jQuery(document).ready(function($) {
            $("#historique-search").on("keyup", function() {
                let value = $(this).val().toLowerCase();
                $("#historique-table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
            
            $("#historique-filter-type").on("change", function() {
                let type = $(this).val();
                if (type === "all") {
                    $("#historique-table tbody tr").show();
                } else {
                    $("#historique-table tbody tr").hide();
                    $("#historique-table tbody tr[data-type=\'" + type + "\']").show();
                }
            });
        });
        </script>';
    }
    
    /**
     * Obtenir les statistiques pour une période
     */
    private static function obtenir_statistiques_periode($periode_jours) {
        global $wpdb;
        
        $date_limite = date('Y-m-d H:i:s', strtotime("-{$periode_jours} days"));
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        // Total recherches
        $total_recherches = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_recherches WHERE date_recherche >= %s",
            $date_limite
        ));
        
        // Total paniers
        $total_paniers = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_paniers WHERE date_modif >= %s",
            $date_limite
        ));
        
        // Utilisateurs uniques (combinaison user_id et session_id)
        $utilisateurs_uniques = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT identifiant) FROM (
                SELECT COALESCE(user_id, session_id) as identifiant FROM $table_recherches WHERE date_recherche >= %s
                UNION
                SELECT session_id as identifiant FROM $table_paniers WHERE date_modif >= %s
            ) as combined_users
        ", $date_limite, $date_limite));
        
        // Activités mobile
        $activites_mobile = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM (
                SELECT 1 FROM $table_recherches WHERE date_recherche >= %s AND device_type = 'mobile'
                UNION ALL
                SELECT 1 FROM $table_paniers WHERE date_modif >= %s AND device_type = 'mobile'
            ) as mobile_activities
        ", $date_limite, $date_limite));
        
        return array(
            'total_recherches' => intval($total_recherches),
            'total_paniers' => intval($total_paniers),
            'utilisateurs_uniques' => intval($utilisateurs_uniques),
            'activites_mobile' => intval($activites_mobile)
        );
    }
    
    /**
     * Obtenir les activités par jour
     */
    private static function obtenir_activites_par_jour($periode_jours) {
        global $wpdb;
        
        $date_limite = date('Y-m-d', strtotime("-{$periode_jours} days"));
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        $resultats = $wpdb->get_results($wpdb->prepare("
            SELECT 
                DATE(date_action) as date,
                SUM(CASE WHEN type = 'recherche' THEN 1 ELSE 0 END) as recherches,
                SUM(CASE WHEN type = 'panier' THEN 1 ELSE 0 END) as paniers,
                COUNT(*) as total
            FROM (
                SELECT date_recherche as date_action, 'recherche' as type FROM $table_recherches WHERE date_recherche >= %s
                UNION ALL
                SELECT date_modif as date_action, 'panier' as type FROM $table_paniers WHERE date_modif >= %s
            ) as combined_activities
            GROUP BY DATE(date_action)
            ORDER BY date ASC
        ", $date_limite, $date_limite));
        
        // Convertir en tableau associatif
        $activites = array();
        foreach ($resultats as $resultat) {
            $activites[] = array(
                'date' => $resultat->date,
                'recherches' => intval($resultat->recherches),
                'paniers' => intval($resultat->paniers),
                'total' => intval($resultat->total)
            );
        }
        
        return $activites;
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
    
    /**
     * Afficher l'historique détaillé (version premium)
     */
    private static function afficher_historique_detaille_premium() {
        global $wpdb;
        
        $periode = isset($_GET['periode']) ? intval($_GET['periode']) : 30;
        $date_limite = date('Y-m-d H:i:s', strtotime("-{$periode} days"));
        
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        $historique = $wpdb->get_results($wpdb->prepare("
            SELECT 
                'recherche' as type,
                terme_recherche as details,
                COALESCE(device_type, 'inconnu') as device_type,
                CASE WHEN r.user_id IS NOT NULL THEN u.display_name ELSE r.session_id END as identifiant,
                CASE WHEN r.user_id IS NOT NULL THEN 'connecté' ELSE 'anonyme' END as type_utilisateur,
                date_recherche as date_action
            FROM $table_recherches r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            WHERE date_recherche >= %s
            
            UNION ALL
            
            SELECT 
                'panier' as type,
                CONCAT('Produit ID: ', product_id, ' (Qté: ', quantity, ')') as details,
                COALESCE(device_type, 'inconnu') as device_type,
                session_id as identifiant,
                'anonyme' as type_utilisateur,
                date_modif as date_action
            FROM $table_paniers
            WHERE date_modif >= %s
            
            ORDER BY date_action DESC
            LIMIT 100
        ", $date_limite, $date_limite));
        
        if (empty($historique)) {
            echo '<div style="text-align: center; padding: 60px 20px; color: #6c757d;">';
            echo '<div style="font-size: 48px; margin-bottom: 20px;">📋</div>';
            echo '<h3 style="margin: 0 0 10px 0;">Aucune activité trouvée</h3>';
            echo '<p style="margin: 0;">L\'historique des activités pour cette période apparaîtra ici</p>';
            echo '</div>';
            return;
        }
        
        ?>
        <div class="cc-table-container">
            <table class="cc-table" id="historique-table">
                <thead>
                    <tr>
                        <th>Type d'activité</th>
                        <th>Détails</th>
                        <th>Appareil</th>
                        <th>Utilisateur</th>
                        <th>Date</th>
                        <th>Temps écoulé</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historique as $index => $activite): ?>
                        <tr class="cc-animate-in" 
                            style="animation-delay: <?php echo $index * 0.02; ?>s;" 
                            data-type="<?php echo $activite->type; ?>"
                            data-device="<?php echo esc_attr($activite->device_type); ?>">
                            
                            <td>
                                <?php if ($activite->type === 'recherche'): ?>
                                    <span class="cc-device-badge" style="background: linear-gradient(135deg, var(--cc-primary), var(--cc-secondary));">
                                        🔍 Recherche
                                    </span>
                                <?php else: ?>
                                    <span class="cc-device-badge" style="background: linear-gradient(135deg, var(--cc-success), #20c997);">
                                        🛒 Panier
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <strong style="color: var(--cc-dark);">
                                    <?php echo esc_html($activite->details); ?>
                                </strong>
                            </td>
                            
                            <td>
                                <span class="cc-device-badge <?php echo $activite->device_type; ?>">
                                    <?php echo self::get_device_icon($activite->device_type); ?> <?php echo ucfirst($activite->device_type); ?>
                                </span>
                            </td>
                            
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <?php if ($activite->type_utilisateur === 'connecté'): ?>
                                        <strong style="color: var(--cc-primary); font-size: 13px;">
                                            <?php echo esc_html($activite->identifiant); ?>
                                        </strong>
                                        <span class="cc-device-badge" style="background: linear-gradient(135deg, #28a745, #20c997); font-size: 9px; padding: 2px 6px;">
                                            👤 Connecté
                                        </span>
                                    <?php else: ?>
                                        <span style="font-family: monospace; font-size: 11px; color: #6c757d;">
                                            <?php echo esc_html(substr($activite->identifiant, 0, 12)) . '...'; ?>
                                        </span>
                                        <span class="cc-device-badge" style="background: linear-gradient(135deg, #ffc107, #ffb300); font-size: 9px; padding: 2px 6px;">
                                            👥 Anonyme
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td style="font-size: 12px; color: #6c757d;">
                                <?php echo date('d/m/Y H:i', strtotime($activite->date_action)); ?>
                            </td>
                            
                            <td style="font-size: 12px; color: #6c757d;">
                                <?php
                                $temps_ecoule = time() - strtotime($activite->date_action);
                                if ($temps_ecoule < 60) {
                                    echo 'À l\'instant';
                                } elseif ($temps_ecoule < 3600) {
                                    echo floor($temps_ecoule / 60) . ' min';
                                } elseif ($temps_ecoule < 86400) {
                                    echo floor($temps_ecoule / 3600) . ' h';
                                } elseif ($temps_ecoule < 2592000) {
                                    echo floor($temps_ecoule / 86400) . ' j';
                                } else {
                                    echo floor($temps_ecoule / 2592000) . ' mois';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (count($historique) >= 100): ?>
            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--cc-border);">
                <p style="color: #6c757d; margin-bottom: 15px; font-size: 13px;">
                    Affichage des 100 activités les plus récentes pour cette période.
                </p>
                <button class="cc-btn cc-btn-secondary" onclick="loadMoreHistory()">
                    📄 Charger plus d'activités
                </button>
            </div>
        <?php endif; ?>
        
        <script>
        // Filtrage en temps réel
        jQuery(document).ready(function($) {
            // Recherche textuelle
            $(document).on('input', '#historique-search', function() {
                var searchTerm = $(this).val().toLowerCase();
                $('#historique-table tbody tr').each(function() {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.includes(searchTerm));
                });
            });
            
            // Filtrage par type d'activité
            $(document).on('click', '[data-activity]', function() {
                var activity = $(this).data('activity');
                $(this).siblings().removeClass('active');
                $(this).addClass('active');
                
                if (activity === 'all') {
                    $('#historique-table tbody tr').show();
                } else {
                    $('#historique-table tbody tr').hide();
                    $('#historique-table tbody tr[data-type="' + activity + '"]').show();
                }
            });
            
            // Filtrage par device
            $(document).on('click', '[data-device]', function() {
                var device = $(this).data('device');
                if (device) {
                    $(this).siblings().removeClass('active');
                    $(this).addClass('active');
                    
                    if (device === 'all') {
                        $('#historique-table tbody tr').show();
                    } else {
                        $('#historique-table tbody tr').hide();
                        $('#historique-table tbody tr[data-device="' + device + '"]').show();
                    }
                }
            });
        });
        
        function loadMoreHistory() {
            ComportementClientAdmin.showToast('Chargement de plus d\'activités...', 'info');
            setTimeout(function() {
                ComportementClientAdmin.showToast('Fonctionnalité à implémenter', 'warning');
            }, 1000);
        }
        </script>
        <?php
    }
}