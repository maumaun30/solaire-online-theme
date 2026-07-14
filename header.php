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

<body <?php body_class('bg-surface text-white'); ?>>
  <?php wp_body_open(); ?>

  <!-- ============================ HEADER ============================ -->
  <header class="so-header header-bar sticky top-0 z-50">
    <div class="relative z-10 mx-auto flex h-[52px] max-w-shell items-center gap-6 px-4 sm:h-[68px] sm:px-6">

      <!-- Logo -->
      <a href="<?php echo esc_url(home_url('/')); ?>" class="flex shrink-0 flex-col leading-none">
        <?php if (has_custom_logo()) : ?>
          <?php the_custom_logo(); ?>
        <?php else : ?>
          <span class="font-logo text-2xl font-semibold tracking-[0.32em] text-white sm:text-[28px]">SOLAIRE</span>
          <span class="font-logo text-[10px] tracking-[0.55em] text-white/70">ONLINE</span>
        <?php endif; ?>
      </a>

      <!-- Desktop nav -->
      <nav class="hidden items-center gap-1 lg:flex" aria-label="<?php esc_attr_e('Primary Menu', 'solaire'); ?>">
        <?php wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'items_wrap'     => '%3$s',
          'walker'         => new Solaire_Nav_Walker('desktop'),
          'fallback_cb'    => false,
        ]); ?>
      </nav>

      <!-- Right actions -->
      <div class="ml-auto flex items-center gap-2 sm:gap-4">
        <a href="https://www.solaireonline.com/register" class="btn-press inline-block whitespace-nowrap rounded-lg bg-[#222529] px-2.5 py-1.5 text-xs font-semibold text-[#f5993d] ring-1 ring-white/15 backdrop-blur-sm transition-colors hover:text-orange-bright hover:ring-orange/40 sm:px-5 sm:py-2 sm:text-sm"><?php esc_html_e('Sign Up', 'solaire'); ?></a>
        <a href="https://www.solaireonline.com/login" class="btn-press inline-block whitespace-nowrap rounded-lg bg-[#222529] px-2.5 py-1.5 text-xs font-semibold text-[#f5993d] ring-1 ring-white/15 backdrop-blur-sm transition-colors hover:text-orange-bright hover:ring-orange/40 sm:px-5 sm:py-2 sm:text-sm"><?php esc_html_e('Login', 'solaire'); ?></a>
        <button id="nav-toggle" aria-label="<?php esc_attr_e('Open menu', 'solaire'); ?>" class="btn-press flex h-7 w-7 items-center justify-center rounded-lg bg-[#222529] text-[#f5993d] ring-1 ring-white/15 backdrop-blur-sm sm:h-9 sm:w-9 lg:hidden">
          <?php echo solaire_icon('menu', 'h-5 w-5'); // phpcs:ignore 
          ?>
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile drawer -->
  <div id="nav-overlay" class="fixed inset-0 z-50 hidden bg-black/60 opacity-0 lg:hidden"></div>
  <aside id="nav-drawer" class="fixed right-0 top-0 z-[60] flex h-full w-full flex-col bg-deep p-6 shadow-2xl sm:w-80 sm:max-w-[80vw] lg:hidden">
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
    <nav class="flex flex-col gap-1 text-base font-semibold text-center" aria-label="<?php esc_attr_e('Mobile Menu', 'solaire'); ?>">
      <?php wp_nav_menu([
        'theme_location' => 'primary',
        'container'      => false,
        'items_wrap'     => '%3$s',
        'walker'         => new Solaire_Nav_Walker('mobile'),
        'fallback_cb'    => false,
      ]); ?>
    </nav>
    <div class="mt-auto flex flex-col gap-3 pt-6">
      <a href="#" class="rounded-md border border-white/15 px-5 py-3 text-center text-sm font-semibold text-white"><?php esc_html_e('Sign Up', 'solaire'); ?></a>
      <a href="#" class="btn-press rounded-md bg-brand-orange px-5 py-3 text-center text-sm font-bold text-white"><?php esc_html_e('Sign In', 'solaire'); ?></a>
    </div>
  </aside>