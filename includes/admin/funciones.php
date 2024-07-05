<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Muestra el icono
function apg_shipping_icono( $etiqueta, $metodo ) {
	$apg_shipping_settings	= maybe_unserialize( get_option( 'woocommerce_apg_shipping_' . $metodo->instance_id .'_settings' ) );
	
	//¿Mostramos el icono?
	if ( ! empty( $apg_shipping_settings[ 'icono' ] ) && @getimagesize( $apg_shipping_settings[ 'icono' ] ) && $apg_shipping_settings[ 'muestra_icono' ] != 'no' ) {
        $impuestos  = ( version_compare( WC_VERSION, '4.4', '<' ) ) ? WC()->cart->tax_display_cart : WC()->cart->get_tax_price_display_mode();
        if ( $impuestos == 'excl' ) {
            $precio = ( $metodo->get_shipping_tax() > 0 && WC()->cart->prices_include_tax ) ? wc_price( $metodo->cost ) . ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>' : wc_price( $metodo->cost );
        } else {
            $precio = ( $metodo->get_shipping_tax() > 0 && ! WC()->cart->prices_include_tax ) ? wc_price( $metodo->cost + $metodo->get_shipping_tax() ) . ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>' : wc_price( $metodo->cost + $metodo->get_shipping_tax() );
        }
        $tamano     = @getimagesize( $apg_shipping_settings[ 'icono' ] );
        $imagen     = '<img class="apg_shipping_icon" src="' . $apg_shipping_settings[ 'icono' ] . '" witdh="' . $tamano[ 0 ] . '" height="' . $tamano[ 1 ] . '" />';
		if ( $apg_shipping_settings[ 'muestra_icono' ] == 'delante' ) {
            $etiqueta   = $imagen . ' ' . apply_filters( 'apg_shipping_label', $etiqueta ); //Icono delante
		} else if ( $apg_shipping_settings[ 'muestra_icono' ] == 'detras' ) {
            $etiqueta   = apply_filters( 'apg_shipping_label', $metodo->label ) . ' ' . $imagen . ':' . $precio; //Icono detrás
		} else {
            $etiqueta   = $imagen . ':' . $precio; //Sólo icono
		}
	} else {
        $etiqueta       = apply_filters( 'apg_shipping_label', $etiqueta ); //Sin icono
    }
	
	//Tiempo de entrega
	if ( ! empty( $apg_shipping_settings[ 'entrega' ] ) ) {
        $etiqueta   .= ( apply_filters( 'apg_shipping_delivery', true ) ) ? '<br /><small class="apg_shipping_delivery">' . sprintf( __( "Estimated delivery time: %s", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_shipping_settings[ 'entrega' ] ) . '</small>' : '<br /><small class="apg_shipping_delivery">' . $apg_shipping_settings[ 'entrega' ] . '</small>';
    }
	
	return $etiqueta;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'apg_shipping_icono', 10, 2 );
	
//Añade clases necesarias para nuevos gastos de envío
function apg_shipping_clases( $metodos ) {
    $metodos[ 'apg_shipping' ]  = 'WC_apg_shipping';

    return $metodos;
}
add_filter( 'woocommerce_shipping_methods', 'apg_shipping_clases', 0 );

//Filtra los medios de pago
function apg_shipping_filtra_medios_de_pago( $medios ) {
    if ( isset( WC()->session->chosen_shipping_methods ) ) {
        $id = explode( ":", WC()->session->chosen_shipping_methods[ 0 ] );
    } else if ( isset( $_POST[ 'shipping_method' ][ 0 ] ) ) {
        $id = explode( ":", $_POST[ 'shipping_method' ][ 0 ] );
    }
    if ( ! isset( $id[ 1 ] ) ) {
        return $medios;
    }
    $apg_shipping_settings	= maybe_unserialize( get_option( 'woocommerce_apg_shipping_' . $id[ 1 ] . '_settings' ) );

    if ( isset( $_POST[ 'payment_method' ] ) && ! $medios ) {
        $medios = $_POST[ 'payment_method' ];
    }

    if ( ! empty( $apg_shipping_settings[ 'pago' ] ) && $apg_shipping_settings[ 'pago' ][ 0 ] != 'todos' ) {
        foreach ( $medios as $nombre => $medio ) {
            if ( is_array( $apg_shipping_settings[ 'pago' ] ) ) {
                if ( ! in_array( $nombre, $apg_shipping_settings[ 'pago' ] ) ) {
                    unset( $medios[ $nombre ] );
                }
            } else { 
                if ( $nombre != $apg_shipping_settings[ 'pago' ] ) {
                    unset( $medios[ $nombre ] );
                }
            }
        }
    }

    return $medios;
}
add_filter( 'woocommerce_available_payment_gateways', 'apg_shipping_filtra_medios_de_pago' );

//Actualiza los medios de pago y las zonas de envío
function apg_shipping_toma_de_datos() {
	global $medios_de_pago, $zonas_de_envio;
	
	$medios_de_pago    = WC()->payment_gateways->payment_gateways(); //Guardamos los medios de pago
    $zonas_de_envio    = WC_Shipping_Zones::get_zones(); //Guardamos las zonas de envío
}
if ( strpos( $_SERVER[ 'REQUEST_URI' ], 'wc-settings&tab=shipping' ) !== false ) {
    add_action( 'admin_init', 'apg_shipping_toma_de_datos' );
}

//Gestiona los gastos de envío
function apg_shipping_gestiona_envios( $envios ) {
    if ( isset( WC()->session->chosen_shipping_methods ) ) {
        $id = explode( ":", WC()->session->chosen_shipping_methods[ 0 ] );
    } else if ( isset( $_POST[ 'shipping_method' ][ 0 ] ) ) {
        $id = explode( ":", $_POST[ 'shipping_method' ][ 0 ] );
    }
    if ( ! isset( $id[ 1 ] ) ) {
        return $envios;
    }
    $apg_shipping_settings  = maybe_unserialize( get_option( 'woocommerce_apg_shipping_' . $id[ 1 ] . '_settings' ) );
    
    if ( isset( $apg_shipping_settings[ 'envio' ] ) && ! empty( $apg_shipping_settings[ 'envio' ] ) ) {
        if ( isset( $envios[ 0 ][ 'rates' ] ) ) {
            foreach ( $envios[ 0 ][ 'rates' ] as $clave => $envio ) {
                foreach( $apg_shipping_settings[ 'envio' ] as $metodo ) {
                    if ( $metodo != 'todos' ) {
                        if ( ( $metodo == 'ninguno' && $id[ 1 ] != $envio->instance_id ) || ( ! in_array( $envio->instance_id, $apg_shipping_settings[ 'envio' ] ) && $id[ 1 ] != $envio->instance_id ) ) {
                            unset( $envios[ 0 ][ 'rates' ][ $clave ] );
                        }
                    }
                }
            }
        }
    }

    return $envios;
}
add_filter( 'woocommerce_shipping_packages', 'apg_shipping_gestiona_envios', 20, 1 );
add_filter( 'woocommerce_cart_shipping_packages', 'apg_shipping_gestiona_envios', 20, 1 );
