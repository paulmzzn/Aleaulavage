<?php
/**
 * Page de monitoring temps réel
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap comportement-v2-realtime">
    <h1>⚡ Monitoring Temps Réel</h1>
    
    <div class="realtime-indicator">
        <div class="realtime-dot"></div>
        Données mises à jour automatiquement toutes les 30 secondes
    </div>
    
    <div class="realtime-stats comportement-grid comportement-grid-4">
        <div class="comportement-metric realtime-active-sessions">
            <div class="stat-number"><?php echo $realtime_stats['active_sessions']; ?></div>
            <div class="stat-label">Sessions Actives</div>
            <div class="stat-sublabel">Dernières 30 min</div>
        </div>
        
        <div class="comportement-metric realtime-page-views">
            <div class="stat-number"><?php echo $realtime_stats['recent_page_views']; ?></div>
            <div class="stat-label">Pages Vues</div>
            <div class="stat-sublabel">Dernière heure</div>
        </div>
        
        <div class="comportement-metric realtime-product-views">
            <div class="stat-number"><?php echo $realtime_stats['recent_product_views']; ?></div>
            <div class="stat-label">Produits Vus</div>
            <div class="stat-sublabel">Dernière heure</div>
        </div>
        
        <div class="comportement-metric realtime-cart-adds">
            <div class="stat-number"><?php echo $realtime_stats['recent_cart_adds']; ?></div>
            <div class="stat-label">Ajouts Panier</div>
            <div class="stat-sublabel">Dernière heure</div>
        </div>
    </div>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Pages Populaires Actuellement</h3>
            <button class="refresh-data-btn comportement-btn comportement-btn-secondary comportement-btn-sm">
                🔄 Actualiser
            </button>
        </div>
        
        <div class="popular-pages-realtime">
            <?php if (!empty($realtime_stats['top_current_pages'])): ?>
                <?php foreach ($realtime_stats['top_current_pages'] as $page): ?>
                    <div class="page-item-realtime">
                        <div class="page-url"><?php echo esc_html(json_decode($page->page_url, true)); ?></div>
                        <div class="page-views"><?php echo $page->views; ?> vues</div>
                        <div class="page-activity">
                            <div class="activity-bar">
                                <div class="activity-fill" style="width: <?php echo min(100, ($page->views / max(1, $realtime_stats['top_current_pages'][0]->views ?? 1)) * 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-activity">
                    <div class="no-activity-icon">😴</div>
                    <h4>Aucune activité récente</h4>
                    <p>Il n'y a pas d'activité détectable sur le site en ce moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="comportement-card">
        <div class="comportement-card-header">
            <h3 class="comportement-card-title">Événements en Direct</h3>
            <div class="realtime-controls">
                <button id="pauseRealtime" class="comportement-btn comportement-btn-secondary comportement-btn-sm">⏸️ Pause</button>
                <button id="clearEvents" class="comportement-btn comportement-btn-secondary comportement-btn-sm">🗑️ Vider</button>
            </div>
        </div>
        
        <div id="realtimeEvents" class="realtime-events">
            <div class="event-placeholder">
                <div class="event-icon">⏱️</div>
                <div class="event-message">En attente d'événements...</div>
            </div>
        </div>
    </div>
    
</div>

<style>
.realtime-stats .comportement-metric.updated {
    animation: realtimeUpdate 0.5s ease;
    border-left-color: var(--success-color);
}

@keyframes realtimeUpdate {
    0% { background: #f8f9fa; }
    50% { background: #d4edda; }
    100% { background: #f8f9fa; }
}

.popular-pages-realtime {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.page-item-realtime {
    display: grid;
    grid-template-columns: 1fr auto 100px;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: var(--transition);
}

.page-item-realtime:hover {
    background: #e9ecef;
}

.page-url {
    color: var(--primary-color);
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.page-views {
    color: var(--success-color);
    font-weight: 600;
    font-size: 14px;
}

.activity-bar {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    width: 100px;
}

.activity-fill {
    height: 100%;
    background: var(--success-color);
    transition: width 0.3s ease;
    border-radius: 3px;
}

.no-activity {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.no-activity-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.no-activity h4 {
    margin: 0 0 10px 0;
    color: var(--dark-color);
}

.no-activity p {
    margin: 0;
    font-size: 14px;
}

.realtime-controls {
    display: flex;
    gap: 10px;
}

.realtime-events {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
}

.event-placeholder {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #6c757d;
    font-style: italic;
    padding: 20px;
    text-align: center;
    justify-content: center;
}

.event-icon {
    font-size: 20px;
}

.realtime-event {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-bottom: 1px solid #f1f1f1;
    animation: fadeInEvent 0.3s ease;
}

.realtime-event:last-child {
    border-bottom: none;
}

@keyframes fadeInEvent {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.event-time {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
    min-width: 60px;
}

.event-type {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    min-width: 80px;
    text-align: center;
}

.event-type.page_view {
    background: #cce5ff;
    color: #004085;
}

.event-type.product_view {
    background: #d4edda;
    color: #155724;
}

.event-type.cart_add {
    background: #fff3cd;
    color: #856404;
}

.event-type.search {
    background: #f8d7da;
    color: #721c24;
}

.event-message {
    flex: 1;
    font-size: 14px;
    color: var(--dark-color);
}

.event-user {
    font-size: 12px;
    color: #6c757d;
    text-align: right;
    min-width: 100px;
}
</style>

<script>
// Simulation d'événements temps réel
let realtimePaused = false;
let eventCount = 0;

function simulateRealtimeEvent() {
    if (realtimePaused) return;
    
    const events = [
        { type: 'page_view', message: 'Nouvelle visite sur la page d\'accueil', user: 'Anonyme' },
        { type: 'product_view', message: 'Consultation produit "Nettoyeur haute pression"', user: 'Client #1234' },
        { type: 'cart_add', message: 'Ajout au panier: Lance à mousse', user: 'Jean Dupont' },
        { type: 'search', message: 'Recherche: "karcher"', user: 'Visiteur anonyme' }
    ];
    
    const randomEvent = events[Math.floor(Math.random() * events.length)];
    const now = new Date().toLocaleTimeString('fr-FR');
    
    const eventHtml = `
        <div class="realtime-event">
            <div class="event-time">${now}</div>
            <div class="event-type ${randomEvent.type}">${randomEvent.type}</div>
            <div class="event-message">${randomEvent.message}</div>
            <div class="event-user">${randomEvent.user}</div>
        </div>
    `;
    
    const $eventsContainer = $('#realtimeEvents');
    $eventsContainer.find('.event-placeholder').remove();
    $eventsContainer.prepend(eventHtml);
    
    // Limiter à 20 événements
    const events_items = $eventsContainer.find('.realtime-event');
    if (events_items.length > 20) {
        events_items.slice(20).remove();
    }
    
    eventCount++;
}

// Démarrer la simulation d'événements
setInterval(simulateRealtimeEvent, 5000);

// Contrôles
$(document).ready(function() {
    $('#pauseRealtime').click(function() {
        realtimePaused = !realtimePaused;
        $(this).html(realtimePaused ? '▶️ Reprendre' : '⏸️ Pause');
    });
    
    $('#clearEvents').click(function() {
        $('#realtimeEvents').html('<div class="event-placeholder"><div class="event-icon">⏱️</div><div class="event-message">En attente d\'événements...</div></div>');
        eventCount = 0;
    });
});
</script>