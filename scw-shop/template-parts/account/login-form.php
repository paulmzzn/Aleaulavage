<?php
/**
 * Login Form for Guests
 *
 * @package SCW_Shop
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="profile-container guest">
	<div class="login-card">
		<div class="login-header">
			<h2>Connexion</h2>
			<p>Accédez à vos tarifs professionnels</p>
		</div>

		<form class="login-form" method="post" action="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<div class="form-group">
				<label for="username">Email</label>
				<input type="text" id="username" name="username" placeholder="exemple@societe.com" required />
			</div>

			<div class="form-group">
				<label for="password">Mot de passe</label>
				<input type="password" id="password" name="password" placeholder="••••••••" required />
			</div>

			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

			<button type="submit" name="login" class="btn-primary full-width">Se connecter</button>

			<div class="login-footer">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Mot de passe oublié ?</a>
				<span style="margin-top: 1rem;">
					Vous êtes un professionnel ?
					<a href="<?php echo esc_url( home_url( '/register' ) ); ?>">Demander l'ouverture d'un compte</a>
				</span>
			</div>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>
	</div>
</div>
