jQuery( function( $ ) {
	$( document ).on( 'mouseover', '.wc-shipping-zone-method-rows', function() {
		$( 'a.wc-shipping-zone-method-settings' ).removeClass( 'wc-shipping-zone-method-settings' );
	} );
} );