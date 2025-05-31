<?php global $apg_shipping; ?>

<h3><a href="<?php echo esc_url( $apg_shipping[ 'plugin_url' ] ?? '' ); ?>" title="Art Project Group"><?php echo esc_html( $apg_shipping[ 'plugin' ] ?? '' ); ?></a></h3>
<p> <?php echo esc_html( $this->method_description ); ?> </p>
<?php include( 'cuadro-informacion.php' ); ?>
<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin image ?>
<div class="cabecera"> <a href="<?php echo esc_url( $apg_shipping[ 'plugin_url' ] ); ?>" title="<?php echo esc_attr( $apg_shipping[ 'plugin' ] ); ?>" target="_blank"><img src="<?php echo esc_url( plugins_url( 'assets/images/cabecera.jpg', DIRECCION_apg_shipping ) ); ?>" class="imagen" alt="<?php echo esc_attr( $apg_shipping[ 'plugin' ] ); ?>" /></a> </div>
<table class="form-table apg-table">
	<?php $this->generate_settings_html( $this->get_instance_form_fields() ); ?>
</table>
