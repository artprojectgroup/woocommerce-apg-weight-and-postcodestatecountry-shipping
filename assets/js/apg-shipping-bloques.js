jQuery(function($) {
	function actualizarIconosAPG() {
		$('input[type="radio"][name^="radio-control-"]').each(function() {
			const $input = $(this);
			const valor  = $input.val(); // ej. "apg_shipping:2"
			const $label = $input.closest('label').find('.wc-block-components-radio-control__label');

			if ( ! valor || $input.data('apg-cargado') ) return;

			$.post(apg_shipping.ajax_url, {
				action: 'apg_shipping_ajax_datos',
				metodo: valor
			}, function(res) {
				if ( ! res.success || ! res.data ) return;

				const d = res.data;
				let icono = '', entrega = '', html = '';

				if ( d.icono && d.muestra !== 'no' ) {
					icono = `<img src="${d.icono}" style="display:inline;" class="apg_icon">`;
				}

				if ( d.muestra === 'delante' ) {
					html = icono + " " + d.titulo;
				} else if ( d.muestra === 'detras' ) {
					html = d.titulo + icono;
				} else if ( d.muestra === 'solo' ) {
					html = icono;
				} else {
					html = d.titulo;
				}

				if ( d.entrega ) {
					html += `<br><small class="apg_shipping_delivery">${d.entrega}</small>`;
				}

				$label.html(html);
				$input.data('apg-cargado', true);
			});
		});
	}

	// Ejecuta en carga inicial + si cambian elementos
	const observer = new MutationObserver(actualizarIconosAPG);
	observer.observe(document.body, { childList: true, subtree: true });

	actualizarIconosAPG();
});