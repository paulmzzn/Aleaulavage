<?php
/**
 * Reseller Mode Switcher (Floating Button)
 *
 * @package SCW_Shop
 */

defined( 'ABSPATH' ) || exit;

$user_role = scw_shop_get_user_role();
$user_mode = scw_shop_get_user_mode();

// Only show for resellers
if ( $user_role !== 'reseller' ) {
    return;
}

$mode_labels = array(
    'gestion' => 'Gestion',
    'achat'   => 'Achat',
    'vitrine' => 'Vitrine',
);

$mode_icons = array(
    'gestion' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
    'achat'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
    'vitrine' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
);

$current_mode = $user_mode ?: 'achat';
?>

<div class="reseller-mode-toggle">
    <div class="mode-menu">
        <button class="mode-menu-btn <?php echo $current_mode === 'gestion' ? 'active' : ''; ?>" data-mode="gestion">
            <?php echo esc_html( $mode_labels['gestion'] ); ?>
        </button>
        <button class="mode-menu-btn <?php echo $current_mode === 'achat' ? 'active' : ''; ?>" data-mode="achat">
            <?php echo esc_html( $mode_labels['achat'] ); ?>
        </button>
        <button class="mode-menu-btn <?php echo $current_mode === 'vitrine' ? 'active' : ''; ?>" data-mode="vitrine">
            <?php echo esc_html( $mode_labels['vitrine'] ); ?>
        </button>
    </div>

    <button class="mode-trigger-btn" title="Mode: <?php echo esc_attr( $mode_labels[ $current_mode ] ); ?>">
        <span class="mode-icon" data-current-mode="<?php echo esc_attr( $current_mode ); ?>">
            <?php echo $mode_icons[ $current_mode ]; ?>
        </span>
    </button>
</div>
