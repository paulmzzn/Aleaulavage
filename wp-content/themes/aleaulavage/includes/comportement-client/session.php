<?php
/**
 * Module de gestion des sessions et détection de device
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientSession {
    
    /**
     * Obtenir ou créer un ID de session unique
     */
    public static function obtenir_session_id() {
        if (!isset($_COOKIE['aleaulavage_session_id'])) {
            $session_id = 'visiteur_' . uniqid();
            setcookie('aleaulavage_session_id', $session_id, time() + (30 * 24 * 60 * 60), '/');
        } else {
            $session_id = sanitize_text_field($_COOKIE['aleaulavage_session_id']);
        }
        return $session_id;
    }
    
    /**
     * Détecter le type de device (mobile/PC)
     */
    public static function detecter_device() {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return 'inconnu';
        }
        
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        // Patterns pour détecter les appareils mobiles
        $mobile_patterns = array(
            '/android/i',
            '/iphone/i',
            '/ipad/i',
            '/ipod/i',
            '/blackberry/i',
            '/windows phone/i',
            '/mobile/i',
            '/webos/i',
            '/opera mini/i',
            '/palm/i'
        );
        
        foreach ($mobile_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return 'mobile';
            }
        }
        
        // Patterns pour détecter les tablettes spécifiquement
        $tablet_patterns = array(
            '/ipad/i',
            '/android(?!.*mobile)/i',
            '/tablet/i'
        );
        
        foreach ($tablet_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return 'tablette';
            }
        }
        
        return 'pc';
    }
    
    /**
     * Obtenir des informations complètes sur la session
     */
    public static function obtenir_infos_session() {
        return array(
            'session_id' => self::obtenir_session_id(),
            'device_type' => self::detecter_device(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'ip_address' => self::obtenir_ip_visiteur(),
            'timestamp' => current_time('mysql')
        );
    }
    
    /**
     * Obtenir l'adresse IP du visiteur
     */
    private static function obtenir_ip_visiteur() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'inconnu';
    }
}