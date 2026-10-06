( function () {
	'use strict';

	var popupId = 'zvq5f6w2pb38Bufq';
	var widget;

	function showWidget() {
		document.body.classList.remove( 'avillas-contact-popup-open' );
		if ( widget ) {
			widget.setAttribute( 'aria-expanded', 'false' );
		}
	}

	function openPopup() {
		if ( 'function' !== typeof window.ulp_open ) {
			console.error( 'The contact popup cannot open because Layered Popups is not ready.' );
			return;
		}
		if ( ! window.ulp_overlays || ! Object.prototype.hasOwnProperty.call( window.ulp_overlays, popupId ) ) {
			console.error( 'The configured AVILLAS contact popup is not available on this page.' );
			return;
		}

		document.body.classList.add( 'avillas-contact-popup-open' );
		widget.setAttribute( 'aria-expanded', 'true' );

		window.avillasManualContactPopupOpen = popupId;
		window.ulp_open( popupId );
	}

	function init() {
		widget = document.getElementById( 'avillas-contact-widget' );
		if ( ! widget ) {
			return;
		}

		widget.addEventListener( 'click', openPopup );

		var previousCloseAfter = window.ulpext_close_after;
		window.ulpext_close_after = function ( id ) {
			try {
				if ( 'function' === typeof previousCloseAfter ) {
					previousCloseAfter.apply( this, arguments );
				}
			} finally {
				if ( String( id ) === popupId ) {
					window.avillasManualContactPopupOpen = false;
					showWidget();
				}
			}
		};
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
