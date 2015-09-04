<?php $envios = apg_shipping_lee_envios(); ?>

<tr valign="top">
  <th scope="row" class="titledesc"><?php echo $opciones['name']; ?> <img class="help_tip" data-tip="<?php echo $opciones['desc_tip']; ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></th>
  <td class="forminp"><table id="envios" class="wc_shipping widefat wp-list-table" cellspacing="0">
      <thead>
        <tr>
          <th class="ordenar sort">&nbsp;</th>
          <th class="name nombre_envio"><?php _e( 'Shipping Methods', 'apg_shipping' ); ?></th>
          <th class="borrar">&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <?php
		if ( $envios ) {
			foreach ( $envios as $envio ) {
        ?>
        <tr>
          <td class="sort">&nbsp;</td>
          <td><input type="text" class="widefat" name="<?php echo $opciones['id']; ?>[]" value="<?php if ( !empty( $envio ) ) { echo esc_attr( $envio ); } ?>" /></td>
          <td><a class="button borrar_fila" href="#envio"><span class="genericon genericon-trash"></span></a></td>
        </tr>
        <?php
			}
		} else {
        ?>
        <tr>
          <td class="sort">&nbsp;</td>
          <td><input type="text" class="widefat" name="<?php echo $opciones['id']; ?>[]" /></td>
          <td><a class="button borrar_fila" href="#envio"><span class="genericon genericon-trash"></span></a></td>
        </tr>
        <?php 
		} 
        ?>
        <!-- empty hidden one for jQuery -->
        <tr id="clonable" class="fila_vacia screen-reader-text">
          <td class="sort">&nbsp;</td>
          <td><input type="text" class="widefat" name="<?php echo $opciones['id']; ?>[]" /></td>
          <td><a class="button borrar_fila" href="#envio"><span class="genericon genericon-trash"></span></a></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <th>&nbsp;</th>
          <th><span class="description">
            <?php _e( 'Drag and drop the above shipping methods to control their display order.', 'apg_shipping' ); ?>
            </span><span class="description anadir">
            <?php _e( 'Add new <strong class="artprojectgroup">APG</strong> shippings methods with this button &#8594;', 'apg_shipping' ); ?>
            </span></th>
          <th class="default" colspan="2"><span><a id="nueva_fila" class="button" href="#envio"><span class="genericon genericon-edit"></span></a></span> 
        </tr>
      </tfoot>
    </table></td>
</tr>
<script type="text/javascript">
jQuery( document ).ready( function( $ ) {
	$( "table.form-table tr:last" ).css( { 
		display: "none" 
	} );
	
	$( '#nueva_fila' ).on( 'click', function() {
		var row = $( '.fila_vacia.screen-reader-text' ).clone( true );
		row.removeClass( 'fila_vacia screen-reader-text' );
		row.removeAttr( 'id' );
		row.removeAttr( 'style' );
		row.insertBefore( '#envios #clonable' );
		row.find( "input" ).focus();
		return false;
	} );
	
	$( '.borrar_fila' ).on( 'click', function() {
		$( this ).closest( 'tr' ).remove();
		return false;
	} );

	$( 'form' ).submit( function( e ) {   
		$( '#woocommerce_apg_shipping' ).val( '' );
		$( "input[name='<?php echo $opciones['id']; ?>\\[\\]']" ).map( function() {
			if ( $( this ).val() ) {
				$( '#woocommerce_apg_shipping' ).val( $( '#woocommerce_apg_shipping' ).val() + $( this ).val() + "\n" );
			}
		} ).get();
		$( "input[name='<?php echo $opciones['id']; ?>\\[\\]']" ).removeAttr( 'name' );
	} );	
} );
</script> 
