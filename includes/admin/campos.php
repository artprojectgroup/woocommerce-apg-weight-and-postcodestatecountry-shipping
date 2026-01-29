<?php
/**
 * Generador de campos de configuración para el método de envío APG Shipping.
 *
 * Devuelve un array asociativo con los campos de opciones y ajustes para el método
 * de envío, compatible con WooCommerce. Los campos incluyen controles para título,
 * tarifas, exclusiones por categoría/rol/atributo, métodos de pago y otros parámetros avanzados.
 *
 * Seguridad: No debe accederse directamente, solo debe incluirse desde la clase del método de envío.
 *
 * Variables de entorno utilizadas:
 * - $this->categorias_de_producto
 * - $this->etiquetas_de_producto
 * - $this->atributos
 * - $this->clases_de_envio
 * - $this->clases_de_envio_tarifas
 * - $this->roles_de_usuario
 * - $this->metodos_de_pago
 * - $this->metodos_de_envio
 *
 * @return array[] Array asociativo de campos de configuración para el método de envío.
 *
 * @package WC-APG-Weight-Shipping
 * @subpackage Admin/Settings
 * @author Art Project Group
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

return ( function() {
	$this->apg_shipping_obtiene_datos( true ); // Recoge los datos (modo campos).
	$apg_shipping_ajax_nonce = wp_create_nonce( 'apg_ajax_terms' );

// Campos del formulario.
// translators: %1$s is a context-dependent item name (e.g., product category, tag, attribute, role, or shipping class); %2$s is the shipping method title.
$apg_texto  = __( "Select the %1\$s where %2\$s doesn't accept shippings.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' );
$apg_campos = [];

// Campo: Activar/desactivar (solo WC < 2.7)
if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
	$apg_campos[ 'activo' ] = [ 
		'title'			=> __( 'Enable/Disable', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Enable this shipping method', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> 'yes'
	];
}
$apg_campos[ 'title' ] = [
		'title'			=> __( 'Method Title', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'desc_tip'		=> __( 'This controls the title which the user sees during checkout.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> __( 'APG Shipping', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
];
if ( wc_tax_enabled() ) {
	$apg_campos[ 'tax_status' ] = [ 
			'title'			=> __( 'Tax Status', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'type'			=> 'select',
			'class'			=> 'wc-enhanced-select',
			'default'		=> 'taxable',
			'options'		=> [
				'taxable'		=> __( 'Taxable', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				'none'			=> __( 'None', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			]
	];
}
$apg_campos[ 'fee' ] = [ 
	'title'			=> __( 'Handling Fee', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'type'			=> 'price',
	'desc_tip'		=> __( 'Fee excluding tax. Enter an amount, e.g. 2.50. Leave blank to disable.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'description'	=> __( 'Fee added to the total cost of items.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'default'		=> '',
	'placeholder'	=> 0,
];
$apg_campos[ 'cargo' ] = [ 
	'title'			=> __( 'Additional Fee', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'type'			=> 'text',
	'desc_tip'		=> __( 'Additional fee excluding tax. Enter an amount, e.g. 2.50, or percentage, e.g. 6%. Leave blank to disable.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'description'	=> __( 'Additional fee added to the total cost of items.<br />You can use  <code>min="fee" max="fee"</code> for percentage based fees, e.g. <code>6%|min="20.50" max="100"</code>.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'default'		=> '',
	'placeholder'	=> 0,
];
$apg_campos[ 'tipo_cargo' ] = [ 
	'title'			=> __( 'Apply per product?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'type'			=> 'checkbox',
	'label'			=> __( 'Apply additional fee per product.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'desc_tip'		=> __( 'To apply additional fee for the number of items.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'default'		=> 'no',
];
// Descripción.
$apg_unidad_peso    = get_option( 'woocommerce_weight_unit' );
$apg_unidad_medidas = get_option( 'woocommerce_dimension_unit' );
$apg_clases_envio   = WC()->shipping->get_shipping_classes();

$apg_descripcion    = '<code>1000|6.95|10x10x10</code><br /><code>10x10x10|6.95</code><br />';
$apg_descripcion   .= '<code>500-1000|4.95</code><br /><code>500+100|2.00+0.25</code><br /><code>500+100-50|2.00+0.25</code><br />';
if ( $apg_clases_envio ) {
    $apg_descripcion    .= __( 'Remember your shipping class name: ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . esc_attr( $this->clases_de_envio_tarifas ) . '<br />';
}
// translators: %1$s is the weight unit (e.g., kg), %2$s is the dimension unit (e.g., cm).
$apg_descripcion            .= sprintf( __( 'Remember your weight unit: %1$s, and dimensions unit: %2$s.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), esc_attr( $apg_unidad_peso ), esc_attr( $apg_unidad_medidas ) );
$apg_campos[ 'tarifas' ]    = [ 
	'title'			=> __( 'Shipping Rates', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'type'			=> 'textarea',
    'desc_tip'      => __( 'Set your weight based rates for postcode/state/country groups (one per line). You may optionally add the maximum dimensions, e.g. "Max weight|Cost|Shipping class name (optional)|LxWxH (optional)". Also you can set your dimensions based rates, e.g. "LxWxH|Cost|Shipping class name (optional)". New: use "Min-Max|Cost" for ranges, or "Start+Step|Cost+Step" for repetitive rates with optional maximum as "Start+Step-Max|Cost+Step".', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'css'			=> 'width:300px;',
	'default'		=> '',
	'description'	=> $apg_descripcion,
];
$apg_campos[ 'tipo_tarifas' ] = [ 
	'title'			=> __( 'Apply shipping rate per...', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'type'			=> 'select',
	'desc_tip'		=> __( 'Select how to apply the shipping rate: per weight, items or cart total.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'class'			=> 'wc-enhanced-select',
	'options'		=> [ 
		'peso'			=> __( 'Total weight', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'unidad'		=> __( 'Total items', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'total'			=> __( 'Cart total', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	 ],
	'description'		=> __( 'Total weight: Apply shipping rate per cart weight (default).<br />Total items: Apply shipping rate per number of items.<br />Cart total: Apply shipping rate per cart total.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
];
if ( WC()->shipping->get_shipping_classes() ) {
	$apg_campos[ 'suma' ] = [ 
		'title'			=> __( 'Highest shipping class rate', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Select if you need just the highest shipping class rate not the sum of shipping classes rates.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> 'no',
	];
}
$apg_campos[ 'maximo' ] = [ 
    'title'				=> __( 'Overweight/over dimensions', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
    'type'				=> 'checkbox',
    'label'				=> __( 'Return the maximum price.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
    'default'			=> 'yes',
];
$apg_categorias_opts  = is_array( $this->categorias_de_producto ) ? $this->categorias_de_producto : [];
$apg_categorias_cnt   = wp_count_terms( 'product_cat' );
if ( is_wp_error( $apg_categorias_cnt ) ) {
	$apg_categorias_cnt = 1000;
}
$apg_categorias_ajax  = $apg_categorias_cnt > 500;
$apg_categorias_saved = (array) $this->get_option( 'categorias_excluidas', [] );
$apg_categorias_seed  = [];
if ( $apg_categorias_ajax && ! empty( $apg_categorias_saved ) ) {
	foreach ( $apg_categorias_saved as $apg_cid ) {
		if ( isset( $apg_categorias_opts[ $apg_cid ] ) ) {
			$apg_categorias_seed[ $apg_cid ] = $apg_categorias_opts[ $apg_cid ];
		}
	}
}
$apg_campos[ 'categorias_excluidas' ]   = [
	// translators: %s is the name of the product category.
	'title'			=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'Product category', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )  ),
	// translators: %1$s is the name of the product category, %2$s is the shipping method title.
	'desc_tip' 		=> sprintf( $apg_texto, __( 'product category', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select apg-ajax-select',
	'custom_attributes'	=> $apg_categorias_ajax ? [
		'data-apg-ajax' => '1',
		'data-source'   => 'categories',
		'data-nonce'    => $apg_shipping_ajax_nonce,
	] : [],
	'options' 		=> $apg_categorias_ajax ? $apg_categorias_seed : $apg_categorias_opts,
	'description'	=> ( $apg_categorias_cnt > 500 ? __( 'Large list. Type to search…', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) : '' ),
];
$apg_campos[ 'tipo_categorias' ] = [
    // translators: %s is the name of the product category.
	'title'			=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'Product category', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
	'type'			=> 'checkbox',
    // translators: %s is the plural "product categories".
	'label'			=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'product categories', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
    // translators: %s is the plural "product categories".
	'desc_tip' 		=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'product categories', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
	'default'		=> 'no',
];
$apg_etiquetas_opts  = is_array( $this->etiquetas_de_producto ) ? $this->etiquetas_de_producto : [];
$apg_etiquetas_cnt   = wp_count_terms( 'product_tag' );
if ( is_wp_error( $apg_etiquetas_cnt ) ) {
	$apg_etiquetas_cnt = 1000;
}
$apg_etiquetas_ajax  = $apg_etiquetas_cnt > 500;
$apg_etiquetas_saved = (array) $this->get_option( 'etiquetas_excluidas', [] );
$apg_etiquetas_seed  = [];
if ( $apg_etiquetas_ajax && ! empty( $apg_etiquetas_saved ) ) {
	foreach ( $apg_etiquetas_saved as $apg_tid ) {
		if ( isset( $apg_etiquetas_opts[ $apg_tid ] ) ) {
			$apg_etiquetas_seed[ $apg_tid ] = $apg_etiquetas_opts[ $apg_tid ];
		}
	}
}
$apg_campos[ 'etiquetas_excluidas' ]    = [
	// translators: %s is the name of the product tag.
	'title'			=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'Product tag', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
	// translators: %1$s is the product tag name, %2$s is the shipping method title.
	'desc_tip' 		=> sprintf( $apg_texto, __( 'product tag', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select apg-ajax-select',
	'custom_attributes' => $apg_etiquetas_ajax ? [
		'data-apg-ajax' => '1',
		'data-source'   => 'tags',
		'data-nonce'    => $apg_shipping_ajax_nonce,
	] : [],
	'options' 		=> $apg_etiquetas_ajax ? $apg_etiquetas_seed : $apg_etiquetas_opts,
	'description'	=> ( $apg_etiquetas_cnt > 500 ? __( 'Large list. Type to search…', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) : '' ),
];
$apg_campos[ 'tipo_etiquetas' ] = [
    // translators: %s is the name of the product tag.
	'title'			=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'Product tag', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
	'type'			=> 'checkbox',
    // translators: %s is the plural "product tags".
	'label'			=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'product tags', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
    // translators: %s is the plural "product tags".
	'desc_tip' 		=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'product tags', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
	'default'		=> 'no',
];
if ( wc_get_attribute_taxonomies() ) {
	$apg_atributos_opts  = is_array( $this->atributos ) ? $this->atributos : [];
	$apg_atributos_cnt   = 0;
	$apg_atributos_tax   = function_exists( 'wc_get_attribute_taxonomy_names' ) ? wc_get_attribute_taxonomy_names() : [];
	$apg_atributos_force = ! empty( $this->apg_atributos_forced_ajax );
	if ( ! $apg_atributos_force && is_array( $apg_atributos_tax ) ) {
		foreach ( $apg_atributos_tax as $taxonomia ) {
		$cnt = wp_count_terms( $taxonomia );
			if ( ! is_wp_error( $cnt ) ) {
				$apg_atributos_cnt += (int) $cnt;
			}
		}
	}
	if ( $apg_atributos_force || ! $apg_atributos_cnt ) {
		$apg_atributos_cnt = 1000;
	}
	$apg_atributos_ajax  = $apg_atributos_cnt > 500 || $apg_atributos_force;
	$apg_atributos_saved = (array) $this->get_option( 'atributos_excluidos', [] );
	$apg_atributos_seed  = [];
	if ( $apg_atributos_ajax && ! empty( $apg_atributos_saved ) ) {
			foreach ( $apg_atributos_saved as $apg_aid ) {
				if ( isset( $apg_atributos_opts[ $apg_aid ] ) ) {
					$apg_atributos_seed[ $apg_aid ] = $apg_atributos_opts[ $apg_aid ];
				}
			}
		}
    $apg_campos[ 'atributos_excluidos' ]    = [
        // translators: %s is the name of the attribute.
        'title'			=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'Attribute', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
        // translators: %1$s is the attribute name, %2$s is the shipping method title.
        'desc_tip' 		=> sprintf( $apg_texto, __( 'attribute', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
        'css'			=> 'width: 450px;',
        'default'		=> '',
        'type'			=> 'multiselect',
        'class'			=> 'wc-enhanced-select apg-ajax-select',
        'custom_attributes' => $apg_atributos_ajax ? [
			'data-apg-ajax' => '1',
			'data-source'   => 'attributes',
			'data-nonce'    => $apg_shipping_ajax_nonce,
		] : [],
        'options' 		=> $apg_atributos_ajax ? $apg_atributos_seed : $apg_atributos_opts,
        'description'	=> ( $apg_atributos_cnt > 500 ? __( 'Large list. Type to search…', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) : '' ),
    ];
    $apg_campos[ 'tipo_atributos' ] = [
        // translators: %s is the name of the attribute.
        'title'			=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'Attribute', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
        'type'			=> 'checkbox',
        // translators: %s is the plural "attributes".
        'label'			=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'attributes', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
        // translators: %s is the plural "attributes".
        'desc_tip' 		=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'attributes', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
        'default'		=> 'no',
    ];
}
if ( WC()->shipping->get_shipping_classes() ) {
	$apg_clases_opts  = is_array( $this->clases_de_envio ) ? $this->clases_de_envio : [];
	$apg_clases_cnt   = count( $apg_clases_opts );
	$apg_clases_ajax  = $apg_clases_cnt > 500;
	$apg_clases_saved = (array) $this->get_option( 'clases_excluidas', [] );
	$apg_clases_seed  = [];
	if ( $apg_clases_ajax && ! empty( $apg_clases_saved ) ) {
		foreach ( $apg_clases_saved as $apg_sid ) {
			if ( isset( $apg_clases_opts[ $apg_sid ] ) ) {
				$apg_clases_seed[ $apg_sid ] = $apg_clases_opts[ $apg_sid ];
			}
		}
	}
    $apg_campos[ 'clases_excluidas' ]   = [
        // translators: %s is the name of the shipping class.
		'title'			=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'Shipping class', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
        // translators: %1$s is the shipping class name, %2$s is the shipping method title.
		'desc_tip' 		=> sprintf( $apg_texto, __( 'shipping class', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
		'css'			=> 'width: 450px;',
		'default'		=> '',
		'type'			=> 'multiselect',
		'class'			=> 'wc-enhanced-select apg-ajax-select',
		'custom_attributes' => $apg_clases_ajax ? [
			'data-apg-ajax' => '1',
			'data-source'   => 'classes',
			'data-nonce'    => $apg_shipping_ajax_nonce,
		] : [],
		'options' 		=> [ 'todas' => __( 'All enabled shipping class', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ] + ( $apg_clases_ajax ? $apg_clases_seed : $apg_clases_opts ),
		'description'	=> ( $apg_clases_cnt > 500 ? __( 'Large list. Type to search…', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) : '' ),
	];
	$apg_campos[ 'tipo_clases' ] = [
        // translators: %s is the name of the shipping class.
		'title'			=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'Shipping class', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
		'type'			=> 'checkbox',
        // translators: %s is the plural "shipping classes".
		'label'			=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'shipping classes', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
        // translators: %s is the plural "shipping classes".
		'desc_tip' 		=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'shipping classes', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
		'default'		=> 'no',
	];
}
$apg_campos[ 'roles_excluidos' ]    = [ 
    // translators: %s is the name of the user role.
	'title'			=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'User role', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
    // translators: %1$s is the user role name, %2$s is the shipping method title.
	'desc_tip' 		=> sprintf( $apg_texto, __( 'user role', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select',
	'options' 		=> [ 
		'invitado'		=> __( 'Guest', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) 
	] + $this->roles_de_usuario,
];
$apg_campos[ 'tipo_roles' ] = [
    // translators: %s is the name of the user role.
	'title'			=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'User role', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
	'type'			=> 'checkbox',
    // translators: %s is the plural "user roles".
	'label'			=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'user roles', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
    // translators: %s is the plural "user roles".
	'desc_tip' 		=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), __( 'user roles', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ),
	'default'		=> 'no',
];
$apg_pago_opts   = is_array( $this->metodos_de_pago ) ? $this->metodos_de_pago : [];
$apg_pago_saved  = (array) $this->get_option( 'pago', [] );
$apg_pago_seed   = [];
if ( empty( $apg_pago_opts ) && ! empty( $apg_pago_saved ) ) {
	foreach ( $apg_pago_saved as $apg_pid ) {
		// translators: %s is the payment method ID.
		$apg_pago_seed[ $apg_pid ] = sprintf( __( 'Payment method %s', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_pid );
	}
}
$apg_campos[ 'pago' ] = [ 
		'title'			=> __( 'Payment gateway', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	    // translators: %s is the shipping method title.
		'desc_tip'		=> sprintf( __( "Payment gateway available for %s", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
		'css'			=> 'width: 450px;',
		'default'		=> [ 
			'todos' 
		],
		'type'			=> 'multiselect',
		'class'			=> 'chosen_select',
		'options' 		=> [ 
			'todos'			=> __( 'All enabled payments', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )
		] + ( ! empty( $apg_pago_opts ) ? $apg_pago_opts : $apg_pago_seed ),
	];
$apg_envio_opts  = is_array( $this->metodos_de_envio ) ? $this->metodos_de_envio : [];
$apg_envio_saved = (array) $this->get_option( 'envio', [] );
$apg_envio_seed  = [];
if ( empty( $apg_envio_opts ) && ! empty( $apg_envio_saved ) ) {
	foreach ( $apg_envio_saved as $apg_eid ) {
		if ( 'todos' === $apg_eid || 'ninguno' === $apg_eid ) {
			continue;
		}
		// translators: %s is the shipping method ID.
		$apg_envio_seed[ $apg_eid ] = sprintf( __( 'Shipping method %s', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $apg_eid );
	}
}
$apg_campos[ 'envio' ] = [
        'title'			=> __( 'Shipping methods', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
        // translators: %s is the shipping method title.
        'desc_tip'		=> sprintf( __( "Shipping methods available in the same shipping zone of %s", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
        'css'			=> 'width: 450px;',
        'default'		=> [
            'todos'
        ],
        'type'			=> 'multiselect',
        'class'			=> 'chosen_select',
        'options' 		=> [
            'todos'			=> __( 'All enabled shipping methods', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
            'ninguno'       => __( 'No other shipping methods', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )
        ] + ( ! empty( $apg_envio_opts ) ? $apg_envio_opts : $apg_envio_seed ),
    ];
$apg_campos[ 'icono' ] = [ 
		'title'			=> __( 'Icon image', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Icon image URL. APG recommends a 60x21px image.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> plugins_url( 'assets/images/apg.jpg', DIRECCION_apg_shipping ),
		'desc_tip'		=> true,
];
$apg_campos[ 'muestra_icono' ] = [ 
		'title'			=> __( 'How show icon image?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'desc_tip' 		=> __( "Select how you want to show the icon image.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select',
		'options'		=> [ 
			'no'			=> __( 'Not show, just title', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'delante'		=> __( 'Before title', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'detras'		=> __( 'After title', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'solo'			=> __( 'No title, just icon', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		 ],
];
$apg_campos[ 'entrega' ] = [ 
		'title'			=> __( 'Estimated delivery time', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Define estimation for delivery time for this shipping method.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> '',
		'desc_tip'		=> true,
];
$apg_campos[ 'debug' ] = [
	'title'			=> __( 'Show debug information?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'type'			=> 'checkbox',
	'label'			=> __( 'Check if you want to show debug information on the cart page.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'desc_tip'		=> __( 'Displays the variables sent to the function dame_tarifa_mas_barata.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'default'		=> 'no',
];

	return $apg_campos;
} )();
