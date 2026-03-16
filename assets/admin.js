/* Fussball Spieltag Widget – Admin Color Picker mit Live-Preview */
jQuery( function ( $ ) {
	$( '.fsw-color-picker' ).wpColorPicker( {
		change: function ( event, ui ) {
			var color = ui.color.toString();
			var name  = $( this ).attr( 'name' );
			if ( name === 'fsw_color_primary' ) {
				document.documentElement.style.setProperty( '--fsw-primary', color );
			} else if ( name === 'fsw_color_accent' ) {
				document.documentElement.style.setProperty( '--fsw-accent', color );
			}
		}
	} );
} );
