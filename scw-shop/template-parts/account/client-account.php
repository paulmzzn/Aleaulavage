<?php
/**
 * Client Account
 *
 * @package SCW_Shop
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$customer = new WC_Customer( $current_user->ID );

// Get recent orders
$customer_orders = wc_get_orders( array(
	'customer' => $current_user->ID,
	'limit'    => 10,
	'orderby'  => 'date',
	'order'    => 'DESC',
) );

// Get customer addresses
$billing_address = array(
	'first_name' => $customer->get_billing_first_name(),
	'last_name'  => $customer->get_billing_last_name(),
	'company'    => $customer->get_billing_company(),
	'address_1'  => $customer->get_billing_address_1(),
	'address_2'  => $customer->get_billing_address_2(),
	'city'       => $customer->get_billing_city(),
	'postcode'   => $customer->get_billing_postcode(),
	'country'    => $customer->get_billing_country(),
	'phone'      => $customer->get_billing_phone(),
);

$shipping_address = array(
	'first_name' => $customer->get_shipping_first_name(),
	'last_name'  => $customer->get_shipping_last_name(),
	'company'    => $customer->get_shipping_company(),
	'address_1'  => $customer->get_shipping_address_1(),
	'address_2'  => $customer->get_shipping_address_2(),
	'city'       => $customer->get_shipping_city(),
	'postcode'   => $customer->get_shipping_postcode(),
	'country'    => $customer->get_shipping_country(),
);
?>

<div class="profile-container client">
	<div class="client-header">
		<h1>Bonjour, <?php echo esc_html( $current_user->display_name ); ?></h1>
		<p>Membre depuis <?php echo esc_html( date( 'Y', strtotime( $current_user->user_registered ) ) ); ?></p>
	</div>

	<div class="client-layout">
		<div class="client-sidebar">
			<button class="menu-item active" data-tab="orders">Mes Commandes</button>
			<button class="menu-item" data-tab="addresses">Mes Adresses</button>
			<a href="<?php echo esc_url( home_url( '/favoris' ) ); ?>" class="menu-item">Mes Favoris</a>
			<button class="menu-item" data-tab="personal-info">Informations personnelles</button>
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="menu-item logout">Déconnexion</a>
		</div>

		<div class="client-content">

			<!-- TAB: MES COMMANDES -->
			<div class="tab-content active" id="tab-orders">
				<div class="card">
					<h3>Historique des commandes</h3>

					<?php if ( ! empty( $customer_orders ) ) : ?>
						<div class="order-list">
							<?php foreach ( $customer_orders as $order ) : ?>
								<div class="order-item">
									<div class="order-info">
										<span class="order-id">#<?php echo esc_html( $order->get_order_number() ); ?></span>
										<span class="order-date"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
									</div>
									<div class="order-details">
										<span><?php echo esc_html( $order->get_item_count() ); ?> article(s)</span>
										<span class="order-total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
									</div>
									<div class="order-status">
										<span class="status-text"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
									</div>
									<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="btn-small">Voir</a>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p style="text-align: center; color: #94a3b8; padding: 2rem;">Vous n'avez pas encore de commandes.</p>
					<?php endif; ?>
				</div>
			</div>

			<!-- TAB: MES ADRESSES -->
			<div class="tab-content" id="tab-addresses">
				<div class="addresses-grid">
					<!-- Billing Address -->
					<div class="card address-card">
						<div class="address-header">
							<h3>Adresse de facturation</h3>
							<button class="btn-edit" data-address-type="billing">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
									<path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
								</svg>
								Modifier
							</button>
						</div>
						<div class="address-display">
							<?php if ( ! empty( $billing_address['address_1'] ) ) : ?>
								<?php if ( ! empty( $billing_address['first_name'] ) ) : ?>
									<p><strong><?php echo esc_html( $billing_address['first_name'] . ' ' . $billing_address['last_name'] ); ?></strong></p>
								<?php endif; ?>
								<?php if ( ! empty( $billing_address['company'] ) ) : ?>
									<p><?php echo esc_html( $billing_address['company'] ); ?></p>
								<?php endif; ?>
								<p><?php echo esc_html( $billing_address['address_1'] ); ?></p>
								<?php if ( ! empty( $billing_address['address_2'] ) ) : ?>
									<p><?php echo esc_html( $billing_address['address_2'] ); ?></p>
								<?php endif; ?>
								<p><?php echo esc_html( $billing_address['postcode'] . ' ' . $billing_address['city'] ); ?></p>
								<?php if ( ! empty( $billing_address['phone'] ) ) : ?>
									<p class="address-phone"><?php esc_html_e( 'Tél:', 'scw-shop' ); ?> <?php echo esc_html( $billing_address['phone'] ); ?></p>
								<?php endif; ?>
							<?php else : ?>
								<p class="address-empty"><?php esc_html_e( 'Aucune adresse de facturation enregistrée', 'scw-shop' ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<!-- Shipping Address -->
					<div class="card address-card">
						<div class="address-header">
							<h3>Adresse de livraison</h3>
							<button class="btn-edit" data-address-type="shipping">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
									<path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
								</svg>
								Modifier
							</button>
						</div>
						<div class="address-display">
							<?php if ( ! empty( $shipping_address['address_1'] ) ) : ?>
								<?php if ( ! empty( $shipping_address['first_name'] ) ) : ?>
									<p><strong><?php echo esc_html( $shipping_address['first_name'] . ' ' . $shipping_address['last_name'] ); ?></strong></p>
								<?php endif; ?>
								<?php if ( ! empty( $shipping_address['company'] ) ) : ?>
									<p><?php echo esc_html( $shipping_address['company'] ); ?></p>
								<?php endif; ?>
								<p><?php echo esc_html( $shipping_address['address_1'] ); ?></p>
								<?php if ( ! empty( $shipping_address['address_2'] ) ) : ?>
									<p><?php echo esc_html( $shipping_address['address_2'] ); ?></p>
								<?php endif; ?>
								<p><?php echo esc_html( $shipping_address['postcode'] . ' ' . $shipping_address['city'] ); ?></p>
							<?php else : ?>
								<p class="address-empty"><?php esc_html_e( 'Aucune adresse de livraison enregistrée', 'scw-shop' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<!-- TAB: INFORMATIONS PERSONNELLES -->
			<div class="tab-content" id="tab-personal-info">
				<div class="card">
					<h3>Informations personnelles</h3>
					<form class="personal-info-form" method="post" action="">
						<?php wp_nonce_field( 'scw_update_account', 'scw_account_nonce' ); ?>

						<div class="form-grid">
							<div class="form-group">
								<label for="account_first_name"><?php esc_html_e( 'Prénom', 'scw-shop' ); ?> <span class="required">*</span></label>
								<input type="text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>" required />
							</div>

							<div class="form-group">
								<label for="account_last_name"><?php esc_html_e( 'Nom', 'scw-shop' ); ?> <span class="required">*</span></label>
								<input type="text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>" required />
							</div>

							<div class="form-group full-width">
								<label for="account_display_name"><?php esc_html_e( 'Nom d\'affichage', 'scw-shop' ); ?> <span class="required">*</span></label>
								<input type="text" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $current_user->display_name ); ?>" required />
							</div>

							<div class="form-group full-width">
								<label for="account_email"><?php esc_html_e( 'Adresse e-mail', 'scw-shop' ); ?> <span class="required">*</span></label>
								<input type="email" name="account_email" id="account_email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required />
							</div>
						</div>

						<div class="form-divider"></div>

						<h4><?php esc_html_e( 'Modifier le mot de passe', 'scw-shop' ); ?></h4>
						<p class="form-hint"><?php esc_html_e( 'Laissez vide si vous ne souhaitez pas changer votre mot de passe.', 'scw-shop' ); ?></p>

						<div class="form-grid">
							<div class="form-group full-width">
								<label for="password_current"><?php esc_html_e( 'Mot de passe actuel', 'scw-shop' ); ?></label>
								<input type="password" name="password_current" id="password_current" autocomplete="off" />
							</div>

							<div class="form-group">
								<label for="password_new"><?php esc_html_e( 'Nouveau mot de passe', 'scw-shop' ); ?></label>
								<input type="password" name="password_new" id="password_new" autocomplete="off" />
							</div>

							<div class="form-group">
								<label for="password_confirm"><?php esc_html_e( 'Confirmer le nouveau mot de passe', 'scw-shop' ); ?></label>
								<input type="password" name="password_confirm" id="password_confirm" autocomplete="off" />
							</div>
						</div>

						<div class="form-actions">
							<button type="submit" name="save_account_details" class="btn-primary">
								<?php esc_html_e( 'Enregistrer les modifications', 'scw-shop' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>

		</div>
	</div>

	<!-- MODAL: EDIT BILLING ADDRESS -->
	<div class="address-modal" id="modal-billing" style="display: none;">
		<div class="modal-overlay"></div>
		<div class="modal-content">
			<div class="modal-header">
				<h3><?php esc_html_e( 'Adresse de facturation', 'scw-shop' ); ?></h3>
				<button class="modal-close" data-modal="billing">&times;</button>
			</div>
			<div class="modal-body">
				<form id="form-billing-address" class="address-form">
					<?php wp_nonce_field( 'scw_save_address', 'address_nonce' ); ?>
					<input type="hidden" name="address_type" value="billing" />

					<div class="form-grid">
						<div class="form-group">
							<label><?php esc_html_e( 'Prénom', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="billing_first_name" value="<?php echo esc_attr( $billing_address['first_name'] ); ?>" required />
						</div>

						<div class="form-group">
							<label><?php esc_html_e( 'Nom', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="billing_last_name" value="<?php echo esc_attr( $billing_address['last_name'] ); ?>" required />
						</div>

						<div class="form-group full-width">
							<label><?php esc_html_e( 'Société', 'scw-shop' ); ?></label>
							<input type="text" name="billing_company" value="<?php echo esc_attr( $billing_address['company'] ); ?>" />
						</div>

						<div class="form-group full-width">
							<label><?php esc_html_e( 'Adresse', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="billing_address_1" value="<?php echo esc_attr( $billing_address['address_1'] ); ?>" required />
						</div>

						<div class="form-group full-width">
							<label><?php esc_html_e( 'Complément d\'adresse', 'scw-shop' ); ?></label>
							<input type="text" name="billing_address_2" value="<?php echo esc_attr( $billing_address['address_2'] ); ?>" />
						</div>

						<div class="form-group">
							<label><?php esc_html_e( 'Code postal', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="billing_postcode" value="<?php echo esc_attr( $billing_address['postcode'] ); ?>" required />
						</div>

						<div class="form-group">
							<label><?php esc_html_e( 'Ville', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="billing_city" value="<?php echo esc_attr( $billing_address['city'] ); ?>" required />
						</div>

						<div class="form-group full-width">
							<label><?php esc_html_e( 'Téléphone', 'scw-shop' ); ?></label>
							<input type="tel" name="billing_phone" value="<?php echo esc_attr( $billing_address['phone'] ); ?>" />
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn-secondary modal-close" data-modal="billing"><?php esc_html_e( 'Annuler', 'scw-shop' ); ?></button>
						<button type="submit" class="btn-primary"><?php esc_html_e( 'Enregistrer', 'scw-shop' ); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- MODAL: EDIT SHIPPING ADDRESS -->
	<div class="address-modal" id="modal-shipping" style="display: none;">
		<div class="modal-overlay"></div>
		<div class="modal-content">
			<div class="modal-header">
				<h3><?php esc_html_e( 'Adresse de livraison', 'scw-shop' ); ?></h3>
				<button class="modal-close" data-modal="shipping">&times;</button>
			</div>
			<div class="modal-body">
				<form id="form-shipping-address" class="address-form">
					<?php wp_nonce_field( 'scw_save_address', 'address_nonce' ); ?>
					<input type="hidden" name="address_type" value="shipping" />

					<div class="form-grid">
						<div class="form-group">
							<label><?php esc_html_e( 'Prénom', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="shipping_first_name" value="<?php echo esc_attr( $shipping_address['first_name'] ); ?>" required />
						</div>

						<div class="form-group">
							<label><?php esc_html_e( 'Nom', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="shipping_last_name" value="<?php echo esc_attr( $shipping_address['last_name'] ); ?>" required />
						</div>

						<div class="form-group full-width">
							<label><?php esc_html_e( 'Société', 'scw-shop' ); ?></label>
							<input type="text" name="shipping_company" value="<?php echo esc_attr( $shipping_address['company'] ); ?>" />
						</div>

						<div class="form-group full-width">
							<label><?php esc_html_e( 'Adresse', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="shipping_address_1" value="<?php echo esc_attr( $shipping_address['address_1'] ); ?>" required />
						</div>

						<div class="form-group full-width">
							<label><?php esc_html_e( 'Complément d\'adresse', 'scw-shop' ); ?></label>
							<input type="text" name="shipping_address_2" value="<?php echo esc_attr( $shipping_address['address_2'] ); ?>" />
						</div>

						<div class="form-group">
							<label><?php esc_html_e( 'Code postal', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="shipping_postcode" value="<?php echo esc_attr( $shipping_address['postcode'] ); ?>" required />
						</div>

						<div class="form-group">
							<label><?php esc_html_e( 'Ville', 'scw-shop' ); ?> <span class="required">*</span></label>
							<input type="text" name="shipping_city" value="<?php echo esc_attr( $shipping_address['city'] ); ?>" required />
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn-secondary modal-close" data-modal="shipping"><?php esc_html_e( 'Annuler', 'scw-shop' ); ?></button>
						<button type="submit" class="btn-primary"><?php esc_html_e( 'Enregistrer', 'scw-shop' ); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
