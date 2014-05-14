<?php
/*
Plugin Name: WooCommerce - APG Weight and Postcode/State/Country Shipping
Version: 1.6.3.1
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

//Definimos las variables
$apg_shipping = array('plugin' 		=> 'WooCommerce - APG Weight and Postcode/State/Country Shipping', 
						'plugin_uri' 	=> 'woocommerce-apg-weight-and-postcodestatecountry-shipping', 
						'donacion' 	=> 'http://www.artprojectgroup.es/donacion',
						'soporte' 		=> 'http://www.artprojectgroup.es/servicios/servicios-para-wordpress-y-woocommerce/soporte-tecnico',
						'plugin_url' 	=> 'http://www.artprojectgroup.es/plugins-para-wordpress/plugins-para-woocommerce/woocommerce-apg-weight-and-postcodestatecountry-shipping', 
						'ajustes' 		=> 'admin.php?page=wc-settings&tab=shipping&section=apg_shipping', 
						'puntuacion' 	=> 'http://wordpress.org/support/view/plugin-reviews/woocommerce-apg-weight-and-postcodestatecountry-shipping');

//Carga el idioma
load_plugin_textdomain('apg_shipping', null, dirname(plugin_basename(__FILE__)) . '/lang');

//Enlaces adicionales personalizados
function apg_shipping_enlaces($enlaces, $archivo) {
	global $apg_shipping;
	
	$plugin = plugin_basename(__FILE__);

	if ($archivo == $plugin) 
	{
		$plugin = apg_shipping_plugin($apg_shipping['plugin_uri']);
		$enlaces[] = '<a href="' . $apg_shipping['donacion'] . '" target="_blank" title="' . __('Make a donation by ', 'apg_shipping') . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[] = '<a href="'. $apg_shipping['plugin_url'] . '" target="_blank" title="' . $apg_shipping['plugin'] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[] = '<a href="https://www.facebook.com/artprojectgroup" title="' . __('Follow us on ', 'apg_shipping') . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __('Follow us on ', 'apg_shipping') . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://plus.google.com/+ArtProjectGroupES" title="' . __('Follow us on ', 'apg_shipping') . 'Google+" target="_blank"><span class="genericon genericon-googleplus-alt"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="' . __('Follow us on ', 'apg_shipping') . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[] = '<a href="http://profiles.wordpress.org/artprojectgroup/" title="' . __('More plugins on ', 'apg_shipping') . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[] = '<a href="mailto:info@artprojectgroup.es" title="' . __('Contact with us by ', 'apg_shipping') . 'e-mail"><span class="genericon genericon-mail"></span></a> <a href="skype:artprojectgroup" title="' . __('Contact with us by ', 'apg_shipping') . 'Skype"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[] = '<div class="star-holder rate"><div style="width:' . esc_attr(str_replace(',', '.', $plugin['rating'])) . 'px;" class="star-rating"></div><div class="star-rate"><a title="' . __('***** Fantastic!', 'apg_shipping') . '" href="' . $apg_shipping['puntuacion'] . '?rate=5#postform" target="_blank"><span></span></a> <a title="' . __('**** Great', 'apg_shipping') . '" href="' . $apg_shipping['puntuacion'] . '?rate=4#postform" target="_blank"><span></span></a> <a title="' . __('*** Good', 'apg_shipping') . '" href="' . $apg_shipping['puntuacion'] . '?rate=3#postform" target="_blank"><span></span></a> <a title="' . __('** Works', 'apg_shipping') . '" href="' . $apg_shipping['puntuacion'] . '?rate=2#postform" target="_blank"><span></span></a> <a title="' . __('* Poor', 'apg_shipping') . '" href="' . $apg_shipping['puntuacion'] . '?rate=1#postform" target="_blank"><span></span></a></div></div>';
	}
	
	return $enlaces;
}
add_filter('plugin_row_meta', 'apg_shipping_enlaces', 10, 2);

//Añade el botón de configuración
function apg_shipping_enlace_de_ajustes($enlaces) { 
	global $apg_shipping;

	$enlaces_de_ajustes = array('<a href="' . $apg_shipping['ajustes'] . '" title="' . __('Settings of ', 'apg_shipping') . $apg_shipping['plugin'] .'">' . __('Settings', 'apg_shipping') . '</a>', '<a href="' . $apg_shipping['soporte'] . '" title="' . __('Support of ', 'apg_shipping') . $apg_shipping['plugin'] .'">' . __('Support', 'apg_shipping') . '</a>');
	foreach($enlaces_de_ajustes as $enlace_de_ajustes)	array_unshift($enlaces, $enlace_de_ajustes); 
	
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
			$this->method_title	= __("APG Shipping", 'apg_shipping');
			$this->init();
		}

		//Inicializa los datos
        function init() {
			add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
			add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'sincroniza_paises'));
			add_action('woocommerce_shipping_apg_free_shipping_is_available', array($this, 'chequea_apg_free_shipping'));

			$this->init_settings();
			if (isset($this->settings['global'])) 
			{
				$this->settings['global_countries'] = $this->settings['global'];
				unset($this->settings['global']);
			}
			$campos = array('enabled', 'title', 'postal_group_no', 'state_group_no', 'country_group_no', 'tax_status', 'fee', 'cargo', 'maximo', 'grupos_excluidos', 'options', 'pago');
			if (get_option('woocommerce_allowed_countries') == 'specific') $campos[] = 'sync_countries';
			if (get_option('woocommerce_allowed_countries') == 'all') $campos[] = 'global_countries';
			if (class_exists('apg_free_shipping')) $campos[] = 'muestra';
			foreach ($campos as $campo) $this->$campo = isset($this->settings[$campo]) ? $this->settings[$campo] : '';
			$this->init_form_fields();
			
			$this->availability		= 'specific';
			$this->countries			= get_option('woocommerce_specific_allowed_countries');
			$this->type				= 'order';
			$this->options				= (array) explode("\n", $this->options);
			$this->apg_free_shipping	= false;

			for ($contador = 1; $this->postal_group_no >= $contador; $contador++) 
			{
				if (isset($this->settings['P' . $contador])) $this->procesa_codigo_postal($this->settings['P' . $contador], 'P' . $contador);
			}
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
					'type'			=> 'price',
					'desc_tip'		=> __('Fee excluding tax. Enter an amount, e.g. 2.50. Leave blank to disable.', 'apg_shipping'),
					'default'		=> '',
					'placeholder'	=> wc_format_localized_price(0)
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
					'desc_tip'		=> __('Set your weight based rates for postcode/state/country groups (one per line). You may optionally add the maximum dimensions. Example: <code>Max weight|Cost|postcode/state/country group code separated by comma (,)|LxWxH (optional)</code>. Also you can set your dimensions based rates. Example: <code>LxWxH|Cost|postcode/state/country group code separated by comma (,)</code>', 'apg_shipping'),
					'css'			=> 'width:300px;',
					'default'		=> '',
					'description'	=> '<code>1000|6.95|P2,S1,C3|10x10x10</code><br /><code>10x10x10|6.95|P2,S1,C3</code><br />' . sprintf(__('Remember your weight unit: %s, and dimensions unit: %s.', 'apg_shipping'), get_option('woocommerce_weight_unit'),get_option('woocommerce_dimension_unit')),
				),
				'maximo' => array(
					'title'			=> __('Overweight/over dimensions', 'apg_shipping'),
					'type'			=> 'checkbox',
					'label'			=> __('Return the maximum price.', 'apg_shipping'),
					'default'		=> 'yes',
				),
				'postal_group_no' => array(
					'title'			=> __('Number of postcode groups', 'apg_shipping'),
					'type'			=> 'number',
					'desc_tip'		=> __('Number of groups of ZIP/Postcode sharing delivery rates. (Hit "Save changes" button after you have changed this setting).', 'apg_shipping'),
					'default'		=> '0',
				),
				'state_group_no' => array(
					'title'			=> __('Number of state groups', 'apg_shipping'),
					'type'			=> 'number',
					'desc_tip'		=> __('Number of groups of states sharing delivery rates. (Hit "Save changes" button after you have changed this setting).', 'apg_shipping'),
					'default'		=> '0',
				),
				'country_group_no' => array(
					'title'			=> __('Number of country groups', 'apg_shipping'),
					'type'			=> 'number',
					'desc_tip'		=> __('Number of groups of countries sharing delivery rates. (Hit "Save changes" button after you have changed this setting).', 'apg_shipping'),
					'default'		=> '0',
				),
			);
			if (get_option('woocommerce_allowed_countries') == 'specific') $this->form_fields['sync_countries'] = array(
					'title'			=> __('Add countries to allowed', 'apg_shipping'),
					'type'			=> 'checkbox',
					'label'			=> __('Countries added to country groups will be automatically added to <em>Allowed Countries</em> in <a href="admin.php?page=wc-settings&tab=general">General settings</a> tab.', 'apg_shipping'),
					'default'		=> 'no',
			);
			if (get_option('woocommerce_allowed_countries') == 'all') $this->form_fields['global_countries'] = array(
					'title'			=> __('Add global group', 'apg_shipping'),
					'type'			=> 'checkbox',
					'label'			=> sprintf(__('Add group C%s for the other countries.', 'apg_shipping'), $this->country_group_no + 1),
					'default'		=> 'no',
			);
			if (WC()->shipping->get_shipping_classes() && get_option('woocommerce_allowed_countries') == 'all') $this->form_fields['Class_C' . ($this->country_group_no + 1)] =  array(
					'title' 		=> sprintf(__('C%s Shipping Class:', 'apg_shipping'), $this->country_group_no + 1),
					'desc_tip' 	=> sprintf(__('Select the shipping class for Country Group %s', 'apg_shipping'), $this->country_group_no + 1),
					'css'			=> 'width: 450px;',
					'default'		=> array('todas'),
					'type'			=> 'multiselect',
					'class'			=> 'chosen_select',
					'options' 		=> array('todas' => __('All enabled shipping class', 'apg_shipping')) + apg_shipping_dame_clases_de_envio(),
			);
			if ($this->tax_status != 'none' && get_option('woocommerce_allowed_countries') == 'all') $this->form_fields['Tax_C' . ($this->country_group_no + 1)] =  array(
					'title' 		=> sprintf(__('C%s Tax Class:', 'apg_shipping'), $this->country_group_no + 1),
					'desc_tip' 	=> sprintf(__('Select the tax class for Country Group %s', 'apg_shipping'), $this->country_group_no + 1),
					'css' 			=> 'min-width:150px;',
					'default'		=> get_option('woocommerce_shipping_tax_class'),
					'type' 			=> 'select',
					'options' 		=> array('standard' => __('Standard', 'apg_shipping')) + apg_shipping_dame_impuestos(),
			);
			$this->form_fields['grupos_excluidos'] = array(
					'title'			=> __('No shipping', 'apg_shipping'),
					'type'			=> 'text',
					'desc_tip'		=> sprintf(__("Group/s of ZIP/Postcode/State where %s doesn't accept shippings. Example: <code>Postcode/state group code separated by comma (,)</code>", 'apg_shipping'), get_bloginfo('name')),
					'default'		=> '',
					'description'	=> '<code>P2,S1</code>',
			);
			$this->form_fields['pago'] = array(
					'title'			=> __('Payment gateway', 'apg_shipping'),
					'desc_tip'		=> sprintf(__("Payment gateway available for %s", 'apg_shipping'), $this->method_title),
					'css'			=> 'width: 450px;',
					'default'		=> array('todos'),
					'type'			=> 'multiselect',
					'class'			=> 'chosen_select',
					'options' 		=> array('todos' => __('All enabled payments', 'apg_shipping')) + apg_shipping_dame_medios_de_pago(),
			);
			if (class_exists('apg_free_shipping')) $this->form_fields['muestra'] = array(
					'title'			=> __('Show only APG Free Shipping', 'apg_shipping'),
					'type'			=> 'checkbox',
					'label'			=> __('Don\'t show shipping cost if <a href="http://wordpress.org/plugins/woocommerce-apg-free-postcodestatecountry-shipping/" target="_blank" title="WordPress.org">WooCommerce - APG Free Postcode/State/Country Shipping</a> is available.', 'apg_shipping'),
					'default'		=> 'no',
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
					'default'  => '',
				);
				if (WC()->shipping->get_shipping_classes()) $this->form_fields['Class_P' . $contador] = array(
					'title'		=> sprintf(__('P%s Shipping Class:', 'apg_shipping'), $contador, $contador),
					'desc_tip' => sprintf(__('Select the shipping class for Postcode Group %s', 'apg_shipping'), $contador),
					'css'		=> 'width: 450px;',
					'default'	=> array('todas'),
					'type'		=> 'multiselect',
					'class'		=> 'chosen_select',
					'options' 	=> array('todas' => __('All enabled shipping class', 'apg_shipping')) + apg_shipping_dame_clases_de_envio(),
				);
				if ($this->tax_status != 'none') $this->form_fields['Tax_P' . $contador] =  array(
					'title' 	=> sprintf(__('P%s Tax Class:', 'apg_shipping'), $contador),
					'desc_tip' => sprintf(__('Select the tax class for Postcode Group %s', 'apg_shipping'), $contador),
					'css' 		=> 'min-width:150px;',
					'default'	=> get_option('woocommerce_shipping_tax_class'),
					'type' 		=> 'select',
					'options' 	=> array('standard' => __('Standard', 'apg_shipping')) + apg_shipping_dame_impuestos(),
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
					'options'	=> $woocommerce->countries->get_states($base_country),
				);
				if (WC()->shipping->get_shipping_classes()) $this->form_fields['Class_S' . $contador] = array(
					'title'		=> sprintf(__('S%s Shipping Class:', 'apg_shipping'), $contador, $contador),
					'desc_tip' => sprintf(__('Select the shipping class for State Group %s', 'apg_shipping'), $contador),
					'css'		=> 'width: 450px;',
					'default'	=> array('todas'),
					'type'		=> 'multiselect',
					'class'		=> 'chosen_select',
					'options' 	=> array('todas' => __('All enabled shipping class', 'apg_shipping')) + apg_shipping_dame_clases_de_envio(),
				);
				if ($this->tax_status != 'none') $this->form_fields['Tax_S' . $contador] =  array(
					'title' 	=> sprintf(__('S%s Tax Class:', 'apg_shipping'), $contador),
					'desc_tip' => sprintf(__('Select the tax class for State Group %s', 'apg_shipping'), $contador),
					'css' 		=> 'min-width:150px;',
					'default'	=> get_option('woocommerce_shipping_tax_class'),
					'type' 		=> 'select',
					'options' 	=> array('standard' => __('Standard', 'apg_shipping')) + apg_shipping_dame_impuestos(),
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
					'options'	=> WC()->countries->get_shipping_countries(),
				);
				if (WC()->shipping->get_shipping_classes()) $this->form_fields['Class_C' . $contador] = array(
					'title'		=> sprintf(__('C%s Shipping Class:', 'apg_shipping'), $contador, $contador),
					'desc_tip' => sprintf(__('Select the shipping class for Country Group %s', 'apg_shipping'), $contador),
					'css'		=> 'width: 450px;',
					'default'	=> array('todas'),
					'type'		=> 'multiselect',
					'class'		=> 'chosen_select',
					'options' 	=> array('todas' => __('All enabled shipping class', 'apg_shipping')) + apg_shipping_dame_clases_de_envio(),
				);
				if ($this->tax_status != 'none') $this->form_fields['Tax_C' . $contador] =  array(
					'title' 	=> sprintf(__('C%s Tax Class:', 'apg_shipping'), $contador),
					'desc_tip' => sprintf(__('Select the tax class for Country Group %s', 'apg_shipping'), $contador),
					'css' 		=> 'min-width:150px;',
					'default'	=> get_option('woocommerce_shipping_tax_class'),
					'type' 		=> 'select',
					'options' 	=> array('standard' => __('Standard', 'apg_shipping')) + apg_shipping_dame_impuestos(),
				);
			}
		}

		//Comprueba el estado de apg_free_shipping
		function chequea_apg_free_shipping($valores) {
			$this->apg_free_shipping = $valores;
			
			return $valores;
		}
		
		//Calcula el gasto de envío
		function calculate_shipping($paquete = array()) {
			global $woocommerce;
			
			//Peso total del pedido
			$peso_total = $woocommerce->cart->cart_contents_weight;
			
			//Toma distintos datos de los productos
			$largo = $ancho = $alto = 0;
			$clases = array();
			foreach ($woocommerce->cart->get_cart() as $identificador => $valores) 
			{
				$producto = $valores['data'];
				$peso = $producto->get_weight() * $valores['quantity'];
				
				//Arregla un problema con los pesos en variaciones virtuales
				if ($producto->is_virtual()) $peso_total -= $peso;

				//Medidas
				if ($producto->length) $largo += ($producto->length * $valores['quantity']);
				if ($producto->width) $ancho += ($producto->width * $valores['quantity']);
				if ($producto->height) $alto += ($producto->height * $valores['quantity']);
				
				//Clase de producto
				if ($producto->needs_shipping()) 
				{
					$clase = $producto->get_shipping_class();
					if (empty($clase)) $clase = 'todas';
					if (!isset($clases[$clase])) $clases[$clase] = $peso;
					else $clases[$clase] += $peso;
				}
     		}
			//$clases['todas'] = $peso_total;
			
			$grupos = $this->dame_grupos($paquete, $clases);
			if (empty($grupos)) return false; //No hay resultados

			$grupos_excluidos = explode(',', preg_replace('/\s+/', '', $this->grupos_excluidos));
			foreach ($grupos_excluidos as $grupo_excluido) if (in_array($grupo_excluido, $grupos)) return false; //No atiende a los grupos excluidos
			
			if ($this->apg_free_shipping && $this->muestra == 'yes') return false; //Sólo muestra el envío gratuito
			
			$tarifas = $this->dame_tarifas($grupos); //Recoge las tarifas programadas

			$precios = $this->dame_tarifa_mas_barata($tarifas, $peso_total, $largo, $ancho, $alto, $grupos, $clases); //Filtra las tarifas
			if (empty($precios)) return false; //No hay tarifa
			
			//Calculamos el precio
			$precio_total = $impuestos_totales = 0;
			$impuestos_parciales = $impuestos_totales = array();
			if ($this->tax_status != 'none') $impuestos = new WC_Tax();
			
			foreach ($precios as $grupo => $precio)
			{
				$precio_total += $precio;
				if ($this->tax_status != 'none') $impuestos_parciales[] = $impuestos->calc_shipping_tax($precio, $impuestos->get_shipping_tax_rates($this->settings['Tax_' . $grupo]));
			}

			foreach ($impuestos_parciales as $impuesto_parcial)
			{
				foreach ($impuesto_parcial as $clave => $impuesto) $impuestos_totales[$clave] = $impuesto;
			}

			//Cargos adicionales
			if ($this->fee > 0) $precio_total += $this->fee;			
			if ($this->cargo > 0) 
			{
				if (strpos($this->cargo, '%')) $precio_total += $precio_total * (str_replace('%', '',$this->cargo) / 100);
				else $precio += $this->cargo;
			}

			$tarifa = array(
				'id'		=> $this->id,
				'label'		=> $this->title,
				'cost'		=> $precio_total,
				'taxes'		=> $impuestos_totales,
				'calc_tax'	=> 'per_order'
			);

			$this->add_rate($tarifa);
		}

		//Selecciona el/los grupo/s según la dirección de envío del cliente
		function dame_grupos($paquete = array(), $clases = array()) {
			$grupo = array();
			$codigo_postal = strtoupper(woocommerce_clean($paquete['destination']['postcode']));
			$codigos_postales = array($codigo_postal);
			$tamano_codigo_postal = strlen($paquete['destination']['postcode']);
			
			//Prepraramos los códigos postales
			for ($i = 0; $i < $tamano_codigo_postal; $i++) 
			{
				$codigo_postal = substr($codigo_postal, 0, -1);
				$codigos_postales[] = $codigo_postal . '*';
			}
			
			//Revisamos los grupos
			$grupos = array ('P' => 'postcode', 'S' => 'state', 'C' => 'country');
			foreach ($grupos as $letra => $nombre)
			{
				$contador = 1;

				while (isset($this->settings[$letra . $contador]) && $this->settings[$letra . $contador]) 
				{
				    if ($nombre == 'postcode')
					{
						$grupos = explode(";", $this->settings[$letra . $contador]);
						foreach ($codigos_postales as $codigo_postal) 
						{
							foreach ($grupos as $grupo_postal)
							{
								if ($codigo_postal == $grupo_postal && isset($this->settings["Class_" . $letra . $contador][0]))
								{
									foreach ($clases as $clase)
									{
										if ($this->settings["Class_" . $letra . $contador][0] == $clase) $grupo[$clase] = $letra . $contador;
									}
								}
								else if ($codigo_postal == $grupo_postal) $grupo[] = $letra . $contador;
							}
						}
					}
					else 
					{
						if (isset($paquete['destination'][$nombre]) && in_array($paquete['destination'][$nombre], $this->settings[$letra . $contador])) 
						{
							if (isset($this->settings["Class_" . $letra . $contador][0]))
							{
								if ($this->settings["Class_" . $letra . $contador][0] == 'todas') $grupo['todas'] = $letra . $contador;
								foreach ($clases as $clase => $peso)
								{
									if ($this->settings["Class_" . $letra . $contador][0] == $clase) $grupo[$clase] = $letra . $contador;
								}
							}
							else $grupo[] = $letra . $contador;
						}
					}
				    $contador++;
				}
			}
			
			//Grupo internacional
			if (empty($grupo) && get_option('woocommerce_allowed_countries') == 'all' && $this->global_countries == 'yes')
			{
				$contador = ($this->country_group_no + 1);
				
				if (isset($this->settings["Class_C" . $contador][0]))
				{
					foreach ($clases as $clase)			
					{
						if ($this->settings["Class_C" . $contador][0] == $clase) $grupo[$clase] = "C" . $contador;
					}
				}
				else $grupo[] = "C" . $contador;
			}

			return $grupo;
        }

		//Devuelve la tarifa aplicable al grupo/s seleccionado/s
		function dame_tarifas($grupos = array()) {
			$tarifas = $tarifa_de_grupo = array();
			
			//Recoge las tarifas programadas
			if (!empty($this->options)) foreach ($this->options as $indice => $opcion) 
			{
			    $tarifa = preg_split('~\s*\|\s*~', preg_replace('/\s+/', '', $opcion));

			    if (sizeof($tarifa) < 3) continue;
				else $tarifas[] = $tarifa;
			}
			
			//Procesa las tarifas
			foreach ($tarifas as $tarifa) 
			{
				$grupos_de_tarifas = explode(",", $tarifa[2]);
				foreach ($grupos_de_tarifas as $grupo_de_tarifa)
				{
					foreach ($grupos as $grupo) if ($grupo_de_tarifa == $grupo) $tarifa_de_grupo[] = $tarifa;
				}
			}
			
			return $tarifa_de_grupo;
		}

		//Selecciona la tarifa más barata
		function dame_tarifa_mas_barata($tarifas, $peso_total, $largo, $ancho, $alto, $grupos, $clases) {
			$gasto_de_envio = $tarifa_gasto_de_envio = array();
			if (!empty($grupos)) foreach ($grupos as $clase => $grupo) 
			{
				$peso = 0;
				if (isset($clases[$clase])) $peso_parcial = $clases[$clase];
				else if (isset($clases['todas'])) $peso_parcial = $clases['todas'];
				else $peso_parcial = $peso_total;

				foreach ($tarifas as $indice => $tarifa)
				{	
					if (strpos($tarifa[2], $grupo) !== false) //El grupo existe en las tarifas recogidas.
					{			
						$tamano = $dimensiones = false;
						if (stripos($tarifa[0], "x")) //Son dimensiones no pesos
						{
							$dimensiones = true;
							$medidas = strtolower($tarifa[0]);
						}
						if (isset($tarifa[3])) $medidas = strtolower($tarifa[3]);
					
						//Son unas medidas
						if (isset($medidas))
						{
							$medidas = explode("x", $medidas);
							if ($largo > $medidas[0] || $ancho > $medidas[1] || $alto > $medidas[2]) $tamano = true;
						}
					
						//Es un peso
						if (!$dimensiones && !$tamano)
						{
							if (!$peso || ($tarifa[0] >= $peso_parcial && $peso_parcial > $peso)) $gasto_de_envio[$grupo] = $tarifa[1];
							else if (($this->maximo == "yes") && empty($gasto_de_envio[$grupo])) $gasto_de_envio[$grupo] = $tarifa[1];
							$peso = $tarifa[0];
						}
						else
						{
							if (!$tamano) $gasto_de_envio[$tarifa[2]] = $tarifa[1];
						}
						$tarifa_gasto_de_envio[$tarifa[2]][] = $tarifa[1];
					}
				}
				if ($this->maximo == "no" && ($peso_parcial > $peso)) unset($gasto_de_envio[$grupo]);
			}

			if (!empty($gasto_de_envio)) return $gasto_de_envio;
			else 
			{
			    if (!empty($tarifa_gasto_de_envio) && $this->maximo == "yes") return $tarifa_gasto_de_envio;
			}
			
			return array();
		}

	    //Actualiza los países específicos
		function sincroniza_paises() {
			if (get_option('woocommerce_allowed_countries') == 'specific' && $this->settings['sync_countries'] == 'yes') 
			{
				$paises = $this->dame_paises_especificos();
				update_option('woocommerce_specific_allowed_countries', $paises);
			} 
		}
		
	    //Devuelve los países específicos
		function dame_paises_especificos() {  
			$contador = 1;
			$paises_iniciales = array();
			while (isset($this->settings['C' . $contador]) && is_array($this->settings['C' . $contador])) 
			{
				$paises_iniciales = array_merge($paises_iniciales, $this->settings['C' . $contador]);
				$contador++;
			}
	
			$this->settings = NULL;
			$this->init_settings();
			$contador = 1;
			$paises_nuevos = array();
			while (isset($this->settings['C' . $contador]) && is_array($this->settings['C' . $contador])) 
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
			wp_enqueue_style('apg_shipping_hoja_de_estilo'); //Carga la hoja de estilo
			include('formulario.php');
		}
	}
	
	//Añade clases necesarias para nuevos gastos de envío
	$contador = count(apg_shipping_lee_envios());
	$cuenta = 2;
	for ($i = 0; $i < $contador; $i++)
	{
		eval("
		class apg_shipping_$cuenta extends apg_shipping {

        	function __construct() {
				global \$woocommerce;
			
				\$shipping = apg_shipping_lee_envios();
	
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
	$contador = count(apg_shipping_lee_envios());
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
				'css'      => 'height: 150px;width: 100%;max-width:590px;',
				'default'  => '',
      		);
    	}

		$anadir_seccion[] = $seccion;
	}
	
	return $anadir_seccion;
}
add_filter('woocommerce_shipping_settings', 'apg_shipping_nuevos_gastos_de_envio');

//Función que lee y devuelve los nuevos gastos de envío
function apg_shipping_lee_envios() {
	global $woocommerce;
	
	$shippings = array_filter(array_map('trim', explode("\n", get_option('woocommerce_apg_shipping'))));
	
	return $shippings;
}

//Función que lee y devuelve los tipos de medios de pago
function apg_shipping_dame_medios_de_pago() {
	global $woocommerce;

	$medios = array();
	
	foreach (get_option('woocommerce_gateway_order') as $medio_de_pago => $numero)
	{
		$configuracion = get_option('woocommerce_' . $medio_de_pago . '_settings');
		if ($configuracion['enabled'] == 'yes') $medios[$medio_de_pago] = $configuracion['title'];
	}

	return $medios;
}

//Función que lee y devuelve los tipos de clases de envío
function apg_shipping_dame_clases_de_envio() {
	global $woocommerce;

	$clases = array();

	if ( WC()->shipping->get_shipping_classes() ) 
	{
		foreach ( WC()->shipping->get_shipping_classes() as $shipping_class ) $clases[esc_attr($shipping_class->slug)] = $shipping_class->name;
	} 
	else $clases[] = __( 'Select a class&hellip;', 'apg_shipping' );
	
	return $clases;
}	

//Recoge los medios de pago
function apg_shipping_filtra_medios_de_pago($medios) {
	global $woocommerce;

	if (isset($woocommerce->session->chosen_shipping_method)) $configuracion = get_option('woocommerce_' . $woocommerce->session->chosen_shipping_method . '_settings');
	else if (isset($_POST['shipping_method'])) $configuracion = get_option('woocommerce_' . $_POST['shipping_method'][0] . '_settings');
	
	if (isset($_POST['payment_method']) && !$medios) $medios = $_POST['payment_method'];

	if (isset($configuracion['pago']) && $configuracion['pago'][0] != 'todos')
	{
		foreach ($medios as $nombre => $medio)
		{
			if (is_array($configuracion['pago']))
			{
				if (!in_array($nombre, $configuracion['pago'])) unset($medios[$nombre]);
			}
			else
			{ 
				if ($nombre != $configuracion['pago']) unset($medios[$nombre]);
			}
		}
	}

	return $medios;
}
add_filter('woocommerce_available_payment_gateways', 'apg_shipping_filtra_medios_de_pago');

//Función que lee y devuelve los tipos de impuestos
function apg_shipping_dame_impuestos() {
	global $woocommerce;
	
	$impuestos = array_filter(array_map('trim', explode("\n", get_option('woocommerce_tax_classes'))));
	$tipos_impuestos = array();
	if ($impuestos)
	{
		foreach ($impuestos as $impuesto) $tipos_impuestos[sanitize_title($impuesto)] = esc_html($impuesto);
	}
	
	return $tipos_impuestos;
}

//Obtiene toda la información sobre el plugin
function apg_shipping_plugin($nombre) {
	$argumentos = (object) array('slug' => $nombre);
	$consulta = array('action' => 'plugin_information', 'timeout' => 15, 'request' => serialize($argumentos));
	$respuesta = get_transient('apg_shipping_plugin');
	if (false === $respuesta) 
	{
		$respuesta = wp_remote_post('http://api.wordpress.org/plugins/info/1.0/', array('body' => $consulta));
		set_transient('apg_shipping_plugin', $respuesta, 24 * HOUR_IN_SECONDS);
	}
	if (!is_wp_error($respuesta)) $plugin = get_object_vars(unserialize($respuesta['body']));
	else $plugin['rating'] = 100;
	
	return $plugin;
}

//Muestra el mensaje de actualización
function apg_shipping_actualizacion() {
	global $apg_shipping;
	
    echo '<div class="error fade" id="message"><h3>' . $apg_shipping['plugin'] . '</h3><h4>' . sprintf(__("Please, update your %s. It's very important!", 'apg_shipping'), '<a href="' . $apg_shipping['ajustes'] . '" title="' . __('Settings', 'apg_shipping') . '">' . __('settings', 'apg_shipping') . '</a>') . '</h4></div>';
}

//Carga las hojas de estilo
function apg_shipping_muestra_mensaje() {
	wp_register_style('apg_shipping_hoja_de_estilo', plugins_url('style.css', __FILE__)); //Carga la hoja de estilo
	wp_register_style('apg_shipping_fuentes', plugins_url('fonts/stylesheet.css', __FILE__)); //Carga la hoja de estilo global
	wp_enqueue_style('apg_shipping_fuentes'); //Carga la hoja de estilo global

	$configuracion = get_option('woocommerce_apg_shipping_settings');
	if (!isset($configuracion['maximo'])) add_action('admin_notices', 'apg_shipping_actualizacion'); //Comprueba si hay que mostrar el mensaje de actualización
}
add_action('admin_init', 'apg_shipping_muestra_mensaje');

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_shipping_desinstalar() {
	delete_option('woocommerce_apg_shipping_settings');
	for ($i = 2; $i < 100; $i++)
	{
		$shipping = 'woocommerce_apg_shipping_' . $i . '_settings';
		if (get_option($shipping)) delete_option($shipping);
	}
	delete_transient('apg_shipping_plugin');
}
register_uninstall_hook( __FILE__, 'apg_shipping_desinstalar' );
?>
