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
<header class="so-header header-bar relative z-50 border-b border-white/5">
  <div class="relative z-10 mx-auto my-2.5 flex h-[68px] max-w-shell items-center gap-6 rounded-2xl bg-white/[0.06] px-4 ring-1 ring-white/10 backdrop-blur-sm sm:px-6">

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
    <div class="ml-auto flex items-center gap-4">
      <a href="#" class="hidden text-sm font-semibold text-orange transition-colors hover:text-orange-bright sm:inline"><?php esc_html_e('Sign Up', 'solaire'); ?></a>
      <a href="#" class="btn-press hidden rounded-md bg-brand-orange px-5 py-2 text-sm font-bold text-white shadow-lg shadow-orange/20 sm:inline-block"><?php esc_html_e('Sign In', 'solaire'); ?></a>
      <button id="nav-toggle" aria-label="<?php esc_attr_e('Open menu', 'solaire'); ?>" class="flex h-10 w-10 items-center justify-center rounded-lg text-white lg:hidden">
        <?php echo solaire_icon('menu', 'h-6 w-6'); // phpcs:ignore ?>
      </button>
    </div>
  </div>
</header>

<!-- Mobile drawer -->
<div id="nav-overlay" class="fixed inset-0 z-50 hidden bg-black/60 opacity-0 lg:hidden"></div>
<aside id="nav-drawer" class="fixed right-0 top-0 z-[60] flex h-full w-full flex-col bg-deep p-6 shadow-2xl sm:w-80 sm:max-w-[80vw] lg:hidden">
  <div class="mb-8 flex items-center justify-between">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="flex shrink-0 flex-col leading-none">
      <?php if (has_custom_logo()) : ?>
          <?php the_custom_logo(); ?>
      <?php else : ?>
          <span class="font-logo text-2xl font-semibold tracking-[0.32em] text-white">SOLAIRE</span>
          <span class="font-logo text-[10px] tracking-[0.55em] text-white/70">ONLINE</span>
      <?php endif; ?>
    </a>
    <button id="nav-close" aria-label="<?php esc_attr_e('Close menu', 'solaire'); ?>" class="flex h-9 w-9 items-center justify-center rounded-lg text-white/70 hover:text-white">
      <?php echo solaire_icon('close', 'h-6 w-6'); // phpcs:ignore ?>
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
