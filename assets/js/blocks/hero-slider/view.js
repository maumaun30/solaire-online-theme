/**
 * Solaire Hero Slider — view.js
 * Loaded as the block's viewScript; initialises every .solaire-hero-slider on
 * the page. No dependencies. Autoplay, dot nav, touch/swipe, pause on hover.
 */
(function () {
    'use strict';

    function initSlider(root) {
        var slides = root.querySelectorAll('.solaire-hero-slider__slide');
        var dots   = root.querySelectorAll('.solaire-hero-slider__dot');
        var total  = slides.length;
        if (total < 2) return;

        var current = 0;
        var timer   = null;
        var delay   = parseInt(root.dataset.autoplay, 10) || 5000;

        function goTo(index) {
            slides[current].classList.remove('is-active');
            slides[current].setAttribute('aria-hidden', 'true');
            if (dots[current]) {
                dots[current].classList.remove('is-active');
                dots[current].setAttribute('aria-selected', 'false');
            }

            current = (index + total) % total;

            slides[current].classList.add('is-active');
            slides[current].setAttribute('aria-hidden', 'false');
            if (dots[current]) {
                dots[current].classList.add('is-active');
                dots[current].setAttribute('aria-selected', 'true');
            }
        }

        function start() {
            clearInterval(timer);
            timer = setInterval(function () { goTo(current + 1); }, delay);
        }

        function stop() { clearInterval(timer); }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goTo(parseInt(dot.dataset.index, 10));
                start();
            });
        });

        root.addEventListener('mouseenter', stop);
        root.addEventListener('focusin',    stop);
        root.addEventListener('mouseleave', start);
        root.addEventListener('focusout',   start);

        // Touch / swipe
        var touchStartX = 0;
        root.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        root.addEventListener('touchend', function (e) {
            var diff = touchStartX - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 40) {
                goTo(diff > 0 ? current + 1 : current - 1);
                start();
            }
        }, { passive: true });

        // Pause autoplay while the slider is off-screen or the tab is hidden.
        document.addEventListener('visibilitychange', function () {
            document.hidden ? stop() : start();
        });

        start();
    }

    function initAll() {
        document.querySelectorAll('.solaire-hero-slider').forEach(initSlider);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
