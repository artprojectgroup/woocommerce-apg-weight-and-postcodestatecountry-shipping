<?php
/**
 * Funciones administrativas, enlaces y utilidades para WC - APG Weight Shipping.
 *
 * Define variables globales, enlaces personalizados en la administración,
 * carga de hojas de estilo, integración de scripts y utilidades de depuración.
 *
 * @package WC-APG-Weight-Shipping
 * @subpackage Includes/Admin
 * @author Art Project Group
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

// Definimos las variables.
$apg_shipping = [	
	'plugin' 		=> 'WC - APG Weight Shipping', 
	'plugin_uri' 	=> 'woocommerce-apg-weight-and-postcodestatecountry-shipping', 
	'donacion' 		=> 'https://artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'https://artprojectgroup.es/tienda/soporte-tecnico',
	'plugin_url' 	=> 'https://artprojectgroup.es/plugins-para-woocommerce/wc-apg-weight-shipping', 
	'ajustes' 		=> 'admin.php?page=wc-settings&tab=shipping', 
	'puntuacion' 	=> 'https://wordpress.org/support/view/plugin-reviews/woocommerce-apg-weight-and-postcodestatecountry-shipping'
];

/**
 * Añade enlaces personalizados en la fila del plugin en la lista de plugins.
 *
 * Incluye enlaces a donación, perfil del autor, redes sociales, otros plugins, contacto y valoración.
 *
 * @param array  $enlaces  Enlaces actuales.
 * @param string $archivo  Archivo del plugin procesado.
 * @return array Enlaces modificados.
 */
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

/**
 * Añade enlaces de acceso rápido a la configuración y soporte del plugin en la página de plugins.
 *
 * @param array $enlaces Enlaces de acción actuales.
 * @return array Enlaces de acción con ajustes y soporte añadidos al principio.
 */
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

/**
 * Muestra un aviso especial en la pantalla de actualización cuando hay cambios mayores en el plugin.
 *
 * @param array    $datos_version_actual Datos de la versión instalada.
 * @param stdClass $datos_nueva_version  Datos de la nueva versión del repositorio.
 * @return void
 */
function apg_shipping_noficacion( $datos_version_actual, $datos_nueva_version ) {
	if ( isset( $datos_nueva_version->upgrade_notice ) && strlen( trim( $datos_nueva_version->upgrade_notice ) ) > 0 && (float) $datos_version_actual[ 'Version' ] < 2.0 ){
        $mensaje = '</p><div class="wc_plugin_upgrade_notice">';
		$mensaje .= __( "<h4>ALERT: 2.0 is a major update</h4>It’s important that you make backups of your <strong>WC - APG Weight Shipping</strong> current configuration and configure it again after upgrade.<br /><em>Remember, the current setting is totally incompatible with WooCommerce 2.6 and you'll lose it</em>.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
        $mensaje .= '</div><p>';
		
		echo wp_kses_post( $mensaje );
	}
}
add_action( 'in_plugin_update_message-woocommerce-apg-weight-and-postcodestatecountry-shipping/apg-shipping.php', 'apg_shipping_noficacion', 10, 2 );

/**
 * Obtiene la información pública del plugin desde la API de WordPress.org y muestra la valoración.
 *
 * Cachea la respuesta durante 24 horas.
 *
 * @param string $nombre Slug del plugin.
 * @return string HTML con las estrellas de valoración o un mensaje de error.
 */
function apg_shipping_plugin( $nombre ) {
	global $apg_shipping;

	$respuesta	= get_transient( 'apg_shipping_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . $nombre );
		set_transient( 'apg_shipping_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}

	if ( is_wp_error( $respuesta ) ) {
        // translators: %s is the plugin name.
		return '<a title="' . sprintf( __( 'Please, rate %s:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_shipping[ 'plugin' ] ) . '" href="' . $apg_shipping[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . __( 'Unknown rating', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</a>';
	}

	$codigo_respuesta = wp_remote_retrieve_response_code( $respuesta );
	if ( 200 !== $codigo_respuesta ) {
		// translators: %s is the plugin name.
		return '<a title="' . sprintf( __( 'Please, rate %s:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_shipping[ 'plugin' ] ) . '" href="' . $apg_shipping[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . __( 'Unknown rating', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</a>';
	}

	$plugin = json_decode( wp_remote_retrieve_body( $respuesta ) );

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

/**
 * Encola la hoja de estilo y el JavaScript necesarios en la administración del plugin.
 *
 * Solo carga los recursos en las páginas relevantes de ajustes de WooCommerce o plugins.
 *
 * @return void
 */
function apg_shipping_estilo() {
    $request_uri    = isset( $_SERVER[ 'REQUEST_URI' ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) ) : '';
    if ( strpos( $request_uri, 'wc-settings&tab=shipping&instance_id' ) !== false || strpos( $request_uri, 'plugins.php' ) !== false ) {
		wp_enqueue_style( 'apg_shipping_hoja_de_estilo', plugins_url( 'assets/css/style.css', DIRECCION_apg_shipping ), [], VERSION_apg_shipping ); // Carga la hoja de estilo global.
	}
    if ( strpos( $request_uri, 'wc-settings&tab=shipping' ) !== false ) {
		wp_enqueue_script( 'apg_shipping_script', plugins_url( 'assets/js/apg-shipping.js', DIRECCION_apg_shipping ), [ 'jquery' ], VERSION_apg_shipping, true );
	}
}
add_action( 'admin_enqueue_scripts', 'apg_shipping_estilo' );

/**
 * Encola y configura el JavaScript necesario para la copia rápida del debug de envío en el frontend.
 *
 * Solo activa si el modo debug del método de envío está activo y en páginas de carrito/checkout.
 *
 * @return void
 */
function apg_shipping_debug_script() {
    if ( ! ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) || ( function_exists( 'wc_is_cart_and_checkout_block_page' ) && wc_is_cart_and_checkout_block_page() )|| ( function_exists( 'wc_is_cart_and_checkout_blocks_page' ) && wc_is_cart_and_checkout_blocks_page() ) || ( function_exists( 'wc_is_cart_and_checkout_blocks_page' ) && wc_is_cart_and_checkout_blocks_page() ) ) ) {
        return;
    }

    if ( is_admin() || ! apg_shipping_debug_activo() ) {
        return;
    }

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
}
add_action( 'wp_enqueue_scripts', 'apg_shipping_debug_script' );

/**
 * Comprueba si el modo debug está activado para el método de envío seleccionado y el usuario actual.
 *
 * @return bool True si está activo, false si no.
 */
function apg_shipping_debug_activo() {
	if ( ! WC()->session || ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	$shipping_methods	= WC()->session->get( 'chosen_shipping_methods', [] );

	foreach ( $shipping_methods as $shipping_method ) {
		if ( strpos( $shipping_method, 'apg_shipping' ) !== false ) {
			$partes			= explode( ':', $shipping_method );
			$instance_id	= $partes[ 1 ] ?? '';
			if ( ! $instance_id ) {
				continue;
			}

			$settings	= get_option( 'woocommerce_apg_shipping_' . $instance_id . '_settings', [] );
			if ( isset( $settings[ 'debug' ] ) && $settings[ 'debug' ] === 'yes' ) {
				return true;
			}
		}
	}

	return false;
}
/**
 * Limpia los datos de métodos de envío en la sesión al recalcular totales si está activo el modo debug.
 *
 * @hook woocommerce_before_calculate_totals
 */
add_action( 'woocommerce_before_calculate_totals', function() {
    if ( ! apg_shipping_debug_activo() ) {
        return;
    }

	if ( WC()->session ) {
        foreach ( WC()->session->get_session_data() as $key => $value ) {
            if ( strpos( $key, 'shipping_for_package_' ) === 0 ) {
                WC()->session->__unset( $key );
            }
        }
    }
}, 99 );
