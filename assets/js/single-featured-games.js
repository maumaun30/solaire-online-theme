/**
 * single-featured-games.js
 *
 * Initializes the Featured Games Swiper for the single post and single promo
 * templates. Both use the same layout — only the class prefix differs
 * (`sp-fg__*` on single.php, `pr-fg__*` on single-promo.php).
 *
 * Enqueued in functions.php with `['swiper']` as a dependency.
 */
(function () {
  'use strict';

  // [swiper-selector, prev-selector, next-selector]
  var TARGETS = [
    ['.sp-fg__swiper', '.sp-fg__prev', '.sp-fg__next'],
    ['.pr-fg__swiper', '.pr-fg__prev', '.pr-fg__next'],
  ];

  function build(el, prevSel, nextSel) {
    return new Swiper(el, {
      slidesPerView: 2,
      spaceBetween: 12,
      watchOverflow: true,
      observer: true,
      observeParents: true,
      navigation: { prevEl: prevSel, nextEl: nextSel },
      breakpoints: {
        480:  { slidesPerView: 3, spaceBetween: 14 },
        768:  { slidesPerView: 4, spaceBetween: 16 },
        1024: { slidesPerView: 6, spaceBetween: 16 },
      },
    });
  }

  function init() {
    var pending = TARGETS.filter(function (t) { return document.querySelector(t[0]); });
    if (!pending.length) return;

    function ready() {
      pending.forEach(function (t) {
        var el = document.querySelector(t[0]);
        if (el && !el.swiper) build(el, t[1], t[2]);
      });
    }

    if (typeof window.Swiper !== 'undefined') { ready(); return; }

    // Poll up to ~2s in case Swiper is loaded async (defer/CDN).
    var tries = 0;
    var timer = setInterval(function () {
      tries++;
      if (typeof window.Swiper !== 'undefined') { clearInterval(timer); ready(); }
      else if (tries > 40) { clearInterval(timer); console.warn('[single-featured-games] Swiper never loaded.'); }
    }, 50);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
