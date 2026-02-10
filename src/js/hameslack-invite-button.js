/*!
 * Invitation button helper.
 */

/* global jQuery, HameslackInvitation */

const $ = jQuery;

$( document ).on( 'click', '.hameslack-invitation-button', function ( e ) {
	e.preventDefault();
	$.ajax( HameslackInvitation.endpoint, {
		method: 'POST',
		beforeSend( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', HameslackInvitation.nonce );
		},
	} )
		.done( function ( response ) {
			// eslint-disable-next-line no-alert
			window.alert( response.message );
			window.location.reload( true );
		} )
		.fail( function ( response ) {
			let msg = HameslackInvitation.error;
			if ( response.responseJSON && response.responseJSON.message ) {
				msg = response.responseJSON.message;
			}
			// eslint-disable-next-line no-alert
			window.alert( msg );
		} );
} );
