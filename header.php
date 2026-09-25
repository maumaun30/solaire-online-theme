<?php

/**
 * Site header — Solaire Online chrome (logo, desktop nav, mobile drawer).
 */

if (!defined('ABSPATH')) {
  exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class('bg-deep text-white'); ?>>
  <?php wp_body_open(); ?>
  <!-- ============================ HEADER ============================ -->
  <header class="so-header header-bar sticky top-0 z-50">
    <div class="relative z-10 mx-auto flex h-[44px] max-w-shell items-center gap-6 pl-2 pr-4 sm:h-[68px] sm:px-6 lg:max-w-none lg:gap-2 lg:px-4 xl:gap-4 xl:px-6 lg:grid 2xl:max-w-[1720px] lg:grid-cols-[1fr_auto_1fr]">

      <!-- Logo -->
      <?php // the_custom_logo() prints its own <a>, so the wrapper is a div — a
      // nested link gets split by the browser into an extra header child. ?>
      <div class="flex shrink-0 flex-col leading-none lg:justify-self-start">
        <?php if (has_custom_logo()) : ?>
          <?php the_custom_logo(); ?>
        <?php else : ?>
          <a href="<?php echo esc_url(home_url('/')); ?>" class="flex flex-col leading-none">
            <span class="font-logo text-2xl font-semibold tracking-[0.32em] text-white sm:text-[28px]">SOLAIRE</span>
            <span class="font-logo text-[10px] tracking-[0.55em] text-white/70">ONLINE</span>
          </a>
        <?php endif; ?>
      </div>

      <!-- Desktop nav -->
      <nav class="so-nav hidden items-center lg:flex" aria-label="<?php esc_attr_e('Primary Menu', 'solaire'); ?>">
        <?php wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'items_wrap'     => '<ul class="so-nav__list flex items-center gap-1">%3$s</ul>',
          'walker'         => new Solaire_Nav_Walker('desktop'),
          'fallback_cb'    => false,
        ]); ?>
      </nav>

      <!-- Right actions -->
      <div class="ml-auto flex shrink-0 items-center gap-2 sm:gap-4 lg:gap-3 lg:justify-self-end">
        <button id="search-toggle" aria-label="<?php esc_attr_e('Open search', 'solaire'); ?>" class="btn-press flex h-7 w-7 items-center justify-center rounded-lg bg-[#222529] text-[#f5993d] ring-1 ring-white/15 backdrop-blur-sm transition-colors hover:text-orange-bright hover:ring-orange/40 sm:h-9 sm:w-9">
          <?php echo solaire_icon('search', 'h-4 w-4 sm:h-[18px] sm:w-[18px]'); // phpcs:ignore 
          ?>
        </button>
        <?php solaire_signup_cta(__('Sign Up', 'solaire'), 'so-cta btn-press inline-block whitespace-nowrap rounded-lg bg-[#222529] px-2.5 py-1.5 text-xs font-semibold text-[#f5993d] ring-1 ring-white/15 backdrop-blur-sm transition-colors hover:text-orange-bright hover:ring-orange/40 sm:px-5 sm:py-2 sm:text-sm'); ?>
        <button id="nav-toggle" aria-label="<?php esc_attr_e('Open menu', 'solaire'); ?>" class="btn-press flex h-7 w-7 items-center justify-center rounded-lg bg-[#222529] text-[#f5993d] ring-1 ring-white/15 backdrop-blur-sm sm:h-9 sm:w-9 lg:hidden">
          <?php echo solaire_icon('menu', 'h-5 w-5'); // phpcs:ignore 
          ?>
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile drawer -->
  <div id="nav-overlay" class="fixed inset-0 z-50 hidden bg-black/60 opacity-0 lg:hidden"></div>
  <aside id="nav-drawer" class="fixed right-0 top-0 z-[60] flex h-full w-full flex-col overflow-hidden bg-deep p-6 shadow-2xl sm:w-80 sm:max-w-[80vw] lg:hidden">
    <div class="drawer-head -mx-6 -mt-6 mb-8 flex items-center justify-between px-6 py-5">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="flex shrink-0 flex-col leading-none">
        <?php if (has_custom_logo()) : ?>
          <?php the_custom_logo(); ?>
        <?php else : ?>
          <span class="font-logo text-2xl font-semibold tracking-[0.32em] text-white">SOLAIRE</span>
          <span class="font-logo text-[10px] tracking-[0.55em] text-white/70">ONLINE</span>
        <?php endif; ?>
      </a>
      <button id="nav-close" aria-label="<?php esc_attr_e('Close menu', 'solaire'); ?>" class="flex h-9 w-9 items-center justify-center rounded-lg text-white/70 hover:text-white">
        <?php echo solaire_icon('close', 'h-6 w-6'); // phpcs:ignore 
        ?>
      </button>
    </div>
    <div class="drawer-scroll -mr-6 flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain pr-6">
      <nav class="so-m-nav text-base font-semibold" aria-label="<?php esc_attr_e('Mobile Menu', 'solaire'); ?>">
        <?php wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'items_wrap'     => '<ul class="so-m-list">%3$s</ul>',
          'walker'         => new Solaire_Nav_Walker('mobile'),
          'fallback_cb'    => false,
        ]); ?>
      </nav>
      <div class="mt-auto flex shrink-0 flex-col gap-3 pb-[env(safe-area-inset-bottom)] pt-6">
        <?php solaire_signup_cta(__('Sign Up', 'solaire'), 'btn-press rounded-md bg-brand-orange px-5 py-3 text-center text-sm font-bold text-white'); ?>
      </div>
    </div>
  </aside>

  <script>
    /* Desktop nav fit: show the full nav whenever it fits the bar, otherwise
       fall back to the drawer (html.nav-collapsed, styled in main.css). Runs
       inline so the first paint is already right; re-checks once web fonts
       land and on resize, since both change the label widths. */
    (function() {
      var root = document.documentElement;
      var bar = document.querySelector('.so-header > div');
      var nav = bar && bar.querySelector('.so-nav');
      var list = nav && nav.querySelector('.so-nav__list');
      if (!list) return;
      var desktop = window.matchMedia('(min-width: 1024px)');

      function fit() {
        var wasCollapsed = root.classList.contains('nav-collapsed');
        root.classList.remove('nav-collapsed');
        // Compare real edges rather than scrollWidth: the hidden dropdown
        // panels are laid out too and would count as overflow.
        var collapse = false;
        if (desktop.matches) {
          var l = list.getBoundingClientRect();
          var logo = bar.firstElementChild.getBoundingClientRect();
          var acts = bar.lastElementChild.getBoundingClientRect();
          var b = bar.getBoundingClientRect();
          var padR = parseFloat(getComputedStyle(bar).paddingRight) || 0;
          // Require breathing room either side, not just no overlap.
          var room = 24;
          collapse = l.left < logo.right + room ||
            l.right > acts.left - room ||
            acts.right > b.right - padR + 1;
        }
        root.classList.toggle('nav-collapsed', collapse);
        // Leaving drawer mode with the drawer open would strand the scroll lock.
        var drawer = document.getElementById('nav-drawer');
        if (wasCollapsed && !collapse && drawer && drawer.classList.contains('open')) {
          var close = document.getElementById('nav-close');
          if (close) close.click();
        }
      }

      var queued = false;
      window.addEventListener('resize', function() {
        if (queued) return;
        queued = true;
        requestAnimationFrame(function() { queued = false; fit(); });
      });
      if (document.fonts && document.fonts.ready) document.fonts.ready.then(fit);
      fit();
    })();
  </script>

  <!-- ======================= SEARCH OVERLAY ======================= -->
  <div id="search-overlay" class="so-search-overlay fixed inset-0 z-[70] hidden items-center justify-center opacity-0" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?php esc_attr_e('Search', 'solaire'); ?>">
    <div class="so-search-overlay__body relative w-full max-w-[720px] p-8">

      <button id="search-close" aria-label="<?php esc_attr_e('Close search', 'solaire'); ?>" class="absolute right-8 top-[-1rem] flex h-9 w-9 items-center justify-center text-white/60 transition hover:text-white">
        <?php echo solaire_icon('close', 'h-7 w-7'); // phpcs:ignore 
        ?>
      </button>

      <p class="font-display text-xs font-semibold uppercase tracking-[0.14em] text-orange"><?php esc_html_e('What are you looking for?', 'solaire'); ?></p>

      <form role="search" method="get" class="so-search-form mt-5 flex items-center gap-3" action="<?php echo esc_url(home_url('/')); ?>">
        <label class="sr-only" for="search-overlay-field"><?php esc_html_e('Search for:', 'solaire'); ?></label>
        <input
          type="search"
          id="search-overlay-field"
          name="s"
          value="<?php echo esc_attr(get_search_query()); ?>"
          placeholder="<?php esc_attr_e('Search for', 'solaire'); ?>"
          autocomplete="off"
          class="so-search-field min-w-0 flex-1 bg-transparent font-display text-2xl font-semibold text-white placeholder-white/25 outline-none sm:text-4xl" />
        <button type="submit" class="btn-press flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-orange text-white sm:h-12 sm:w-12" aria-label="<?php esc_attr_e('Search', 'solaire'); ?>">
          <?php echo solaire_icon('search', 'h-5 w-5'); // phpcs:ignore 
          ?>
        </button>
      </form>

    </div>
  </div>
