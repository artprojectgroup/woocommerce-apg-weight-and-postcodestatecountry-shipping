<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;


//Muestra el icono
function apg_shipping_icono( $etiqueta, $metodo ) {
	$apg_shipping_settings	= maybe_unserialize( get_option( 'woocommerce_apg_shipping_' . $metodo->instance_id .'_settings' ) );
	
	//¿Mostramos el icono?
	if ( !empty( $apg_shipping_settings[ 'icono' ] ) && @getimagesize( $apg_shipping_settings[ 'icono' ] ) && $apg_shipping_settings[ 'muestra_icono' ] != 'no' ) {
		$tamano = @getimagesize( $apg_shipping_settings[ 'icono' ] );
		$imagen	= '<img class="apg_shipping_icon" src="' . $apg_shipping_settings[ 'icono' ] . '" witdh="' . $tamano[ 0 ] . '" height="' . $tamano[ 1 ] . '" />';
		if ( $apg_shipping_settings[ 'muestra_icono' ] == 'delante' ) {
			$etiqueta = $imagen . ' ' . $etiqueta; //Icono delante
		} else if ( $apg_shipping_settings[ 'muestra_icono' ] == 'detras' ) {
			$etiqueta = $metodo->label . ' ' . $imagen . ':' . wc_price( $metodo->cost ); //Icono detrás
		} else {
			$etiqueta = $imagen . ':' . wc_price( $metodo->cost ); //Sólo icono
		}
	}
	
	//Tiempo de entrega
	if ( !empty( $apg_shipping_settings[ 'entrega' ] ) ) {
		$etiqueta .= '<br /><small class="apg_shipping_delivery">' . sprintf( __( "Estimated delivery time: %s", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_shipping_settings[ 'entrega' ] ) . '</small>';
	}
	
	return $etiqueta;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'apg_shipping_icono', 10, 2 );
