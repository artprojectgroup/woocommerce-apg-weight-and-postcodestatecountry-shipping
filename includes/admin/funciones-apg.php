<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Definimos las variables
$apg_shipping = [	
	'plugin' 		=> 'WC - APG Weight Shipping', 
	'plugin_uri' 	=> 'woocommerce-apg-weight-and-postcodestatecountry-shipping', 
	'donacion' 		=> 'https://artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'https://artprojectgroup.es/tienda/soporte-tecnico',
	'plugin_url' 	=> 'https://artprojectgroup.es/plugins-para-woocommerce/wc-apg-weight-shipping', 
	'ajustes' 		=> 'admin.php?page=wc-settings&tab=shipping', 
	'puntuacion' 	=> 'https://wordpress.org/support/view/plugin-reviews/woocommerce-apg-weight-and-postcodestatecountry-shipping'
];
$medios_de_pago = [];
$zonas_de_envio = [];

//Enlaces adicionales personalizados
function apg_shipping_enlaces( $enlaces, $archivo ) {
	global $apg_shipping;

	if ( $archivo == DIRECCION_apg_shipping ) {
		$plugin = apg_shipping_plugin( $apg_shipping[ 'plugin_uri' ] );
		$enlaces[] = '<a href="' . $apg_shipping[ 'donacion' ] . '" target="_blank" title="' . __( 'Make a donation by ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[] = '<a href="'. $apg_shipping[ 'plugin_url' ] . '" target="_blank" title="' . $apg_shipping[ 'plugin' ] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[] = '<a href="https://www.facebook.com/artprojectgroup" title="' . __( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://es.linkedin.com/in/artprojectgroup" title="' . __( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[] = '<a href="https://profiles.wordpress.org/artprojectgroup/" title="' . __( 'More plugins on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[] = '<a href="mailto:info@artprojectgroup.es" title="' . __( 'Contact with us by ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'e-mail"><span class="genericon genericon-mail"></span></a>';
		$enlaces[] = apg_shipping_plugin( $apg_shipping[ 'plugin_uri' ] );
	}
	
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'apg_shipping_enlaces', 10, 2 );

//Añade el botón de configuración
function apg_shipping_enlace_de_ajustes( $enlaces ) { 
	global $apg_shipping;

	$enlaces_de_ajustes = [
		'<a href="' . $apg_shipping[ 'ajustes' ] . '" title="' . __( 'Settings of ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . $apg_shipping[ 'plugin' ] .'">' . __( 'Settings', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</a>', 
		'<a href="' . $apg_shipping[ 'soporte' ] . '" title="' . __( 'Support of ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . $apg_shipping[ 'plugin' ] .'">' . __( 'Support', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</a>'
	];
	foreach ( $enlaces_de_ajustes as $enlace_de_ajustes ) {
		array_unshift( $enlaces, $enlace_de_ajustes );
	}
	
	return $enlaces; 
}
$plugin = DIRECCION_apg_shipping; 
add_filter( "plugin_action_links_$plugin", 'apg_shipping_enlace_de_ajustes' );

//Añade notificación de actualización
function apg_shipping_noficacion( $datos_version_actual, $datos_nueva_version ) {
	if ( isset( $datos_nueva_version->upgrade_notice ) && strlen( trim( $datos_nueva_version->upgrade_notice ) ) > 0 && (float) $datos_version_actual[ 'Version' ] < 2.0 ){
        $mensaje = '</p><div class="wc_plugin_upgrade_notice">';
		$mensaje .= __( "<h4>ALERT: 2.0 is a major update</h4>It’s important that you make backups of your <strong>WC - APG Weight Shipping</strong> current configuration and configure it again after upgrade.<br /><em>Remember, the current setting is totally incompatible with WooCommerce 2.6 and you'll lose it</em>.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
        $mensaje .= '</div><p>';
		
		echo wp_kses_post( $mensaje );
	}
}
add_action( 'in_plugin_update_message-woocommerce-apg-weight-and-postcodestatecountry-shipping/apg-shipping.php', 'apg_shipping_noficacion', 10, 2 );

//Obtiene toda la información sobre el plugin
function apg_shipping_plugin( $nombre ) {
	global $apg_shipping;
	
	$respuesta	= get_transient( 'apg_shipping_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . $nombre  );
		set_transient( 'apg_shipping_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( ! is_wp_error( $respuesta ) ) {
		$plugin = json_decode( wp_remote_retrieve_body( $respuesta ) );
	} else {
        // translators: %s is the plugin name.
	   return '<a title="' . sprintf( __( 'Please, rate %s:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_shipping[ 'plugin' ] ) . '" href="' . $apg_shipping[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . __( 'Unknown rating', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</a>';
	}

    $rating = [
	   'rating'		=> $plugin->rating,
	   'type'		=> 'percent',
	   'number'		=> $plugin->num_ratings,
	];
	ob_start();
	wp_star_rating( $rating );
	$estrellas = ob_get_contents();
	ob_end_clean();

    // translators: %s is the plugin name.
	return '<a title="' . sprintf( __( 'Please, rate %s:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_shipping[ 'plugin' ] ) . '" href="' . $apg_shipping[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

//Hoja de estilo y JavaScript
function apg_shipping_estilo() {
    $request_uri    = isset( $_SERVER[ 'REQUEST_URI' ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) ) : '';
    if ( strpos( $request_uri, 'wc-settings&tab=shipping&instance_id' ) !== false || strpos( $request_uri, 'plugins.php' ) !== false ) {
		wp_enqueue_style( 'apg_shipping_hoja_de_estilo', plugins_url( 'assets/css/style.css', DIRECCION_apg_shipping ), [], VERSION_apg_shipping ); //Carga la hoja de estilo global
	}
    if ( strpos( $request_uri, 'wc-settings&tab=shipping' ) !== false ) {
		wp_enqueue_script( 'apg_shipping_script', plugins_url( 'assets/js/apg-shipping.js', DIRECCION_apg_shipping ), [ 'jquery' ], VERSION_apg_shipping, true );
	}
}
add_action( 'admin_enqueue_scripts', 'apg_shipping_estilo' );

//JavaScript para depuración
function apg_shipping_debug_script() {
    if ( is_admin() || ! class_exists( 'WC_Session_Handler' ) || ! WC()->session ) {
        return;
    }

    // Busca cualquier instancia de debug activa
    foreach ( WC()->session->get_session_data() as $key => $val ) {
        if ( strpos( $key, 'apg_shipping_debug_' ) === 0 && $val ) {
            wp_register_script( 'apg-shipping-debug', '', [], VERSION_apg_shipping, true );
            wp_enqueue_script( 'apg-shipping-debug' );

            wp_add_inline_script( 'apg-shipping-debug', "
            document.addEventListener('DOMContentLoaded', function () {
                const btn = document.getElementById('apg-copy-debug-button');
                const debugWrapper = document.getElementById('apg-shipping-debug-wrapper');
                if (btn && debugWrapper) {
                    btn.addEventListener('click', function () {
                        const range = document.createRange();
                        range.selectNodeContents(debugWrapper);
                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                        try {
                            document.execCommand('copy');
                            alert('" . esc_js( __( 'Debug text copied to clipboard.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ) . "');
                        } catch (err) {
                            alert('" . esc_js( __( 'Failed to copy debug text.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ) . "');
                        }
                        selection.removeAllRanges();
                    });
                }
            });
            " );
            break;
        }
    }
}
add_action( 'wp_enqueue_scripts', 'apg_shipping_debug_script' );
