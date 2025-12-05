<?php
/**
 * Reseller Dashboard
 *
 * @package SCW_Shop
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$company_name = get_user_meta( $current_user->ID, '_company_name', true ) ?: 'Hydro Clean Services';
$siret = get_user_meta( $current_user->ID, '_siret', true ) ?: '123 456 789 00012';
$global_margin = get_user_meta( $current_user->ID, '_global_margin', true ) ?: 38;

// Get initials for avatar
$initials = strtoupper( substr( $company_name, 0, 1 ) . substr( $company_name, strpos( $company_name, ' ' ) + 1, 1 ) );

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'dashboard';
?>

<div class="profile-container reseller">
	<div class="dashboard-header">
		<div class="company-badge">
			<div class="company-avatar"><?php echo esc_html( $initials ); ?></div>
			<div>
				<h1><?php echo esc_html( $company_name ); ?></h1>
				<span class="badge-pro">Compte Revendeur</span>
			</div>
		</div>
		<div class="dashboard-nav">
			<button class="nav-tab <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>"
			        onclick="window.location.href='<?php echo esc_url( add_query_arg( 'tab', 'dashboard' ) ); ?>'">
				Tableau de bord
			</button>
			<button class="nav-tab <?php echo $active_tab === 'clients' ? 'active' : ''; ?>"
			        onclick="window.location.href='<?php echo esc_url( add_query_arg( 'tab', 'clients' ) ); ?>'">
				Mes Clients
			</button>
			<button class="nav-tab <?php echo $active_tab === 'settings' ? 'active' : ''; ?>"
			        onclick="window.location.href='<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>'">
				Ma Boutique
			</button>
		</div>
	</div>

	<div class="dashboard-content">

		<?php if ( $active_tab === 'dashboard' ) : ?>
			<!-- TAB DASHBOARD -->
			<div class="dashboard-grid">
				<div class="card stat-card">
					<h3>Performance du mois</h3>
					<div class="stat-row">
						<div class="stat">
							<span class="label">Chiffre d'affaires</span>
							<span class="value">12,450 €</span>
						</div>
						<div class="stat">
							<span class="label">Marge Nette</span>
							<span class="value text-green">4,200 €</span>
						</div>
					</div>
				</div>

				<div class="card config-card">
					<h3>Configuration Tarifaire</h3>
					<p class="info-text">Coefficient multiplicateur par défaut appliqué aux nouveaux produits.</p>
					<div class="margin-input-wrapper">
						<label>Marge par défaut (%)</label>
						<input type="number" value="<?php echo esc_attr( $global_margin ); ?>" id="global-margin" />
					</div>
				</div>

				<div class="card full-width">
					<div class="card-header">
						<h3>Dernières Commandes</h3>
					</div>
					<table class="data-table">
						<thead>
							<tr>
								<th>Client</th>
								<th>Date</th>
								<th>Montant</th>
								<th>Statut</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><strong>Garage du Nord</strong></td>
								<td>Auj. 10:30</td>
								<td>450.00 €</td>
								<td><span class="status-pill pending">En attente</span></td>
							</tr>
							<tr>
								<td><strong>Station Total Wash</strong></td>
								<td>Hier 14:15</td>
								<td>2,100.00 €</td>
								<td><span class="status-pill success">Validée</span></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

		<?php elseif ( $active_tab === 'clients' ) : ?>
			<!-- TAB CLIENTS -->
			<div class="clients-list-view">
				<div class="card full-width">
					<div class="card-header">
						<h3>Liste des clients (3)</h3>
						<button class="btn-outline">+ Nouveau Client</button>
					</div>
					<table class="data-table">
						<thead>
							<tr>
								<th>Nom</th>
								<th>Type</th>
								<th>CA Total</th>
								<th>Prix Spécifiques</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><strong>Garage du Nord</strong></td>
								<td>Garage</td>
								<td>4,500 €</td>
								<td><span class="tag-custom">Activés (1)</span></td>
								<td><button class="link-btn">Gérer</button></td>
							</tr>
							<tr>
								<td><strong>Station Total Wash</strong></td>
								<td>Station</td>
								<td>12,800 €</td>
								<td><span class="tag-default">Standard</span></td>
								<td><button class="link-btn">Gérer</button></td>
							</tr>
							<tr>
								<td><strong>Lavage Express 77</strong></td>
								<td>Indépendant</td>
								<td>890 €</td>
								<td><span class="tag-default">Standard</span></td>
								<td><button class="link-btn">Gérer</button></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

		<?php elseif ( $active_tab === 'settings' ) : ?>
			<!-- TAB SETTINGS -->
			<div class="settings-view">
				<div class="card">
					<h3>Personnalisation de votre boutique</h3>
					<p class="info-text">Choisissez la couleur principale que verront vos clients lorsqu'ils se connectent à votre boutique.</p>

					<?php
					$colors = array(
						array( 'name' => 'Bleu SCW', 'hex' => '#0ea5e9' ),
						array( 'name' => 'Rouge Énergie', 'hex' => '#ef4444' ),
						array( 'name' => 'Vert Nature', 'hex' => '#10b981' ),
						array( 'name' => 'Violet Tech', 'hex' => '#8b5cf6' ),
						array( 'name' => 'Orange Industriel', 'hex' => '#f97316' ),
						array( 'name' => 'Gris Premium', 'hex' => '#334155' ),
					);
					$current_color = get_user_meta( $current_user->ID, '_store_color', true ) ?: '#0ea5e9';
					?>

					<div class="color-picker-grid">
						<?php foreach ( $colors as $color ) : ?>
							<div class="color-swatch <?php echo $current_color === $color['hex'] ? 'selected' : ''; ?>"
							     data-color="<?php echo esc_attr( $color['hex'] ); ?>">
								<div class="swatch-preview" style="background-color: <?php echo esc_attr( $color['hex'] ); ?>;"></div>
								<span class="swatch-name"><?php echo esc_html( $color['name'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="preview-box" style="margin-top: 2rem;">
						<h4>Aperçu bouton client :</h4>
						<button id="preview-button" style="background-color: <?php echo esc_attr( $current_color ); ?>; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 8px; font-weight: bold;">
							Ajouter au panier
						</button>
					</div>
				</div>
			</div>

		<?php endif; ?>

	</div>
</div>
