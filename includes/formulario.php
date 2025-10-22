<?php 
/**
 * Vista del panel de ajustes del método de envío APG Shipping para WooCommerce.
 *
 * Este archivo genera la interfaz de administración para configurar el método de envío, mostrando el título, la descripción,
 * un cuadro de información, la cabecera gráfica y el formulario de opciones personalizadas.
 *
 * Seguridad: No debe accederse directamente. Solo se debe cargar dentro del contexto de WooCommerce Shipping.
 *
 * Variables globales utilizadas:
 * - $apg_shipping  Array con información básica del plugin (nombre, URL, etc.).
 * - $this          Instancia del método de envío (WC_apg_shipping).
 *
 * @package WC-APG-Weight-Shipping
 * @subpackage Admin/Templates
 * @author Art Project Group
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

global $apg_shipping;
?>
<h3><a href="<?php echo esc_url( $apg_shipping[ 'plugin_url' ] ?? '' ); ?>" title="Art Project Group"><?php echo esc_html( $apg_shipping[ 'plugin' ] ?? '' ); ?></a></h3>
<p> <?php echo wp_kses_post( $this->method_description ); ?> </p>
<?php include( 'cuadro-informacion.php' ); ?>
<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin image ?>
<div class="cabecera"> <a href="<?php echo esc_url( $apg_shipping[ 'plugin_url' ] ); ?>" title="<?php echo esc_attr( $apg_shipping[ 'plugin' ] ); ?>" target="_blank"><img src="<?php echo esc_url( plugins_url( 'assets/images/cabecera.jpg', DIRECCION_apg_shipping ) ); ?>" class="imagen" alt="<?php echo esc_attr( $apg_shipping[ 'plugin' ] ); ?>" /></a> </div>
<table class="form-table apg-table">
	<?php $this->generate_settings_html( $this->get_instance_form_fields() ); ?>
</table>
