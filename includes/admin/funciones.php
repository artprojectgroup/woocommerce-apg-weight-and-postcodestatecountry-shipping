<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Muestra el icono
function apg_shipping_icono( $etiqueta, $metodo ) {
    //Variables
    $instance_id = $metodo->instance_id;
    $cache_key   = "apg_shipping_icono_{$instance_id}";

    //Usa etiqueta en caché
    $etiqueta   = get_transient( $cache_key );
    if ( false !== $etiqueta ) {
        return $etiqueta;
    }

    //Obtiene configuración del método de envío
    $opcion_bruta           = get_option( "woocommerce_apg_shipping_{$instance_id}_settings" );
    $apg_shipping_settings  = is_array( $opcion_bruta ) ? $opcion_bruta : maybe_unserialize( $opcion_bruta );
    	
	//¿Mostramos el icono?
    $icon_url       = $apg_shipping_settings[ 'icono' ] ?? '';
    $mostrar_icono  = $apg_shipping_settings[ 'muestra_icono' ] ?? '';
    if ( ! empty( $icon_url ) && filter_var( $icon_url, FILTER_VALIDATE_URL ) && $mostrar_icono !== 'no' ) {
        //Añade el precio
        $impuestos  = ( version_compare( WC_VERSION, '4.4', '<' ) ) ? WC()->cart->tax_display_cart : WC()->cart->get_tax_price_display_mode();
        if ( $impuestos == 'excl' ) {
            $precio = ( $metodo->get_shipping_tax() > 0 && WC()->cart->prices_include_tax ) ? wc_price( $metodo->cost ) . ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>' : wc_price( $metodo->cost );
        } else {
            $precio = ( $metodo->get_shipping_tax() > 0 && ! WC()->cart->prices_include_tax ) ? wc_price( $metodo->cost + $metodo->get_shipping_tax() ) . ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>' : wc_price( $metodo->cost + $metodo->get_shipping_tax() );
        }

        
        //Procesa imagen y obtiene su tamaño
        require_once ABSPATH . 'wp-admin/includes/file.php'; // Asegura que download_url() existe
        $ancho  = null;
        $alto   = null;
        $icon   = download_url( $icon_url );
        if ( ! is_wp_error( $icon ) ) {
            $tamano = wp_getimagesize( $icon );
            if ( is_array( $tamano ) ) {
                list( $ancho, $alto )   = $tamano;
            }
            wp_delete_file( $icon );
        }

        //Construcción de la etiqueta <img>
        // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin image
        $imagen = '<img class="apg_shipping_icon apg_icon" src="' . esc_url( $icon_url ) . '"';
        $imagen .= $ancho ? ' width="' . intval( $ancho ) . '"' : '';
        $imagen .= $alto  ? ' height="' . intval( $alto ) . '"' : '';
        $imagen .= ' style="display:inline;" />';

        $titulo  = apply_filters( 'apg_shipping_label', $metodo->label );
        if ( $mostrar_icono === 'delante' ) {
            $etiqueta   = $imagen . ' ' . $titulo . ':' . $precio; //Icono delante
        } else if ( $mostrar_icono === 'detras' ) {
            $etiqueta   = $titulo . ' ' . $imagen . ':' . $precio; //Icono detrás
        } else {
            $etiqueta   = $imagen . ':' . $precio; //Sólo icono
        }
	} else {
        $etiqueta       = apply_filters( 'apg_shipping_label', $etiqueta ); //Sin icono
    }
	
	//Tiempo de entrega
    $entrega    = $apg_shipping_settings[ 'entrega' ];
	if ( ! empty( $entrega ) ) {
        // translators: %s is the estimated delivery time (e.g., "24-48 hours").
        $etiqueta   .= ( apply_filters( 'apg_shipping_delivery', true ) ) ? '<br /><small class="apg_shipping_delivery">' . sprintf( __( "Estimated delivery time: %s", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $entrega ) . '</small>' : '<br /><small class="apg_shipping_delivery">' . $entrega . '</small>';
    }

    //Guarda en caché durante una hora
    set_transient( $cache_key, $etiqueta, HOUR_IN_SECONDS );
    
    // Inyecta los datos como <span data-apg="..."> para bloques
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST && ( is_cart() || is_checkout() ) ) {
        $json_data = base64_encode( wp_json_encode( [
            'icono'     => $icon_url,
            'muestra'   => $mostrar_icono,
            'entrega'   => $entrega,
        ] ) );

        $etiqueta .= '<span class="apg_shipping_data" style="display:none" data-apg="' . esc_attr( $json_data ) . '"></span>';
    }

    return $etiqueta;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'apg_shipping_icono', PHP_INT_MAX, 2 );
	
//Añade clases necesarias para nuevos gastos de envío
function apg_shipping_clases( $metodos ) {
    $metodos[ 'apg_shipping' ]  = 'WC_apg_shipping';

    return $metodos;
}
add_filter( 'woocommerce_shipping_methods', 'apg_shipping_clases', 0 );

//Filtra los medios de pago
function apg_shipping_filtra_medios_de_pago( $medios ) {
    $apg_shipping_settings  = apg_shipping_dame_configuracion();

    if ( ! empty( $apg_shipping_settings[ 'pago' ] ) && $apg_shipping_settings[ 'pago' ][ 0 ] != 'todos' ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( isset( $_POST[ 'payment_method' ] ) && empty( $medios ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $medios = [ sanitize_text_field( wp_unslash( $_POST[ 'payment_method' ] ) ) ];
        }
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
	
	$medios_de_pago    = get_transient( 'apg_shipping_payment_gateways' ); //Obtiene los medios de pago
    if ( false === $medios_de_pago ) {
        $medios_de_pago = WC()->payment_gateways->payment_gateways();
        set_transient( 'apg_shipping_payment_gateways', $medios_de_pago, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
    }
    
    $zonas_de_envio    = get_transient( 'apg_shipping_zonas_de_envio' ); //Obtiene las zonas de envío
    if ( false === $zonas_de_envio ) {
        $zonas_de_envio = WC_Shipping_Zones::get_zones();
		set_transient( 'apg_shipping_zonas_de_envio', $zonas_de_envio, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
	}
}
$request_uri    = isset( $_SERVER[ 'REQUEST_URI' ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) ) : '';
if ( strpos( $request_uri, 'wc-settings&tab=shipping' ) !== false ) {
    add_action( 'admin_init', 'apg_shipping_toma_de_datos' );
}

//Gestiona los gastos de envío
function apg_shipping_gestiona_envios( $envios ) {
    $apg_shipping_settings  = apg_shipping_dame_configuracion();

    if ( isset( $apg_shipping_settings[ 'envio' ] ) && is_array( $apg_shipping_settings[ 'envio' ] ) && !empty( $apg_shipping_settings[ 'envio' ] ) ) {
        if ( isset( $envios[ 0 ][ 'rates' ] ) ) {
            foreach ( $envios[ 0 ][ 'rates' ] as $clave => $envio ) {
                $instance_id = $envio->instance_id;

                foreach ( $apg_shipping_settings[ 'envio' ] as $metodo ) {
                    if ( $metodo !== 'todos' ) {
                        if ( $metodo === 'ninguno' || ! in_array( $instance_id, $apg_shipping_settings[ 'envio' ], true ) ) {
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

//Devuelve la configuración del método de envío
function apg_shipping_dame_configuracion() {
    $id = [];
    //Corrección propuesta por @rabbitshavefangs en https://wordpress.org/support/topic/problem-in-line-50-of-functiones-php/
    if ( isset( WC()->session ) && is_object( WC()->session ) ) {
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        if ( ! empty( $chosen_shipping_methods ) && isset( $chosen_shipping_methods[ 0 ] ) ) {
            $id = explode( ":", $chosen_shipping_methods[ 0 ] );
        }
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    } elseif ( isset( $_POST[ 'shipping_method' ] ) && isset( $_POST[ 'shipping_method' ][ 0 ] ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $id = explode( ":", sanitize_text_field( wp_unslash( $_POST[ 'shipping_method' ][ 0 ] ) ?? '' ) );
    } else {
        return;
    }
    
    return ( isset( $id[ 1 ] ) ) ? maybe_unserialize( get_option( 'woocommerce_apg_shipping_' . $id[ 1 ] . '_settings' ) ) : [];
}

//Limpia la caché de los iconos
function apg_shipping_borra_cache_icono_dinamico( $option, $old_value, $value ) {
	if ( strpos( $option, 'woocommerce_apg_shipping_' ) === 0 && strpos( $option, '_settings' ) !== false ) {
		//Extrae el instance_id desde la opción
		if ( preg_match( '/woocommerce_apg_shipping_(\d+)_settings/', $option, $matches ) ) {
			$instance_id = $matches[ 1 ];
			$cache_key   = "apg_shipping_icono_{$instance_id}";
			delete_transient( $cache_key );
		}
	}
}
add_action( 'updated_option', 'apg_shipping_borra_cache_icono_dinamico', 10, 3 );

//Limpia la caché de taxonomías
function apg_shipping_borra_cache_taxonomias_producto( $term_id, $tt_id, $taxonomy ) {
	if ( in_array( $taxonomy, [ 'product_cat', 'product_tag' ], true ) ) {
		delete_transient( 'apg_shipping_' . $taxonomy );
	}
}
add_action( 'edited_term', 'apg_shipping_borra_cache_taxonomias_producto', 10, 3 );
add_action( 'delete_term', 'apg_shipping_borra_cache_taxonomias_producto', 10, 3 );

//Limpia la caché de clases de envío
function apg_shipping_borra_cache_clases_envio() {
	delete_transient( 'apg_shipping_clases_envio' );
}
add_action( 'woocommerce_shipping_classes_save_class', 'apg_shipping_borra_cache_clases_envio' );
add_action( 'woocommerce_shipping_classes_delete_class', 'apg_shipping_borra_cache_clases_envio' );

//Limpia la caché de roles
function apg_shipping_borra_cache_roles_usuario() {
	delete_transient( 'apg_shipping_roles_usuario' );
}
add_action( 'profile_update', 'apg_shipping_borra_cache_roles_usuario' );
add_action( 'user_register', 'apg_shipping_borra_cache_roles_usuario' );

//Limpia la caché de métodos de pago
function apg_shipping_borra_cache_metodos_pago() {
	delete_transient( 'apg_shipping_payment_gateways' );
	delete_transient( 'apg_shipping_metodos_pago' );
}
add_action( 'update_option_woocommerce_gateway_order', 'apg_shipping_borra_cache_metodos_pago' );
add_action( 'woocommerce_update_options_payment_gateways', 'apg_shipping_borra_cache_metodos_pago' );

//Limpia la caché de los zonas de envío
function apg_shipping_borra_cache_zonas_envio() {
	delete_transient( 'apg_shipping_zonas_de_envio' );
}
add_action( 'woocommerce_update_options_shipping', 'apg_shipping_borra_cache_zonas_envio' );

//Limpia la caché de métodos de envío al guardar opciones
function apg_shipping_borra_cache_metodos_envio( $instance_id ) {
	delete_transient( 'apg_shipping_metodos_envio_' . absint( $instance_id ) );
}
add_action( 'woocommerce_update_shipping_method', 'apg_shipping_borra_cache_metodos_envio' );

//Limpia la caché de atributos
function apg_shipping_borra_cache_atributos() {
	delete_transient( 'apg_shipping_atributos' );
}
add_action( 'woocommerce_attribute_added', 'apg_shipping_borra_cache_atributos', 10 );
add_action( 'woocommerce_attribute_updated', 'apg_shipping_borra_cache_atributos', 10 );
add_action( 'woocommerce_attribute_deleted', 'apg_shipping_borra_cache_atributos', 10 );
