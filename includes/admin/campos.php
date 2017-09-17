<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Campos del formulario
$campos = array(
	'activo' => array(
		'title'			=> __( 'Enable/Disable', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Enable this shipping method', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> 'yes',
	),
	'title' => array(
		'title'			=> __( 'Method Title', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'desc_tip'		=> __( 'This controls the title which the user sees during checkout.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> __( 'APG Shipping', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	),
	'tax_status' => array(
		'title'			=> __( 'Tax Status', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select',
		'default'		=> 'taxable',
		'options'		=> array(
			'taxable'		=> __( 'Taxable', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'none'			=> __( 'None', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		),
	),
	'fee' => array(
		'title'			=> __( 'Handling Fee', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'price',
		'desc_tip'		=> __( 'Fee excluding tax. Enter an amount, e.g. 2.50. Leave blank to disable.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'description'	=> __( 'Fee added to the total cost of items.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> '',
		'placeholder'	=> 0,
	),
	'cargo' => array(
		'title'			=> __( 'Additional Fee', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'desc_tip'		=> __( 'Additional fee excluding tax. Enter an amount, e.g. 2.50, or percentage, e.g. 6%. Leave blank to disable.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'description'	=> __( 'Additional fee added to the total cost of items.<br />You can use  <code>min="fee" max="fee"</code> for percentage based fees, e.g. <code>6%|min="20.50" max="100"</code>.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> '',
		'placeholder'	=> 0,
	),
	'tipo_cargo' => array(
		'title'			=> __( 'Apply per product?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Apply additional fee per product.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'desc_tip'		=> __( 'To apply additional fee for the number of items.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> 'no',
	),
	'tarifas' => array(
		'title'			=> __( 'Shipping Rates', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'textarea',
		'desc_tip'		=> __( 'Set your weight based rates for postcode/state/country groups (one per line). You may optionally add the maximum dimensions, e.g. <code>Max weight|Cost|Shipping class name (optional)|LxWxH (optional)</code>. Also you can set your dimensions based rates, e.g. <code>LxWxH|Cost|Shipping class name (optional)</code>.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'css'			=> 'width:300px;',
		'default'		=> '',
		'description'	=> '<code>1000|6.95|10x10x10</code><br /><code>10x10x10|6.95</code><br />' . ( ( WC()->shipping->get_shipping_classes() ) ?  __( 'Remember your shipping class name: ', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . $this->clases_de_envio_tarifas . "<br />" : "" ) . sprintf( __( 'Remember your weight unit: %s, and dimensions unit: %s.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), get_option( 'woocommerce_weight_unit' ), get_option( 'woocommerce_dimension_unit' ) ),
	),
	'tipo_tarifas' => array(
		'title'			=> __( 'Apply shipping rate per...', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'select',
		'desc_tip'		=> __( 'Select how to apply the shipping rate: per weight, items or cart total.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'class'			=> 'wc-enhanced-select',
		'options'		=> array( 
			'peso'			=> __( 'Total weight', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'unidad'		=> __( 'Total items', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'total'			=> __( 'Cart total', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		 ),
		'description'		=> __( 'Total weight: Apply shipping rate per cart weight (default).<br />Total items: Apply shipping rate per number of items.<br />Cart total: Apply shipping rate per cart total.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	),
	'suma' => array(
		'title'			=> __( 'Highest shipping class rate', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Select if you need just the highest shipping class rate not the sum of shipping classes rates.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> 'no',
	),
	'maximo' => array(
		'title'			=> __( 'Overweight/over dimensions', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Return the maximum price.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> 'yes',
	),
);
if ( WC()->shipping->get_shipping_classes() ) {
	$campos['clases_excluidas'] = array( 
		'title'		=> __( 'No shipping (Shipping class)', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'desc_tip' 	=> sprintf( __( "Select the shipping class where %s doesn't accept shippings.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
		'css'		=> 'width: 450px;',
		'default'	=> '',
		'type'		=> 'multiselect',
		'class'		=> 'wc-enhanced-select',
		'options' 	=> array( 
			'todas' 	=> __( 'All enabled shipping class', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) 
		) + $this->clases_de_envio,
	);
}
/*
$campos['tipo_clases'] = array(
	'title'			=> __( 'Only shipping?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'type'			=> 'checkbox',
	'label'			=> __( 'Ship only to this shipping class.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'desc_tip'		=> __( 'To apply additional fee for the number of items.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'default'		=> 'no',
);
*/
$campos['roles_excluidos'] = array( 
	'title'			=> __( 'No shipping (User role)', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'desc_tip' 		=> sprintf( __( "Select the user role where %s doesn't accept shippings.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select',
	'options' 		=> array( 
		'invitado'		=> __( 'Guest', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) 
	) + $this->roles_de_usuario,
);
/*
$campos['tipo_roles'] = array(
	'title'			=> __( 'Only shipping?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'type'			=> 'checkbox',
	'label'			=> __( 'Ship only to this user role.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'desc_tip'		=> __( 'To apply additional fee for the number of items.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'default'		=> 'no',
);
*/
$campos['pago'] = array(
	'title'			=> __( 'Payment gateway', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'desc_tip'		=> sprintf( __( "Payment gateway available for %s", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> array( 
		'todos' 
	),
	'type'			=> 'multiselect',
	'class'			=> 'chosen_select',
	'options' 		=> array( 
		'todos'			=> __( 'All enabled payments', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )
	) + $this->metodos_de_pago,
);
$campos['icono'] = array( 
		'title'			=> __( 'Icon image', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Icon image URL. APG recommends a 60x21px image.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> plugins_url( 'assets/images/apg.jpg', DIRECCION_apg_shipping ),
		'desc_tip'		=> true,
);
$campos['muestra_icono'] = array( 
		'title'			=> __( 'How show icon image?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'desc_tip' 		=> __( "Select how you want to show the icon image.", 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select',
		'options'		=> array( 
			'no'			=> __( 'Not show, just title', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'delante'		=> __( 'Before title', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'detras'		=> __( 'After title', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
			'solo'			=> __( 'No title, just icon', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		 ),
);
$campos['entrega'] = array( 
		'title'			=> __( 'Estimated delivery time', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Define estimation for delivery time for this shipping method.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
		'default'		=> '',
		'desc_tip'		=> true,
);
$campos['debug'] = array(
	'title'			=> __( 'Show debug information?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'type'			=> 'checkbox',
	'label'			=> __( 'Check if you want to show debug information on the cart page.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'desc_tip'		=> __( 'Displays the variables sent to the function dame_tarifa_mas_barata.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
	'default'		=> 'no',
);

return $campos;
