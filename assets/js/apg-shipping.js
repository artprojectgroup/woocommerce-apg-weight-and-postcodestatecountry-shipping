jQuery( function( $ ) {
	$( document ).on( 'mouseover', '.wc-shipping-zone-method-settings', function() {
        if ( $( this ).closest( 'tr' ).find( '.wc-shipping-zone-method-type' ).text() == 'APG Shipping' ) {
            $( this ).removeClass( 'wc-shipping-zone-method-settings' );
        }
	} );
} );