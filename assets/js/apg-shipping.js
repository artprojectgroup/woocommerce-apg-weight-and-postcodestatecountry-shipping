jQuery( function( $ ) {
	$( document ).on( 'mouseover', '.wc-shipping-zone-method-settings', function() {
        if ( $( this ).closest( 'tr' ).find( '.wc-shipping-zone-method-type' ).text().indexOf( "APG" ) > -1 || $( this ).closest( 'tr' ).find( '.wc-shipping-zone-method-title' ).text().indexOf( "APG" ) > -1 ) {
            $( this ).removeClass( 'wc-shipping-zone-method-settings' );
        }
	} );
    $( document.body ).on( 'wc_backbone_modal_loaded', function( evt, target ) {
        if ( 'wc-modal-shipping-method-settings' === target ) {
            $( 'select' ).selectWoo();
        }
    } );
} );