<?php
/**
 * Page d'administration des recommandations comportementales
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientAdminRecommendations {
    
    /**
     * Afficher la page des recommandations
     */
    public static function afficher_page() {
        // Générer de nouvelles recommandations si nécessaire
        self::generer_recommandations_automatiques();
        
        // Récupérer les recommandations
        $recommendations = self::obtenir_recommendations();
        
        ?>
        <div class="comportement-client-wrapper">
            <div class="comportement-client-container">
                
                <!-- Header -->
                <div class="cc-page-header">
                    <div>
                        <h1 class="cc-page-title">💡 Recommandations Comportementales</h1>
                        <p class="cc-page-subtitle">Actions suggérées basées sur l'analyse des comportements clients</p>
                    </div>
                    <div class="cc-actions-bar">
                        <button class="cc-btn cc-btn-primary" onclick="generateNewRecommendations()">
                            🔄 Générer Nouvelles Recommandations
                        </button>
                        <button class="cc-btn cc-btn-secondary" onclick="dismissAllRecommendations()">
                            ✅ Masquer Toutes
                        </button>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="cc-stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                    <div class="cc-stat-card">
                        <div class="cc-stat-icon">🔥</div>
                        <div class="cc-stat-number"><?php echo count(array_filter($recommendations, function($r) { return $r->priorite === 'haute'; })); ?></div>
                        <div class="cc-stat-label">Priorité Haute</div>
                    </div>
                    
                    <div class="cc-stat-card">
                        <div class="cc-stat-icon">⚠️</div>
                        <div class="cc-stat-number"><?php echo count(array_filter($recommendations, function($r) { return $r->priorite === 'moyenne'; })); ?></div>
                        <div class="cc-stat-label">Priorité Moyenne</div>
                    </div>
                    
                    <div class="cc-stat-card">
                        <div class="cc-stat-icon">📊</div>
                        <div class="cc-stat-number"><?php echo count(array_filter($recommendations, function($r) { return $r->priorite === 'basse'; })); ?></div>
                        <div class="cc-stat-label">Priorité Basse</div>
                    </div>
                    
                    <div class="cc-stat-card">
                        <div class="cc-stat-icon">⏰</div>
                        <div class="cc-stat-number"><?php echo count($recommendations); ?></div>
                        <div class="cc-stat-label">Total Actives</div>
                    </div>
                </div>

                <!-- Liste des recommandations -->
                <div class="cc-recommendations-grid">
                    <?php if (empty($recommendations)): ?>
                        <div class="cc-card" style="text-align: center; padding: 60px;">
                            <div style="font-size: 64px; margin-bottom: 20px;">🎯</div>
                            <h3>Aucune recommandation active</h3>
                            <p>Cliquez sur "Générer Nouvelles Recommandations" pour analyser les comportements clients récents.</p>
                            <button class="cc-btn cc-btn-primary" onclick="generateNewRecommendations()">
                                🔄 Générer Recommandations
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recommendations as $reco): ?>
                            <div class="cc-recommendation-card priority-<?php echo $reco->priorite; ?>" data-id="<?php echo $reco->id; ?>">
                                <div class="cc-reco-header">
                                    <div class="cc-reco-type">
                                        <?php echo self::icone_type_recommendation($reco->type_recommendation); ?>
                                        <span><?php echo self::libelle_type_recommendation($reco->type_recommendation); ?></span>
                                    </div>
                                    <div class="cc-reco-priority priority-<?php echo $reco->priorite; ?>">
                                        <?php echo self::icone_priorite($reco->priorite); ?>
                                        <?php echo ucfirst($reco->priorite); ?>
                                    </div>
                                </div>
                                
                                <div class="cc-reco-content">
                                    <h3><?php echo esc_html($reco->titre); ?></h3>
                                    <p><?php echo esc_html($reco->description); ?></p>
                                    
                                    <?php if ($reco->actions_suggerees): ?>
                                        <div class="cc-reco-actions">
                                            <h4>Actions suggérées :</h4>
                                            <div class="cc-action-list">
                                                <?php 
                                                $actions = json_decode($reco->actions_suggerees, true);
                                                if ($actions && is_array($actions)):
                                                    foreach ($actions as $action): ?>
                                                        <div class="cc-action-item">
                                                            <span class="cc-action-icon">✓</span>
                                                            <?php echo esc_html($action); ?>
                                                        </div>
                                                    <?php endforeach;
                                                endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="cc-reco-footer">
                                    <div class="cc-reco-meta">
                                        <small>Créé le <?php echo date('d/m/Y à H:i', strtotime($reco->date_creation)); ?></small>
                                    </div>
                                    <div class="cc-reco-buttons">
                                        <button class="cc-btn cc-btn-small cc-btn-success" onclick="dismissRecommendation(<?php echo $reco->id; ?>)">
                                            ✅ Traité
                                        </button>
                                        <button class="cc-btn cc-btn-small cc-btn-secondary" onclick="viewRecommendationData(<?php echo $reco->id; ?>)">
                                            📊 Détails
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <script>
        function generateNewRecommendations() {
            ComportementClientAdmin.showToast('Génération de nouvelles recommandations...', 'info');
            
            jQuery.post(ajaxurl, {
                action: 'generate_new_recommendations',
                nonce: '<?php echo wp_create_nonce('comportement_client_nonce'); ?>'
            }).done(function(response) {
                if (response.success) {
                    ComportementClientAdmin.showToast('Nouvelles recommandations générées !', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    ComportementClientAdmin.showToast('Erreur lors de la génération', 'error');
                }
            });
        }

        function dismissRecommendation(id) {
            jQuery.post(ajaxurl, {
                action: 'dismiss_recommendation',
                recommendation_id: id,
                nonce: '<?php echo wp_create_nonce('comportement_client_nonce'); ?>'
            }).done(function(response) {
                if (response.success) {
                    jQuery('[data-id="' + id + '"]').fadeOut();
                    ComportementClientAdmin.showToast('Recommandation marquée comme traitée', 'success');
                } else {
                    ComportementClientAdmin.showToast('Erreur', 'error');
                }
            });
        }

        function dismissAllRecommendations() {
            if (confirm('Masquer toutes les recommandations actuelles ?')) {
                jQuery.post(ajaxurl, {
                    action: 'dismiss_all_recommendations',
                    nonce: '<?php echo wp_create_nonce('comportement_client_nonce'); ?>'
                }).done(function(response) {
                    if (response.success) {
                        window.location.reload();
                    }
                });
            }
        }

        function viewRecommendationData(id) {
            // Afficher les détails de données de la recommandation
            ComportementClientAdmin.showToast('Fonctionnalité à venir...', 'info');
        }
        </script>

        <style>
        .cc-recommendations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .cc-recommendation-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid #ddd;
        }

        .cc-recommendation-card.priority-haute {
            border-left-color: #dc3545;
        }

        .cc-recommendation-card.priority-moyenne {
            border-left-color: #ffc107;
        }

        .cc-recommendation-card.priority-basse {
            border-left-color: #28a745;
        }

        .cc-recommendation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .cc-reco-header {
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e9ecef;
        }

        .cc-reco-type {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #495057;
        }

        .cc-reco-priority {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .cc-reco-priority.priority-haute {
            background: #dc3545;
            color: white;
        }

        .cc-reco-priority.priority-moyenne {
            background: #ffc107;
            color: #212529;
        }

        .cc-reco-priority.priority-basse {
            background: #28a745;
            color: white;
        }

        .cc-reco-content {
            padding: 20px;
        }

        .cc-reco-content h3 {
            margin: 0 0 10px 0;
            color: #212529;
            font-size: 18px;
        }

        .cc-reco-content p {
            margin: 0 0 15px 0;
            color: #6c757d;
            line-height: 1.5;
        }

        .cc-reco-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .cc-reco-actions h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #495057;
        }

        .cc-action-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .cc-action-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #495057;
        }

        .cc-action-icon {
            color: #28a745;
            font-weight: bold;
        }

        .cc-reco-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e9ecef;
        }

        .cc-reco-meta small {
            color: #6c757d;
        }

        .cc-reco-buttons {
            display: flex;
            gap: 8px;
        }

        .cc-btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        </style>
        <?php
    }
    
    /**
     * Obtenir toutes les recommandations actives
     */
    private static function obtenir_recommendations() {
        global $wpdb;
        
        $table_recommendations = $wpdb->prefix . 'comportement_recommendations';
        
        return $wpdb->get_results("
            SELECT * FROM $table_recommendations 
            WHERE is_dismissed = 0 
            ORDER BY 
                CASE priorite 
                    WHEN 'haute' THEN 1 
                    WHEN 'moyenne' THEN 2 
                    WHEN 'basse' THEN 3 
                END,
                date_creation DESC
        ");
    }
    
    /**
     * Générer des recommandations automatiques basées sur les données
     */
    public static function generer_recommandations_automatiques() {
        global $wpdb;
        
        // Nettoyer les anciennes recommandations (plus de 7 jours)
        $table_recommendations = $wpdb->prefix . 'comportement_recommendations';
        $wpdb->query("DELETE FROM $table_recommendations WHERE date_creation < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        
        // Corriger les comptages de résultats si nécessaire
        self::corriger_comptages_resultats();
        
        // 1. Recommandations pour produits en rupture souvent recherchés
        self::generer_reco_produits_rupture();
        
        // 2. Recommandations pour produits jamais achetés
        self::generer_reco_produits_jamais_achetes();
        
        // 3. Recommandations pour produits abandonnés dans le panier
        self::generer_reco_paniers_abandonnes();
        
        // 4. Recommandations pour optimisation mobile
        self::generer_reco_optimisation_mobile();
        
        // 5. Recommandations pour termes de recherche populaires
        self::generer_reco_recherches_populaires();
    }
    
    /**
     * Corriger les comptages de résultats pour les recherches récentes
     */
    private static function corriger_comptages_resultats() {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        
        // Récupérer les recherches récentes avec un comptage potentiellement incorrect
        $recherches_recentes = $wpdb->get_results("
            SELECT id, terme_recherche, resultats_count
            FROM $table_recherches 
            WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL 1 DAY)
            AND (resultats_count = 0 OR resultats_count IS NULL)
            LIMIT 100
        ");
        
        foreach ($recherches_recentes as $recherche) {
            $vrais_resultats = self::verifier_resultats_recherche($recherche->terme_recherche);
            
            // Mettre à jour si différent
            if ($vrais_resultats != $recherche->resultats_count) {
                $wpdb->update(
                    $table_recherches,
                    array('resultats_count' => $vrais_resultats),
                    array('id' => $recherche->id)
                );
            }
        }
    }
    
    /**
     * Générer des recommandations pour les produits en rupture souvent recherchés
     */
    private static function generer_reco_produits_rupture() {
        global $wpdb;
        
        $table_rupture = $wpdb->prefix . 'recherches_rupture_stock';
        
        $produits_problematiques = $wpdb->get_results("
            SELECT 
                product_name,
                terme_recherche,
                COUNT(*) as recherches_count,
                COUNT(DISTINCT COALESCE(user_id, session_id)) as utilisateurs_uniques
            FROM $table_rupture 
            WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY product_name, terme_recherche
            HAVING recherches_count >= 3
            ORDER BY recherches_count DESC
            LIMIT 5
        ");
        
        foreach ($produits_problematiques as $produit) {
            $actions = array(
                "Reconstituer le stock pour '{$produit->product_name}'",
                "Contacter le fournisseur pour un réapprovisionnement urgent",
                "Proposer des produits alternatifs aux visiteurs",
                "Ajouter une notification de retour en stock"
            );
            
            self::creer_recommendation(
                'stock_rupture',
                "Produit '{$produit->product_name}' en forte demande",
                "Ce produit a été recherché {$produit->recherches_count} fois par {$produit->utilisateurs_uniques} utilisateurs différents cette semaine, mais est en rupture de stock.",
                'haute',
                json_encode($produit),
                json_encode($actions)
            );
        }
    }
    
    /**
     * Générer des recommandations pour les produits jamais achetés
     */
    private static function generer_reco_produits_jamais_achetes() {
        $produits = ComportementClientRechercheTracker::obtenir_produits_jamais_achetes();
        
        if (count($produits) >= 5) {
            $top_produits = array_slice($produits, 0, 5);
            $noms_produits = array_map(function($p) { return $p->product_name; }, $top_produits);
            
            $actions = array(
                "Analyser pourquoi ces produits ne se vendent pas",
                "Revoir le prix ou la description des produits",
                "Améliorer la visibilité de ces produits",
                "Créer des promotions ciblées",
                "Demander des avis clients sur ces produits"
            );
            
            self::creer_recommendation(
                'produits_jamais_achetes',
                count($produits) . " produits recherchés mais jamais achetés",
                "Plusieurs produits génèrent de l'intérêt (recherches) mais ne convertissent pas en ventes. Top 5: " . implode(', ', $noms_produits),
                'moyenne',
                json_encode($produits),
                json_encode($actions)
            );
        }
    }
    
    /**
     * Générer des recommandations pour les paniers abandonnés
     */
    private static function generer_reco_paniers_abandonnes() {
        $produits = ComportementClientRechercheTracker::obtenir_produits_panier_non_achetes();
        
        if (count($produits) >= 3) {
            $total_abandons = array_sum(array_map(function($p) { return $p->ajouts_panier; }, $produits));
            $top_produits = array_slice($produits, 0, 3);
            
            $actions = array(
                "Mettre en place des emails de relance panier abandonné",
                "Analyser les frais de livraison (cause fréquente d'abandon)",
                "Simplifier le processus de commande",
                "Proposer des codes de réduction pour finaliser l'achat",
                "Ajouter des avis clients sur les produits abandonnés"
            );
            
            self::creer_recommendation(
                'paniers_abandonnes',
                "$total_abandons produits abandonnés dans les paniers",
                "De nombreux produits sont ajoutés au panier mais ne sont pas achetés. Cela représente un manque à gagner important.",
                'haute',
                json_encode($produits),
                json_encode($actions)
            );
        }
    }
    
    /**
     * Générer des recommandations pour l'optimisation mobile
     */
    private static function generer_reco_optimisation_mobile() {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        
        $stats_mobile = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_actions,
                COUNT(CASE WHEN device_type = 'mobile' THEN 1 END) as actions_mobile,
                COUNT(CASE WHEN device_type = 'mobile' AND is_no_stock_search = 1 THEN 1 END) as recherches_vides_mobile
            FROM $table_recherches 
            WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        
        if ($stats_mobile && $stats_mobile->actions_mobile > 0) {
            $pourcentage_mobile = ($stats_mobile->actions_mobile / $stats_mobile->total_actions) * 100;
            $taux_echec_mobile = $stats_mobile->recherches_vides_mobile > 0 ? 
                ($stats_mobile->recherches_vides_mobile / $stats_mobile->actions_mobile) * 100 : 0;
            
            if ($pourcentage_mobile > 60 && $taux_echec_mobile > 20) {
                $actions = array(
                    "Optimiser l'expérience de recherche mobile",
                    "Améliorer la navigation mobile",
                    "Tester la vitesse de chargement sur mobile",
                    "Simplifier le processus d'achat mobile",
                    "Ajouter des suggestions de recherche mobile"
                );
                
                self::creer_recommendation(
                    'optimisation_mobile',
                    "Expérience mobile à améliorer",
                    "{$pourcentage_mobile}% de votre trafic est mobile, mais {$taux_echec_mobile}% des recherches mobiles ne donnent pas de résultats.",
                    'moyenne',
                    json_encode($stats_mobile),
                    json_encode($actions)
                );
            }
        }
    }
    
    /**
     * Générer des recommandations pour les termes de recherche populaires
     */
    private static function generer_reco_recherches_populaires() {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        
        // Récupérer les termes de recherche fréquents
        $termes_frequents = $wpdb->get_results("
            SELECT 
                terme_recherche,
                COUNT(*) as recherches_count,
                AVG(resultats_count) as avg_resultats,
                COUNT(DISTINCT COALESCE(user_id, session_id)) as utilisateurs_uniques
            FROM $table_recherches 
            WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND terme_recherche != ''
            AND terme_recherche NOT LIKE 'test%'
            AND terme_recherche NOT LIKE '%test%'
            GROUP BY terme_recherche
            HAVING recherches_count >= 3
            ORDER BY recherches_count DESC
            LIMIT 20
        ");
        
        // Vérifier manuellement quels termes ont vraiment 0 résultat
        $termes_sans_resultats = array();
        foreach ($termes_frequents as $terme_data) {
            $resultats = self::verifier_resultats_recherche($terme_data->terme_recherche);
            
            // Si le terme a vraiment 0 résultat ou très peu (moins de 2)
            if ($resultats < 2) {
                $terme_data->resultats_reels = $resultats;
                $termes_sans_resultats[] = $terme_data;
            }
        }
        
        // Limiter aux 10 premiers
        $termes_sans_resultats = array_slice($termes_sans_resultats, 0, 10);
        
        if (!empty($termes_sans_resultats)) {
            $termes_liste = array_map(function($t) { return "'{$t->terme_recherche}' ({$t->recherches_count}x)"; }, $termes_sans_resultats);
            
            $actions = array(
                "Vérifier pourquoi ces termes populaires ne donnent pas de résultats",
                "Ajouter des produits correspondant à ces termes de recherche",
                "Créer des synonymes ou redirections de recherche",
                "Améliorer l'algorithme de recherche interne",
                "Analyser la demande pour de nouveaux produits"
            );
            
            self::creer_recommendation(
                'recherches_vides',
                count($termes_sans_resultats) . " termes populaires sans résultats",
                "Ces termes sont régulièrement recherchés mais donnent peu/pas de résultats: " . implode(', ', array_slice($termes_liste, 0, 5)),
                'haute',
                json_encode($termes_sans_resultats),
                json_encode($actions)
            );
        }
    }
    
    /**
     * Vérifier manuellement les résultats d'une recherche
     */
    private static function verifier_resultats_recherche($terme) {
        $args = array(
            'post_type' => array('product', 'post', 'page'),
            'post_status' => 'publish',
            's' => $terme,
            'fields' => 'ids',
            'posts_per_page' => 1
        );
        
        $search_query = new WP_Query($args);
        return $search_query->found_posts;
    }
    
    /**
     * Créer une nouvelle recommandation
     */
    private static function creer_recommendation($type, $titre, $description, $priorite, $data_source, $actions_suggerees) {
        global $wpdb;
        
        $table_recommendations = $wpdb->prefix . 'comportement_recommendations';
        
        // Vérifier si cette recommandation existe déjà
        $existe = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table_recommendations 
            WHERE type_recommendation = %s 
            AND titre = %s 
            AND is_dismissed = 0
            AND date_creation >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        ", $type, $titre));
        
        if (!$existe) {
            $wpdb->insert(
                $table_recommendations,
                array(
                    'type_recommendation' => $type,
                    'titre' => $titre,
                    'description' => $description,
                    'priorite' => $priorite,
                    'data_source' => $data_source,
                    'actions_suggerees' => $actions_suggerees
                )
            );
        }
    }
    
    /**
     * Obtenir l'icône pour un type de recommandation
     */
    private static function icone_type_recommendation($type) {
        $icones = array(
            'stock_rupture' => '📦',
            'produits_jamais_achetes' => '🔍',
            'paniers_abandonnes' => '🛒',
            'optimisation_mobile' => '📱',
            'recherches_vides' => '🔎'
        );
        
        return isset($icones[$type]) ? $icones[$type] : '💡';
    }
    
    /**
     * Obtenir le libellé pour un type de recommandation
     */
    private static function libelle_type_recommendation($type) {
        $libelles = array(
            'stock_rupture' => 'Rupture de Stock',
            'produits_jamais_achetes' => 'Produits Non Vendus',
            'paniers_abandonnes' => 'Paniers Abandonnés',
            'optimisation_mobile' => 'Mobile',
            'recherches_vides' => 'Recherches Vides'
        );
        
        return isset($libelles[$type]) ? $libelles[$type] : 'Général';
    }
    
    /**
     * Obtenir l'icône pour une priorité
     */
    private static function icone_priorite($priorite) {
        $icones = array(
            'haute' => '🔥',
            'moyenne' => '⚠️',
            'basse' => '📊'
        );
        
        return isset($icones[$priorite]) ? $icones[$priorite] : '📌';
    }
}