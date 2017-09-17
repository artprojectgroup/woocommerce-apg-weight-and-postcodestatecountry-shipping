<?php
/*
Plugin Name: WooCommerce - APG Weight and Postcode/State/Country Shipping
Version: 2.2.2
Plugin URI: https://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/
Description: Add to WooCommerce the calculation of shipping costs based on the order weight and postcode, province (state) and country of customer's address. Lets you add an unlimited shipping rates. Created from <a href="http://profiles.wordpress.org/andy_p/" target="_blank">Andy_P</a> <a href="http://wordpress.org/plugins/awd-weightcountry-shipping/" target="_blank"><strong>AWD Weight/Country Shipping</strong></a> plugin and the modification of <a href="http://wordpress.org/support/profile/mantish" target="_blank">Mantish</a> publicada en <a href="http://gist.github.com/Mantish/5658280" target="_blank">GitHub</a>.
Author URI: https://artprojectgroup.es/
Author: Art Project Group
Requires at least: 3.8
Tested up to: 4.9
WC requires at least: 2.6
WC tested up to: 3.2

Text Domain: woocommerce-apg-weight-and-postcodestatecountry-shipping
Domain Path: /languages

@package WooCommerce - APG Weight and Postcode/State/Country Shipping
@category Core
@author Art Project Group
*/

//Igual no deberías poder abrirme
if ( !defined( 'ABSPATH' ) ) {
    exit();
}

//Definimos constantes
define( 'DIRECCION_apg_shipping', plugin_basename( __FILE__ ) );

//Definimos las variables
$apg_shipping = array(	
	'plugin' 		=> 'WooCommerce - APG Weight and Postcode/State/Country Shipping', 
	'plugin_uri' 	=> 'woocommerce-apg-weight-and-postcodestatecountry-shipping', 
	'donacion' 		=> 'https://artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'https://wcprojectgroup.es/tienda/ticket-de-soporte',
	'plugin_url' 	=> 'https://artprojectgroup.es/plugins-para-wordpress/plugins-para-woocommerce/woocommerce-apg-weight-and-postcodestatecountry-shipping', 
	'ajustes' 		=> 'admin.php?page=wc-settings&tab=shipping', 
	'puntuacion' 	=> 'https://wordpress.org/support/view/plugin-reviews/woocommerce-apg-weight-and-postcodestatecountry-shipping'
);
$medios_de_pago = array();

//Carga el idioma
load_plugin_textdomain( 'woocommerce-apg-weight-and-postcodestatecountry-shipping', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );

//Enlaces adicionales personalizados
function apg_shipping_enlaces( $enlaces, $archivo ) {
	global $apg_shipping;

	if ( $archivo == DIRECCION_apg_shipping ) {
		$plugin = apg_shipping_plugin( $apg_shipping['plugin_uri'] );
		$enlaces[] = '<a href="' . $apg_shipping['donacion'] . '" target="_blank" title="' . __( 'Make a donation by ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[] = '<a href="'. $apg_shipping['plugin_url'] . '" target="_blank" title="' . $apg_shipping['plugin'] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[] = '<a href="https://www.facebook.com/artprojectgroup" title="' . __( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://plus.google.com/+ArtProjectGroupES" title="' . __( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'Google+" target="_blank"><span class="genericon genericon-googleplus-alt"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="' . __( 'Follow us on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[] = '<a href="https://profiles.wordpress.org/artprojectgroup/" title="' . __( 'More plugins on ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[] = '<a href="mailto:info@artprojectgroup.es" title="' . __( 'Contact with us by ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'e-mail"><span class="genericon genericon-mail"></span></a> <a href="skype:artprojectgroup" title="' . __( 'Contact with us by ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . 'Skype"><span class="genericon genericon-skype"></span></a>';
		$enlaces[] = apg_shipping_plugin( $apg_shipping['plugin_uri'] );
	}
	
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'apg_shipping_enlaces', 10, 2 );

//Añade el botón de configuración
function apg_shipping_enlace_de_ajustes( $enlaces ) { 
	global $apg_shipping;

	$enlaces_de_ajustes = array(
		'<a href="' . $apg_shipping['ajustes'] . '" title="' . __( 'Settings of ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . $apg_shipping['plugin'] .'">' . __( 'Settings', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</a>', 
		'<a href="' . $apg_shipping['soporte'] . '" title="' . __( 'Support of ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . $apg_shipping['plugin'] .'">' . __( 'Support', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</a>'
	);
	foreach ( $enlaces_de_ajustes as $enlace_de_ajustes ) {
		array_unshift( $enlaces, $enlace_de_ajustes );
	}
	
	return $enlaces; 
}
$plugin = DIRECCION_apg_shipping; 
add_filter( "plugin_action_links_$plugin", 'apg_shipping_enlace_de_ajustes' );

//Añade notificación de actualización
function apg_shipping_noficacion( $datos_version_actual, $datos_nueva_version ) {
	if ( isset( $datos_nueva_version->upgrade_notice ) && strlen( trim( $datos_nueva_version->upgrade_notice ) ) > 0 && (float) $datos_version_actual['Version'] < 2.0 ){
        $mensaje = '</p><div class="wc_plugin_upgrade_notice">';
		$mensaje .= __( "<h4>ALERT: 2.0 is a major update</h4>It’s important that you make backups of your <strong>WooCommerce - APG Weight and Postcode/State/Country Shipping</strong> current configuration and configure it again after upgrade.<br /><em>Remember, the current setting is totally incompatible with WooCommerce 2.6 and you'll lose it</em>.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
        $mensaje .= '</div><p>';
		
		echo $mensaje;
	}
}
add_action( 'in_plugin_update_message-woocommerce-apg-weight-and-postcodestatecountry-shipping/apg-shipping.php', 'apg_shipping_noficacion', 10, 2 );

//¿Está activo WooCommerce?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
	//Contine la clase que crea los nuevos gastos de envío
	function apg_shipping_inicio() {
		if ( !class_exists( 'WC_Shipping_Method' ) ) {
			return;
		}
	
		class WC_apg_shipping extends WC_Shipping_Method {				
			//Variables
			public $clases_de_envio			= array();
			public $roles_de_usuario		= array();
			public $metodos_de_pago			= array();
			public $clases_de_envio_tarifas	= "";
	
			public function __construct( $instance_id = 0 ) {
				$this->id					= 'apg_shipping';
				$this->instance_id			= absint( $instance_id );
				$this->method_title			= __( "APG Shipping", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
				$this->method_description	= __( 'Lets you calculate shipping cost based on Postcode/State/Country and weight of the cart. Lets you set an unlimited weight bands on per postcode/state/country basis and group the groups that that share same delivery cost/bands.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
				$this->supports				= array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);
				$this->init();
			}

			//Inicializa los datos
			public function init() {
				$this->apg_shipping_dame_clases_de_envio(); //Obtiene todas las clases de envío
				$this->apg_shipping_dame_roles_de_usuario(); //Obtiene todos los roles de usuario
				$this->apg_shipping_dame_metodos_de_pago(); //Obtiene todos los métodos de pago
	
				$this->init_settings(); //Recogemos todos los valores
				$this->init_form_fields(); //Crea los campos de opciones
				
				//Inicializamos variables
				$campos = array(
					'activo', 
					'title', 
					'tax_status', 
					'fee', 
					'cargo',
					'tipo_cargo', 
					'tarifas', 
					'tipo_tarifas',
					'suma',
					'maximo', 
					'clases_excluidas', 
					'roles_excluidos', 
					'pago',
					'icono',
					'muestra_icono',
					'entrega',
					'debug',
				);
				foreach ( $campos as $campo ) {
					$this->$campo = $this->get_option( $campo );
				}
				$this->tarifas = (array) explode( "\n", $this->tarifas );

				//Acción
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}
			
			//Formulario de datos
			public function init_form_fields() {
				$this->instance_form_fields = include( 'includes/admin/campos.php' );
			}
	
			//Pinta el formulario
			public function admin_options() {
				include_once( 'includes/formulario.php' );
			}
	
			//Función que lee y devuelve los tipos de clases de envío
			public function apg_shipping_dame_clases_de_envio() {
				if ( WC()->shipping->get_shipping_classes() ) {
					foreach ( WC()->shipping->get_shipping_classes() as $clase_de_envio ) {
						$this->clases_de_envio[esc_attr( $clase_de_envio->slug )] = $clase_de_envio->name;
						$this->clases_de_envio_tarifas .= esc_attr( $clase_de_envio->slug ) . " -> " . $clase_de_envio->name . ", ";
					}
				} else {
					$this->clases_de_envio[] = __( 'Select a class&hellip;', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
				}
				$this->clases_de_envio_tarifas = substr( $this->clases_de_envio_tarifas, 0, -2 ) . ".";
			}	

			//Función que lee y devuelve los roles de usuario
			public function apg_shipping_dame_roles_de_usuario() {
				$wp_roles = new WP_Roles();

				foreach( $wp_roles->role_names as $rol => $nombre ) {
					$this->roles_de_usuario[$rol] = $nombre;
				}
			}

			//Función que lee y devuelve los métodos de pago
			public function apg_shipping_dame_metodos_de_pago() {
				global $medios_de_pago;
				
				foreach( $medios_de_pago as $clave => $medio_de_pago ) {
					$this->metodos_de_pago[$medio_de_pago->id] = $medio_de_pago->title;
				}
			}
			
			//Calcula el gasto de envío
			public function calculate_shipping( $paquete = array() ) {
				if ( $this->activo == 'no' ) {
					return false; //No está activo
				}
				
				//Comprobamos los roles excluidos
				if ( !empty( $this->roles_excluidos ) ) {
					if ( empty( wp_get_current_user()->roles ) && in_array( 'invitado', $this->roles_excluidos ) ) { //Usuario invitado
						return false; //Role excluido
					}
					foreach( wp_get_current_user()->roles as $rol ) { //Usuario con rol
						if ( in_array( $rol, $this->roles_excluidos ) ) {
							return false; //Role excluido
						}
					}
				}

				//Variables
				$volumen	= 0;
				$largo		= 0;
				$ancho		= 0;
				$alto		= 0;
				$clases		= array();
				$medidas	= array();

				//Peso total del pedido
				$peso_total = WC()->cart->get_cart_contents_weight();
				//Productos totales del pedido
				$productos_totales = WC()->cart->get_cart_contents_count();
				//Precio total del pedido
				$precio_total = WC()->cart->get_displayed_subtotal();

				//Comprobamos si está activo WPML para coger la traducción correcta de la clase de envío
				if ( function_exists('icl_object_id') && !function_exists( 'pll_the_languages' ) ) {
					global $sitepress;
					do_action( 'wpml_switch_language', $sitepress->get_default_language() );
				}

				//Toma distintos datos de los productos
				foreach ( WC()->cart->get_cart() as $identificador => $valores ) {
					$producto = $valores['data'];

					//Toma el peso del producto
					$peso = $producto->get_weight() * $valores['quantity'];
					
					//Toma el precio del producto
					if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
						$precio = ( WC()->cart->tax_display_cart == 'excl' ) ? $producto->get_price_excluding_tax() * $valores['quantity'] : $producto->get_price_including_tax() * $valores['quantity'];
					} else {
						$precio = ( WC()->cart->tax_display_cart == 'excl' ) ? wc_get_price_excluding_tax( $producto ) * $valores['quantity'] : wc_get_price_including_tax( $producto ) * $valores['quantity'];
					}
					//Compatibilidad con WooCommerce Product Bundles
					if ( $producto->is_type( 'bundle' ) ) {
						$precio = $producto->get_bundle_price( 'min' );
					}

					//No atiende a las clases de envío excluidas
					if ( $this->clases_excluidas ) {
						//Clase de producto
						if ( in_array( $producto->get_shipping_class(), $this->clases_excluidas ) || ( in_array( "todas", $this->clases_excluidas ) && $producto->get_shipping_class() ) ) {
							$peso_total -= $peso;
							$productos_totales -= $valores['quantity'];
							if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
								$precio_total = ( WC()->cart->tax_display_cart == 'excl' ) ? $precio_total - $producto->get_price_excluding_tax() * $valores['quantity'] : $precio_total - $producto->get_price_including_tax() * $valores['quantity'];
							} else {
								$precio_total = ( WC()->cart->tax_display_cart == 'excl' ) ? $precio_total - wc_get_price_excluding_tax( $producto ) * $valores['quantity'] : $precio_total - wc_get_price_including_tax( $producto ) * $valores['quantity'];	
							}
							continue; 
						}
					}
					
					//Ajuste para los productos virtual y bundle
					if ( $producto->is_virtual() && !isset( $valores['bundled_by'] ) ) {
						$peso_total			-= $peso;
						$productos_totales	-= $valores['quantity'];
						$precio_total		-= $precio;
					}

					//Medidas y volúmenes
					if ( $producto->get_length() && $producto->get_width() && $producto->get_height() ) {
						$volumen += $producto->get_length() * $producto->get_width() * $producto->get_height() * $valores['quantity'];
					}
					$medidas[] = array(
						'largo'		=> $producto->get_length(),
						'ancho'		=> $producto->get_width(),
						'alto'		=> $producto->get_height(),
						'cantidad'	=> $valores['quantity'],
					);
					if ( $producto->get_length() > $largo ) {
						$largo = $producto->get_length();
					}
					if ( $producto->get_width() > $ancho ) {
						$ancho = $producto->get_width();
					}
					if ( $producto->get_height() > $alto ) {
						$alto = $producto->get_height();
					}

					//Clase de envío
					if ( $producto->needs_shipping() ) {
						$clase = ( $producto->get_shipping_class() ) ? $producto->get_shipping_class() : 'sin-clase';
						//Inicializamos la clase general
						if ( !isset ($clases['todas'] ) ) {
							$clases['todas'] = 0;
						}
						//Guardamos peso, cantidad de productos o total del pedido
						$cantidad = ( $this->tipo_tarifas == "unidad" ) ? $valores['quantity'] : $peso;
						if ( $this->tipo_tarifas == "total" ) {
							$cantidad = $precio;
						}
						$clases['todas'] += $cantidad;
						if ( !isset( $clases[$clase] ) ) {
							$clases[$clase] = $cantidad;
						} else if ( $clase != 'todas' ) {
							$clases[$clase] += $cantidad;
						}
					}
				}

				//Comprobamos si está activo WPML para devolverlo al idioma que estaba activo
				if ( function_exists('icl_object_id') && !function_exists( 'pll_the_languages' ) ) {
					do_action( 'wpml_switch_language', ICL_LANGUAGE_CODE );
				}
				
				if ( $this->tipo_tarifas == "unidad" ) {
					$peso_total = $productos_totales;
				} else if ( $this->tipo_tarifas == "total" ) {
					$peso_total = $precio_total;
				}

				if ( empty( $medidas ) && empty( $clases ) ) {
					return false; //No hay productos
				}
				
				//Muestra información de depuración
				if ( $this->debug == 'yes' ) {
					echo "<pre>";
					echo "Peso total: " . $peso_total . PHP_EOL; 
					echo "Volumen: " . $volumen . PHP_EOL; 
					echo "Largo: " . $largo . PHP_EOL; 
					echo "Ancho: " . $ancho . PHP_EOL; 
					echo "Alto: " . $alto . PHP_EOL;  
					echo "Medidas: " . print_r( $medidas, true ) . PHP_EOL; 
					echo "Clases: " . print_r( $clases, true );
					echo "</pre>";
				}

				$tarifas = $this->dame_tarifa_mas_barata( $peso_total, $volumen, $largo, $ancho, $alto, $medidas, $clases ); //Filtra las tarifas
				if ( empty( $tarifas ) ) {
					return false; //No hay tarifa
				}
				
				//Calculamos el importe total
				$importe = 0;
				if ( !empty( $this->suma ) &&  $this->suma == "yes" ) {
					$importe = max( $tarifas );
				} else {
					foreach( $tarifas as $tarifa ) {
						$importe += $tarifa;
					}					
				}

				//Calculamos el precio
				$suma_cargos = 0;
	
				//Cargos adicionales
				if ( $this->fee > 0 ) { //Cargo por manipulación
					$suma_cargos += $this->fee;			
				}
				//¿Cargo adicional por producto?
				$cargo_por_producto = ( $this->tipo_cargo == "no" ) ? 1 : WC()->cart->get_cart_contents_count();
				
				if ( $this->cargo > 0 && !strpos( $this->cargo, '%' ) ) { //Cargo adicional normal
					$suma_cargos += $this->cargo * $cargo_por_producto;
				} else if ( $this->cargo > 0 && strpos( $this->cargo, '%' ) && !strpos( $this->cargo, '|' ) ) { //Cargo adicional porcentaje
					$suma_cargos += ( $importe * ( str_replace( '%', '', $this->cargo ) / 100 ) ) * $cargo_por_producto;
				} else if ( $this->cargo > 0 && strpos( $this->cargo, '%' ) && strpos( $this->cargo, '|' ) ) { //Porcentaje con mínimo y máximo
					//Recogemos los valores mínimo y máximo
					$porcentaje = explode( '|', $this->cargo );
					preg_match( '/min=[\"|\'](.*)[\"|\'][\s+|$]/', $porcentaje[1], $minimo );
					preg_match( '/max=[\"|\'](.*)[\"|\']$/', $porcentaje[1], $maximo );
					
					$calculo_de_porcentaje = ( $importe * ( str_replace( '%', '', $this->cargo ) / 100 ) ) * $cargo_por_producto;
					//Comprobamos el mínimo
					if ( isset( $minimo[1] ) && $minimo[1] > $calculo_de_porcentaje ) {
						$calculo_de_porcentaje = $minimo[1];
					}
					//Comprobamos el máximo
					if ( isset( $maximo[1] ) && $calculo_de_porcentaje > $maximo[1] ) {
						$calculo_de_porcentaje = $maximo[1];
					}
					//Añade el cargo
					$suma_cargos += $calculo_de_porcentaje;
				}

				//Actualizamos precio
				$importe	+= $suma_cargos;
				//¿Impuestos?
				$impuestos	= ( $this->tax_status != 'none' ) ? '' : false;

				$tarifa = array(
					'id'		=> $this->get_rate_id(),
					'label'		=> $this->title,
					'cost'		=> $importe,
					'taxes'		=> $impuestos,
					'calc_tax'	=> 'per_order'
				);
				
				$this->add_rate( $tarifa );
				
				do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $tarifa );
			}
			
			//Recoge las tarifas programadas
			public function dame_tarifas() {
				$tarifas = array();
				
				//Recoge las tarifas programadas
				if ( !empty( $this->tarifas ) ) {
					foreach ( $this->tarifas as $indice => $opcion ) {
						$tarifa = preg_split( '~\s*\|\s*~', preg_replace( '/\s+/', '', $opcion ) );
	
						if ( sizeof( $tarifa ) < 2 ) {
							continue;
						} else {
							$tarifas[] = $tarifa;
						}
					}
				}
	
				return $tarifas;
			}

			//Selecciona la tarifa más barata
			public function dame_tarifa_mas_barata( $peso_total, $volumen_total, $largo, $ancho, $alto, $medidas, $clases ) {
				//Variables
				$tarifa_mas_barata			= array();
				$peso_parcial				= array();
				$peso_anterior				= 0;
				$largo_anterior				= 0;
				$ancho_anterior				= 0;
				$alto_anterior				= 0;
				$clase_de_envio_anterior	= '';

				//Obtenemos las tarifas
				$tarifas = $this->dame_tarifas();

				//Reajustamos pesos
				foreach ( $clases as $clase => $peso ) {
					if ( $clase != 'todas' && apg_busca_en_array( $clase, $tarifas ) ) {
						$clases['todas'] -= $peso;
					}
				}

				//Aplicamos tarifas
				foreach ( $tarifas as $indice => $tarifa ) {	
					//Variables
					$calculo_volumetrico	= false;
					$excede_dimensiones		= false;
					$clase_de_envio			= false;
					unset( $medidas_tarifa ); //Fix by DJ Team Digital
					
					//Comprobamos medidas
					if ( stripos( $tarifa[0], "x" ) ) { //Son dimensiones no pesos
						$calculo_volumetrico = true;
						$medidas_tarifa = strtolower( $tarifa[0] );
					}
					if ( isset( $tarifa[2] ) && stripos( $tarifa[2], "x" ) ) {
						$medidas_tarifa = strtolower( $tarifa[2] );
					}
					if ( isset( $tarifa[3] ) && stripos( $tarifa[3], "x" ) ) {
						$medidas_tarifa = strtolower( $tarifa[3] );
					}
					//¿Existen medidas?
					if ( isset( $medidas_tarifa ) ) {
						$medida_tarifa = explode( "x", $medidas_tarifa );
						if ( ( $largo > $medida_tarifa[0] || $ancho > $medida_tarifa[1] || $alto > $medida_tarifa[2] ) || $volumen_total > ( $medida_tarifa[0] * $medida_tarifa[1] * $medida_tarifa[2] ) ) {
							$excede_dimensiones = true; //Excede el tamaño o volumen máximo
						}
					}
					
					//Comprobamos clases de envío
					if ( $clases['todas'] == $peso_total ) {
						$clase_de_envio = 'todas';
					} else {
						if ( isset( $tarifa[2] ) && !stripos( $tarifa[2], "x" ) && array_key_exists( $tarifa[2], $clases ) ) {
							$clase_de_envio = $tarifa[2];
						} else if ( isset( $tarifa[2] ) && !stripos( $tarifa[2], "x" ) && !array_key_exists( $tarifa[2], $clases ) ) {
							$clase_de_envio = 'todas';					
						} else if ( !isset( $tarifa[2] ) || !stripos( $tarifa[2], "x" ) ) {
							$clase_de_envio = 'sin-clase';					
						}
					}
					//Prevenimos errores
					if ( $clase_de_envio == 'sin-clase' && !isset( $clases['sin-clase'] ) ) {
						$clase_de_envio = 'todas';
					}
					if ( $clase_de_envio_anterior != $clase_de_envio ) {
						$clase_de_envio_anterior	= $clase_de_envio;
						$peso_anterior				= 0;
						$largo_anterior				= 0;
						$ancho_anterior				= 0;
						$alto_anterior				= 0;
					}

					//Obtenemos la tarifa más barata
					if ( !$calculo_volumetrico && !$excede_dimensiones ) { //Es un peso
						if ( ( !$peso_anterior && $tarifa[0] >= $clases[$clase_de_envio] ) || ( $tarifa[0] >= $clases[$clase_de_envio] && $clases[$clase_de_envio] > $peso_anterior ) ) {
							$tarifa_mas_barata[$clase_de_envio] = $tarifa[1];
						} else if ( $this->maximo == "yes" && ( empty( $tarifa_mas_barata[$clase_de_envio] ) || $clases[$clase_de_envio] > $peso_anterior ) ) { //El peso es mayor que el de la tarifa máxima
							$tarifa_mas_barata[$clase_de_envio] = $tarifa[1];
						}
						//Guardamos el peso actual
						$peso_anterior = $tarifa[0];
					} else if ( $calculo_volumetrico && !$excede_dimensiones ) { //Es una medida
						$volumen = $medida_tarifa[0] * $medida_tarifa[1] * $medida_tarifa[2];

						if ( !$largo_anterior || ( ( $volumen > $volumen_total ) && ( $medida_tarifa[0] >= $largo && $largo > $largo_anterior ) && ( $medida_tarifa[1] >= $ancho && $ancho > $ancho_anterior ) && ( $medida_tarifa[2] >= $alto && $alto > $alto_anterior ) ) ) {
							$tarifa_mas_barata[$clase_de_envio] = $tarifa[1];									
						} else if ( $this->maximo == "yes" && ( empty( $tarifa_mas_barata[$clase_de_envio] ) || ( $largo > $largo_anterior && $ancho > $ancho_anterior && $alto > $alto_anterior ) ) ) { //Las medidas son mayores que la de la tarifa máxima
							$tarifa_mas_barata[$clase_de_envio] = $tarifa[1];
						}
						//Guardamos las medidas actuales
						$largo_anterior = $medida_tarifa[0];
						$ancho_anterior = $medida_tarifa[1];
						$alto_anterior = $medida_tarifa[2];
					} else if ( $this->maximo == "yes" && ( empty( $tarifa_mas_barata[$clase_de_envio] ) || $tarifa_mas_barata[$clase_de_envio] < $tarifa[1] ) ) { //Las medidas son mayores que la de la tarifa máxima
						$tarifa_mas_barata[$clase_de_envio] = $tarifa[1];
					}
				}

				if ( $clases['todas'] == 0 && count( $tarifa_mas_barata ) > 1 ) { //Prevenimos errores de duplicación de tarifas
					unset( $tarifa_mas_barata['todas'] );
				}
				
				if ( $this->maximo == "no" && ( ( $peso_anterior && $clases[$clase_de_envio] > $peso_anterior ) || ( $calculo_volumetrico && $excede_dimensiones ) ) ) { //Se ha excedido la tarifa máxima
					unset( $tarifa_mas_barata[$clase_de_envio] );
				}

				if ( !empty( $tarifa_mas_barata ) ) {
					return $tarifa_mas_barata;
				} else {
					return array();
				}
			}
		}
	}
	add_action( 'plugins_loaded', 'apg_shipping_inicio', 0 );
	
	//Añade clases necesarias para nuevos gastos de envío
	function apg_shipping_clases( $metodos ) {
		$metodos[ 'apg_shipping' ] = 'WC_apg_shipping';
	
		return $metodos;
	}
	add_filter( 'woocommerce_shipping_methods', 'apg_shipping_clases', 0 );
	
	//Filtra los medios de pago
	function apg_shipping_filtra_medios_de_pago( $medios ) {
		if ( isset( WC()->session->chosen_shipping_methods ) ) {
			$id = explode( ":", WC()->session->chosen_shipping_methods[0] );
		} else if ( isset( $_POST['shipping_method'][0] ) ) {
			$id = explode( ":", $_POST['shipping_method'][0] );
		}
		if ( empty( $id ) ) {
			return $medios;
		}
		$configuracion	= maybe_unserialize( get_option( 'woocommerce_apg_shipping_' . $id[1] .'_settings' ) );
		
		if ( isset( $_POST['payment_method'] ) && !$medios ) {
			$medios = $_POST['payment_method'];
		}

		if ( !empty( $configuracion['pago'] ) && $configuracion['pago'][0] != 'todos' ) {
			foreach ( $medios as $nombre => $medio ) {
				if ( is_array( $configuracion['pago'] ) ) {
					if ( !in_array( $nombre, $configuracion['pago'] ) ) {
						unset( $medios[$nombre] );
					}
				} else { 
					if ( $nombre != $configuracion['pago'] ) {
						unset( $medios[$nombre] );
					}
				}
			}
		}

		return $medios;
	}
	add_filter( 'woocommerce_available_payment_gateways', 'apg_shipping_filtra_medios_de_pago' );
} else {
	add_action( 'admin_notices', 'apg_shipping_requiere_wc' );
}

//Busca en un array multidimensional
function apg_busca_en_array( $busqueda, $array_de_busqueda, $extricto = true ) {
	foreach ( $array_de_busqueda as $valor_a_comparar ) {
		if ( ( $extricto ? $valor_a_comparar === $busqueda : $valor_a_comparar == $busqueda ) || ( is_array( $valor_a_comparar ) && apg_busca_en_array( $busqueda, $valor_a_comparar, $extricto ) ) ) {
			return true;
		}
	}

	return false;
}

//Muestra el icono
function apg_shipping_icono( $etiqueta, $metodo ) {
	$gasto_de_envio	= explode( ":", $etiqueta );
	$id				= explode( ":", $metodo->id );
	$configuracion	= maybe_unserialize( get_option( 'woocommerce_apg_shipping_' . $id[1] .'_settings' ) );
	//¿Mostramos el icono?
	if ( !empty( $configuracion['icono'] ) && @getimagesize( $configuracion['icono'] ) && $configuracion['muestra_icono'] != 'no' ) {
		$tamano = @getimagesize( $configuracion['icono'] );
		$imagen	= '<img class="apg_shipping_icon" src="' . $configuracion['icono'] . '" witdh="' . $tamano[0] . '" height="' . $tamano[1] . '" />';
		if ( $configuracion['muestra_icono'] == 'delante' ) {
			$etiqueta = $imagen . ' ' . $etiqueta; //Icono delante
		} else if ( $configuracion['muestra_icono'] == 'detras' ) {
			$etiqueta = $gasto_de_envio[0] . ' ' . $imagen . ':' . $gasto_de_envio[1]; //Icono detrás
		} else {
			$etiqueta = $imagen . ':' . $gasto_de_envio[1]; //Sólo icono
		}
	}
	//Tiempo de entrega
	if ( !empty( $configuracion['entrega'] ) ) {
		$etiqueta .= '<br /><small class="apg_shipping_delivery">' . sprintf( __( "Estimated delivery time: %s", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $configuracion['entrega'] ) . '</small>';
	}
	
	return $etiqueta;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'apg_shipping_icono', 10, 2 );

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function apg_shipping_requiere_wc() {
	global $apg_shipping;
		
	echo '<div class="error fade" id="message"><h3>' . $apg_shipping['plugin'] . '</h3><h4>' . __( "This plugin require WooCommerce active to run!", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_apg_shipping );
}

//Obtiene toda la información sobre el plugin
function apg_shipping_plugin( $nombre ) {
	global $apg_shipping;

	$argumentos = ( object ) array( 
		'slug' => $nombre 
	);
	$consulta = array( 
		'action' => 'plugin_information', 
		'timeout' => 15, 
		'request' => serialize( $argumentos )
	);
	$respuesta = get_transient( 'apg_shipping_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_post( 'http://api.wordpress.org/plugins/info/1.0/', array( 
			'body' => $consulta)
		);
		set_transient( 'apg_shipping_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( !is_wp_error( $respuesta ) ) {
		$plugin = get_object_vars( unserialize( $respuesta['body'] ) );
	} else {
		$plugin['rating'] = 100;
	}
	
	$rating = array(
	   'rating'	=> $plugin['rating'],
	   'type'	=> 'percent',
	   'number'	=> $plugin['num_ratings'],
	);
	ob_start();
	wp_star_rating( $rating );
	$estrellas = ob_get_contents();
	ob_end_clean();

	return '<a title="' . sprintf( __( 'Please, rate %s:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_shipping['plugin'] ) . '" href="' . $apg_shipping['puntuacion'] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

//Muestra el mensaje de actualización
function apg_shipping_actualizacion() {
	global $apg_shipping;
	
    echo '<div class="error fade" id="message"><h3>' . $apg_shipping['plugin'] . '</h3><h4>' . sprintf( __( "Please, update your %s. It's very important!", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), '<a href="' . $apg_shipping['ajustes'] . '" title="' . __( 'Settings', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '">' . __( 'settings', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</a>' ) . '</h4></div>';
}

//Carga las hojas de estilo
function apg_shipping_muestra_mensaje() {
	global $medios_de_pago;
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
		$medios_de_pago = WC()->payment_gateways->payment_gateways(); //Guardamos los medios de cobro
	}
	wp_enqueue_style( 'apg_shipping_hoja_de_estilo', plugins_url( 'assets/css/style.css', __FILE__ ) ); //Carga la hoja de estilo global
	wp_enqueue_script( 'apg_shipping_script', plugins_url( 'assets/js/apg-shipping.js', __FILE__ ) );

	/*$configuracion = get_option( 'woocommerce_apg_shipping_settings' );
	if ( !isset( $configuracion['maximo'] ) ) {
		add_action( 'admin_notices', 'apg_shipping_actualizacion' ); //Comprueba si hay que mostrar el mensaje de actualización
	}*/
}
add_action( 'admin_init', 'apg_shipping_muestra_mensaje' );

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_shipping_desinstalar() {
	$contador = 0;
	while( $contador < 100 ) {
		delete_option( 'woocommerce_apg_shipping_' . $contador . 'settings' );
		$contador++;
	}
	delete_transient( 'apg_shipping_plugin' );
}
register_uninstall_hook( __FILE__, 'apg_shipping_desinstalar' );
