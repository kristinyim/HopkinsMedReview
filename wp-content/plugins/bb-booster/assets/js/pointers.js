/* globals bb_booster */

jQuery( document ).ready( function( $ ) {

	$.each( bb_booster.pointers, function( i, pointer ) {

		render( pointer );

	} );

	function render( pointer ) {

		var options = $.extend( pointer.options, {

			pointerClass: 'wp-pointer bb-booster-pointer',

			close: function() {
				$.post( bb_booster.ajaxurl, {
					pointer: pointer.id,
					action: 'dismiss-wp-pointer'
				} );
			}

		} );

		$( pointer.target ).pointer( options ).pointer( 'open' );

	}

	$( '.wp-pointer' ).css( 'font-family', '"Open Sans", sans-serif' );

} );
