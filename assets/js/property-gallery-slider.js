/**
 * Avillas Property Gallery Slider
 * Lightweight replacement for the parent theme's jQuery FlexSlider gallery
 * on the single property page. No external dependency (no jQuery/FlexSlider).
 */
( function () {
	'use strict';

	// Cache la superposition (spinner + placeholder) une fois l'image chargée (ou en erreur).
	function watchImageLoading( img, overlay ) {
		if ( ! overlay ) {
			return;
		}
		if ( img.complete && img.naturalWidth > 0 ) {
			overlay.classList.add( 'is-hidden' );
			return;
		}
		var hide = function () { overlay.classList.add( 'is-hidden' ); };
		img.addEventListener( 'load', hide );
		img.addEventListener( 'error', hide );
	}

	function initLoadingIndicators( root ) {
		Array.prototype.forEach.call( root.querySelectorAll( '.aps-slide' ), function ( slide ) {
			// L'image r\u00e9elle est celle du lien (le premier <img> est le placeholder de secours).
			var img = slide.querySelector( 'a img' );
			if ( img ) {
				watchImageLoading( img, slide.querySelector( '.aps-loading' ) );
			}
		} );

		Array.prototype.forEach.call( root.querySelectorAll( '.aps-thumb' ), function ( thumb ) {
			var img = thumb.querySelector( 'img' );
			if ( img ) {
				watchImageLoading( img, thumb.querySelector( '.aps-thumb-loading' ) );
			}
		} );
	}

	function initSlider( root ) {
		var viewport = root.querySelector( '.aps-viewport' );
		var track    = root.querySelector( '.aps-slides' );
		var slides   = Array.prototype.slice.call( root.querySelectorAll( '.aps-slide' ) );
		var thumbs   = Array.prototype.slice.call( root.querySelectorAll( '.aps-thumb' ) );
		var prevBtn  = root.querySelector( '.aps-prev' );
		var nextBtn  = root.querySelector( '.aps-next' );
		var loop     = 'true' === root.getAttribute( 'data-loop' );
		var index    = 0;

		if ( ! track || slides.length === 0 ) {
			return;
		}

		function update() {
			track.style.transform = 'translateX(-' + ( index * 100 ) + '%)';

			thumbs.forEach( function ( thumb, i ) {
				thumb.classList.toggle( 'is-active', i === index );
			} );

			slides.forEach( function ( slide, i ) {
				slide.setAttribute( 'aria-hidden', i === index ? 'false' : 'true' );
			} );

			// Fait passer l'image de la diapo active en priorité haute pour que son spinner
			// disparaisse dès son arrivée, sans attendre les autres images du slider.
			var activeImg = slides[ index ] && slides[ index ].querySelector( 'a img' );
			if ( activeImg && 'lazy' === activeImg.getAttribute( 'loading' ) ) {
				activeImg.setAttribute( 'loading', 'eager' );
				activeImg.setAttribute( 'fetchpriority', 'high' );
			}

			if ( prevBtn && nextBtn && ! loop ) {
				prevBtn.disabled = index === 0;
				nextBtn.disabled = index === slides.length - 1;
			}

			var activeThumb = thumbs[ index ];
			if ( activeThumb && activeThumb.scrollIntoView ) {
				activeThumb.scrollIntoView( { block: 'nearest', inline: 'nearest', behavior: 'smooth' } );
			}
		}

		function goTo( target ) {
			if ( slides.length <= 1 ) {
				return;
			}
			if ( loop ) {
				index = ( target + slides.length ) % slides.length;
			} else {
				index = Math.max( 0, Math.min( slides.length - 1, target ) );
			}
			update();
		}

		if ( slides.length <= 1 ) {
			if ( prevBtn ) { prevBtn.style.display = 'none'; }
			if ( nextBtn ) { nextBtn.style.display = 'none'; }
		} else {
			if ( prevBtn ) { prevBtn.addEventListener( 'click', function () { goTo( index - 1 ); } ); }
			if ( nextBtn ) { nextBtn.addEventListener( 'click', function () { goTo( index + 1 ); } ); }
		}

		thumbs.forEach( function ( thumb, i ) {
			thumb.addEventListener( 'click', function () { goTo( i ); } );
		} );

		root.setAttribute( 'tabindex', '0' );
		root.addEventListener( 'keydown', function ( e ) {
			if ( 'ArrowLeft' === e.key ) { goTo( index - 1 ); }
			if ( 'ArrowRight' === e.key ) { goTo( index + 1 ); }
		} );

		// Touch swipe support.
		var touchStartX = null;
		if ( viewport ) {
			viewport.addEventListener( 'touchstart', function ( e ) {
				touchStartX = e.touches[ 0 ].clientX;
			}, { passive: true } );

			viewport.addEventListener( 'touchend', function ( e ) {
				if ( null === touchStartX ) {
					return;
				}
				var dx = e.changedTouches[ 0 ].clientX - touchStartX;
				if ( Math.abs( dx ) > 40 ) {
					goTo( dx > 0 ? index - 1 : index + 1 );
				}
				touchStartX = null;
			}, { passive: true } );
		}

		update();
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		Array.prototype.forEach.call(
			document.querySelectorAll( '.avillas-property-slider' ),
			function ( root ) {
				initLoadingIndicators( root );
				initSlider( root );
			}
		);
	} );
} )();
