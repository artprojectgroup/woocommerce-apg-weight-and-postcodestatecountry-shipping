<?php
/*
Plugin Name: WC - APG Weight Shipping
Requires Plugins: woocommerce
Version: 3.5.0.1
Plugin URI: https://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/
Description: Add to WooCommerce the calculation of shipping costs based on the order weight and postcode, province (state) and country of customer's address. Lets you add an unlimited shipping rates. Created from <a href="https://profiles.wordpress.org/andy_p/" target="_blank">Andy_P</a> <a href="https://wordpress.org/plugins/awd-weightcountry-shipping/" target="_blank"><strong>AWD Weight/Country Shipping</strong></a> plugin and the modification of <a href="https://wordpress.org/support/profile/mantish" target="_blank">Mantish</a> published in <a href="https://gist.github.com/Mantish/5658280" target="_blank">GitHub</a>.
Author URI: https://artprojectgroup.es/
Author: Art Project Group
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.9
WC requires at least: 5.6
WC tested up to: 10.1.0

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
define( 'VERSION_apg_shipping', '3.5.0.1' );

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
				$this->method_title			= __( 'APG Shipping', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
				$this->method_description	= __( 'Lets you calculate shipping cost based on Postcode/State/Country and weight of the cart. Lets you set an unlimited weight bands on per postcode/state/country basis and group the groups that that share same delivery cost/bands.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '<span class="apg-weight-marker"></span>';
				$this->supports				= [
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
                    'shipping-calculation',
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
                        'hide_empty'    => false,
                    ];

                    $datos          = get_categories( $argumentos );
                    $this->{$tipo}  = [];

                    foreach ( $datos as $dato ) {
                        $this->{$tipo}[ $dato->term_id ] = $dato->name;
                    }

                    set_transient( $transient, $this->{$tipo}, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
                }
			}
            
			//Función que lee y devuelve los tipos de clases de envío
			public function apg_shipping_dame_clases_de_envio() {
                //Obtiene las clases de envío desde la caché
                $clases_de_envio = get_transient( 'apg_shipping_clases_envio' );

                if ( empty( $clases_de_envio ) || ! isset( $clases_de_envio[ 'clases' ], $clases_de_envio[ 'tarifas' ] ) ) {
                    $clases                         = WC()->shipping->get_shipping_classes();
                    $this->clases_de_envio          = [];
                    $this->clases_de_envio_tarifas  = '';

                    if ( ! empty( $clases ) ) {
                        foreach ( $clases as $clase_de_envio ) {
                            $slug   = esc_attr( $clase_de_envio->slug );
                            $name   = $clase_de_envio->name;
                            
                            $this->clases_de_envio[ $slug ] = $name;
                            $this->clases_de_envio_tarifas  .= $slug . ' -> ' . $name . ', ';                            
                        }
                        //Elimina la última coma y añade punto final
                        $this->clases_de_envio_tarifas  = rtrim( $this->clases_de_envio_tarifas, ', ' ) . '.';
                    } else {
                        $this->clases_de_envio[]        = __( 'Select a class&hellip;', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
                        $this->clases_de_envio_tarifas  = '';
                    }

                    //Guarda en caché el array completo
                    $clases_de_envio = [
                        'clases'    => $this->clases_de_envio,
                        'tarifas'   => $this->clases_de_envio_tarifas,
                    ];
                    set_transient( 'apg_shipping_clases_envio', $clases_de_envio, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
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

                    if ( isset( $wp_roles->role_names ) && is_array( $wp_roles->role_names ) ) {
                        foreach ( $wp_roles->role_names as $rol => $nombre ) {
                            $this->roles_de_usuario[ $rol ] = $nombre;
                        }
                    }

                    set_transient( 'apg_shipping_roles_usuario', $this->roles_de_usuario, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
                }
			}
            
			//Función que lee y devuelve los métodos de envío
			public function apg_shipping_dame_metodos_de_envio() {
                global $zonas_de_envio, $wpdb;
                
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- No se puede usar nonce en este contexto (lectura segura con absint)
                $instancia  = isset( $_REQUEST[ 'instance_id' ] ) ? absint( wp_unslash( $_REQUEST[ 'instance_id' ] ) ) : absint( $this->instance_id );
                
                if ( ! $instancia || ! function_exists( 'WC' ) ) {
                    return;
                }
                
                //Obtiene los métodos de envío desde la caché
                $cache_key              = 'apg_shipping_metodos_envio_' . $instancia;
                $this->metodos_de_envio = get_transient( $cache_key );

                if ( empty( $this->metodos_de_envio ) ) {
                    $this->metodos_de_envio = [];
                    //Obtiene la zona de envío de esta instancia
                    $zona_de_envio          = wp_cache_get( "apg_zone_{$instancia}" );
                    if ( false === $zona_de_envio ) {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- No existe una función alternativa en WooCommerce
                        $zona_de_envio  = $wpdb->get_var( $wpdb->prepare( "SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE instance_id = %d LIMIT 1;", $instancia ) );
                        wp_cache_set( "apg_zone_{$instancia}", $zona_de_envio );
                    }

                    //Recorre zonas cacheadas
                    if ( ! empty( $zona_de_envio ) && is_array( $zonas_de_envio ) ) {
                        foreach ( $zonas_de_envio as $zona ) {
                            if ( ( int ) $zona[ 'id' ] === ( int ) $zona_de_envio && !empty( $zona[ 'shipping_methods' ] ) ) {
                                foreach ( $zona[ 'shipping_methods' ] as $metodo ) {
                                    if ( is_array( $metodo ) && isset( $metodo[ 'instance_id' ] ) && $metodo[ 'instance_id' ] != $instancia ) {
                                        $this->metodos_de_envio[ $metodo[ 'instance_id' ] ] = $metodo[ 'title' ];
                                    }
                                }
                            }
                        }
                    }

                    set_transient( $cache_key, $this->metodos_de_envio, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
                }
			}
			
			//Función que lee y devuelve los métodos de pago
			public function apg_shipping_dame_metodos_de_pago() {
                //Obtiene los métodos de pago desde la caché
                $this->metodos_de_pago  = get_transient( 'apg_shipping_metodos_de_pago' );

                if ( empty( $this->metodos_de_pago ) ) {
                    //Obtiene los métodos de pago
                    global $medios_de_pago;
                    $this->metodos_de_pago  = [];
                    if ( is_array( $medios_de_pago ) && ! empty( $medios_de_pago ) ) {
                        foreach( $medios_de_pago as $id => $titulo ) {
                            $this->metodos_de_pago[ $id ] = $titulo;
                        }
                    }

                    //Guarda la caché durante un mes
                    set_transient( 'apg_shipping_metodos_de_pago',  $this->metodos_de_pago, 30 * DAY_IN_SECONDS );
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
                if ( !empty( $taxonomias ) && is_array( $taxonomias ) ) {
                    foreach ( $taxonomias as $atributo ) {
                        if ( empty( $atributo->attribute_name ) || empty( $atributo->attribute_label ) ) {
                            continue;
                        }
                        
                        $nombre_taxonomia = 'pa_' . $atributo->attribute_name;
                        $terminos         = get_terms( [ 'taxonomy' => $nombre_taxonomia, 'hide_empty' => false ] );

                        if ( ! is_wp_error( $terminos ) && ! empty( $terminos ) ) {
                            foreach ( $terminos as $termino ) {
                                $atributos[ esc_attr( $atributo->attribute_label ) ][ $nombre_taxonomia . '-' . $termino->slug ] = $termino->name;
                            }
                        }
                    }
                }

                $this->atributos = $atributos;

                set_transient( 'apg_shipping_atributos', $atributos, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
			}
            
            //Reduce valores en categorías, etiquetas y clases de envío excluídas
			public function reduce_valores( &$peso_total, $peso, &$productos_totales, $valores, &$precio_total, $producto ) {
				$peso_total			-= $peso;
				$productos_totales	-= $valores[ 'quantity' ];
                $cantidad           = $valores[ 'quantity' ];

                if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
                    $precio_unitario    = ( WC()->cart->tax_display_cart === 'excl' ) ? $producto->get_price_excluding_tax() : $producto->get_price_including_tax();
                } elseif ( version_compare( WC_VERSION, '4.4', '<' ) ) {
                    $precio_unitario    = ( WC()->cart->tax_display_cart === 'excl' ) ? wc_get_price_excluding_tax( $producto ) : wc_get_price_including_tax( $producto );
                } else {
                    $precio_unitario    = ( WC()->cart->get_tax_price_display_mode() === 'excl' ) ? wc_get_price_excluding_tax( $producto ) :  wc_get_price_including_tax( $producto );
				}
                
                $precio_total   -= $precio_unitario * $cantidad;
			}

			//Habilita el envío
			public function is_available( $paquete ) {
                $disponible = true;

				//Comprueba si está activo el plugin
                if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
                    if ( isset( $this->activo ) && $this->activo === 'no' ) {
                        $disponible = false;
                    }
                } else {
                    if ( ! $this->is_enabled() ) {
                        $disponible = false;
                    }
                }

                return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $disponible, $paquete, $this );
			}
            
			//Calcula el gasto de envío
            public function calculate_shipping( $paquete = [] ) {
                //Recoge los datos
				$this->apg_shipping_obtiene_datos();

                //Validación por roles
                $roles_usuario  = wp_get_current_user()->roles;

                if ( ! empty( $this->roles_excluidos ) ) {
                    $es_invitado    = empty( $roles_usuario );

                    if ( ( $es_invitado && $this->tipo_roles === 'no' && in_array( 'invitado', $this->roles_excluidos ) ) || ( $es_invitado && $this->tipo_roles === 'yes' && !in_array( 'invitado', $this->roles_excluidos ) ) ) { //Usuario invitado
                        return false;
                    }

                    if ( ! $es_invitado ) {
                        foreach ( $roles_usuario as $rol ) {
                            if ( ( in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles === 'no' ) || ( !in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles === 'yes' ) ) {
                                return false;
                            }
                        }
                    }
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
                    $modo_impuestos = version_compare( WC_VERSION, '4.4', '<' ) ? WC()->cart->tax_display_cart : WC()->cart->get_tax_price_display_mode();
                    if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
                        $precio_unitario    = ( $modo_impuestos === 'excl' ) ? $producto->get_price_excluding_tax() : $producto->get_price_including_tax();
                    } else {
                        $precio_unitario    = ( $modo_impuestos === 'excl' ) ? wc_get_price_excluding_tax( $producto ) : wc_get_price_including_tax( $producto );
                    }

                    $precio = $precio_unitario * $valores[ 'quantity' ];

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
                
                //Limpieza del transient del icono para evitar datos obsoletos
                delete_transient( 'apg_shipping_icono_' . $this->instance_id );
                
                //Limpia la caché si cambia el total
                if ( WC()->session ) {
                    WC()->session->__unset( 'apg_debugs_' . $this->instance_id );
                }
				do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $tarifa );
			}

            //Recoge las tarifas programadas
            public function dame_tarifas( $clases ) {
                //Variables
                $tarifas        = [];

                if ( empty( $this->tarifas ) ) {
                    return $tarifas;
                }
                
                //Procesa las tarifas programadas
                foreach ( $this->tarifas as $opcion ) {
                    //Divide y limpia espacios
                    $partes = preg_split( '~\s*\|\s*~', preg_replace( '/\s+/', '', trim( $opcion ) ) );

                    //Tarifa incorrecta o salto de línea
                    if ( count( $partes ) < 2 ) {
                        continue;
                    }

                    //Reinicia variables
                    $tarifa = [];
                    $clase  = 'sin-clase';

                    //Detecta medidas en la tarifa
                    foreach ( $partes as $i => $valor ) {
                        if ( $this->apg_medida_valida( $valor ) ) {
                            $tarifa[ 'medidas' ]    = strtolower( $valor );
                            if ( $i === 0 || $i === 3 ) { //Comprueba si el primer o el cuarto valor es una dimensión
                                unset( $partes[ $i ] ); //Elimina las medidas
                            }
                            
                            continue;
                        }
                    }
                    
                    //Tarifas repetitivas del tipo PESO+INCREMENTO[-MAXIMO]|PRECIO+INCREMENTO[|CLASE][|MEDIDAS]
                    if ( isset( $partes[ 0 ], $partes[ 1 ] ) && preg_match( '/^(\d+(?:\.\d+)?)\+(\d+(?:\.\d+)?)(?:-(\d+))?$/', $partes[ 0 ], $tarifa_peso ) && preg_match( '/^(\d+(?:\.\d+)?)\+(\d+(?:\.\d+)?)/', $partes[ 1 ], $tarifa_importe ) ) {
                        $peso_inicial       = ( float ) $tarifa_peso[ 1 ];
                        $incremento         = ( float ) $tarifa_peso[ 2 ];
                        $maximo             = isset( $tarifa_peso[ 3 ] ) ? ( int ) $tarifa_peso[ 3 ] : 10;

                        $importe_inicial    = ( float ) $tarifa_importe[ 1 ];
                        $incremento_importe = ( float ) $tarifa_importe[ 2 ];

                        //Clase
                        $extra_clase        = isset( $partes[ 2 ] ) && ! $this->apg_medida_valida( $partes[ 2 ] ) ? $partes[ 2 ] : $clase;
                        
                        //Detecta medidas en la tarifa
                        foreach ( $partes as $i => $valor ) {
                            if ( $this->apg_medida_valida( $valor ) ) {
                                $medidas    = strtolower( $valor );
                            }
                        }

                        //Genera las tarifas
                        for ( $i = 0; $i <= $maximo; $i++ ) {
                            $tarifa = [
                                'peso'      => $peso_inicial + $incremento * $i,
                                'importe'   => round( $importe_inicial + $incremento_importe * $i, 2 ),
                            ];
                            
                            if ( isset( $medidas ) ) {
                                $tarifa[ 'medidas' ]    = $medidas;
                            }
                            
                            //Determina clase
                            $clase  = isset( $clases[ $extra_clase ] ) ? $extra_clase : 'todas';

                            $tarifas[ $clase ][] = $tarifa;
                        }

                        continue;
                    }
                    
                    //Asigna pesos
                    if ( isset( $partes[0] ) ) {
                        if ( preg_match( '/^(\d+)-(\d+)$/', $partes[ 0 ], $matches ) ) {
                            $tarifa[ 'peso_min' ]   = ( float ) $matches[ 1 ];
                            $tarifa[ 'peso' ]       = ( float ) $matches[ 2 ];
                        } else {
                            $tarifa[ 'peso' ]       = ( float ) $partes[ 0 ];
                        }
                        unset( $partes[ 0 ] );
                    }

                    //Asigna importes
                    $tarifa[ 'importe' ] = $partes[ 1 ];
                    unset( $partes[ 1 ] ); //Eliminamos el importe

                    //Clases de envío
                    if ( isset( $partes[ 2 ] ) && ! $this->apg_medida_valida( $partes[ 2 ] ) ) {
                        $valor  = $partes[ 2 ];

                        if ( isset( $clases[ $valor ] ) ) {
                            $clase  = $valor;
                        } else {
                            $clase  = 'todas';
                        }
                        unset( $partes[ 2 ] );
                    }

                    $tarifas[ $clase ][] = $tarifa;
                }

                return $tarifas;
            }
            
            //Selecciona la tarifa más barata
            public function dame_tarifa_mas_barata( $peso_total, $volumen_total, $largo, $ancho, $alto, $medidas, $clases, $tarifas ) {
                //Variables
                static $debug_mostrado      = false;
                $debugs_key                 = 'apg_debugs_' . $this->instance_id;
                $session                    = WC()->session;
                $debugs_mostrados           = $session ? $session->get( $debugs_key, [] ) : [];
                $tarifa_mas_barata          = [];
                $peso_anterior              = 0;
                $largo_anterior             = 0;
                $ancho_anterior             = 0;
                $alto_anterior              = 0;
                $clase_de_envio_anterior    = '';

                //Previene errores y reajusta pesos
                foreach ( $clases as $clase => $peso ) {
                    if ( $clase !== 'todas' && isset( $tarifas[ $clase ] ) ) {
                        $clases[ 'todas' ]  -= $peso;
                    }
                }
                if ( $clases[ 'todas' ] < 0.00001 ) { //Correct float values operations issues. Fix by lhall-amphibee: https://github.com/artprojectgroup/woocommerce-apg-weight-and-postcodestatecountry-shipping/pull/4
                    $clases[ 'todas' ]  = 0;
                }
                if ( isset( $clases[ 'sin-clase' ] ) && $clases[ 'todas' ] > 0 ) {
                    $clases[ 'sin-clase' ]  += $clases[ 'todas' ];
                }

                //Aplica tarifas
                foreach ( $tarifas as $tipo => $tarifas_por_tipo ) {
                    //Variable
                    $clase_de_envio = $tipo;

                    //Previene errores
                    if ( $clase_de_envio == 'sin-clase' && ! isset( $clases[ 'sin-clase' ] ) ) {
                        $clase_de_envio = 'todas';
                    }
                    if ( $clase_de_envio_anterior != $clase_de_envio ) {
                        $clase_de_envio_anterior    = $clase_de_envio;
                        $peso_anterior              = 0;
                        $largo_anterior             = 0;
                        $ancho_anterior             = 0;
                        $alto_anterior              = 0;
                    }

                    //Obtiene la tarifa más barata
                    foreach ( $tarifas_por_tipo as $tarifa ) {
                        //Inicializa variables
                        $calculo_volumetrico    = false;
                        $excede_dimensiones     = false;
                        unset( $medida_tarifa ); //Fix by DJ Team Digital

                        //Comprueba si tiene medidas
                        if ( isset( $tarifa[ 'medidas' ] ) && $this->apg_medida_valida( $tarifa[ 'medidas' ] ) ) {
                            if ( ! isset( $tarifa[ 'peso' ] ) ) { //Son medidas sin peso
                                $calculo_volumetrico    = true;
                            }

                            //Comprueba el volumen
                            $medida_tarifa  = array_map( 'floatval', explode( 'x', $tarifa[ 'medidas' ] ) );
                            if ( count( $medida_tarifa ) !== 3 || in_array( 0, $medida_tarifa, true ) ) {
                                continue;  //Medida malformada
                            }
                            list( $largo_tarifa, $ancho_tarifa, $alto_tarifa )  = $medida_tarifa;
                            
                            if ( $largo > $largo_tarifa || $ancho > $ancho_tarifa || $alto > $alto_tarifa || $volumen_total > ( $largo_tarifa * $ancho_tarifa * $alto_tarifa ) ) {
                                $excede_dimensiones = true; //Excede el tamaño o volumen máximo
                            }
                        }
                        
                        $valor_clase    = floatval( $clases[ $clase_de_envio ] ?? 0 );
                        $importe        = floatval( str_replace( ',', '.', $tarifa[ 'importe' ] ) );

                        if ( ! $calculo_volumetrico && ! $excede_dimensiones ) { //Es un peso
                            //Tramos definidos con peso mínimo y máximo (X-Y)
                            if ( isset( $tarifa[ 'peso_min' ], $tarifa[ 'peso' ] ) ) {
                                if ( $valor_clase >= $tarifa[ 'peso_min' ] && $valor_clase <= $tarifa[ 'peso' ] ) {
                                    if ( ! isset( $tarifa_mas_barata[ $clase_de_envio ] ) || $importe < $tarifa_mas_barata[ $clase_de_envio ] ) {
                                        $tarifa_mas_barata[ $clase_de_envio ] = $importe;
                                    }
                                }
                            //Tarifa simple con peso máximo
                            } elseif ( ! isset( $tarifa[ 'peso_min' ] ) && isset( $tarifa[ 'peso' ] ) ) {
                                if ( ( ! $peso_anterior && $tarifa[ 'peso' ] >= $valor_clase ) || ( $tarifa[ 'peso' ] >= $valor_clase && $valor_clase > $peso_anterior ) ) {
                                    if ( ! isset( $tarifa_mas_barata[ $clase_de_envio ] ) || $importe < $tarifa_mas_barata[ $clase_de_envio ] ) {
                                        $tarifa_mas_barata[ $clase_de_envio ] = $importe;
                                    }
                                }
                            }
                            
                            //Guarda el peso actual
                            $peso_anterior  = $tarifa[ 'peso' ];
                            
                        } elseif ( $calculo_volumetrico && ! $excede_dimensiones ) { //Es una medida
                            $volumen    = $largo_tarifa * $ancho_tarifa * $alto_tarifa;
                            if ( ! $largo_anterior || ( $volumen > $volumen_total && $largo_tarifa >= $largo && $largo > $largo_anterior && $ancho_tarifa >= $ancho && $ancho > $ancho_anterior && $alto_tarifa >= $alto && $alto > $alto_anterior ) ) {
                                $tarifa_mas_barata[ $clase_de_envio ]   = $importe;
                            } elseif ( $this->maximo == "yes" && ( empty( $tarifa_mas_barata[ $clase_de_envio ] ) || ( $largo > $largo_anterior && $ancho > $ancho_anterior && $alto > $alto_anterior ) ) ) { //Las medidas son mayores que la de la tarifa máxima
                                $tarifa_mas_barata[ $clase_de_envio ]   = $importe;
                            }
                            //Guarda las medidas actuales
                            $largo_anterior = $largo_tarifa;
                            $ancho_anterior = $ancho_tarifa;
                            $alto_anterior  = $alto_tarifa;

                        } elseif ( $this->maximo == "yes" && ( empty( $tarifa_mas_barata[ $clase_de_envio ] ) || $tarifa_mas_barata[ $clase_de_envio ] < $importe ) ) { //Las medidas son mayores que la de la tarifa máxima
                            $tarifa_mas_barata[ $clase_de_envio ] = $importe;
                        }
                    }
                }

                //Previene errores de duplicación de tarifas
                if ( isset( $tarifa_mas_barata[ 'todas' ] ) && $clases[ 'todas' ] == 0 && count( $tarifa_mas_barata ) > 1 ) {
                    unset( $tarifa_mas_barata[ 'todas' ] );
                }

                //Se ha excedido la tarifa máxima
                if ( $this->maximo == "no" && ( ( $peso_anterior && $valor_clase > $peso_anterior ) || ( $calculo_volumetrico && $excede_dimensiones ) ) ) {
                    unset( $tarifa_mas_barata[ $clase_de_envio ] );
                }
				
				//Si no se ha encontrado ninguna tarifa válida pero está marcada la opción "Mostrar el precio máximo"
				if ( $this->maximo === 'yes' && empty( $tarifa_mas_barata[ $clase_de_envio ] ) ) {
					foreach ( $tarifas as $clase => $tarifas_por_clase ) {
						$ultima_tarifa	= end( $tarifas_por_clase );
						if ( isset( $ultima_tarifa[ 'importe' ] ) ) {
							$tarifa_mas_barata[ $clase ]	= floatval( str_replace( ',', '.', $ultima_tarifa[ 'importe' ] ) );
						}
					}
				}
                
                //Muestra información de depuración a los administradores
				if ( $this->debug === 'yes' && current_user_can( 'manage_options' ) && ! is_admin() && ! wp_doing_ajax() && ! wp_is_json_request() && $debug_mostrado === false && empty( $debugs_mostrados[ '__resumen__' ] ) ) {
                    echo '<div id="apg-shipping-debug-wrapper">';
                    echo '<h4>' . esc_html__( 'Calculated totals.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</h4>';
                    echo '<p><strong>' . esc_html__( 'Shipping method:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . ' ' . esc_html( $this->method_title ) . ' - ID: ' . esc_html( $this->instance_id ) . '.</strong></p>';
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
                    echo '<h4>' . esc_html__( 'Selected rate:', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</h4>';
                    echo '<pre>' . esc_html( print_r( $tarifa_mas_barata, true ) ) . '</pre>';
                    echo '</div>';
                    echo '<p><button type="button" id="apg-copy-debug-button" style="margin-top:10px;">📋 ' . esc_html__( 'Copy full debug info', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</button></p>';

					// translators: %1$s: shipping method name with a link to its settings.
                    $mensaje = __( 'If you do not want these data to be displayed, disable the debug option in the settings of the %1$s method.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
                    echo '<p><strong>' . sprintf( esc_html( $mensaje ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&instance_id=' . $this->instance_id ) ) . '" target="_blank">' . esc_html( $this->method_title ) . '</a>' ) . '</strong></p>';
                    $debug_mostrado = true;
                    $debugs_mostrados[ '__resumen__' ] = true;

                    if ( $session ) {
                        $session->set( $debugs_key, $debugs_mostrados );
                        $session->set( 'apg_shipping_debug_' . $this->instance_id, null );
                    }
                }
                if ( ! empty( $tarifa_mas_barata ) ) {
                    return $tarifa_mas_barata;
                } else {
                    return [];
                }
            }
            
            //Comprueba si una medida es válida
            private function apg_medida_valida( $medida ) {
                return preg_match( '/^\d+x\d+x\d+$/', $medida );
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
    $metodo = isset( $_POST[ 'metodo' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'metodo' ] ) ) : '';
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
        'entrega'   => wp_kses_post( $entrega ),
        'icono'     => esc_url_raw( $opciones[ 'icono' ] ?? '' ),
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
    
    //Elimina opciones residuales del plugin en desinstalación. Cache no necesaria aquí.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%woocommerce_apg_shipping_%'" );
    //Borra los transientes antiguos.  Cache no necesaria aquí.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_apg_shipping_%'" );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_apg_shipping_%'" );
}
register_uninstall_hook( __FILE__, 'apg_shipping_desinstalar' );
