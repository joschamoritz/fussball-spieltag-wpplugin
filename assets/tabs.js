/* Fussball Spieltag Widget – Tab-Navigation */
( function () {
	'use strict';

	document.querySelectorAll( '.fsw-home' ).forEach( function ( widget ) {
		var tabs   = Array.from( widget.querySelectorAll( '.fsw-tab[data-i]' ) );
		var panels = Array.from( widget.querySelectorAll( '.fsw-panel' ) );
		if ( ! tabs.length ) return;

		function activate( idx, moveFocus ) {
			tabs.forEach( function ( tab, i ) {
				tab.classList.toggle( 'fsw-active', i === idx );
			} );
			panels.forEach( function ( panel ) {
				panel.classList.toggle( 'fsw-show', parseInt( panel.dataset.i, 10 ) === idx );
			} );
			if ( moveFocus ) tabs[ idx ].focus();
		}

		tabs.forEach( function ( tab, idx ) {
			tab.addEventListener( 'click', function () {
				activate( idx, false );
			} );
			tab.addEventListener( 'keydown', function ( e ) {
				var last = tabs.length - 1;
				if ( e.key === 'ArrowRight' ) {
					e.preventDefault();
					activate( idx < last ? idx + 1 : 0, true );
				}
				if ( e.key === 'ArrowLeft' ) {
					e.preventDefault();
					activate( idx > 0 ? idx - 1 : last, true );
				}
			} );
		} );
	} );
} )();
