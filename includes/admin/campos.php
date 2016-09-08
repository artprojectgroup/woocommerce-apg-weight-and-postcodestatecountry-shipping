<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Campos del formulario
$campos = array(
	'title' => array(
		'title'			=> __( 'Method Title', 'apg_shipping' ),
		'type'			=> 'text',
		'desc_tip'		=> __( 'This controls the title which the user sees during checkout.', 'apg_shipping' ),
		'default'		=> __( 'APG Shipping', 'apg_shipping' ),
	),
	'tax_status' => array(
		'title'			=> __( 'Tax Status', 'apg_shipping' ),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select',
		'default'		=> 'taxable',
		'options'		=> array(
			'taxable'		=> __( 'Taxable', 'apg_shipping' ),
			'none'			=> __( 'None', 'apg_shipping' ),
		),
	),
	'fee' => array(
		'title'			=> __( 'Handling Fee', 'apg_shipping' ),
		'type'			=> 'price',
		'desc_tip'		=> __( 'Fee excluding tax. Enter an amount, e.g. 2.50. Leave blank to disable.', 'apg_shipping' ),
		'description'	=> __( 'Fee added to the total cost of items.', 'apg_shipping' ),
		'default'		=> '',
		'placeholder'	=> 0,
	),
	'cargo' => array(
		'title'			=> __( 'Additional Fee', 'apg_shipping' ),
		'type'			=> 'text',
		'desc_tip'		=> __( 'Additional fee excluding tax. Enter an amount, e.g. 2.50, or percentage, e.g. 6%. Leave blank to disable.', 'apg_shipping' ),
		'description'	=> __( 'Additional fee added to the total cost of items.<br />You can use  <code>min="fee" max="fee"</code> for percentage based fees, e.g. <code>6%|min="20.50" max="100"</code>.', 'apg_shipping' ),
		'default'		=> '',
		'placeholder'	=> 0,
	),
	'tipo_cargo' => array(
		'title'			=> __( 'Apply per product?', 'apg_shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Apply additional fee per product.', 'apg_shipping' ),
		'desc_tip'		=> __( 'To apply additional fee for the number of items.', 'apg_shipping' ),
		'default'		=> 'no',
	),
	'tarifas' => array(
		'title'			=> __( 'Shipping Rates', 'apg_shipping' ),
		'type'			=> 'textarea',
		'desc_tip'		=> __( 'Set your weight based rates for postcode/state/country groups (one per line). You may optionally add the maximum dimensions, e.g. <code>Max weight|Cost|Shipping class name (optional)|LxWxH (optional)</code>. Also you can set your dimensions based rates, e.g. <code>LxWxH|Cost|Shipping class name (optional)</code>.', 'apg_shipping' ),
		'css'			=> 'width:300px;',
		'default'		=> '',
		'description'	=> '<code>1000|6.95|10x10x10</code><br /><code>10x10x10|6.95</code><br />' . ( ( WC()->shipping->get_shipping_classes() ) ?  __( 'Remember your shipping class name: ', 'apg_shipping' ) . $this->clases_de_envio_tarifas . "<br />" : "" ) . sprintf( __( 'Remember your weight unit: %s, and dimensions unit: %s.', 'apg_shipping' ), get_option( 'woocommerce_weight_unit' ), get_option( 'woocommerce_dimension_unit' ) ),
	),
	'tipo_tarifas' => array(
		'title'			=> __( 'Apply per product?', 'apg_shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Apply shipping rate per product.', 'apg_shipping' ),
		'desc_tip'		=> __( 'To apply shipping rate for the number of items.', 'apg_shipping' ),
		'default'		=> 'no',
	),
	'maximo' => array(
		'title'			=> __( 'Overweight/over dimensions', 'apg_shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Return the maximum price.', 'apg_shipping' ),
		'default'		=> 'yes',
	),
);
if ( WC()->shipping->get_shipping_classes() ) {
	$campos['clases_excluidas'] = array( 
		'title'		=> __( 'No shipping (Shipping class)', 'apg_shipping' ),
		'desc_tip' 	=> sprintf( __( "Select the shipping class where %s doesn't accept shippings.", 'apg_shipping' ), get_bloginfo( 'name' ) ),
		'css'		=> 'width: 450px;',
		'default'	=> '',
		'type'		=> 'multiselect',
		'class'		=> 'wc-enhanced-select',
		'options' 	=> array( 
			'todas' => __( 'All enabled shipping class', 'apg_shipping' ) 
		) + $this->clases_de_envio,
	);
}

return $campos;
?>
