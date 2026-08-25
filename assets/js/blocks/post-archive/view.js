/**
 * Solaire Post Archive — Load More.
 *
 * Loaded as the block's viewScript. The AJAX URL, action and nonce all ride on
 * the button's data attributes, so no wp_localize_script handle is needed.
 */
(function () {
  'use strict';

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.solaire-post-archive__load-more');
    if (!btn) return;

    e.preventDefault();

    var grid  = document.getElementById(btn.dataset.grid);
    var block = document.querySelector('[data-block-id="' + btn.dataset.block + '"]');
    if (!grid || !block) return;

    var postType = block.dataset.postType || 'post';
    var perPage  = parseInt(block.dataset.perPage, 10) || 4;
    var page     = (parseInt(block.dataset.currentPage, 10) || 1) + 1;

    btn.disabled    = true;
    btn.textContent = 'Loading…';

    var body = new URLSearchParams({
      action   : btn.dataset.action,
      nonce    : btn.dataset.nonce,
      post_type: postType,
      per_page : perPage,
      page     : page,
    });

    fetch(btn.dataset.ajaxUrl, {
      method : 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body   : body.toString(),
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.success) throw new Error('AJAX error');

        if (res.data.html) {
          var tmp = document.createElement('div');
          tmp.innerHTML = res.data.html;

          // Fade each new card in, staggered.
          Array.from(tmp.children).forEach(function (card, i) {
            card.style.opacity    = '0';
            card.style.transform  = 'translateY(20px)';
            card.style.transition = 'opacity 0.4s ease ' + i * 0.08 + 's, transform 0.4s ease ' + i * 0.08 + 's';
            grid.appendChild(card);
            requestAnimationFrame(function () {
              requestAnimationFrame(function () {
                card.style.opacity   = '1';
                card.style.transform = 'translateY(0)';
              });
            });
          });
        }

        block.dataset.currentPage = page;

        if (!res.data.has_more) {
          var wrap = btn.closest('.solaire-post-archive__load-more-wrap');
          if (wrap) wrap.remove();
        } else {
          btn.disabled    = false;
          btn.textContent = 'Load More';
        }
      })
      .catch(function () {
        btn.disabled    = false;
        btn.textContent = 'Load More';
      });
  });
})();
