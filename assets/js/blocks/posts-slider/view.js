/**
 * Posts Slider — front-end behaviour.
 *
 * One slide visible at a time; prev/next arrows + dot navigation. Non-looping,
 * like the ranking-list carousel: the prev arrow is disabled on the first slide
 * and the next arrow on the last. Plain vanilla JS (no build step) so it can be
 * enqueued as-is via the block's `viewScript`. Each instance is independent.
 */
( function () {
  function initSlider( root ) {
    var track = root.querySelector( '.posts-slider__track' );
    var slides = root.querySelectorAll( '.posts-slider__slide' );
    if ( ! track || slides.length === 0 ) {
      return;
    }

    var prevBtn = root.querySelector( '.posts-slider__arrow[data-dir="prev"]' );
    var nextBtn = root.querySelector( '.posts-slider__arrow[data-dir="next"]' );
    var index = 0;
    var count = slides.length;

    function goTo( next ) {
      // Clamp to the available range — no wrap-around.
      index = Math.max( 0, Math.min( count - 1, next ) );
      track.style.transform = 'translateX(-' + index * 100 + '%)';

      // Disable the arrows at each end, matching the ranking-list carousel.
      if ( prevBtn ) {
        prevBtn.disabled = index <= 0;
      }
      if ( nextBtn ) {
        nextBtn.disabled = index >= count - 1;
      }
    }

    root.addEventListener( 'click', function ( e ) {
      var arrow = e.target.closest( '.posts-slider__arrow' );
      if ( ! arrow ) {
        return;
      }
      e.preventDefault();
      if ( arrow.disabled ) {
        return;
      }
      goTo( index + ( arrow.getAttribute( 'data-dir' ) === 'prev' ? -1 : 1 ) );
    } );

    goTo( 0 );
  }

  function initAll() {
    var sliders = document.querySelectorAll( '.posts-slider:not(.posts-slider--editor)' );
    for ( var i = 0; i < sliders.length; i++ ) {
      initSlider( sliders[ i ] );
    }
  }

  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', initAll );
  } else {
    initAll();
  }
} )();
