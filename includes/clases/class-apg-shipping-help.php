<?php
/**
 * Ayuda contextual de los ajustes del método de envío: la pestaña «Ayuda» nativa de WordPress, con
 * un panel por cada bloque de la pantalla y una entrada por cada ajuste. La versión completa vive en
 * la web y se enlaza desde el lateral.
 *
 * A diferencia de un plugin con su propia página de ajustes, aquí la pantalla es la de ajustes de
 * WooCommerce, compartida con todo lo demás: los paneles sólo se añaden cuando se está editando una
 * instancia de este método, y la ayuda de WooCommerce se respeta en lugar de sustituirla.
 *
 * @package WC-APG-Weight-Shipping
 */

defined( 'ABSPATH' ) || exit;

/**
 * Añade los paneles de ayuda a los ajustes del método de envío.
 */
class APG_Shipping_Help {

	/**
	 * Identificador del método de envío, para no añadir la ayuda en los ajustes de otro.
	 */
	const METODO = 'apg_shipping';

	/**
	 * Dirección de la documentación completa, en español.
	 */
	const DOCS_URL = 'https://artprojectgroup.es/documentacion/wc-apg-envio-por-peso';

	/**
	 * Dirección de la documentación completa, en inglés.
	 */
	const DOCS_URL_EN = 'https://artprojectgroup.com/documentation/wc-apg-envio-por-peso';

	/**
	 * Añade los paneles del plugin a la pantalla de ajustes de envío.
	 *
	 * Se engancha en load-woocommerce_page_wc-settings, que cubre toda la pantalla de ajustes de
	 * WooCommerce, así que lo primero es comprobar que de verdad se está editando una instancia de
	 * este método.
	 *
	 * @return void
	 */
	public static function add_tabs() {
		if ( ! self::es_pantalla_del_metodo() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		// Aquí no se llama a remove_help_tabs(): la pantalla es de WooCommerce y su ayuda sigue
		// siendo útil. Los paneles de este método se añaden detrás de los suyos.
		foreach ( self::get_panels() as $id => $panel ) {
			$screen->add_help_tab(
				array(
					'id'      => 'apg_shipping_help_' . $id,
					'title'   => $panel['title'],
					'content' => self::render( $panel['items'] ),
				)
			);
		}

		// El lateral también es de WooCommerce: se conserva lo que haya y se añade lo del plugin.
		$screen->set_help_sidebar(
			$screen->get_help_sidebar() .
			'<p><strong>' . esc_html__( 'More help', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</strong></p>' .
			'<p><a href="' . esc_url( self::documentacion() ) . '" target="_blank">' . esc_html__( 'Full documentation', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) . '</a></p>'
		);
	}

	/**
	 * Comprueba que se está editando una instancia de este método de envío.
	 *
	 * Los parámetros se leen de la dirección sin nonce porque sólo deciden si se muestra la ayuda:
	 * no cambian nada ni dan acceso a nada.
	 *
	 * @return bool
	 */
	private static function es_pantalla_del_metodo() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$pestana     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$instancia   = isset( $_GET['instance_id'] ) ? absint( wp_unslash( $_GET['instance_id'] ) ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( 'shipping' !== $pestana || ! $instancia || ! class_exists( 'WC_Shipping_Zones' ) ) {
			return false;
		}

		$metodo = WC_Shipping_Zones::get_shipping_method( $instancia );

		return ( $metodo && self::METODO === $metodo->id );
	}

	/**
	 * Dirección de la documentación en el idioma de quien lee la pantalla.
	 *
	 * @return string
	 */
	private static function documentacion() {
		return ( 0 === strpos( get_user_locale(), 'es' ) ) ? self::DOCS_URL : self::DOCS_URL_EN;
	}

	/**
	 * Pinta un panel: un párrafo de entrada y una lista de término => explicación.
	 *
	 * @param array $items Primer elemento: texto de entrada (cadena). Después, término => explicación.
	 * @return string
	 */
	private static function render( array $items ) {
		$html = '';
		$list = '';

		foreach ( $items as $term => $text ) {
			if ( is_int( $term ) ) {
				$html .= '<p>' . esc_html( $text ) . '</p>';
			} else {
				$list .= '<li><strong>' . esc_html( $term ) . '</strong>: ' . esc_html( $text ) . '</li>';
			}
		}

		return $html . ( '' !== $list ? '<ul>' . $list . '</ul>' : '' );
	}

	/**
	 * Paneles de la pantalla de ajustes, en el orden de sus bloques.
	 *
	 * @return array ID del panel => array( title, items ).
	 */
	private static function get_panels() {
		return array(
			'method'     => array(
				'title' => __( 'The method', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'This method belongs to the shipping zone you are editing, so it only ever applies to the countries, states and postcodes of that zone. You can add it as many times as you need, each copy with its own rates.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Method Title', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'the name the customer reads at checkout. APG Shipping by default.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Tax Status', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )   => __( 'whether the shipping cost is taxed or not. It only shows when taxes are enabled in WooCommerce.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				),
			),

			'fees'       => array(
				'title' => __( 'Fees', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'What is added on top of the rate the table gives. Both boxes are added to the shipping cost, never to the price of the products.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Handling Fee', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )      => __( 'a fixed amount added to the cost of the order.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Additional Fee', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )    => __( 'another amount on top, which can also be a percentage, as in 6%, and accepts a floor and a ceiling: 6%|min="20.50" max="100".', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Apply per product?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'charges the additional fee once per item in the cart instead of once per order.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				),
			),

			'rates'      => array(
				'title' => __( 'Shipping rates', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'The table that decides the price, one rule per line. The plugin reads them from the top down and charges the first one that fits, so write them from the smallest to the largest. Under the box you have your own weight and dimension units, and the names of your shipping classes, so you do not have to go and look them up.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Shipping Rates', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )            => __( 'the rules. Up to this weight and this price: 1000|6.95. With a maximum size: 1000|6.95|10x10x10. By size alone: 10x10x10|6.95. A range: 500-1000|4.95. And a repeating rate, so you do not write a hundred lines: 500+100|2.00+0.25 means 2.00 up to 500, and 0.25 more for every 100 after that, with 500+100-50|2.00+0.25 stopping at 50.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Apply shipping rate per...', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'what the first number of each rule means: the total weight of the cart, which is the usual one, the number of items, or the cart total.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Highest shipping class rate', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'with several shipping classes in the same cart, charges only the dearest one instead of adding them all up. It only shows when your shop has shipping classes.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Overweight/over dimensions', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'what to do with an order that goes over the last rule of the table. Ticked, which is how it comes, it charges the highest rate; unticked, the method does not show at checkout and that order cannot be shipped this way.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				),
			),

			'exclusions' => array(
				'title' => __( 'Exclusions', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'What this method refuses to carry. There are five pairs of settings, one for product categories, tags, attributes, shipping classes and user roles, and the two in each pair work together.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'No shipping (...)', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'the list of categories, tags, attributes, shipping classes or roles this method does not accept. Leave a list empty and nothing is excluded by it. With more than five hundred entries the list turns into a search box, so you type instead of scrolling.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Shipping (...)?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )   => __( 'turns the list around: ticked, the method carries only what is on the list and refuses everything else.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				),
			),

			'checkout'   => array(
				'title' => __( 'At checkout', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'Which payment methods and which other shipping methods can be used together with this one.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Payment gateway', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'the payment methods the customer is offered when choosing this shipping method. Useful for leaving cash on delivery out of a shipping method that does not take it.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Shipping methods', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'the other shipping methods of the same zone that are shown alongside this one. Unselect one and it disappears from checkout whenever this method is available.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				),
			),

			'appearance' => array(
				'title' => __( 'How it looks', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'What the customer sees next to the price at checkout.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Icon image', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' )            => __( 'the address of an image to show with the method, typically the carrier logo. 60x21 pixels works well.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'How to show icon image?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'where the icon goes: before the title, after it, or on its own with no title at all.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Estimated delivery time', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'a free text shown under the method, such as 24 to 48 hours. Leave it empty and nothing is shown.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				),
			),

			'debug'      => array(
				'title' => __( 'Troubleshooting', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'For when the price that comes out is not the one you expected.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
					__( 'Show debug information?', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) => __( 'prints on the cart page the weight, the dimensions and the rule the plugin used to reach that price, so you can see which line of the table matched. Remember to untick it before your customers see it.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ),
				),
			),
		);
	}
}
