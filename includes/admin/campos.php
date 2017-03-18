<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Campos del formulario
$campos = array(
	'activo' => array(
		'title'			=> __( 'Enable/Disable', 'apg_shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Enable this shipping method', 'apg_shipping' ),
		'default'		=> 'yes',
	),
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
		'title'			=> __( 'Apply shipping rate per...', 'apg_shipping' ),
		'type'			=> 'select',
		'desc_tip'		=> __( 'Select how to apply the shipping rate: per weight, items or cart total.', 'apg_shipping' ),
		'class'			=> 'wc-enhanced-select',
		'options'		=> array( 
			'peso'			=> __( 'Total weight', 'apg_shipping' ),
			'unidad'		=> __( 'Total items', 'apg_shipping' ),
			'total'			=> __( 'Cart total', 'apg_shipping' ),
		 ),
		'description'		=> __( 'Total weight: Apply shipping rate per cart weight (default).<br />Total items: Apply shipping rate per number of items.<br />Cart total: Apply shipping rate per cart total.', 'apg_shipping' ),
	),
	'suma' => array(
		'title'			=> __( 'Highest shipping class rate', 'apg_shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Select if you need just the highest shipping class rate not the sum of shipping classes rates.', 'apg_shipping' ),
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
		'desc_tip' 	=> sprintf( __( "Select the shipping class where %s doesn't accept shippings.", 'apg_shipping' ), $this->method_title ),
		'css'		=> 'width: 450px;',
		'default'	=> '',
		'type'		=> 'multiselect',
		'class'		=> 'wc-enhanced-select',
		'options' 	=> array( 
			'todas' 	=> __( 'All enabled shipping class', 'apg_shipping' ) 
		) + $this->clases_de_envio,
	);
}
$campos['roles_excluidos'] = array( 
	'title'			=> __( 'No shipping (User role)', 'apg_shipping' ),
	'desc_tip' 		=> sprintf( __( "Select the user role where %s doesn't accept shippings.", 'apg_shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select',
	'options' 		=> array( 
		'invitado'		=> __( 'Guest', 'apg_shipping' ) 
	) + $this->roles_de_usuario,
);
$campos['pago'] = array(
	'title'			=> __( 'Payment gateway', 'apg_shipping' ),
	'desc_tip'		=> sprintf( __( "Payment gateway available for %s", 'apg_shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> array( 
		'todos' 
	),
	'type'			=> 'multiselect',
	'class'			=> 'chosen_select',
	'options' 		=> array( 
		'todos'			=> __( 'All enabled payments', 'apg_shipping' )
	) + $this->metodos_de_pago,
);
$campos['icono'] = array( 
		'title'			=> __( 'Icon image', 'apg_shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Icon image URL. APG recommends a 60x21px image.', 'apg_shipping' ),
		'default'		=> plugins_url( 'assets/images/apg.jpg', DIRECCION_apg_shipping ),
		'desc_tip'		=> true,
);
$campos['muestra_icono'] = array( 
		'title'			=> __( 'How show icon image?', 'apg_shipping' ),
		'desc_tip' 		=> __( "Select how you want to show the icon image.", 'apg_shipping' ),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select',
		'options'		=> array( 
			'no'			=> __( 'Not show, just title', 'apg_shipping' ),
			'delante'		=> __( 'Before title', 'apg_shipping' ),
			'detras'		=> __( 'After title', 'apg_shipping' ),
			'solo'			=> __( 'No title, just icon', 'apg_shipping' ),
		 ),
);
$campos['entrega'] = array( 
		'title'			=> __( 'Estimated delivery time', 'apg_shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Define estimation for delivery time for this shipping method.', 'apg_shipping' ),
		'default'		=> '',
		'desc_tip'		=> true,
);

return $campos;
?>
