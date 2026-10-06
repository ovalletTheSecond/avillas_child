/**
 * Layered Popups - cooldown guard.
 *
 * Once a Layered Popups popup has been shown and closed, it is prevented
 * from reopening for COOLDOWN_MINUTES, using one cookie per popup id so
 * several distinct popups on the site are tracked independently.
 *
 * Hooks into the plugin's own extension points (ulpext_open_before /
 * ulpext_close_after, see wp-content/plugins/layered-popups/js/script.js)
 * and additionally wraps the global ulp_open() so a popup on cooldown
 * never opens at all (no flash of content).
 */
( function () {
	'use strict';

	var COOLDOWN_MINUTES = 15;
	var COOKIE_PREFIX = 'avillas_ulp_cooldown_';

	function readCookie( name ) {
		var pairs = document.cookie.split( '; ' );
		for ( var i = 0; i < pairs.length; i++ ) {
			var pair = pairs[ i ].split( '=' );
			if ( pair[ 0 ] === name ) {
				return decodeURIComponent( pair[ 1 ] || '' );
			}
		}
		return null;
	}

	function writeCookie( name, value, minutes ) {
		var date = new Date();
		date.setTime( date.getTime() + minutes * 60 * 1000 );
		document.cookie = name + '=' + encodeURIComponent( value ) + '; expires=' + date.toUTCString() + '; path=/';
	}

	function isOnCooldown( id ) {
		return null !== readCookie( COOKIE_PREFIX + id );
	}

	function startCooldown( id ) {
		writeCookie( COOKIE_PREFIX + id, String( Date.now() ), COOLDOWN_MINUTES );
	}

	// Belt-and-braces: force-hide a popup/overlay still on cooldown, in case
	// something manages to display it outside of ulp_open() (e.g. a manual trigger).
	function forceHide( id ) {
		[ 'ulp-' + id, 'ulp-' + id + '-overlay' ].forEach( function ( elementId ) {
			var el = document.getElementById( elementId );
			if ( el ) {
				el.style.setProperty( 'display', 'none', 'important' );
				el.style.setProperty( 'z-index', '-100', 'important' );
			}
		} );
	}

	function releaseForcedHide( id ) {
		[ 'ulp-' + id, 'ulp-' + id + '-overlay' ].forEach( function ( elementId ) {
			var el = document.getElementById( elementId );
			if ( el && '-100' === el.style.getPropertyValue( 'z-index' ) ) {
				el.style.removeProperty( 'display' );
				el.style.removeProperty( 'z-index' );
			}
		} );
	}

	function hideAnyAlreadyOnCooldown() {
		document.querySelectorAll( '.ulp-window, .ulp-inline-window, .ulp-overlay' ).forEach( function ( el ) {
			var match = /^ulp-(.+?)(-overlay)?$/.exec( el.id || '' );
			if ( match && isOnCooldown( match[ 1 ] ) ) {
				forceHide( match[ 1 ] );
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', hideAnyAlreadyOnCooldown );

	// Called by script.js right before a popup is displayed.
	window.ulpext_open_before = function ( id ) {
		if ( String( window.avillasManualContactPopupOpen || '' ) === String( id ) ) {
			releaseForcedHide( id );
			window.avillasManualContactPopupOpen = false;
			return;
		}
		if ( isOnCooldown( id ) ) {
			forceHide( id );
		}
	};

	// Called by script.js once a popup has finished closing - our "end of display" moment.
	window.ulpext_close_after = function ( id ) {
		startCooldown( id );
	};

	// Primary guard: skip opening entirely (no flash) when the popup is on cooldown.
	if ( 'function' === typeof window.ulp_open ) {
		var nativeUlpOpen = window.ulp_open;
		window.ulp_open = function ( id ) {
			if ( String( window.avillasManualContactPopupOpen || '' ) === String( id ) ) {
				releaseForcedHide( id );
			}
			if ( isOnCooldown( id ) && String( window.avillasManualContactPopupOpen || '' ) !== String( id ) ) {
				return false;
			}
			return nativeUlpOpen.apply( this, arguments );
		};
	}
} )();
