/**
 * Posts Slider — front-end behaviour.
 *
 * Shows however many cards the block is set to per breakpoint (1–3) and steps
 * by that same number, so a 3-up slider advances three cards at a time. The
 * count is not hardcoded here: style.css resolves --ps-cur per media query and
 * this script reads it back, which keeps the layout and the step in sync at
 * every width, including after a resize.
 *
 * Non-looping, like the ranking-list carousel: the prev arrow is disabled at
 * the start and the next arrow once the last card is in view. Plain vanilla JS
 * (no build step) so it can be enqueued as-is via the block's `viewScript`.
 * Each instance is independent.
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

    /* Cards currently visible — whatever the CSS resolved --ps-cur to at this
       width. Falls back to 1 if the property is missing or unparseable. */
    function perView() {
      var raw = getComputedStyle( root ).getPropertyValue( '--ps-cur' );
      var value = parseInt( raw, 10 );
      if ( ! value || value < 1 ) {
        value = 1;
      }
      // Never claim to show more cards than exist.
      return Math.min( value, count );
    }

    /* Last valid start index: the position where the final card sits flush with
       the right edge, so the track never scrolls past the end into blank space. */
    function maxIndex() {
      return Math.max( 0, count - perView() );
    }

    /* Measured rather than assumed: one step is a card plus the gap after it,
       which is the only way the maths stays right for any gap or card width. */
    function stepPx() {
      var gap = parseFloat( getComputedStyle( track ).columnGap );
      if ( isNaN( gap ) ) {
        gap = 0;
      }
      return slides[ 0 ].getBoundingClientRect().width + gap;
    }

    function goTo( next ) {
      var max = maxIndex();
      index = Math.max( 0, Math.min( max, next ) );
      track.style.transform = 'translateX(-' + index * stepPx() + 'px)';

      if ( prevBtn ) {
        prevBtn.disabled = index <= 0;
      }
      if ( nextBtn ) {
        nextBtn.disabled = index >= max;
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
      // Advance by a full page of cards, not a single card.
      var step = perView();
      goTo( index + ( arrow.getAttribute( 'data-dir' ) === 'prev' ? -step : step ) );
    } );

    /* A resize can cross a breakpoint and change both the cards per view and
       the card width, so re-clamp and re-measure from the current index. */
    var resizeTimer;
    window.addEventListener( 'resize', function () {
      clearTimeout( resizeTimer );
      resizeTimer = setTimeout( function () {
        // Snap to a whole page so the row never rests mid-card.
        var step = perView();
        goTo( Math.round( index / step ) * step );
      }, 150 );
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
