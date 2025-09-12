<?php
/**
 * Nouveau système de comportement client - Version modulaire
 * 
 * Ce fichier remplace l'ancien admin-comportement.php avec une architecture modulaire
 * et la détection automatique du type d'appareil (Mobile/PC/Tablette).
 * 
 * @package Aleaulavage
 * @version 2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de compatibilité pour maintenir les fonctions existantes
 */
class ComportementClientCompatibilite {
    
    /**
     * Maintenir la fonction obtenir_session_id() pour la compatibilité
     */
    public static function setup_backward_compatibility() {
        // Fonction de compatibilité pour obtenir_session_id()
        if (!function_exists('obtenir_session_id')) {
            function obtenir_session_id() {
                return ComportementClientSession::obtenir_session_id();
            }
        }
        
        // Fonction de compatibilité pour determiner_statut_panier()
        if (!function_exists('determiner_statut_panier')) {
            function determiner_statut_panier($type, $identifiant, $date_modif_panier) {
                return ComportementClientCompatibilite::determiner_statut_panier_legacy($type, $identifiant, $date_modif_panier);
            }
        }
    }
    
    /**
     * Version legacy de determiner_statut_panier pour la compatibilité
     */
    public static function determiner_statut_panier_legacy($type, $identifiant, $date_modif_panier) {
        global $wpdb;
        
        if ($type === 'connecte') {
            $commandes = wc_get_orders([
                'customer' => $identifiant,
                'limit' => 5,
                'orderby' => 'date',
                'order' => 'DESC',
                'date_created' => '>' . (time() - (7 * 24 * 60 * 60))
            ]);
            
            foreach ($commandes as $commande) {
                if (strtotime($commande->get_date_created()) > strtotime($date_modif_panier)) {
                    return 'converti';
                }
            }
        } else {
            $commandes = wc_get_orders([
                'limit' => 10,
                'orderby' => 'date',
                'order' => 'DESC',
                'date_created' => '>' . (time() - (7 * 24 * 60 * 60))
            ]);
            
            foreach ($commandes as $commande) {
                if (strtotime($commande->get_date_created()) > strtotime($date_modif_panier)) {
                    return 'potentiellement_converti';
                }
            }
        }
        
        return 'abandonné';
    }
}

// Charger le nouveau système modulaire
require_once __DIR__ . '/comportement-client/init.php';

// Setup de la compatibilité
ComportementClientCompatibilite::setup_backward_compatibility();

/**
 * Notice d'information pour les administrateurs
 */
add_action('admin_notices', function() {
    if (current_user_can('manage_options') && isset($_GET['page']) && strpos($_GET['page'], 'comportement-clients') !== false) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Système mis à jour !</strong> Le système de comportement client a été restructuré avec les améliorations suivantes :</p>';
        echo '<ul style="margin-left: 20px; list-style-type: disc;">';
        echo '<li>Architecture modulaire pour une meilleure maintenance</li>';
        echo '<li>Détection automatique du type d\'appareil (📱 Mobile, 💻 PC, 📱 Tablette)</li>';
        echo '<li>Nouvelles statistiques par device</li>';
        echo '<li>Interface d\'administration améliorée</li>';
        echo '<li>Compatibilité maintenue avec l\'ancien système</li>';
        echo '</ul>';
        echo '</div>';
    }
});

/**
 * Hook de debug pour les développeurs
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('wp_footer', function() {
        if (current_user_can('manage_options') && isset($_GET['debug_comportement'])) {
            $debug_info = ComportementClientInit::obtenir_infos_debug();
            echo '<div style="position: fixed; bottom: 10px; right: 10px; background: #000; color: #fff; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; z-index: 9999; max-width: 400px;">';
            echo '<strong>Debug Comportement Client:</strong><br>';
            echo 'Version BD: ' . $debug_info['version_bd'] . '<br>';
            echo 'Session ID: ' . ComportementClientSession::obtenir_session_id() . '<br>';
            echo 'Device: ' . ComportementClientSession::detecter_device() . '<br>';
            echo 'Tables OK: ' . ($debug_info['table_paniers_existe'] && $debug_info['table_recherches_existe'] ? '✓' : '✗') . '<br>';
            echo 'Device columns: ' . ($debug_info['colonne_device_paniers'] && $debug_info['colonne_device_recherches'] ? '✓' : '✗') . '<br>';
            echo 'Total paniers: ' . $debug_info['total_paniers'] . '<br>';
            echo 'Total recherches: ' . $debug_info['total_recherches'] . '<br>';
            echo '</div>';
        }
    });
}