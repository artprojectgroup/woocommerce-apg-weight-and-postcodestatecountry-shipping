<?php
/*
Plugin Name: WC - APG Weight Shipping
Requires Plugins: woocommerce
Version: 2.6.4
Plugin URI: https://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/
Description: Add to WooCommerce the calculation of shipping costs based on the order weight and postcode, province (state) and country of customer's address. Lets you add an unlimited shipping rates. Created from <a href="https://profiles.wordpress.org/andy_p/" target="_blank">Andy_P</a> <a href="https://wordpress.org/plugins/awd-weightcountry-shipping/" target="_blank"><strong>AWD Weight/Country Shipping</strong></a> plugin and the modification of <a href="https://wordpress.org/support/profile/mantish" target="_blank">Mantish</a> published in <a href="https://gist.github.com/Mantish/5658280" target="_blank">GitHub</a>.
Author URI: https://artprojectgroup.es/
Author: Art Project Group
Requires at least: 5.0
Tested up to: 6.8
WC requires at least: 5.6
WC tested up to: 9.5

Text Domain: woocommerce-apg-weight-and-postcodestatecountry-shipping
Domain Path: /languages

@package WC - APG Weight Shipping
@category Core
@author Art Project Group
*/

//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Definimos constantes
define( 'DIRECCION_apg_shipping', plugin_basename( __FILE__ ) );

//Funciones generales de APG
include_once( 'includes/admin/funciones-apg.php' );

//¿Está activo WooCommerce?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
    //Añade compatibilidad con HPOS
    add_action( 'before_woocommerce_init', function() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    } );

    //Contine la clase que crea los nuevos gastos de envío
	function apg_shipping_inicio() {
		if ( ! class_exists( 'WC_Shipping_Method' ) ) {
			return;
		}
		
		//Cargamos funciones necesarias
		include_once( 'includes/admin/funciones.php' );

		#[AllowDynamicProperties]
		class WC_apg_shipping extends WC_Shipping_Method {				
			//Variables
			public $categorias_de_producto   = [];
			public $etiquetas_de_producto    = [];
			public $clases_de_envio          = [];
			public $roles_de_usuario         = [];
			public $metodos_de_envio         = [];
			public $metodos_de_pago          = [];
            public $atributos                = [];
			public $clases_de_envio_tarifas  = "";
	
			public function __construct( $instance_id = 0 ) {
				$this->id					= 'apg_shipping';
				$this->instance_id			= absint( $instance_id );
				$this->method_title			= __( "APG Shipping", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
				$this->method_description	= __( 'Lets you calculate shipping cost based on Postcode/State/Country and weight of the cart. Lets you set an unlimited weight bands on per postcode/state/country basis and group the groups that that share same delivery cost/bands.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
				$this->supports				= [
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				];
				$this->init();
			}

			//Inicializa los datos
			public function init() {	
				$this->init_settings(); //Recogemos todos los valores
				$this->init_form_fields(); //Crea los campos de opciones

                //Inicializamos variables
				$campos = [
					'title',
					'tax_status',
					'fee',
					'cargo',
					'tipo_cargo',
					'tarifas',
					'tipo_tarifas',
					'suma',
					'maximo',
					'categorias_excluidas',
					'tipo_categorias',
					'etiquetas_excluidas',
					'tipo_etiquetas',
                    'atributos_excluidos',
                    'tipo_atributos',
					'clases_excluidas',
					'tipo_clases',
					'roles_excluidos',
					'tipo_roles',
					'pago',
                    'envio',
					'icono',
					'muestra_icono',
					'entrega',
					'debug',
				];
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					$campos[] = 'activo';
				}
				foreach ( $campos as $campo ) {
					$this->$campo = $this->get_option( $campo );
				}
				$this->tarifas = (array) explode( "\n", $this->tarifas );

				//Acción
				add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
			}
			
			//Formulario de datos
			public function init_form_fields() {
				$this->instance_form_fields = include( 'includes/admin/campos.php' );
			}
	
			//Pinta el formulario
			public function admin_options() {
				include_once( 'includes/formulario.php' );
			}
			
			//Función que lee y devuelve las categorías/etiquetas de producto
			public function apg_shipping_dame_datos_de_producto( $tipo ) {
				$taxonomy = ( $tipo == 'categorias_de_producto' ) ? 'product_cat' : 'product_tag';
				
				$argumentos = [
					'taxonomy'		=> $taxonomy,
					'orderby'		=> 'name',
					'show_count'	=> 0,
					'pad_counts'	=> 0,
					'hierarchical'	=> 1,
					'title_li'		=> '',
					'hide_empty'	=> 0
				];
				$datos = get_categories( $argumentos );
				
				foreach ( $datos as $dato ) {
					$this->{$tipo}[ $dato->term_id ] = $dato->name;
				}
			}
			
			//Obtiene todos los datos necesarios
			public function apg_shipping_obtiene_datos() {
				$this->apg_shipping_dame_datos_de_producto( 'categorias_de_producto' ); //Obtiene todas las categorías de producto
				$this->apg_shipping_dame_datos_de_producto( 'etiquetas_de_producto' ); //Obtiene todas las etiquetas de producto
				$this->apg_shipping_dame_clases_de_envio(); //Obtiene todas las clases de envío
				$this->apg_shipping_dame_roles_de_usuario(); //Obtiene todos los roles de usuario
				$this->apg_shipping_dame_metodos_de_envio(); //Obtiene todas los métodos de envío
				$this->apg_shipping_dame_metodos_de_pago(); //Obtiene todos los métodos de pago
				$this->apg_shipping_dame_atributos(); //Obtiene todos los atributos
			}

			//Función que lee y devuelve los tipos de clases de envío
			public function apg_shipping_dame_clases_de_envio() {
				if ( WC()->shipping->get_shipping_classes() ) {
					foreach ( WC()->shipping->get_shipping_classes() as $clase_de_envio ) {
						$this->clases_de_envio[ esc_attr( $clase_de_envio->slug ) ] = $clase_de_envio->name;
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
                    $this->roles_de_usuario[ $rol ] = $nombre;
				}
			}
            
			//Función que lee y devuelve los métodos de envío
			public function apg_shipping_dame_metodos_de_envio() {
                global $zonas_de_envio, $wpdb;
                
                if ( isset( $_REQUEST[ 'instance_id' ] ) ) {
                    $zona_de_envio  = $wpdb->get_var( $wpdb->prepare( "SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods as methods WHERE methods.instance_id = %d LIMIT 1;", $_REQUEST[ 'instance_id' ] ) );

                    if ( ! empty( $zona_de_envio ) ) {
                        foreach ( $zonas_de_envio as $zona ) {
                            foreach ( $zona[ 'shipping_methods' ] as $gasto_envio ) {
                                if ( $zona_de_envio == $zona[ 'id' ] && $gasto_envio->instance_id != $_REQUEST[ 'instance_id' ] ) {
                                    $this->metodos_de_envio[ $gasto_envio->instance_id ] = $gasto_envio->title;
                                }
                            }
                        }
                    }
                }
			}
			
			//Función que lee y devuelve los métodos de pago
			public function apg_shipping_dame_metodos_de_pago() {
				global $medios_de_pago;
				
                if ( is_array( $medios_de_pago ) && ! empty( $medios_de_pago ) ) {
                    foreach( $medios_de_pago as $clave => $medio_de_pago ) {
                        $this->metodos_de_pago[ $medio_de_pago->id ] = $medio_de_pago->title;
                    }
                }
			}

			//Función que lee y devuelve los atributos
			public function apg_shipping_dame_atributos() {
				if ( wc_get_attribute_taxonomies() ) {
					foreach ( wc_get_attribute_taxonomies() as $atributo ) {
						$terminos	= get_terms( array( 'taxonomy' => 'pa_' . $atributo->attribute_name ) );
						if ( ! is_wp_error( $terminos ) ) {
							foreach ( $terminos as $termino ) {
								$this->atributos[ esc_attr( $atributo->attribute_label ) ][ 'pa_' . $atributo->attribute_name . "-" . $termino->slug ] = $termino->name;
							}
						}
					}
				}
			}
            //Reduce valores en categorías, etiquetas y clases de envío excluídas
			public function reduce_valores( &$peso_total, $peso, &$productos_totales, $valores, &$precio_total, $producto ) {
				$peso_total			-= $peso;
				$productos_totales	-= $valores[ 'quantity' ];
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					$precio_total = ( WC()->cart->tax_display_cart == 'excl' ) ? $precio_total - $producto->get_price_excluding_tax() * $valores[ 'quantity' ] : $precio_total - $producto->get_price_including_tax() * $valores[ 'quantity' ];
                } elseif ( version_compare( WC_VERSION, '4.4', '<' ) ) {
					$precio_total = ( WC()->cart->tax_display_cart == 'excl' ) ? $precio_total - wc_get_price_excluding_tax( $producto ) * $valores[ 'quantity' ] : $precio_total - wc_get_price_including_tax( $producto ) * $valores[ 'quantity' ];	
                } else {
					$precio_total = ( WC()->cart->get_tax_price_display_mode() == 'excl' ) ? $precio_total - wc_get_price_excluding_tax( $producto ) * $valores[ 'quantity' ] : $precio_total - wc_get_price_including_tax( $producto ) * $valores[ 'quantity' ];	
				}
			}

			//Habilita el envío
			public function is_available( $paquete ) {
				//Comprueba si está activo el plugin
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					if ( $this->activo == 'no' ) {
						return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this ); //No está activo
					}
				} else {
					if ( ! $this->is_enabled() ) {
						return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this ); //No está activo
					}
				}
				
				return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true, $paquete, $this );
			}
			
			//Calcula el gasto de envío
			public function calculate_shipping( $paquete = [] ) {
				//Recoge los datos
				$this->apg_shipping_obtiene_datos();

				//Comprueba los roles excluidos
                $validacion = true;
                if ( ! empty( $this->roles_excluidos ) ) {
					if ( empty( wp_get_current_user()->roles ) ) {
                        if ( ( in_array( 'invitado', $this->roles_excluidos ) && $this->tipo_roles == 'no' ) ||
                            ( ! in_array( 'invitado', $this->roles_excluidos ) && $this->tipo_roles == 'yes' ) ) { //Usuario invitado
                            $validacion = false; //Role excluido
                        } else {
                            $validacion = true;
                        }                   
                    } else {
                        foreach( wp_get_current_user()->roles as $rol ) { //Usuario con rol
                            if ( ( in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles == 'no' ) || 
                            ( ! in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles == 'yes' ) ) {
                                $validacion = false; //Role excluido
                            } else {
                                $validacion = true;
                            }
                        }
                    }                        
				}
                if ( ! $validacion ) {
                    return false; //No está activo
                }
                
				//Variables
				$volumen	= 0;
				$largo		= 0;
				$ancho		= 0;
				$alto		= 0;
				$clases		= [];
				$medidas	= [];

				
				$peso_total         = WC()->cart->get_cart_contents_weight(); //Peso total del pedido
				$productos_totales  = WC()->cart->get_cart_contents_count(); //Productos totales del pedido
				$precio_total       = WC()->cart->get_displayed_subtotal(); //Precio total del pedido

				//Comprueba si está activo WPML para coger la traducción correcta de la clase de envío
				if ( function_exists( 'icl_object_id' ) && ! function_exists( 'pll_the_languages' ) ) {
					global $sitepress;
                    
					do_action( 'wpml_switch_language', $sitepress->get_default_language() );
				}

				//Toma distintos datos de los productos
				foreach ( WC()->cart->get_cart() as $identificador => $valores ) {
					$producto  = $valores[ 'data' ];

					//Toma el peso del producto
					$peso      = ( $producto->get_weight() > 0 ) ? $producto->get_weight() * $valores[ 'quantity' ] : 0;
					
					//Toma el precio del producto
					if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
						$precio = ( WC()->cart->tax_display_cart == 'excl' ) ? $producto->get_price_excluding_tax() * $valores[ 'quantity' ] : $producto->get_price_including_tax() * $valores[ 'quantity' ];
                    } elseif ( version_compare( WC_VERSION, '4.4', '<' ) ) {
						$precio = ( WC()->cart->tax_display_cart == 'excl' ) ? wc_get_price_excluding_tax( $producto ) * $valores[ 'quantity' ] : wc_get_price_including_tax( $producto ) * $valores[ 'quantity' ];
                    } else {
						$precio = ( WC()->cart->get_tax_price_display_mode() == 'excl' ) ? wc_get_price_excluding_tax( $producto ) * $valores[ 'quantity' ] : wc_get_price_including_tax( $producto ) * $valores[ 'quantity' ];
					}
					//Compatibilidad con WooCommerce Product Bundles
					if ( $producto->is_type( 'bundle' ) ) {
						$precio = $producto->get_bundle_price( 'min' ) * $valores[ 'quantity' ];
					}

					//No atiende a las categorías de producto excluidas
					if ( ! empty( $this->categorias_excluidas ) ) {
						if ( $producto->is_type( 'variation' ) ) {
							$parent = wc_get_product( $producto->get_parent_id() );
							if ( ( ! empty( array_intersect( $parent->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'no' ) || 
								( empty( array_intersect( $parent->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'yes' ) ) {
								return false;
							}
						} else {
							if ( ( ! empty( array_intersect( $producto->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'no' ) || 
								( empty( array_intersect( $producto->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'yes' ) ) {
								return false;
							}
						}
					}

					//No atiende a las etiquetas de producto excluidas
					if ( ! empty( $this->etiquetas_excluidas ) ) {
						if ( $producto->is_type( 'variation' ) ) {
							$parent = wc_get_product( $producto->get_parent_id() );
							if ( ( ! empty( array_intersect( $parent->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'no' ) || 
								( empty( array_intersect( $parent->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'yes' ) ) {
								return false;
							}
						} else {
							if ( ( ! empty( array_intersect( $producto->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'no' ) || 
								( empty( array_intersect( $producto->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'yes' ) ) {
								return false;
							}
						}
					}

                    //No atiende a las atributos excluidos
					if ( ! empty( $this->atributos_excluidos ) ) {
                        $atributos_excluidos    = [];
                        foreach ( $this->atributos_excluidos as $atributos ) {
                            $atributos                              = explode( "-", $atributos );
                            $atributos_excluidos[ $atributos[ 0 ] ] = $atributos[ 1 ]; 
                        }
                        
                        if ( ( ! empty( array_intersect_assoc( $producto->get_attributes(), $atributos_excluidos ) ) && $this->tipo_atributos == 'no' ) || 
                            ( empty( array_intersect_assoc( $producto->get_attributes(), $atributos_excluidos ) ) && $this->tipo_atributos == 'yes' ) ) {
                            return false;
                        }
					}

					//No atiende a las clases de envío excluidas
					if ( ! empty( $this->clases_excluidas ) ) {
						if ( ( ( in_array( $producto->get_shipping_class(), $this->clases_excluidas ) || ( in_array( "todas", $this->clases_excluidas ) && $producto->get_shipping_class() ) ) && $this->tipo_clases == 'no' ) ||
							( ! in_array( $producto->get_shipping_class(), $this->clases_excluidas ) && ! in_array( "todas", $this->clases_excluidas ) && $this->tipo_clases == 'yes' ) ) {
							$this->reduce_valores( $peso_total, $peso, $productos_totales, $valores, $precio_total, $producto );
							
							continue; 
						}
					}
					
					//Ajuste para los productos virtual y bundle
					if ( $producto->is_virtual() && ! isset( $valores[ 'bundled_by' ] ) ) {
						$peso_total			-= $peso;
						$productos_totales	-= $valores[ 'quantity' ];
						$precio_total		-= $precio;
					}

					//Volumen
					if ( $producto->get_length() && $producto->get_width() && $producto->get_height() ) {
						$volumen += $producto->get_length() * $producto->get_width() * $producto->get_height() * $valores[ 'quantity' ];
					}
					
					//Medidas
					$medidas[] = [
						'largo'		=> $producto->get_length(),
						'ancho'		=> $producto->get_width(),
						'alto'		=> $producto->get_height(),
						'cantidad'	=> $valores[ 'quantity' ],
					];
					
					//Almacena el valor del lado más grande
					if ( $producto->get_length() > $largo ) {
						$largo = $producto->get_length();
					}
					if ( $producto->get_width() > $ancho ) {
						$ancho = $producto->get_width();
					}
					if ( $producto->get_height() > $alto ) {
						$alto = $producto->get_height();
					}

					//Valor temporal que alamecena el peso, cantidad de productos o total del pedido (según configuración)
					$cantidad = ( $this->tipo_tarifas == "unidad" ) ? $valores[ 'quantity' ] : $peso;
					if ( $this->tipo_tarifas == "total" ) {
						$cantidad = $precio;
					}

					//Clase de envío
					if ( $producto->needs_shipping() ) {
						$clase = ( $producto->get_shipping_class() ) ? $producto->get_shipping_class() : 'sin-clase';
						//Inicializamos la clase general
						if ( ! isset ($clases[ 'todas' ] ) ) {
							$clases[ 'todas' ] = 0;
						}
						$clases[ 'todas' ] += $cantidad;
						//Creamos o inicializamos la clase correspondiente
						if ( ! isset( $clases[ $clase ] ) ) {
							$clases[ $clase ] = $cantidad;
						} else if ( $clase != 'todas' ) {
							$clases[ $clase ] += $cantidad;
						}
					}
				}

				//Comprobamos si está activo WPML para devolverlo al idioma que estaba activo
				if ( function_exists('icl_object_id') && ! function_exists( 'pll_the_languages' ) ) {
					do_action( 'wpml_switch_language', ICL_LANGUAGE_CODE );
				}
				
				//Reajusta el valor del peso total en caso de que se haya configurado cantidad de productos o total del pedido 
				if ( $this->tipo_tarifas == "unidad" ) {
					$peso_total = $productos_totales;
				} else if ( $this->tipo_tarifas == "total" ) {
					$peso_total = $precio_total;
				}

				//No hay productos a los que aplicar las tarifas
				if ( empty( $medidas ) && empty( $clases ) ) {
					return false;
				}

				//Obtenemos las tarifas
				$tarifas = $this->dame_tarifas( $clases );
				
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
					echo "Tarifas: " . print_r( $tarifas, true );
					echo "</pre>";
				}
				
				//Obtiene la tarifa
				$tarifa_mas_barata = $this->dame_tarifa_mas_barata( $peso_total, $volumen, $largo, $ancho, $alto, $medidas, $clases, $tarifas ); //Filtra las tarifas
				if ( empty( $tarifa_mas_barata ) ) {
					return false; //No hay tarifa
				}
				
				//Calculamos el importe total
				$importe = 0;
				if ( ! empty( $this->suma ) &&  $this->suma == "yes" ) {
					$importe = max( $tarifa_mas_barata );
				} else {
					foreach( $tarifa_mas_barata as $tarifa ) {
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
				
				if ( $this->cargo > 0 && ! strpos( $this->cargo, '%' ) ) { //Cargo adicional normal
					$suma_cargos += $this->cargo * $cargo_por_producto;
				} else if ( $this->cargo > 0 && strpos( $this->cargo, '%' ) && ! strpos( $this->cargo, '|' ) ) { //Cargo adicional porcentaje
					$suma_cargos += ( $importe * ( str_replace( '%', '', $this->cargo ) / 100 ) ) * $cargo_por_producto;
				} else if ( $this->cargo > 0 && strpos( $this->cargo, '%' ) && strpos( $this->cargo, '|' ) ) { //Porcentaje con mínimo y máximo
					//Recogemos los valores mínimo y máximo
					$porcentaje = explode( '|', $this->cargo );
					preg_match( '/min=[ \"|\' ](.*)[ \"|\' ][ \s+|$ ]/', $porcentaje[ 1 ], $minimo );
					preg_match( '/max=[ \"|\' ](.*)[ \"|\' ]$/', $porcentaje[ 1 ], $maximo );
					
					$calculo_de_porcentaje = ( $importe * ( str_replace( '%', '', $this->cargo ) / 100 ) ) * $cargo_por_producto;
					//Comprobamos el mínimo
					if ( isset( $minimo[ 1 ] ) && $minimo[ 1 ] > $calculo_de_porcentaje ) {
						$calculo_de_porcentaje = $minimo[ 1 ];
					}
					//Comprobamos el máximo
					if ( isset( $maximo[ 1 ] ) && $calculo_de_porcentaje > $maximo[ 1 ] ) {
						$calculo_de_porcentaje = $maximo[ 1 ];
					}
					//Añade el cargo
					$suma_cargos += $calculo_de_porcentaje;
				}

				//Actualizamos precio
				$importe	+= $suma_cargos;
				//¿Impuestos?
				$impuestos	= ( ! empty( $this->tax_status ) && $this->tax_status != 'none' ) ? '' : false;

				$tarifa = [
					'id'		=> $this->get_rate_id(),
					'label'		=> $this->title,
					'cost'		=> $importe,
					'taxes'		=> $impuestos,
					'calc_tax'	=> 'per_order'
				];
				
				$this->add_rate( $tarifa );
                
				do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $tarifa );
			}
			
			//Recoge las tarifas programadas
			public function dame_tarifas( $clases ) {
                $tarifas    = [];
                
				//Procesa las tarifas programadas
				if ( ! empty( $this->tarifas ) ) {
					foreach ( $this->tarifas as $indice => $opcion ) {
						$tarifa = preg_split( '~\s*\|\s*~', preg_replace( '/\s+/', '', $opcion ) );
	
						if ( sizeof( $tarifa ) < 2 ) { //Tarifa incorrecta o salto de línea
							continue;
						} else {
							//Inicializa variables
							$clase = 'sin-clase';
							
							//Medidas
							if ( preg_match( "/(\d+x\d+x\d+)/", $tarifa[ 0 ] ) ) { //El primer valor es una dimensión
								$tarifa[ 'medidas' ]	= strtolower( $tarifa[ 0 ] );
								unset( $tarifa[ 0 ] ); //Eliminamos las medidas
							}
							if ( isset( $tarifa[ 2 ] ) && preg_match( "/(\d+x\d+x\d+)/", $tarifa[ 2 ] ) ) { //El tercer valor es una dimensiones
								$tarifa[ 'medidas' ]	= strtolower( $tarifa[ 2 ] );
							}
							if ( isset( $tarifa[ 3 ] ) && preg_match( "/(\d+x\d+x\d+)/", $tarifa[ 3 ] ) ) { //El cuarto valor es una dimensiones
								$tarifa[ 'medidas' ]	= strtolower( $tarifa[ 3 ] );
								unset( $tarifa[ 3 ] ); //Eliminamos las medidas
							}
							
							//Clases de envío
							if ( isset( $tarifa[ 2 ] ) && ! preg_match( "/(\d+x\d+x\d+)/", $tarifa[ 2 ] ) && array_key_exists( $tarifa[ 2 ], $clases ) ) {
								$clase	= $tarifa[ 2 ];
							} else if ( isset( $tarifa[ 2 ] ) && ! preg_match( "/(\d+x\d+x\d+)/", $tarifa[ 2 ] ) && ! array_key_exists( $tarifa[ 2 ], $clases ) ) {
								$clase	= 'todas';					
							} else if ( ! isset( $tarifa[ 2 ] ) || ! preg_match( "/(\d+x\d+x\d+)/", $tarifa[ 2 ] ) ) {
								$clase	= 'sin-clase';					
							}

							//Pesos
							if ( isset( $tarifa[ 0 ] ) ) {
								$tarifa[ 'peso' ] = $tarifa[ 0 ];
								unset( $tarifa[ 0 ] ); //Eliminamos el peso
							}
							
							//Importes
							$tarifa[ 'importe' ] = $tarifa[ 1 ];
							unset( $tarifa[ 1 ] ); //Eliminamos el importe
							
							//Eliminamos las medidas o la clase de envío
							if ( isset( $tarifa[ 2 ] ) ) {
								unset( $tarifa[ 2 ] );
							}
							
							$tarifas[ $clase ][] = $tarifa;
						}
					}
				}

                return $tarifas;
			}

			//Selecciona la tarifa más barata
			public function dame_tarifa_mas_barata( $peso_total, $volumen_total, $largo, $ancho, $alto, $medidas, $clases, $tarifas ) {
				//Variables
				$tarifa_mas_barata			= [];
				$peso_parcial				= [];
				$peso_anterior				= 0;
				$largo_anterior				= 0;
				$ancho_anterior				= 0;
				$alto_anterior				= 0;
				$clase_de_envio_anterior	= '';

				//Prevenimos errores y reajustamos pesos
				foreach ( $clases as $clase => $peso ) {
					if ( $clase != 'todas' && apg_busca_en_array( $clase, $tarifas ) ) {
						$clases[ 'todas' ] -= $peso;
					}
				}                
 				if ( $clases[ 'todas' ] < 0.00001 ) { //Correct float values operations issues. Fix by lhall-amphibee: https://github.com/artprojectgroup/woocommerce-apg-weight-and-postcodestatecountry-shipping/pull/4
                    $clases[ 'todas' ] = 0;
                }
				if ( isset( $clases[ 'sin-clase' ] ) && $clases[ 'todas' ] > 0 ) {
					$clases[ 'sin-clase' ]	+= $clases[ 'todas' ];
				}

				//Aplicamos tarifas
				foreach ( $tarifas as $tipo => $tarifas_por_tipo ) {	
					//Variable
					$clase_de_envio			= $tipo;
					
					//Prevenimos errores
					if ( $clase_de_envio == 'sin-clase' && ! isset( $clases[ 'sin-clase' ] ) ) {
						$clase_de_envio = 'todas';
					}
					if ( $clase_de_envio_anterior	!= $clase_de_envio ) {
						$clase_de_envio_anterior	= $clase_de_envio;
						$peso_anterior				= 0;
						$largo_anterior				= 0;
						$ancho_anterior				= 0;
						$alto_anterior				= 0;
					}

					//Obtenemos la tarifa más barata
					foreach ( $tarifas_por_tipo as $tarifa ) {
						//Inicializa variables
						$calculo_volumetrico	= false;
						$excede_dimensiones		= false;
						unset( $medida_tarifa ); //Fix by DJ Team Digital
						
						//Comprobamos si tiene medidas
						if ( isset( $tarifa[ 'medidas' ] ) ) { 
							if ( ! isset( $tarifa[ 'peso' ] ) ) { //Son medidas sin peso
								$calculo_volumetrico	= true;
							}
							
							//Comprueba el volumen
							$medida_tarifa = explode( "x", $tarifa[ 'medidas' ] );
							if ( ( $largo > $medida_tarifa[ 0 ] || $ancho > $medida_tarifa[ 1 ] || $alto > $medida_tarifa[ 2 ] ) || 
								$volumen_total > ( $medida_tarifa[ 0 ] * $medida_tarifa[ 1 ] * $medida_tarifa[ 2 ] ) ) {
								$excede_dimensiones = true; //Excede el tamaño o volumen máximo
							}
						}

						if ( ! $calculo_volumetrico && ! $excede_dimensiones ) { //Es un peso
							if ( ( ! $peso_anterior && $tarifa[ 'peso' ] >= $clases[ $clase_de_envio ] ) || 
								( $tarifa[ 'peso' ] >= $clases[ $clase_de_envio ] && $clases[ $clase_de_envio ] > $peso_anterior ) ) {
								$tarifa_mas_barata[ $clase_de_envio ] = $tarifa[ 'importe' ];
                                if ( $this->debug == 'yes' ) {
                                    echo "Es un peso: <pre>";
                                    echo "Peso anterior: $peso_anterior <br />";
                                    print_r( $tarifa );
                                    echo "</pre>";
                                }
							} else if ( $this->maximo == "yes" && ( empty( $tarifa_mas_barata[ $clase_de_envio ] ) || $clases[ $clase_de_envio ] > $peso_anterior ) ) { //El peso es mayor que el de la tarifa máxima
								$tarifa_mas_barata[ $clase_de_envio ] = $tarifa[ 'importe' ];
                                if ( $this->debug == 'yes' ) {
                                    echo "Es un peso que excede la tarifa máxima: <pre>";
                                    echo "Peso anterior: $peso_anterior <br />";
                                    print_r( $tarifa );
                                    echo "</pre>";
                                }
							}
							
							//Guardamos el peso actual
							$peso_anterior = $tarifa[ 'peso' ];
						} else if ( $calculo_volumetrico && ! $excede_dimensiones ) { //Es una medida
							if ( isset( $tarifa[ 'medidas' ] ) ) { 
								$medida_tarifa = explode( "x", $tarifa[ 'medidas' ] );
								$volumen = $medida_tarifa[ 0 ] * $medida_tarifa[ 1 ] * $medida_tarifa[ 2 ];

								if ( ! $largo_anterior || ( ( $volumen > $volumen_total ) && ( $medida_tarifa[ 0 ] >= $largo && $largo > $largo_anterior ) && ( $medida_tarifa[ 1 ] >= $ancho && $ancho > $ancho_anterior ) && ( $medida_tarifa[ 2 ] >= $alto && $alto > $alto_anterior ) ) ) {
									$tarifa_mas_barata[ $clase_de_envio ] = $tarifa[ 'importe' ];									
                                    if ( $this->debug == 'yes' ) {
                                        echo "Es una medida: <pre>";
                                        echo "Medida anterior: $largo_anterior x $ancho_anterior x $alto_anterior <br />";
                                        print_r( $tarifa );
                                        echo "</pre>";
                                    }
								} else if ( $this->maximo == "yes" && ( empty( $tarifa_mas_barata[ $clase_de_envio ] ) || ( $largo > $largo_anterior && $ancho > $ancho_anterior && $alto > $alto_anterior ) ) ) { //Las medidas son mayores que la de la tarifa máxima
									$tarifa_mas_barata[ $clase_de_envio ] = $tarifa[ 'importe' ];
                                    if ( $this->debug == 'yes' ) {
                                        echo "Es una medida que excede la tarifa máxima: <pre>";
                                        echo "Medida anterior: $largo_anterior x $ancho_anterior x $alto_anterior <br />";
                                        print_r( $tarifa );
                                        echo "</pre>";
                                    }
								}

								//Guardamos las medidas actuales
								$largo_anterior	= $medida_tarifa[ 0 ];
								$ancho_anterior	= $medida_tarifa[ 1 ];
								$alto_anterior	= $medida_tarifa[ 2 ];
							}
						} else if ( $this->maximo == "yes" && ( empty( $tarifa_mas_barata[ $clase_de_envio ] ) || $tarifa_mas_barata[ $clase_de_envio ] < $tarifa[ 'importe' ] ) ) { //Las medidas son mayores que la de la tarifa máxima
							$tarifa_mas_barata[ $clase_de_envio ] = $tarifa[ 'importe' ];
                            if ( $this->debug == 'yes' ) {
                                echo "Es una medida que excede la tarifa máxima: <pre>";
                                echo "Medida anterior: $largo_anterior x $ancho_anterior x $alto_anterior <br />";
                                print_r( $tarifa );
                                echo "</pre>";
                            }
						}
					}
				}

				//Prevenimos errores de duplicación de tarifas
				if ( $clases[ 'todas' ] == 0 && count( $tarifa_mas_barata ) > 1 ) {
					unset( $tarifa_mas_barata[ 'todas' ] );
				}
				
				//Se ha excedido la tarifa máxima
				if ( $this->maximo == "no" && ( ( $peso_anterior && $clases[ $clase_de_envio ] > $peso_anterior ) || ( $calculo_volumetrico && $excede_dimensiones ) ) ) {
					unset( $tarifa_mas_barata[ $clase_de_envio ] );
				}

				if ( ! empty( $tarifa_mas_barata ) ) {
					return $tarifa_mas_barata;
				} else {
					return [];
				}
			}
		}
	}
	add_action( 'plugins_loaded', 'apg_shipping_inicio', 0 );
} else {
	add_action( 'admin_notices', 'apg_shipping_requiere_wc' );
}

//Busca en un array multidimensional
function apg_busca_en_array( $busqueda, $array_de_busqueda, $estricto = true ) {
    if ( is_array( $array_de_busqueda ) || is_object( $array_de_busqueda ) ) {
        foreach ( $array_de_busqueda as $indice => $valor_a_comparar ) {
            if ( ( $estricto ? $indice === $busqueda : $indice == $busqueda ) || 
                ( is_array( $valor_a_comparar ) && apg_busca_en_array( $busqueda, $valor_a_comparar, $estricto ) ) ) {
                return true;
            }
        }
    }

	return false;
}

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function apg_shipping_requiere_wc() {
	global $apg_shipping;
		
	echo '<div class="error fade" id="message"><h3>' . $apg_shipping[ 'plugin' ] . '</h3><h4>' . __( "This plugin require WooCommerce active to run!", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_apg_shipping );
}

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_shipping_desinstalar() {
    global $wpdb;
    
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%woocommerce_apg_shipping_%'" );
	delete_transient( 'apg_shipping_plugin' );
}
register_uninstall_hook( __FILE__, 'apg_shipping_desinstalar' );
