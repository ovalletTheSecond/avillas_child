( function () {
	'use strict';

	function init() {
		document.querySelectorAll( '.avillas-random-properties' ).forEach( function ( slider ) {
			var track = slider.querySelector( '.avillas-random-properties__track' );
			var cards = track.querySelectorAll( '.avillas-random-properties__card' );
			var controls = slider.querySelector( '.avillas-random-properties__controls' );
			var counter = slider.querySelector( '.avillas-random-properties__counter' );
			var reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

			if ( cards.length < 2 ) {
				return;
			}

			controls.hidden = false;

			function currentIndex() {
				return Math.min( cards.length - 1, Math.max( 0, Math.round( track.scrollLeft / track.clientWidth ) ) );
			}

			function move( direction ) {
				var index = ( currentIndex() + direction + cards.length ) % cards.length;
				track.scrollTo( { left: index * track.clientWidth, behavior: reducedMotion.matches ? 'instant' : 'smooth' } );
			}

			controls.querySelectorAll( 'button' ).forEach( function ( button ) {
				button.addEventListener( 'click', function () {
					move( Number( button.dataset.direction ) );
				} );
			} );

			track.addEventListener( 'keydown', function ( event ) {
				if ( event.target !== track || ( 'ArrowLeft' !== event.key && 'ArrowRight' !== event.key ) ) {
					return;
				}
				event.preventDefault();
				move( 'ArrowRight' === event.key ? 1 : -1 );
			} );

			track.addEventListener( 'scroll', function () {
				counter.textContent = ( currentIndex() + 1 ) + ' / ' + cards.length;
			}, { passive: true } );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
