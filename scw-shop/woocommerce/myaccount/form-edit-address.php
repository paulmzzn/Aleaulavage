<?php
/**
 * Edit address form
 *
 * @package SCW_Shop
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? __( 'Adresse de facturation', 'woocommerce' ) : __( 'Adresse de livraison', 'woocommerce' );

do_action( 'woocommerce_before_edit_account_address_form' ); ?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else : ?>

	<div class="edit-address-container">
		<!-- Back button -->
		<div class="edit-address-header">
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>" class="back-link">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<line x1="19" y1="12" x2="5" y2="12"></line>
					<polyline points="12 19 5 12 12 5"></polyline>
				</svg>
				<?php esc_html_e( 'Retour aux adresses', 'scw-shop' ); ?>
			</a>
			<h1><?php echo esc_html( $page_title ); ?></h1>
		</div>

		<div class="card edit-address-card">
			<form method="post">

				<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

				<div class="woocommerce-address-fields">
					<div class="form-grid">
						<?php foreach ( $address as $key => $field ) : ?>
							<?php
							// Determine if field should be full width
							$full_width_fields = array( 'address_1', 'address_2', 'postcode', 'city', 'country', 'state' );
							$field_class = in_array( $key, $full_width_fields ) ? 'form-group full-width' : 'form-group';
							?>
							<div class="<?php echo esc_attr( $field_class ); ?>">
								<?php woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

				<div class="form-actions">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn-secondary">
						<?php esc_html_e( 'Annuler', 'scw-shop' ); ?>
					</a>
					<button type="submit" class="btn-primary" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>">
						<?php esc_html_e( 'Enregistrer l\'adresse', 'scw-shop' ); ?>
					</button>
				</div>

				<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
				<input type="hidden" name="action" value="edit_address" />

			</form>
		</div>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
