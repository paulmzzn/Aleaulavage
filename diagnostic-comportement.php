<?php
/**
 * Script de diagnostic pour le système de comportement v2
 */

// Incluez ce fichier dans WordPress pour le diagnostic
require_once dirname(__FILE__) . '/wp-config.php';
require_once dirname(__FILE__) . '/wp-content/themes/aleaulavage/includes/comportement-v2.php';

echo "<h1>Diagnostic Système Comportement v2</h1>";

global $wpdb;

// 1. Vérifier les tables
echo "<h2>1. État des tables</h2>";
$tables = [
    'paniers_anonymes',
    'recherches_anonymes', 
    'comportement_events',
    'comportement_analytics_daily'
];

foreach ($tables as $table) {
    $full_table = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'");
    $status = $exists ? "✅ Existe" : "❌ Manquante";
    echo "<p>Table $full_table: $status</p>";
    
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
        echo "<p>Nombre d'enregistrements: $count</p>";
    }
}

// 2. Vérifier l'option d'activation
echo "<h2>2. Activation du système</h2>";
$enabled = get_option('comportement_v2_enabled', false);
echo "<p>Option comportement_v2_enabled: " . ($enabled ? "✅ Activé" : "❌ Désactivé") . "</p>";

// 3. Vérifier les hooks
echo "<h2>3. État des hooks</h2>";
$tracker = ComportementSystemV2::get_instance()->get_tracker();
if ($tracker) {
    echo "<p>Tracker instance: ✅ Créée</p>";
    echo "<p>Tracking enabled: " . ($tracker->is_tracking_enabled() ? "✅ Oui" : "❌ Non") . "</p>";
} else {
    echo "<p>Tracker instance: ❌ Non trouvée</p>";
}

// 4. Test d'insertion d'événement
echo "<h2>4. Test d'insertion d'événement</h2>";
if ($tracker) {
    $test_result = $tracker->track_event('test_diagnostic', [
        'message' => 'Test de diagnostic',
        'timestamp' => current_time('mysql')
    ]);
    echo "<p>Test d'insertion: " . ($test_result ? "✅ Succès" : "❌ Échec") . "</p>";
    
    // Vérifier si l'événement a été inséré
    $table_events = $wpdb->prefix . 'comportement_events';
    $recent_test = $wpdb->get_var("SELECT COUNT(*) FROM $table_events WHERE event_type = 'test_diagnostic' AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
    echo "<p>Événement de test trouvé: " . ($recent_test > 0 ? "✅ Oui ($recent_test)" : "❌ Non") . "</p>";
}

// 5. Vérifier les scripts JavaScript
echo "<h2>5. Scripts JavaScript</h2>";
$tracking_js = get_template_directory() . '/includes/comportement/assets/tracking.js';
if (file_exists($tracking_js)) {
    echo "<p>Script tracking.js: ✅ Existe</p>";
} else {
    echo "<p>Script tracking.js: ❌ Manquant</p>";
}

// 6. Récupérer les derniers événements
echo "<h2>6. Derniers événements (5 plus récents)</h2>";
$table_events = $wpdb->prefix . 'comportement_events';
$recent_events = $wpdb->get_results("SELECT * FROM $table_events ORDER BY timestamp DESC LIMIT 5");

if ($recent_events) {
    echo "<table border='1'><tr><th>ID</th><th>Type</th><th>Timestamp</th><th>Session</th><th>User ID</th></tr>";
    foreach ($recent_events as $event) {
        echo "<tr>";
        echo "<td>{$event->id}</td>";
        echo "<td>{$event->event_type}</td>";
        echo "<td>{$event->timestamp}</td>";
        echo "<td>" . substr($event->session_id, -8) . "</td>";
        echo "<td>{$event->user_id}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Aucun événement trouvé</p>";
}

echo "<p><strong>Diagnostic terminé</strong></p>";
?>