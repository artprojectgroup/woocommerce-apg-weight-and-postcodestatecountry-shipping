/**
 * Script para manejar la configuración del método de envío APG.
 */
jQuery(function($) {
	$(document).on('mouseover', '.wc-shipping-zone-method-settings', function () {
		const $tr = $(this).closest('tr');

		// Detecta si tiene el marcador oculto del método APG
		if ($tr.find('.apg-weight-marker').length > 0) {
			$(this).removeClass('wc-shipping-zone-method-settings');
		}
	});

	// Si usa selectWoo tras abrir el modal
	$(document.body).on('wc_backbone_modal_loaded', function(evt, target) {
		if ('wc-modal-shipping-method-settings' === target) {
			$('select').selectWoo();
		}
	});
});