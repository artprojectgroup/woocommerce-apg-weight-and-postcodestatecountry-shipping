<?php global $apg_shipping; ?>

<h3><a href="<?php echo $apg_shipping[ 'plugin_url' ]; ?>" title="Art Project Group"><?php echo $apg_shipping[ 'plugin' ]; ?></a></h3>
<p> <?php echo $this->method_description; ?> </p>
<?php include( 'cuadro-informacion.php' ); ?>
<div class="cabecera"> <a href="<?php echo $apg_shipping[ 'plugin_url' ]; ?>" title="<?php echo $apg_shipping[ 'plugin' ]; ?>" target="_blank"><img src="<?php echo plugins_url( 'assets/images/cabecera.jpg', DIRECCION_apg_shipping ); ?>" class="imagen" alt="<?php echo $apg_shipping[ 'plugin' ]; ?>" /></a> </div>
<table class="form-table apg-table">
  <?php $this->generate_settings_html( $this->get_instance_form_fields() ); ?>
</table>
<!--/.form-table--> 
<!--
<script>
jQuery( document ).ready( function ( $ ) {
    $( 'tr:has( #woocommerce_apg_shipping_prueba2 )' ).hide();
    $( '#woocommerce_apg_shipping_prueba' ).on( 'change', function () {
        if ( $(this).children("option:selected").val() == 'si' ) {
            $( 'tr:has( #woocommerce_apg_shipping_prueba2 )' ).toggle();
        }
    } );
} );    
</script>
-->