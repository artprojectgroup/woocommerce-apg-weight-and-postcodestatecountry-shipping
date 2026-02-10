<?php 
/**
 * Plantilla informativa y de enlaces del plugin APG Shipping para WooCommerce.
 *
 * Muestra enlaces a donaciones, redes sociales, plugins, documentación, soporte y contacto.
 * Se utiliza como bloque auxiliar en la página de ajustes del método de envío.
 *
 * Seguridad: No debe accederse directamente. Solo debe incluirse desde el panel de administración de WooCommerce.
 *
 * Variables globales utilizadas:
 * - $apg_shipping  Array asociativo con información relevante del plugin (URL, nombre, soporte, donación, etc.).
 *
 * @package WC-APG-Weight-Shipping
 * @subpackage Admin/Templates
 * @author Art Project Group
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit; ?>
<div class="informacion">
	<!-- Fila: Donación y autor -->
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'If you enjoyed and find helpful this plugin, please make a donation:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="<?php echo esc_url( $apg_shipping[ 'donacion' ] ); ?>" target="_blank" title="<?php echo esc_attr( __( 'Make a donation by ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ); ?>APG"> <span class="genericon genericon-cart"></span> </a> </p>
		</div>
		<div class="columna">
			<p>Art Project Group:</p>
			<p> <a href="https://www.artprojectgroup.es" title="Art Project Group" target="_blank"> <strong class="artprojectgroup">APG</strong> </a> </p>
		</div>
	</div>

	<!-- Fila: Redes sociales y más plugins -->
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'Follow us:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="https://www.facebook.com/artprojectgroup" title="<?php echo esc_attr__( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://x.com/artprojectgroup" title="<?php echo esc_attr__( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>X" target="_blank"><span class="genericon genericon-x-alt"></span></a> <a href="https://es.linkedin.com/in/artprojectgroup" title="<?php echo esc_attr__( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a> </p>
		</div>
		<div class="columna">
			<p>
				<?php esc_html_e( 'More plugins:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="https://profiles.wordpress.org/artprojectgroup/" title="<?php echo esc_attr__( 'More plugins on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a> </p>
		</div>
	</div>

	<!-- Fila: Contacto y Documentación/Soporte -->
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'Contact with us:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="mailto:info@artprojectgroup.es" title="<?php echo esc_attr__( 'Contact with us by ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>e-mail"><span class="genericon genericon-mail"></span></a> </p>
		</div>
		<div class="columna">
			<p>
				<?php esc_html_e( 'Documentation and Support:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="<?php echo esc_url( $apg_shipping[ 'plugin_url' ] ); ?>" title="<?php echo esc_attr( $apg_shipping[ 'plugin' ] ); ?>"><span class="genericon genericon-book"></span></a> <a href="<?php echo esc_url( $apg_shipping[ 'soporte' ] ); ?>" title="<?php esc_attr_e( 'Support', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ); ?>"><span class="genericon genericon-cog"></span></a> </p>
		</div>
	</div>

	<!-- Fila final: Valoración -->
	<div class="fila final">
		<div class="columna">
			<p>
				<?php
				// translators: %s is the plugin name.
				echo esc_html( sprintf( __( 'Please, rate %s:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_shipping[ 'plugin' ] ) );
				?>
			</p>
			<?php echo wp_kses_post( apg_shipping_plugin( $apg_shipping[ 'plugin_uri' ] ) ); ?> </div>
		<div class="columna final"></div>
	</div>
</div>
