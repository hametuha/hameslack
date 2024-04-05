/**
 * Description
 */

/*global HameslackInvitation: false */

( function( $ ) {
	'use strict';

	$( document ).on( 'click', '.hameslack-invitation-button', function( e ) {
		e.preventDefault();
		$.ajax( HameslackInvitation.endpoint, {
			method: 'POST',
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', HameslackInvitation.nonce );
			},
		} ).done( function( response ) {
			alert( response.message );
			window.location.reload( true );
		} ).fail( function( response ) {
			let msg = HameslackInvitation.error;
			if ( response.responseJSON && response.responseJSON.message ) {
				msg = response.responseJSON.message;
			}
			alert( msg );
		} );
	} );
}( jQuery ) );
