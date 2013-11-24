<?php
/*
Plugin Name: WooCommerce - APG Weight and Postcode/State/Country Shipping
Version: 0.4
Plugin URI: http://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/
Description: Add to WooCommerce the calculation of shipping costs based on the order weight and postcode, province (state) and country of customer's address. Lets you add an unlimited shipping rates. Created from <a href="http://profiles.wordpress.org/andy_p/" target="_blank">Andy_P</a> <a href="http://wordpress.org/plugins/awd-weightcountry-shipping/" target="_blank"><strong>AWD Weight/Country Shipping</strong></a> plugin and the modification of <a href="http://wordpress.org/support/profile/mantish" target="_blank">Mantish</a> publicada en <a href="https://gist.github.com/Mantish/5658280" target="_blank">GitHub</a>.
Author URI: http://www.artprojectgroup.es/

Author: Art Project Group
Text Domain: apg_shipping
Domain Path: /lang
License: GPL2
*/

/*  Copyright 2013  artprojectgroup  (email : info@artprojectgroup.es)

    This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//Carga el idioma
load_plugin_textdomain('apg_shipping', null, dirname(plugin_basename(__FILE__)) . '/lang');

//Enlaces adicionales personalizados
function apg_shipping_enlaces($enlaces, $archivo) {
	$plugin = plugin_basename(__FILE__);

	if ($archivo == $plugin) 
	{
		$enlaces[] = '<a href="http://www.artprojectgroup.es/plugins-para-wordpress/woocommerce-apg-weight-and-postcodestatecountry-shipping" target="_blank" title="Art Project Group">' . __('Visit the official plugin website', 'apg_shipping') . '</a>';
		$enlaces[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LB54JTPQGW9ZW" target="_blank" title="PayPal"><img alt="Google Video Sitemap Feed With Multisite Support" src="' . __('https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif', 'apg_shipping') . '" width="53" height="15" style="vertical-align:text-bottom;"></a>';
	}
	
	return $enlaces;
}
add_filter('plugin_row_meta', 'apg_shipping_enlaces', 10, 2);

//Añade el botón de configuración
function apg_shipping_enlace_de_ajustes($enlaces) { 
	$enlace_de_ajustes = '<a href="admin.php?page=woocommerce_settings&tab=shipping&section=apg_shipping" title="' . __('Settings', 'apg_shipping') . '">' . __('Settings', 'apg_shipping') . '</a>'; 
	array_unshift($enlaces, $enlace_de_ajustes); 
	
	return $enlaces; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'apg_shipping_enlace_de_ajustes');

//Contine la clase que crea los nuevos gastos de envío
function apg_shipping_inicio() {
	if (!class_exists('WC_Shipping_Method')) return;

	class apg_shipping extends WC_Shipping_Method {

		function __construct() {
			$this->id 				= 'apg_shipping';
			$this->method_title	= __('APG Shipping', 'apg_shipping');
			$this->init();
		}

		//Inicializa los datos
        function init() {
			$this->admin_page_heading       = __('Weight based shipping', 'apg_shipping');
			$this->admin_page_description   = __('Define shipping by weight and postcode/state/country', 'apg_shipping');

			add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
			add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'sincroniza_paises'));

			$this->init_form_fields();
			$this->init_settings();

			$this->enabled				= $this->settings['enabled'];
			$this->title				= $this->settings['title'];
			$this->postal_group_no	= $this->settings['postal_group_no'];
			$this->state_group_no		= $this->settings['state_group_no'];
			$this->country_group_no	= $this->settings['country_group_no'];
			$this->sync_countries		= $this->settings['sync_countries'];
			$this->availability		= 'specific';
			$this->states				= $this->settings['states'];
			$this->countries			= $this->settings['countries'];
			$this->type				= 'order';
			$this->tax_status			= $this->settings['tax_status'];
			$this->fee					= $this->settings['fee'];
			$this->cargo				= $this->settings['cargo'];
			$this->options				= isset($this->settings['options']) ? $this->settings['options'] : '';
			$this->options				= (array) explode("\n", $this->options);
			
			for ($contador = 1; $this->postal_group_no >= $contador; $contador++) $this->procesa_codigo_postal($this->settings['P' . $contador], 'P' . $contador);
			$this->pinta_grupos_codigos_postales();
			$this->pinta_grupos_estados();
			$this->pinta_grupos_paises();        
        }
		
		//Procesa el código postal
		function procesa_codigo_postal($codigo_postal, $id) {
			if (strstr($codigo_postal, '-'))
			{
				$codigos_postales = explode(';', $codigo_postal);
				$numeros_codigo_postal = array();
				foreach ($codigos_postales as $codigo_postal)
				{
					if (strstr($codigo_postal, '-')) 
					{
						$partes_codigo_postal = explode('-', $codigo_postal);
						if (is_numeric($partes_codigo_postal[0]) && is_numeric($partes_codigo_postal[1]) && $partes_codigo_postal[1] > $partes_codigo_postal[0])
						{
							for ($i = $partes_codigo_postal[0]; $i <= $partes_codigo_postal[1]; $i++)
							{
								if ($i)
								{
									if (strlen($i) < 5) $i = str_pad($i, 5, "0", STR_PAD_LEFT);
									$numeros_codigo_postal[] = $i;
								}
							}
						}
					}
					else $numeros_codigo_postal[] = $codigo_postal;
				}
				$this->settings[$id] = implode(';', $numeros_codigo_postal);
			}
		}
		
		//Formulario de datos
		function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'			=> __('Enable/Disable', 'apg_shipping'),
					'type'			=> 'checkbox',
					'label'			=> __('Enable this shipping method', 'apg_shipping'),
					'default'		=> 'no',
				),
			    'title' => array(
					'title'			=> __('Method Title', 'apg_shipping'),
					'type'			=> 'text',
					'desc_tip'		=> __('This controls the title which the user sees during checkout.', 'apg_shipping'),
					'default'		=> __('APG Shipping', 'apg_shipping'),
				),
				'tax_status' => array(
					'title'			=> __('Tax Status', 'apg_shipping'),
					'type'			=> 'select',
					'default'		=> 'taxable',
					'options'		=> array(
						'taxable'		=> __('Taxable', 'apg_shipping'),
						'none'			=> __('None', 'apg_shipping'),
					),
				),
				'fee' => array(
					'title'			=> __('Handling Fee', 'apg_shipping'),
					'type'			=> 'text',
					'desc_tip'		=> __('Fee excluding tax. Enter an amount, e.g. 2.50. Leave blank to disable.', 'apg_shipping'),
					'default'		=> '',
				),
				'cargo' => array(
					'title'			=> __('Additional Fee', 'apg_shipping'),
					'type'			=> 'text',
					'desc_tip'		=> __('Additional fee excluding tax. Enter an amount, e.g. 2.50, or percentage, e.g. 6%. Leave blank to disable.', 'apg_shipping'),
					'default'		=> '',
				),
				'options' => array(
					'title'			=> __('Shipping Rates', 'apg_shipping'),
					'type'			=> 'textarea',
					'desc_tip'		=> __('Set your weight based rates for postcode/state/country groups (one per line). Example: <code>Max weight|Cost|postcode/state/country group code separated by comma (,)</code>.', 'apg_shipping'),
					'css'			=> 'width:300px;',
					'default'		=> '',
					'description'	=> '1000|6.95|P2,S1,C3',
				),
				'postal_group_no' => array(
					'title'			=> __('Number of postcode groups', 'apg_shipping'),
					'type'			=> 'text',
					'desc_tip'		=> __('Number of groups of ZIP/Postcode sharing delivery rates. (Hit "Save changes" button after you have changed this setting).', 'apg_shipping'),
					'default'		=> '1',
				),
				'state_group_no' => array(
					'title'			=> __('Number of state groups', 'apg_shipping'),
					'type'			=> 'text',
					'desc_tip'		=> __('Number of groups of states sharing delivery rates. (Hit "Save changes" button after you have changed this setting).', 'apg_shipping'),
					'default'		=> '1',
				),
				'country_group_no' => array(
					'title'			=> __('Number of country groups', 'apg_shipping'),
					'type'			=> 'text',
					'desc_tip'		=> __('Number of groups of countries sharing delivery rates. (Hit "Save changes" button after you have changed this setting).', 'apg_shipping'),
					'default'		=> '1',
				),
				'sync_countries' => array(
					'title'			=> __('Add countries to allowed', 'apg_shipping'),
					'type'			=> 'checkbox',
					'label'			=> __('Countries added to country groups will be automatically added to <em>Allowed Countries</em> in <a href="/wp-admin/admin.php?page=woocommerce_settings&tab=general">General settings</a> tab.', 'apg_shipping'),
					'default'		=> 'no',
				),
			);
		}

		//Muestra los campos para los grupos de códigos postales
		function pinta_grupos_codigos_postales() {
			global $woocommerce;

			$numero = $this->postal_group_no;

			for ($contador = 1; $numero >= $contador; $contador++) 
			{
				$this->form_fields['P' . $contador] =  array(
					'title'    => sprintf(__('Postcode Group %s (P%s)', 'apg_shipping'), $contador, $contador),
					'type'     => 'text',
					'desc_tip'	=> __('Add the postcodes for this group. Semi-colon (;) separate multiple values. Wildcards (*) can be used. Example: <code>07*</code>. Ranges for numeric postcodes will be expanded into individual postcodes. Example: <code>12345-12350</code>.', 'apg_shipping'),
					'css'      => 'width: 450px;',
					'default'  => ''
				);
				if ($this->tax_status != 'none') $this->form_fields['Tax_P' . $contador] =  array(
					'title' 	=> sprintf(__('P%s Tax Class:', 'apg_shipping'), $contador),
					'desc_tip' => sprintf(__('Select the tax class for Postcode Group %s', 'apg_shipping'), $contador),
					'css' 		=> 'min-width:150px;',
					'default'	=> get_option('woocommerce_shipping_tax_class'),
					'type' 		=> 'select',
					'options' 	=> array('standard' => __('Standard', 'apg_shipping')) + dame_impuestos()
				);
			}
		}

		//Muestra los campos para los grupos de estados (provincias)
		function pinta_grupos_estados() {
			global $woocommerce;

			$numero = $this->state_group_no;

			$base_country = $woocommerce->countries->get_base_country();

			for ($contador = 1; $numero >= $contador; $contador++) 
			{
				$this->form_fields['S' . $contador] =  array(
					'title'		=> sprintf(__('State Group %s (S%s)', 'apg_shipping'), $contador, $contador),
					'type'		=> 'multiselect',
					'class'		=> 'chosen_select',
					'css'		=> 'width: 450px;',
					'desc_tip'	=> __('Select the states for this group.', 'apg_shipping'),
					'default'	=> '',
					'options'	=> $woocommerce->countries->get_states($base_country)
				);
				if ($this->tax_status != 'none') $this->form_fields['Tax_S' . $contador] =  array(
					'title' 	=> sprintf(__('S%s Tax Class:', 'apg_shipping'), $contador),
					'desc_tip' => sprintf(__('Select the tax class for State Group %s', 'apg_shipping'), $contador),
					'css' 		=> 'min-width:150px;',
					'default'	=> get_option('woocommerce_shipping_tax_class'),
					'type' 		=> 'select',
					'options' 	=> array('standard' => __('Standard', 'apg_shipping')) + dame_impuestos()
				);
			}
		}

		//Muestra los campos para los grupos de países
		function pinta_grupos_paises() {
			global $woocommerce;  

			$numero = $this->country_group_no;
	        
			for ($contador = 1; $numero >= $contador; $contador++) 
			{
				$this->form_fields['C' . $contador] =  array(
					'title'		=> sprintf(__('Country Group %s (C%s)', 'apg_shipping'), $contador, $contador),
					'type'		=> 'multiselect',
					'class'		=> 'chosen_select',
					'css'		=> 'width: 450px;',
					'desc_tip'	=> __('Select the countries for this group.', 'apg_shipping'),
					'default'	=> '',
					'options'	=> $woocommerce->countries->countries
				);
				if ($this->tax_status != 'none') $this->form_fields['Tax_C' . $contador] =  array(
					'title' 	=> sprintf(__('C%s Tax Class:', 'apg_shipping'), $contador),
					'desc_tip' => sprintf(__('Select the tax class for Country Group %s', 'apg_shipping'), $contador),
					'css' 		=> 'min-width:150px;',
					'default'	=> get_option('woocommerce_shipping_tax_class'),
					'type' 		=> 'select',
					'options' 	=> array('standard' => __('Standard', 'apg_shipping')) + dame_impuestos()
				);
			}    
		}

		//Calcula el gasto de envío
		function calculate_shipping($paquete = array()) {
			global $woocommerce;

			$grupo = $this->dame_grupos($paquete);
			$tarifas = $this->dame_tarifas($grupo);
			
			$peso = $woocommerce->cart->cart_contents_weight;
			$precio = $this->dame_tarifa_mas_barata($tarifas, $peso);

			if ($precio === false) return false;

			if ($this->fee > 0) $precio += $this->fee;			
			if ($this->cargo > 0) 
			{
				if (strpos($this->cargo, '%')) $precio += $precio * (str_replace( '%', '',$this->cargo) / 100);
				else $precio += $this->cargo;
			}

			$impuestos = false;
			if ($this->tax_status != 'none') 
			{
				$impuestos = new WC_Tax();
				$impuestos = $impuestos->calc_shipping_tax($precio, $impuestos->get_rates($this->settings['Tax_' . $grupo]));
			}

			$tarifa = array(
				'id'		=> $this->id,
				'label'		=> $this->title,
				'cost'		=> $precio,
				'taxes'		=> $impuestos,
				'calc_tax'	=> 'per_order'
			);

			$this->add_rate($tarifa);
		}

		//Selecciona el/los grupo/s según la dirección de envío del cliente
		function dame_grupos($paquete = array()) {
			$codigo_postal = strtoupper(woocommerce_clean($paquete['destination']['postcode']));
			$codigos_postales = array($codigo_postal);
			$tamano_codigo_postal = strlen($paquete['destination']['postcode']);

			for ($i = 0; $i < $tamano_codigo_postal; $i++) 
			{
				$codigo_postal = substr($codigo_postal, 0, -1);
				$codigos_postales[] = $codigo_postal . '*';
			}

			$grupos = array ('P' => 'postcode', 'S' => 'state', 'C' => 'country');
			foreach ($grupos as $letra => $nombre)
			{
				$contador = 1;

				while (isset($this->settings[$letra . $contador])) 
				{
				    if ($nombre == 'postcode')
					{
						$grupos = explode(";", $this->settings[$letra . $contador]);
						foreach ($codigos_postales as $codigo_postal) 
						{
							foreach ($grupos as $grupo_tarifa)
							{
								if ($codigo_postal == $grupo_tarifa) $grupo = $letra . $contador;
							}
						}
					}
					else 
					{
						if (in_array($paquete['destination'][$nombre], $this->settings[$letra . $contador])) $grupo = $letra . $contador;
					}
				    $contador++;
				}
	
    	        if (isset($grupo)) return $grupo;				
			}
        }

		//Devuelve la tarifa aplicable al grupo/s seleccionado/s
		function dame_tarifas($grupo = null) {
			$tarifas = array();
			if (sizeof($this->options) > 0) foreach ($this->options as $indice => $opcion) 
			{
			    $tarifa = preg_split('~\s*\|\s*~', trim($opcion));

			    if (sizeof($tarifa) !== 3) continue;
				else $tarifas[] = $tarifa;
			}

			foreach ($tarifas as $tarifa) 
			{
				$grupos = explode(",", $tarifa[2]);
				foreach ($grupos as $grupo_tarifa)
				{
				    if ($grupo_tarifa == $grupo) $tarifa_de_grupo[] = $tarifa;
				}
			}
			
			return $tarifa_de_grupo;
		}

		//Selecciona la tarifa más barata
		function dame_tarifa_mas_barata($tarifas, $peso) {
			if ($peso == 0) return 0; // no shipping for cart without weight

			if (sizeof($tarifas) > 0) foreach ($tarifas as $indice => $tarifa) 
			{
			    if ($peso <= $tarifa[0]) $gasto_de_envio[] = $tarifa[1];
			    $tarifa_gasto_de_envio[] = $tarifa[1];
			}

			if (sizeof($gasto_de_envio) > 0) return min($gasto_de_envio);
			else 
			{
			    if (sizeof($tarifa_gasto_de_envio) > 0) return max($tarifa_gasto_de_envio);
			}
			
			return false;
		}

	    //Actualiza los países específicos
		function sincroniza_paises() {
			if ($this->settings['sync_countries'] == 'yes') 
			{
				$paises = $this->dame_paises_especificos();
				update_option('woocommerce_specific_allowed_countries', $paises);
			} 
		}
		
	    //Devuelve los países específicos
		function dame_paises_especificos() {  
			$contador = 1;
			$paises_iniciales = array();
			while (is_array($this->settings['C' . $contador])) 
			{
				$paises_iniciales = array_merge($paises_iniciales, $this->settings['C' . $contador]);
				$contador++;
			}
	
			$this->settings = NULL;
			$this->init_settings();
			$contador = 1;
			$paises_nuevos = array();
			while (is_array($this->settings['C' . $contador])) 
			{
			    $paises_nuevos = array_merge($paises_nuevos, $this->settings['C' . $contador]);
				$contador++;
			}
			
			$allowed_countries = get_option('woocommerce_specific_allowed_countries');
			if (is_array($allowed_countries)) $paises = array_merge($paises_nuevos, $allowed_countries);
			$paises = array_unique($paises_nuevos);
			
			$paises_borrados = array_diff($paises_iniciales, $paises_nuevos);
			if (!empty($paises_borrados))
			{
				foreach ($paises_borrados as $pais_borrado) 
				{
					if (($indice = array_search($pais_borrado, $paises)) !== false) unset($paises[$indice]);
				}
			}

        	return $paises;
    	}

		//Pinta el formulario
		public function admin_options() {
			?>
			<style type="text/css">
			div.donacion {
				background: #FFFFE0;
				border: 1px solid #E6DB55;
				float: right;
				margin: 10px 0px;
				padding: 10px;
				width: 220px;
				text-align: center;
			}
			div.donacion div {
				padding: 10px;
				margin: 10px auto 0px;
				width: 190px;
				border-top: 1px solid #E6DB55;
			}
			.woocommerce table.form-table {
				clear:none!important;
				width:80%;
			}
			</style>
			<h3><?php _e('Weight and Postcode/State/Country based shipping', 'apg_shipping'); ?></h3>
			<p><?php _e('Lets you calculate shipping cost based on Postcode/State/Country and weight of the cart. Lets you set an unlimited weight bands on per postcode/state/country basis and group the groups that that share same delivery cost/bands. For more help and know how to use the plugin visit <a href="http://www.artprojectgroup.es/plugins-para-wordpress/woocommerce-apg-weight-and-postcodestatecountry-shipping" target="_blank">WooCoomerce - APG Weight and Postcode/State/Country Shipping</a>.', 'apg_shipping'); ?></p>
			<div class="donacion"><?php _e('If you enjoyed and find helpful this plugin, please make a donation.', 'apg_shipping'); ?>
				<div><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LB54JTPQGW9ZW" target="_blank" title="PayPal"><img alt="WooCoomerce - APG Weight and Postcode/State/Country Shipping" border="0" src="<?php _e('https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif', 'apg_shipping'); ?>" width="92" height="26"></a></div>
			</div>
			<table class="form-table">
			   	<?php $this->generate_settings_html(); ?>
			</table><!--/.form-table-->
			<?php
		}
	}
	
	//Añade clases necesarias para nuevos gastos de envío
	$contador = count(dame_apg_shipping());
	$cuenta = 2;
	for ($i = 0; $i < $contador; $i++)
	{
		eval("
		class apg_shipping_$cuenta extends apg_shipping {

        	function __construct() {
				global \$woocommerce;
			
				\$shipping = dame_apg_shipping();
	
				\$this->id 			= \"apg_shipping_$cuenta\";
        	    \$this->method_title	= __(\$shipping[$i], 'apg_shipping');

				parent::init();
	        }
		}
		");
		$cuenta++;
	}
}
add_action('plugins_loaded', 'apg_shipping_inicio', 0);

//Añade APG Shipping a WooCommerce
function apg_shipping_anade_gastos_de_envio($methods) {
	$methods[] = 'apg_shipping';

	$cuenta = 2;
	$contador = count(dame_apg_shipping());
	for ($i = 0; $i < $contador; $i++)
	{
		$shipping = 'apg_shipping_' . $cuenta;
		$methods[] = $shipping;
		$cuenta++;
	}

	for ($i = $cuenta; $i < 100; $i++)
	{
		$shipping = 'woocommerce_apg_shipping_' . $i . '_settings';
		if (get_option($shipping)) delete_option($shipping);
	}

	return $methods;
}
add_filter('woocommerce_shipping_methods', 'apg_shipping_anade_gastos_de_envio');

//Añade un nuevo campo a Opciones de envío para añadir nuevos gastos de envío
function apg_shipping_nuevos_gastos_de_envio($configuracion) {
	$anadir_seccion = array();

	foreach ($configuracion as $seccion) 
	{
		if ((isset($seccion['id']) && $seccion['id'] == 'shipping_options') && (isset($seccion['type']) && $seccion['type'] == 'sectionend')) 
		{
			$anadir_seccion[] = array(
				'name'     => __('Additional Shipping', 'apg_shipping'),
				'desc_tip' => __('List additonal shipping classes below (1 per line). This is in addition to the default <code>APG shipping</code>.', 'apg_shipping'),
				'id'       => 'woocommerce_apg_shipping',
				'type'     => 'textarea',
				'css'      => 'min-width:300px;',
				'default'  => '',
      		);
    	}

		$anadir_seccion[] = $seccion;
	}
	
	return $anadir_seccion;
}
add_filter('woocommerce_shipping_settings', 'apg_shipping_nuevos_gastos_de_envio');

//Función que lee y devuelve los nuevos gastos de envío
function dame_apg_shipping() {
	global $woocommerce;
	
	$shippings = array_filter(array_map('trim', explode("\n", get_option('woocommerce_apg_shipping'))));
	
	return $shippings;
}

//Función que lee y devuelve los tipos de impuestos
function dame_impuestos() {
	global $woocommerce;
	
	$impuestos = array_filter(array_map('trim', explode( "\n", get_option('woocommerce_tax_classes'))));
	$tipos_impuestos = array();
	if ($impuestos)
	{
		foreach ($impuestos as $impuesto) $tipos_impuestos[sanitize_title($impuesto)] = esc_html($impuesto);
	}
	
	return $tipos_impuestos;
}
?>
