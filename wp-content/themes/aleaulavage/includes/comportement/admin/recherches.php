<?php
/**
 * Page d'analyse des recherches
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-recherches">
    <h1>🔍 Recherches Clients</h1>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Recherches par terme</h3>
        </div>
        
        <div class="recherches-par-terme">
            <?php if (!empty($recherches_data['par_terme'])): ?>
                <div class="recherches-table">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Terme de recherche</th>
                                <th>Nombre de recherches</th>
                                <th>Utilisateurs uniques</th>
                                <th>Dernière recherche</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recherches_data['par_terme'] as $recherche): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($recherche['terme']); ?></strong></td>
                                    <td><?php echo intval($recherche['total_recherches']); ?></td>
                                    <td><?php echo intval($recherche['utilisateurs_uniques']); ?></td>
                                    <td><?php echo esc_html(date('d/m/Y H:i', strtotime($recherche['derniere_recherche']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">🔍</div>
                    <h4>Aucune recherche trouvée</h4>
                    <p>Il n'y a pas encore de recherches enregistrées.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Recherches par utilisateur</h3>
        </div>
        
        <div class="recherches-par-utilisateur">
            <?php if (!empty($recherches_data['par_utilisateur'])): ?>
                <div class="recherches-table">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Type</th>
                                <th>Nombre de recherches</th>
                                <th>Dernière recherche</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recherches_data['par_utilisateur'] as $user_data): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($user_data['display_name']); ?></strong>
                                        <?php if ($user_data['user_email']): ?>
                                            <br><small><?php echo esc_html($user_data['user_email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="user-type user-type-<?php echo $user_data['type']; ?>">
                                            <?php echo $user_data['type'] === 'connecte' ? 'Connecté' : 'Anonyme'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo intval($user_data['total_recherches']); ?></td>
                                    <td><?php echo esc_html(date('d/m/Y H:i', strtotime($user_data['derniere_recherche']))); ?></td>
                                    <td>
                                        <button class="button-link view-user-searches" data-user="<?php echo esc_attr($user_data['user_id'] ?: $user_data['session_id']); ?>">
                                            Voir détails
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">👤</div>
                    <h4>Aucun utilisateur trouvé</h4>
                    <p>Il n'y a pas encore d'utilisateurs ayant effectué des recherches.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.no-data {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.no-data-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.no-data h4 {
    margin: 0 0 10px 0;
    color: var(--dark-color);
}

.no-data p {
    margin: 0;
    font-size: 14px;
}

.user-type {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.user-type-connecte {
    background: #d4edda;
    color: #155724;
}

.user-type-anonyme {
    background: #e2e3e5;
    color: #383d41;
}

.view-user-searches {
    color: #0073aa;
    text-decoration: none;
    font-size: 13px;
}

.view-user-searches:hover {
    text-decoration: underline;
}

.recherches-table {
    overflow-x: auto;
}

.recherches-table table {
    min-width: 600px;
}
</style>

<script>
$(document).ready(function() {
    $('.view-user-searches').click(function(e) {
        e.preventDefault();
        var userId = $(this).data('user');
        // TODO: Implémenter la vue détaillée des recherches d'un utilisateur
        alert('Détails pour utilisateur: ' + userId);
    });
});
</script>