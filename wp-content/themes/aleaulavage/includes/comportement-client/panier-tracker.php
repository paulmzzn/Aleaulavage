<?php
/**
 * Module de tracking des paniers
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientPanierTracker {
    
    /**
     * Initialiser les hooks
     */
    public static function init() {
        add_action('woocommerce_add_to_cart', array(__CLASS__, 'tracker_ajout_panier'), 10, 6);
        add_action('wp_login', array(__CLASS__, 'transferer_panier_anonyme_vers_connecte'), 10, 2);
    }
    
    /**
     * Tracker les ajouts au panier
     */
    public static function tracker_ajout_panier($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        global $wpdb;
        
        $session_info = ComportementClientSession::obtenir_infos_session();
        $session_id = $session_info['session_id'];
        $device_type = $session_info['device_type'];
        $user_id = get_current_user_id();
        
        if ($user_id) {
            self::tracker_panier_utilisateur_connecte($user_id, $product_id, $quantity, $device_type);
        } else {
            self::tracker_panier_anonyme($session_id, $product_id, $quantity, $device_type);
        }
    }
    
    /**
     * Tracker le panier d'un utilisateur connecté
     */
    private static function tracker_panier_utilisateur_connecte($user_id, $product_id, $quantity, $device_type) {
        $panier_actuel = get_user_meta($user_id, '_historique_panier', true);
        if (!is_array($panier_actuel)) {
            $panier_actuel = array();
        }
        
        $produit_trouve = false;
        foreach ($panier_actuel as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $panier_actuel[$key]['quantity'] = $quantity;
                $panier_actuel[$key]['date_modif'] = current_time('mysql');
                $panier_actuel[$key]['device_type'] = $device_type;
                $produit_trouve = true;
                break;
            }
        }
        
        if (!$produit_trouve) {
            $panier_actuel[] = array(
                'product_id' => $product_id,
                'quantity' => $quantity,
                'date_ajout' => current_time('mysql'),
                'date_modif' => current_time('mysql'),
                'device_type' => $device_type
            );
        }
        
        update_user_meta($user_id, '_historique_panier', $panier_actuel);
    }
    
    /**
     * Tracker le panier d'un visiteur anonyme
     */
    private static function tracker_panier_anonyme($session_id, $product_id, $quantity, $device_type) {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_paniers WHERE session_id = %s AND product_id = %d",
            $session_id, $product_id
        ));
        
        if ($existing) {
            $wpdb->update(
                $table_paniers,
                array(
                    'quantity' => $quantity, 
                    'date_modif' => current_time('mysql'),
                    'device_type' => $device_type
                ),
                array('id' => $existing->id)
            );
        } else {
            $wpdb->insert(
                $table_paniers,
                array(
                    'session_id' => $session_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'device_type' => $device_type
                )
            );
        }
    }
    
    /**
     * Transférer le panier anonyme vers l'utilisateur connecté
     */
    public static function transferer_panier_anonyme_vers_connecte($user_login, $user) {
        global $wpdb;
        
        if (!isset($_COOKIE['aleaulavage_session_id'])) {
            return;
        }
        
        $session_id = sanitize_text_field($_COOKIE['aleaulavage_session_id']);
        $user_id = $user->ID;
        
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $panier_anonyme = $wpdb->get_results($wpdb->prepare("
            SELECT product_id, quantity, date_ajout, date_modif, device_type 
            FROM $table_paniers 
            WHERE session_id = %s
        ", $session_id));
        
        if ($panier_anonyme) {
            $panier_user = get_user_meta($user_id, '_historique_panier', true);
            if (!is_array($panier_user)) {
                $panier_user = array();
            }
            
            foreach ($panier_anonyme as $item_anonyme) {
                $produit_trouve = false;
                
                foreach ($panier_user as $key => $item_user) {
                    if ($item_user['product_id'] == $item_anonyme->product_id) {
                        if (strtotime($item_anonyme->date_modif) > strtotime($item_user['date_modif'])) {
                            $panier_user[$key]['quantity'] = $item_anonyme->quantity;
                            $panier_user[$key]['date_modif'] = $item_anonyme->date_modif;
                            $panier_user[$key]['device_type'] = $item_anonyme->device_type;
                        }
                        $produit_trouve = true;
                        break;
                    }
                }
                
                if (!$produit_trouve) {
                    $panier_user[] = array(
                        'product_id' => $item_anonyme->product_id,
                        'quantity' => $item_anonyme->quantity,
                        'date_ajout' => $item_anonyme->date_ajout,
                        'date_modif' => $item_anonyme->date_modif,
                        'device_type' => $item_anonyme->device_type
                    );
                }
            }
            
            update_user_meta($user_id, '_historique_panier', $panier_user);
            
            $wpdb->delete($table_paniers, array('session_id' => $session_id));
            
            $table_recherches = $wpdb->prefix . 'recherches_anonymes';
            $wpdb->update(
                $table_recherches,
                array('user_id' => $user_id),
                array('session_id' => $session_id, 'user_id' => null)
            );
        }
    }
}

// Initialiser le tracker
ComportementClientPanierTracker::init();