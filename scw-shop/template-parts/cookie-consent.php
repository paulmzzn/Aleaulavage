<?php
/**
 * Cookie Consent Banner
 *
 * @package SCW_Shop
 */
?>

<div class="cookie-banner" id="cookie-banner" style="display: none;">
	<div class="cookie-content">
		<div class="cookie-icon">🍪</div>
		<div class="cookie-text">
			<h3><?php esc_html_e( 'Nous respectons votre vie privée', 'scw-shop' ); ?></h3>
			<p>
				<?php esc_html_e( 'Nous utilisons des cookies pour améliorer votre expérience, analyser le trafic et sécuriser votre navigation.', 'scw-shop' ); ?>
				<a href="#"><?php esc_html_e( 'En savoir plus', 'scw-shop' ); ?></a>
			</p>
		</div>
	</div>
	<div class="cookie-actions">
		<button class="btn-cookie secondary" id="cookie-decline">
			<?php esc_html_e( 'Continuer sans accepter', 'scw-shop' ); ?>
		</button>
		<button class="btn-cookie primary" id="cookie-accept">
			<?php esc_html_e( 'Tout accepter', 'scw-shop' ); ?>
		</button>
	</div>
</div>
