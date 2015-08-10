<?php global $apg_shipping; ?>

<h3><a href="<?php echo $apg_shipping['plugin_url']; ?>" title="Art Project Group"><?php echo $apg_shipping['plugin']; ?></a></h3>
<p>
  <?php _e( 'Lets you calculate shipping cost based on Postcode/State/Country and weight of the cart. Lets you set an unlimited weight bands on per postcode/state/country basis and group the groups that that share same delivery cost/bands.', 'apg_shipping' ); ?>
</p>
<?php include( 'cuadro-informacion.php' ); ?>
<div class="cabecera"> <a href="<?php echo $apg_shipping['plugin_url']; ?>" title="<?php echo $apg_shipping['plugin']; ?>" target="_blank"><img src="<?php echo plugins_url( '../assets/images/cabecera.jpg', __FILE__ ); ?>" class="imagen" alt="<?php echo $apg_shipping['plugin']; ?>" /></a> </div>
<table class="form-table apg-table">
  <?php $this->generate_settings_html(); ?>
</table>
<!--/.form-table--> 
<script type="text/javascript">
jQuery( document ).ready( function( $ ) {	
<?php 
if ( get_option( 'woocommerce_allowed_countries' ) == 'all' ) { 
	$global_countries = 'woocommerce_' . $this->id . '_global_countries';
	$impuestos = 'woocommerce_' . $this->id . '_Tax_C' . ( $this->country_group_no + 1 );
?>	
	var control_global_countries = function( capa ) {
    	if ( capa.is( ':checked' ) ) {
			$( '#<?php echo $impuestos; ?>' ).closest( 'tr' ).show();
		} else {
			$( '#<?php echo $impuestos; ?>' ).closest( 'tr' ).hide();
		}
	};

	control_global_countries( $( '#<?php echo $global_countries; ?>' ) );

	$( '#<?php echo $global_countries; ?>' ).on( 'change', function() {
		control_global_countries( $( this ) );
	});
<?php 
} 
?>
});
</script> 
