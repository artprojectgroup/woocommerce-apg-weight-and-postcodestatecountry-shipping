<?php
/*
Plugin Name: WC - APG Weight Shipping
Requires Plugins: woocommerce
Version: 3.2
Plugin URI: https://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/
Description: Add to WooCommerce the calculation of shipping costs based on the order weight and postcode, province (state) and country of customer's address. Lets you add an unlimited shipping rates. Created from <a href="https://profiles.wordpress.org/andy_p/" target="_blank">Andy_P</a> <a href="https://wordpress.org/plugins/awd-weightcountry-shipping/" target="_blank"><strong>AWD Weight/Country Shipping</strong></a> plugin and the modification of <a href="https://wordpress.org/support/profile/mantish" target="_blank">Mantish</a> published in <a href="https://gist.github.com/Mantish/5658280" target="_blank">GitHub</a>.
Author URI: https://artprojectgroup.es/
Author: Art Project Group
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.9
WC requires at least: 5.6
WC tested up to: 9.9

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
define( 'VERSION_apg_shipping', '3.2' );

//Funciones generales de APG
include_once( 'includes/admin/funciones-apg.php' );

//¿Está activo WooCommerce?
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
            public $categorias_de_producto      = [];
            public $etiquetas_de_producto       = [];
            public $clases_de_envio             = [];
            public $roles_de_usuario            = [];
            public $metodos_de_envio            = [];
            public $metodos_de_pago             = [];
            public $atributos                   = [];
            public $clases_de_envio_tarifas     = "";
            
			public function __construct( $instance_id = 0 ) {
				$this->id					= 'apg_shipping';
				$this->instance_id			= absint( $instance_id );
				$this->method_title			= __( "APG Shipping", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
				$this->method_description	= __( 'Lets you calculate shipping cost based on Postcode/State/Country and weight of the cart. Lets you set an unlimited weight bands on per postcode/state/country basis and group the groups that that share same delivery cost/bands.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '<span class="apg-weight-marker"></span>';
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
					$campos[]  = 'activo';
				}
				foreach ( $campos as $campo ) {
					$this->$campo  = $this->get_option( $campo );
				}
                $this->tarifas  = (array) explode( "\n", $this->tarifas ?? '' );
                
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
			
			//Función que lee y devuelve las categorías/etiquetas de producto
			public function apg_shipping_dame_datos_de_producto( $tipo ) {
                if ( ! in_array( $tipo, [ 'categorias_de_producto', 'etiquetas_de_producto' ], true ) ) {
                    return;
                }

                //Tipo de taxonomía
                $taxonomy   = ( $tipo === 'categorias_de_producto' ) ? 'product_cat' : 'product_tag';
                $transient  = 'apg_shipping_' . $taxonomy;

                //Obtiene las taxonomías desde la caché
                $this->{$tipo}  = get_transient( $transient );

                if ( empty( $this->{$tipo} ) ) {
                    $argumentos = [
                        'taxonomy'      => $taxonomy,
                        'orderby'       => 'name',
                        'show_count'    => 0,
                        'pad_counts'    => 0,
                        'hierarchical'  => 1,
                        'title_li'      => '',
                        'hide_empty'    => 0,
                    ];

                    $datos          = get_categories( $argumentos );
                    $this->{$tipo}  = [];

                    foreach ( $datos as $dato ) {
                        $this->{$tipo}[ $dato->term_id ] = $dato->name;
                    }

                    //Guarda en caché por un mes
                    set_transient( $transient, $this->{$tipo}, 30 * DAY_IN_SECONDS );
                }
			}
            
			//Función que lee y devuelve los tipos de clases de envío
			public function apg_shipping_dame_clases_de_envio() {
                //Obtiene las clases de envío desde la caché
                $clases_de_envio = get_transient( 'apg_shipping_clases_envio' );

                if ( empty( $clases_de_envio ) ) {
                    $clases                         = WC()->shipping->get_shipping_classes();
                    $this->clases_de_envio          = [];
                    $this->clases_de_envio_tarifas  = '';

                    if ( ! empty( $clases ) ) {
                        foreach ( $clases as $clase_de_envio ) {
                            $this->clases_de_envio[ esc_attr( $clase_de_envio->slug ) ] = $clase_de_envio->name;
                            $this->clases_de_envio_tarifas                              .= esc_attr( $clase_de_envio->slug ) . ' -> ' . $clase_de_envio->name . ', ';
                        }
                        $this->clases_de_envio_tarifas  = substr( $this->clases_de_envio_tarifas, 0, -2 ) . ".";
                    } else {
                        $this->clases_de_envio[]        = __( 'Select a class&hellip;', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
                    }

                    //Guarda en caché el array completo
                    $clases_de_envio = [
                        'clases'    => $this->clases_de_envio,
                        'tarifas'   => $this->clases_de_envio_tarifas,
                    ];
                    set_transient( 'apg_shipping_clases_envio', $clases_de_envio, 30 * DAY_IN_SECONDS );
                } else {
                    $this->clases_de_envio          = $clases_de_envio[ 'clases' ];
                    $this->clases_de_envio_tarifas  = $clases_de_envio[ 'tarifas' ];
                }
			}	

			//Función que lee y devuelve los roles de usuario
			public function apg_shipping_dame_roles_de_usuario() {
                //Obtiene los roles de usuario desde la caché
                $this->roles_de_usuario = get_transient( 'apg_shipping_roles_usuario' );

                if ( empty( $this->roles_de_usuario ) ) {
                    $wp_roles               = new WP_Roles();
                    $this->roles_de_usuario = [];

                    foreach ( $wp_roles->role_names as $rol => $nombre ) {
                        $this->roles_de_usuario[ $rol ] = $nombre;
                    }

                    //Guarda en caché por un mes
                    set_transient( 'apg_shipping_roles_usuario', $this->roles_de_usuario, 30 * DAY_IN_SECONDS );
                }
			}
            
			//Función que lee y devuelve los métodos de envío
			public function apg_shipping_dame_metodos_de_envio() {
                global $zonas_de_envio, $wpdb;
                
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
                $instancia  = isset( $_REQUEST[ 'instance_id' ] ) ? absint( wp_unslash( $_REQUEST[ 'instance_id' ] ) ) : absint( $this->instance_id );
                
                if ( ! $instancia ) {
                    return;
                }
                
                //Obtiene los métodos de envío desde la caché
                $cache_key              = 'apg_shipping_metodos_envio_' . $instancia;
                $this->metodos_de_envio = get_transient( $cache_key );

                if ( empty( $this->metodos_de_envio ) ) {
                    $this->metodos_de_envio = [];
                    $zona_de_envio          = wp_cache_get( "apg_zone_{$instancia}" );
                    if ( false === $zona_de_envio ) {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- No existe una función alternativa en WooCommerce
                        $zona_de_envio  = $wpdb->get_var( $wpdb->prepare( "SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE instance_id = %d LIMIT 1;", $instancia ) );
                        wp_cache_set( "apg_zone_{$instancia}", $zona_de_envio );
                    }

                    if ( ! empty( $zona_de_envio ) && is_array( $zonas_de_envio ) ) {
                        foreach ( $zonas_de_envio as $zona ) {
                            if ( $zona[ 'id' ] == $zona_de_envio && ! empty( $zona[ 'shipping_methods' ] ) ) {
                                foreach ( $zona[ 'shipping_methods' ] as $gasto_envio ) {
                                    if ( $gasto_envio->instance_id != $instancia ) {
                                        $this->metodos_de_envio[ $gasto_envio->instance_id ] = $gasto_envio->get_method_title();
                                    }
                                }
                            }
                        }
                    }

                    //Guarda la caché durante un mes
                    set_transient( $cache_key, $this->metodos_de_envio, 30 * DAY_IN_SECONDS );
                }
			}
			
			//Función que lee y devuelve los métodos de pago
			public function apg_shipping_dame_metodos_de_pago() {
                //Obtiene los métodos de pago desde la caché
                $this->metodos_de_pago  = get_transient( 'apg_shipping_metodos_pago' );

                if ( empty( $this->metodos_de_pago ) ) {
                    //Obtiene los métodos de pago
                    global $medios_de_pago;
                    $this->metodos_de_pago  = [];
                    if ( is_array( $medios_de_pago ) && ! empty( $medios_de_pago ) ) {
                        foreach( $medios_de_pago as $clave => $medio_de_pago ) {
                            $this->metodos_de_pago[ $medio_de_pago->id ] = $medio_de_pago->title;
                        }
                    }

                    //Guarda la caché durante un mes
                    set_transient( 'apg_shipping_metodos_pago',  $this->metodos_de_pago, 30 * DAY_IN_SECONDS );
                }
			}

			//Función que lee y devuelve los atributos
			public function apg_shipping_dame_atributos() {
                //Obtiene los atributos desde la caché
                $atributos  = get_transient( 'apg_shipping_atributos' );
                
                if ( is_array( $atributos ) && ! empty( $atributos ) ) {
                    $this->atributos    = $atributos;
                    return;
                }
                
                //Obtiene los atributos
                $atributos  = [];
                $taxonomias = wc_get_attribute_taxonomies();
                if ( $taxonomias ) {
                    foreach ( $taxonomias as $atributo ) {
                        $nombre_taxonomia = 'pa_' . $atributo->attribute_name;
                        $terminos         = get_terms( [ 'taxonomy' => $nombre_taxonomia, 'hide_empty' => false ] );

                        if ( ! is_wp_error( $terminos ) ) {
                            foreach ( $terminos as $termino ) {
                                $atributos[ esc_attr( $atributo->attribute_label ) ][ $nombre_taxonomia . '-' . $termino->slug ] = $termino->name;
                            }
                        }
                    }
                }

                $this->atributos = $atributos;

                //Guarda la caché durante un mes
                set_transient( 'apg_shipping_atributos', $atributos, 30 * DAY_IN_SECONDS );
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

                //Verifica roles excluidos
                $usuario_roles = wp_get_current_user()->roles;
                if ( !empty( $this->roles_excluidos ) ) {
                    $rol_invitado = empty( $usuario_roles );
                    foreach ( $rol_invitado ? [ 'invitado' ] : $usuario_roles as $rol ) {
                        if ( ( in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles === 'no' ) || ( !in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles === 'yes' ) ) {
                            return false;
                        }
                    }
                }

                //Variables
                $volumen    = 0;
                $largo      = 0;
                $ancho      = 0;
                $alto       = 0;
                $clases     = [];
                $medidas    = [];

                $peso_total         = WC()->cart->get_cart_contents_weight(); //Peso total del pedido
                $productos_totales  = WC()->cart->get_cart_contents_count(); //Productos totales del pedido
                $precio_total       = WC()->cart->get_displayed_subtotal(); //Precio total del pedido

                //WPML - Obtiene el nombre del idioma predeterminado de la clase de envío
                if ( function_exists( 'icl_object_id' ) && ! function_exists( 'pll_the_languages' ) ) {
                    global $sitepress;
                    
                    do_action( 'wpml_switch_language', $sitepress->get_default_language() );
                }

                //Toma distintos datos de los productos
                foreach ( WC()->cart->get_cart() as $valores ) {
                    $producto   = $valores[ 'data' ];
                    //Toma el peso del producto
                    $peso       = ( $producto->get_weight() > 0 ) ? $producto->get_weight() * $valores[ 'quantity' ] : 0;

                    //Exclusión por categoría
                    if ( ! empty( $this->categorias_excluidas ) ) {
                        $categorias = $producto->is_type( 'variation' ) ? wc_get_product( $producto->get_parent_id() )->get_category_ids() : $producto->get_category_ids();
                        if ( ( !empty( array_intersect( $categorias, $this->categorias_excluidas ) ) && $this->tipo_categorias === 'no' ) || ( empty( array_intersect( $categorias, $this->categorias_excluidas ) ) && $this->tipo_categorias === 'yes' ) ) {
                            return false;
                        }
                    }

                    //Exclusión por etiqueta
                    if ( ! empty( $this->etiquetas_excluidas ) ) {
                        $etiquetas  = $producto->is_type( 'variation' ) ? wc_get_product( $producto->get_parent_id() )->get_tag_ids() : $producto->get_tag_ids();
                        if ( ( !empty( array_intersect( $etiquetas, $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas === 'no' ) || ( empty( array_intersect( $etiquetas, $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas === 'yes' ) ) {
                            return false;
                        }
                    }

                    //Exclusión por atributos
                    if ( ! empty( $this->atributos_excluidos ) ) {
                        $excluidos  = [];
                        foreach ( $this->atributos_excluidos as $attr ) {
                            $partes = explode( '-', $attr );
                            if ( isset( $partes[ 0 ], $partes[ 1 ] ) ) {
                                $excluidos[ $partes[ 0 ] ]  = $partes[ 1 ];
                            }
                        }
                        if ( ( ! empty( array_intersect_assoc( $producto->get_attributes(), $excluidos ) ) && $this->tipo_atributos === 'no' ) || ( empty( array_intersect_assoc( $producto->get_attributes(), $excluidos ) ) && $this->tipo_atributos === 'yes' ) ) {
                            return false;
                        }
                    }

                    //Exclusión por clase de envío
                    if ( ! empty( $this->clases_excluidas ) ) {
                        $clase      = $producto->get_shipping_class();
                        $excluido   = in_array( $clase, $this->clases_excluidas ) || ( in_array( 'todas', $this->clases_excluidas ) && $clase );
                        $incluido   = ! in_array( $clase, $this->clases_excluidas ) && ! in_array( 'todas', $this->clases_excluidas );
                        if ( ( $excluido && $this->tipo_clases === 'no' ) || ( $incluido && $this->tipo_clases === 'yes' ) ) {
                            $this->reduce_valores( $peso_total, $peso, $productos_totales, $valores, $precio_total, $producto );
                            continue;
                        }
                    }

                    //Ajusta el precio de bundles antes de hacer nada más
                    if ( $producto->is_type( 'bundle' ) && method_exists( $producto, 'get_bundle_price' ) ) {
                        $precio = $producto->get_bundle_price( 'min' ) * $valores[ 'quantity' ];
                    }
                    
                    //Ajuste para productos virtual y bundle
                    if ( $producto->is_virtual() && !isset( $valores[ 'bundled_by' ] ) ) {
                        $peso_total         -= $peso;
                        $productos_totales  -= $valores[ 'quantity' ];
                        $precio_total       -= $producto->get_price() * $valores[ 'quantity' ];
                    }

                    //Volumen
                    if ( $producto->get_length() && $producto->get_width() && $producto->get_height() ) {
                        $volumen    += $producto->get_length() * $producto->get_width() * $producto->get_height() * $valores[ 'quantity' ];
                    }

                    //Medidas
                    $medidas[]  = [
                        'largo'     => $producto->get_length(),
                        'ancho'     => $producto->get_width(),
                        'alto'      => $producto->get_height(),
                        'cantidad'  => $valores[ 'quantity' ],
                    ];

                    //Almacena el valor del lado más grande
                    $largo  = max( $largo, $producto->get_length() );
                    $ancho  = max( $ancho, $producto->get_width() );
                    $alto   = max( $alto, $producto->get_height() );

                    //Valor temporal que alamecena el peso, cantidad de productos o total del pedido (según configuración)
                    $cantidad   = $this->tipo_tarifas === 'unidad' ? $valores[ 'quantity' ] : ( $this->tipo_tarifas === 'total' ? $producto->get_price() * $valores[ 'quantity' ] : $peso );

                    //Clase de envío
                    if ( $producto->needs_shipping() ) {
                        $clase              = $producto->get_shipping_class() ? : 'sin-clase';
                        $clases[ 'todas' ]  = isset( $clases[ 'todas' ] ) ? $clases[ 'todas' ] + $cantidad : $cantidad;
                        $clases[ $clase ]   = isset( $clases[ $clase ] ) ? $clases[ $clase ] + $cantidad : $cantidad;
                    }
                }

                //WPML - Retome el idioma actual
                if ( function_exists( 'icl_object_id' ) && ! function_exists( 'pll_the_languages' ) ) {
                    do_action( 'wpml_switch_language', ICL_LANGUAGE_CODE );
                }

                //Reajusta el valor del peso total en caso de que se haya configurado cantidad de productos o total del pedido 
                if ( $this->tipo_tarifas === 'unidad' ) {
                    $peso_total = $productos_totales;
                } elseif ( $this->tipo_tarifas === 'total' ) {
                    $peso_total = $precio_total;
                }

                //No hay productos a los que aplicar las tarifas
                if ( empty( $medidas ) && empty( $clases ) ) {
                    return false;
                }

                //Genera las tarifas y obtiene la tarifa
                $tarifas            = $this->dame_tarifas( $clases );
                $tarifa_mas_barata  = $this->dame_tarifa_mas_barata( $peso_total, $volumen, $largo, $ancho, $alto, $medidas, $clases, $tarifas );

                //No hay tarifa
                if ( empty( $tarifa_mas_barata ) ) {
                    return false;
                }

                //Importe base
                $importe    = 0;
                foreach ( $tarifa_mas_barata as $tarifa ) {
                    $importe    += is_array( $tarifa ) && isset( $tarifa[ 'importe' ] ) ? $tarifa[ 'importe' ] : ( float ) $tarifa;
                }

                if ( ! empty( $this->suma ) && $this->suma === 'yes' ) {
                    $importe    = max( array_map( function ( $t ) {
                        return is_array( $t ) && isset( $t[ 'importe' ] ) ? ( float ) $t[ 'importe' ] : ( float ) $t;
                    }, $tarifa_mas_barata ) );
                }

                //Cargos adicionales
                $suma_cargos    = 0;
                if ( $this->fee > 0 ) {
                    $suma_cargos    += $this->fee;
                }

                $cargo_por_producto = ( $this->tipo_cargo === 'no' ) ? 1 : WC()->cart->get_cart_contents_count();

                if ( $this->cargo > 0 && ! strpos( $this->cargo, '%' ) ) {
                    $suma_cargos    += $this->cargo * $cargo_por_producto;
                } elseif ( $this->cargo > 0 && strpos( $this->cargo, '%' ) && ! strpos( $this->cargo, '|' ) ) {
                    $suma_cargos    += ( $importe * ( str_replace( '%', '', $this->cargo ) / 100 ) ) * $cargo_por_producto;
                } elseif ( $this->cargo > 0 && strpos( $this->cargo, '|' ) ) {
                    $porcentaje     = explode( '|', $this->cargo ?? '' );
                    preg_match( '/min=["\']?([\d.]+)["\']?/', $porcentaje[ 1 ], $min );
                    preg_match( '/max=["\']?([\d.]+)["\']?/', $porcentaje[ 1 ], $max );
                    $calc   = ( $importe * ( str_replace( '%', '', $this->cargo ) / 100 ) ) * $cargo_por_producto;
                    if ( isset( $min[ 1 ] ) && $min[ 1 ] > $calc ) {
                        $calc   = $min[ 1 ];
                    }
                    if ( isset( $max[ 1 ] ) && $calc > $max[ 1 ] ) {
                        $calc   = $max[ 1 ];
                    }
                    $suma_cargos    += $calc;
                }

                //Importe final
                $importe    += $suma_cargos;

                //Devuelve la tarifa
                $tarifa = [
                    'id'        => $this->get_rate_id(),
                    'label'     => $this->title,
                    'cost'      => $importe,
                    'taxes'     => ( $this->tax_status !== 'none' ) ? '' : false,
                    'calc_tax'  => 'per_order',
                ];

                $this->add_rate( $tarifa );

                // Limpia la caché si cambia el total
                if ( WC()->session ) {
                    WC()->session->__unset( 'apg_debugs_' . $this->instance_id );
                }

                do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $tarifa );
            }

			//Recoge las tarifas programadas y genera el array de tarifas interpretando múltiples formatos
            public function dame_tarifas( $clases ) {
                $tarifas = [];

                //Procesa las tarifas programadas
                if ( ! empty( $this->tarifas ) ) {
                    foreach ( $this->tarifas as $indice => $opcion ) {
                        $opcion = trim( $opcion );

                        //Formato repetitivo: 10+2-20|14+1.5|clase|medidas o 10+2|14+1.5|clase|medidas
                        if ( preg_match( '/^(\d+(?:\.\d+)?)\+(\d+(?:\.\d+)?)(?:\-(\d+(?:\.\d+)?))?\|(\d+(?:\.\d+)?)\+(\d+(?:\.\d+)?)(?:\|([^\|]+))?(?:\|(\d+x\d+x\d+))?$/', $opcion, $match ) ) {
                            $peso_inicio    = ( float ) $match[ 1 ];
                            $peso_salto     = ( float ) $match[ 2 ];
                            $iteraciones    = isset( $match[ 3 ] ) && $match[ 3 ] !== '' ? ( float ) $match[ 3 ] : $peso_inicio + $peso_salto * 10;
                            $precio_inicio  = ( float ) $match[ 4 ];
                            $precio_salto   = ( float ) $match[ 5 ];
                            $clase_input    = isset( $match[ 6 ] ) ? $match[ 6 ] : '';
                            $medidas        = isset( $match[ 7 ] ) ? strtolower( $match[ 7 ] ) : null;

                            //Determina la clase
                            if ( $clase_input && array_key_exists( $clase_input, $clases ) ) {
                                $clase = $clase_input;
                            } elseif ( $clase_input !== '' ) {
                                $clase = 'todas';
                            } else {
                                $clase = 'sin-clase';
                            }

                            //Iteraciones
                            for ( $i = 0; $i <= $iteraciones; $i++ ) {
                                $peso_min   = $peso_inicio + $i * $peso_salto;
                                $peso_max   = $peso_min + $peso_salto;
                                $precio     = $precio_inicio + $i * $precio_salto;

                                //Genera la tarifa
                                $tarifa     = [
                                    'peso_min'  => $peso_min,
                                    'peso'      => $peso_max,
                                    'importe'   => number_format( $precio, 2, '.', '' )
                                ];
                                if ( $medidas ) {
                                    $tarifa[ 'medidas' ]    = $medidas;
                                }
                                $tarifas[ $clase ][]    = $tarifa;
                            }

                            continue;
                        }

                        //Formato rango: X-Y|P[|clase][|medidas]
                        if ( preg_match( '/^(\d+(?:\.\d+)?)\-(\d+(?:\.\d+)?)\|(\d+(?:\.\d+)?)(?:\|([^\|]+))?(?:\|(\d+x\d+x\d+))?$/', $opcion, $match ) ) {
                            $peso_min       = ( float )$match[ 1 ];
                            $peso_max       = ( float )$match[ 2 ];
                            $importe        = $match[ 3 ];
                            $clase_input    = isset( $match[ 4 ] ) ? $match[ 4 ] : '';
                            $medidas        = isset( $match[ 5 ] ) ? strtolower( $match[ 5 ] ) : null;

                            //Determina la clase
                            if ( $clase_input && array_key_exists( $clase_input, $clases ) ) {
                                $clase = $clase_input;
                            } elseif ( $clase_input !== '' ) {
                                $clase = 'todas';
                            } else {
                                $clase = 'sin-clase';
                            }

                            //Genera la tarifa
                            $tarifa = [
                                'peso_min'  => $peso_min,
                                'peso'      => $peso_max,
                                'importe'   => $importe
                            ];
                            if ( $medidas ) {
                                $tarifa[ 'medidas' ]    = $medidas;
                            }

                            $tarifas[ $clase ][]    = $tarifa;

                            continue;
                        }

                        //Formato estándar y mixto
                        $tarifa_raw = preg_split( '/\s*\|\s*/', preg_replace( '/\s+/', '', $opcion ) );
                        if ( count( $tarifa_raw ) < 2 ) {
                            continue;
                        }

                        $peso       = $tarifa_raw[ 0 ] ?? null;
                        $importe    = $tarifa_raw[ 1 ] ?? null;
                        $tercero    = $tarifa_raw[ 2 ] ?? null;
                        $cuarto     = $tarifa_raw[ 3 ] ?? null;

                        $clase      = 'sin-clase';
                        $medidas    = null;

                        //¿Tercer valor es medidas o clase?
                        if ( $tercero ) {
                            if ( preg_match( '/^\d+x\d+x\d+$/', $tercero ) ) {
                                $medidas    = strtolower( $tercero );
                            } elseif ( array_key_exists( $tercero, $clases ) ) {
                                $clase      = $tercero;
                            } elseif ( $tercero !== '' ) {
                                $clase      = 'todas';
                            }
                        }

                        //¿Cuarto valor es medidas?
                        if ( $cuarto && preg_match( '/^\d+x\d+x\d+$/', $cuarto ) ) {
                            $medidas    = strtolower( $cuarto );
                        }

                        //Valida peso e importe
                        if ( ! $peso || ! $importe ) {
                            continue;
                        }

                        //Genera la tarifa
                        $tarifa = [
                            'peso'      => $peso,
                            'importe'   => $importe
                        ];
                        if ( $medidas ) {
                            $tarifa[ 'medidas' ]    = $medidas;
                        }
                        $tarifas[ $clase ][]    = $tarifa;
                    }
                }

                return $tarifas;
            }

            //Selecciona la tarifa más barata
            public function dame_tarifa_mas_barata( $peso_total, $volumen_total, $largo, $ancho, $alto, $medidas, $clases, $tarifas ) {
                //Activa la depuración
                if ( $this->debug === 'yes' && WC()->session ) {
                    WC()->session->set( 'apg_shipping_debug_' . $this->instance_id, true );
                }
                
                //Variables
                $tarifa_mas_barata      = [];
                $peso_anterior          = [];
                $debugs_key             = 'apg_debugs_' . $this->instance_id;
                $session                = WC()->session;
                $debugs_mostrados       = $session ? $session->get( $debugs_key, [] ) : [];
                static $debug_mostrado  = false;

                //Muestra información de depuración
                if ( $this->debug == 'yes' && empty( $debugs_mostrados[ '__resumen__' ] ) && $debug_mostrado === false && ! defined( 'REST_REQUEST' ) ) {
                    echo '<div id="apg-shipping-debug-wrapper">';
                    echo '<h4>' . esc_html__( 'Calculated totals.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</h4>';
                    echo '<p><strong>' . esc_html__( 'Shipping method:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . ' ' . esc_html( $this->method_title ) . " - ID: " .  esc_html( $this->instance_id ) . '.</strong></p>';
                    echo '<ul>';
                    echo '<li>' . esc_html__( 'Cart total weight:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . ' ' . esc_html( $peso_total ) . '</li>';
                    echo '<li>' . esc_html__( 'Cart total volume:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . ' ' . esc_html( $volumen_total ) . '</li>';
                    echo '<li>' . esc_html__( 'Cart total length:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . ' ' . esc_html( $largo ) . '</li>';
                    echo '<li>' . esc_html__( 'Cart total width:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . ' ' . esc_html( $ancho ) . '</li>';
                    echo '<li>' . esc_html__( 'Cart total height:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . ' ' . esc_html( $alto ) . '</li>';
                    echo '</ul>';
                    echo '<h4>' . esc_html__( 'Processed data.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</h4>';
                    echo '<ul>';
                    echo '<li>' . esc_html__( 'Processed measures:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '<pre>' . esc_html( print_r( $medidas, true ) ) . '</pre></li>';
                    echo '<li>' . esc_html__( 'Processed classes:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '<pre>' . esc_html( print_r( $clases, true ) ) . '</pre></li>';
                    echo '<li>' . esc_html__( 'Processed rates:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '<pre>' . esc_html( print_r( $tarifas, true ) ) . '</pre></li>';
                    echo '</ul>';
                }

                //Ordena por prioridad: clases reales > sin-clase > todas
                $prioridad  = array_merge( array_diff( array_keys( $clases ), [ 'sin-clase', 'todas' ] ), array_intersect( [ 'sin-clase', 'todas' ], array_keys( $tarifas ) ) );
                
                //Aplica tarifas en orden de prioridad
                foreach ( $prioridad as $clase_envio ) {
                    if ( ! isset( $tarifas[ $clase_envio ] ) ) {
                        continue;
                    }

                    $tarifas_por_clase  = $tarifas[ $clase_envio ];
                    $peso_clase         = isset( $clases[ $clase_envio ] ) ? ( float )$clases[ $clase_envio ] : $peso_total;

                    $peso_anterior[ $clase_envio ] = 0;

                    foreach ( $tarifas_por_clase as $tarifa ) {
                        //Formato X-Y
                        if ( isset( $tarifa[ 'peso_min' ], $tarifa[ 'peso_max' ] ) ) {
                            if ( $peso_clase >= $tarifa[ 'peso_min' ] && $peso_clase <= $tarifa[ 'peso_max' ] ) {
                                $tarifa_mas_barata[ $clase_envio ]  = $tarifa[ 'importe' ];
                                break;
                            }
                            
                            continue;
                        }

                        //Formato X+
                        if ( isset( $tarifa[ 'peso' ] ) && is_numeric( $tarifa[ 'peso' ] ) ) {
                            if ( $peso_clase >= $tarifa[ 'peso' ] && $tarifa[ 'peso' ] > $peso_anterior[ $clase_envio ] ) {
                                $tarifa_mas_barata[ $clase_envio ]  = $tarifa[ 'importe' ];
                                $peso_anterior[ $clase_envio ]      = $tarifa[ 'peso' ];
                            }
                            
                            continue;
                        }

                        //Formato X+Y o X+Y-Z
                        if ( isset( $tarifa[ 'peso_min' ], $tarifa[ 'peso_max' ], $tarifa[ 'importe' ] ) ) {
                            if ( $peso_clase >= $tarifa[ 'peso_min' ] && $peso_clase < $tarifa[ 'peso_max' ] ) {
                                $tarifa_mas_barata[ $clase_envio ]  = $tarifa[ 'importe' ];
                                break;
                            }
                            
                            continue;
                        }
                    }

                    //Si no se encuentra tarifa y está activo el modo máximo
                    if ( empty( $tarifa_mas_barata[ $clase_envio ] ) && $this->maximo === 'yes' ) {
                        $ultima = end( $tarifas_por_clase );
                        if ( isset( $ultima[ 'importe' ] ) ) {
                            $tarifa_mas_barata[ $clase_envio ]  = $ultima[ 'importe' ];
                        }
                    }

                    //Depuración
                    if ( empty( $tarifa_mas_barata[ $clase_envio ] ) && $this->debug === 'yes' ) {
                        echo '<p><strong>' . esc_html__( 'No shipping rate found for class:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . ' ' . esc_html( $clase_envio ) . '</strong></p>';
                    }

                    //Si ya hemos encontrado una tarifa válida específica, paramos
                    if ( isset( $tarifa_mas_barata[ $clase_envio ] ) ) {
                        break;
                    }
                }

				//Muestra información de depuración
                if ( $this->debug == 'yes' && empty( $debugs_mostrados[ '__resumen__' ] ) && $debug_mostrado === false && ! defined( 'REST_REQUEST' ) ) {
                    echo '<h4>' . esc_html__( 'Selected rate:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</h4>';
                    echo '<pre>' . esc_html( print_r( $tarifa_mas_barata, true ) ) . '</pre>';
                    echo '</div>'; //Cierra la capa
                    echo '<p><button type="button" id="apg-copy-debug-button" style="margin-top:10px;">📋 ' . esc_html__( 'Copy full debug info', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</button></p>';
                    // translators: %1$s is the HTML link to the shipping method settings page with the method name as anchor text.
                    $mensaje = __( 'If you do not want these data to be displayed, disable the debug option in the settings of the %1$s method.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
                    echo '<p><strong>' . sprintf( esc_html( $mensaje ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&instance_id=' . $this->instance_id ) ) . '" target="_blank">' . esc_html( $this->method_title ) . '</a>' ) . '</strong></p>';
                    $debug_mostrado                     = true;
                    $debugs_mostrados[ '__resumen__' ]  = true;
                    if ( $session ) {
                        $session->set( $debugs_key, $debugs_mostrados );
                        $session->set( 'apg_shipping_debug_' . $this->instance_id, null );
                    }
                }

                return !empty( $tarifa_mas_barata ) ? $tarifa_mas_barata : [];
            }
        }
	}
	add_action( 'plugins_loaded', 'apg_shipping_inicio', 0 );
} else {
	add_action( 'admin_notices', 'apg_shipping_requiere_wc' );
}

//Añade soporte a Checkout y Cart Block
function apg_shipping_script_bloques() {
    //Evita ejecución en backend/editor REST
    if ( is_admin() || wp_doing_ajax() || defined( 'REST_REQUEST' ) ) {
        return; 
    }
    
	//Detecta bloques de WooCommerce para carrito o checkout
	$bloques   = function_exists( 'has_block' ) && ( has_block( 'woocommerce/cart', wc_get_page_id( 'cart' ) ) || has_block( 'woocommerce/checkout', wc_get_page_id( 'checkout' ) ) );

	if ( ! $bloques ) {
        return; //No se están usando bloques de carrito o checkout
	}

    $script_handle  = 'apg-shipping-bloques';
    if ( ! wp_script_is( $script_handle, 'enqueued' ) ) {
        wp_enqueue_script( $script_handle, plugins_url( 'assets/js/apg-shipping-bloques.js', DIRECCION_apg_shipping ), [ 'jquery' ], VERSION_apg_shipping, true );
        wp_localize_script( $script_handle, 'apg_shipping', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
    }
}
add_action( 'enqueue_block_assets', 'apg_shipping_script_bloques' );

//Añade la etiqueta a los bloques
function apg_shipping_ajax_datos() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
    $metodo = isset( $_POST[ 'metodo' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'metodo' ] ) ) : '';;
    if ( ! preg_match( '/^([a-zA-Z0-9_]+):(\d+)$/', $metodo, $method ) ) {
        wp_send_json_error( __( 'Invalid format', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) );
    }

    list( , $slug, $instance_id )   = $method;
    $opciones                       = get_option( "woocommerce_{$slug}_{$instance_id}_settings" );
    if ( ! is_array( $opciones ) ) {
        wp_send_json_error( __( 'No data available', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) );
    }
    
	//Tiempo de entrega
    $entrega    = $opciones[ 'entrega' ] ?? '';
	if ( ! empty( $entrega ) ) {
        // translators: %s is the estimated delivery time (e.g., "24-48 hours").
        $entrega    = ( apply_filters( 'apg_shipping_delivery', true ) ) ? sprintf( __( "Estimated delivery time: %s", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $entrega ) : $entrega;
    }
    wp_send_json_success( [
        'titulo'    => $opciones[ 'title' ] ?? ucfirst( $slug ),
        'entrega'   => $entrega,
        'icono'     => $opciones[ 'icono' ] ?? '',
        'muestra'   => $opciones[ 'muestra_icono' ] ?? '',
    ] );
}
add_action( 'wp_ajax_apg_shipping_ajax_datos', 'apg_shipping_ajax_datos' );
add_action( 'wp_ajax_nopriv_apg_shipping_ajax_datos', 'apg_shipping_ajax_datos' );

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function apg_shipping_requiere_wc() {
	global $apg_shipping;
		
    echo '<div class="error fade" id="message">';
    echo '<h3>' . esc_html( $apg_shipping[ 'plugin' ] ) . '</h3>';
    echo '<h4>' . esc_html__( 'This plugin requires WooCommerce to be active in order to run!', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</h4>';
    echo '</div>';
	deactivate_plugins( DIRECCION_apg_shipping );
}

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_shipping_desinstalar() {
    global $wpdb;
    
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Limpieza forzada de opciones temporales propias del plugin
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%woocommerce_apg_shipping_%'" );
}
register_uninstall_hook( __FILE__, 'apg_shipping_desinstalar' );
