<?php 
/**
 * Vista del panel de ajustes del método de envío APG Shipping para WooCommerce.
 *
 * Este archivo genera la interfaz de administración para configurar el método de envío, mostrando el título, la descripción,
 * un cuadro de información, la cabecera gráfica y el formulario de opciones personalizadas.
 *
 * Seguridad: No debe accederse directamente. Solo se debe cargar dentro del contexto de WooCommerce Shipping.
 *
 * Variables globales utilizadas:
 * - $apg_shipping  Array con información básica del plugin (nombre, URL, etc.).
 * - $this          Instancia del método de envío (WC_apg_shipping).
 *
 * @package WC-APG-Weight-Shipping
 * @subpackage Admin/Templates
 * @author Art Project Group
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

global $apg_shipping;
?>
<h3><a href="<?php echo esc_url( $apg_shipping[ 'plugin_url' ] ?? '' ); ?>" title="Art Project Group"><?php echo esc_html( $apg_shipping[ 'plugin' ] ?? '' ); ?></a></h3>
<p> <?php echo wp_kses_post( $this->method_description ); ?> </p>
<?php include __DIR__ . '/cuadro-informacion.php'; ?>
<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin image ?>
<div class="cabecera"> <a href="<?php echo esc_url( $apg_shipping[ 'plugin_url' ] ); ?>" title="<?php echo esc_attr( $apg_shipping[ 'plugin' ] ); ?>" target="_blank"><img src="<?php echo esc_url( plugins_url( 'assets/images/cabecera.jpg', DIRECCION_apg_shipping ) ); ?>" class="imagen" alt="<?php echo esc_attr( $apg_shipping[ 'plugin' ] ); ?>" /></a> </div>
<table class="form-table apg-table">
	<?php $this->generate_settings_html( $this->get_instance_form_fields() ); ?>
</table>
<script>
jQuery(function($){
    function initAjaxSelect($el){
        if(!$el.length || !$el.is("select")) return;
        var data = $el.data();
        if(!data.apgAjax) return;
        var src = data.source || "";
        var nonce = data.nonce || "";
        $el.selectWoo({
            ajax: {
                transport: function(params, success, failure){
                    $.ajax({
                        url: ajaxurl,
                        method: "GET",
                        data: {
                            action: "apg_shipping_search_terms",
                            source: src,
                            nonce: nonce,
                            q: params.data.q || "",
                            page: params.data.page || 1
                        }
                    }).then(success).catch(failure);
                },
                delay: 250,
                data: function(params){ return { q: params.term || "", page: params.page || 1 }; },
                processResults: function(data){ return data || { results: [] }; }
            },
            minimumInputLength: 1,
            allowClear: true,
            placeholder: "<?php echo esc_js( __( 'Search…', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ); ?>",
            language: {
                inputTooShort: function(args){
                    var tmpl = "<?php
                        // translators: %d is the number of characters required.
                        echo esc_js( __( 'Please enter %d or more characters.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) );
                    ?>";
                    return tmpl.replace('%d', (args.minimum - args.input.length));
                },
                noResults: function(){ return "<?php echo esc_js( __( 'No results found.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ); ?>"; },
                searching: function(){ return "<?php echo esc_js( __( 'Searching…', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ); ?>"; },
                loadingMore: function(){ return "<?php echo esc_js( __( 'Loading more results…', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ); ?>"; },
                errorLoading: function(){ return "<?php echo esc_js( __( 'The results could not be loaded.', 'woocommerce-apg-weight-and-postcodestatecountry-shipping' ) ); ?>"; }
            }
        });
    }
    $("select.apg-ajax-select").each(function(){ initAjaxSelect($(this)); });
    $(document.body).on("wc-enhanced-select-init", function(){
        $("select.apg-ajax-select").each(function(){
            if(!$(this).data("select2")) initAjaxSelect($(this));
        });
    });
});
</script>
