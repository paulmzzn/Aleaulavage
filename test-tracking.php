<?php
/**
 * Test simple du tracking
 */

// Chargez WordPress
require_once dirname(__FILE__) . '/wp-config.php';

// Forcer la création des tables
require_once dirname(__FILE__) . '/wp-content/themes/aleaulavage/includes/comportement/core/database.php';
ComportementDatabase::create_tables();

// Obtenir le tracker
require_once dirname(__FILE__) . '/wp-content/themes/aleaulavage/includes/comportement-v2.php';
$system = ComportementSystemV2::get_instance();
$tracker = $system->get_tracker();

echo "<h1>Test du tracking</h1>";

if ($tracker) {
    echo "<p>Tracker instance trouvée</p>";
    
    // Test direct d'insertion
    $result = $tracker->track_event('test_manual', [
        'message' => 'Test manuel depuis script',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    echo "<p>Résultat du test: " . ($result ? "✅ Succès" : "❌ Échec") . "</p>";
    
    // Vérifier dans la base
    global $wpdb;
    $table = $wpdb->prefix . 'comportement_events';
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE event_type = 'test_manual'");
    echo "<p>Événements test_manual dans la base: $count</p>";
    
    // Afficher les derniers événements
    $events = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC LIMIT 5");
    if ($events) {
        echo "<h3>Derniers événements:</h3>";
        echo "<table border='1'><tr><th>ID</th><th>Type</th><th>Timestamp</th><th>Session</th></tr>";
        foreach ($events as $event) {
            echo "<tr><td>{$event->id}</td><td>{$event->event_type}</td><td>{$event->timestamp}</td><td>" . substr($event->session_id, -8) . "</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p>❌ Aucune instance de tracker trouvée</p>";
}
?>