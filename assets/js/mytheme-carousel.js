/**
 * mytheme-carousel.js
 *
 * Initializes Swiper for all .mytheme-carousel blocks on the page.
 * Enqueue this via functions.php after wp_enqueue_script('swiper').
 */

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.mytheme-carousel.swiper').forEach((el) => {
    let config = {};

    try {
      config = JSON.parse(el.dataset.swiper || '{}');
    } catch (e) {
      console.warn('[mytheme-carousel] Invalid swiper config on element:', el, e);
    }

    new Swiper(el, {
      // Spread the config from data-swiper (effect, loop, autoplay, pagination, etc.)
      ...config,

      // Always include pagination element binding if config says pagination is enabled
      pagination: config.pagination
        ? {
            el: el.querySelector('.swiper-pagination'),
            clickable: true,
          }
        : false,

      // Clean up active slide class so our CSS animations re-trigger on loop
      on: {
        slideChangeTransitionStart() {
          // Let CSS handle the animation reset by briefly removing active class
          // Swiper re-adds swiper-slide-active automatically
        },
      },
    });
  });
});