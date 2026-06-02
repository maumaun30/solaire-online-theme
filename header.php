<?php
/**
 * Site header — Solaire Online chrome (logo, desktop nav, mobile drawer).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Resolve a nav item's URL from its taxonomy slug, falling back to '#'.
 */
function solaire_nav_url($key)
{
    if ($key === 'home') {
        return home_url('/');
    }
    if ($key === 'live-slots') {
        $link = get_post_type_archive_link('game');
        return $link ?: '#';
    }
    $term = get_term_by('slug', $key, 'game_category');
    if ($term && !is_wp_error($term)) {
        $link = get_term_link($term);
        if (!is_wp_error($link)) {
            return $link;
        }
    }
    return '#';
}

$solaire_nav = [
    'home'        => ['label' => 'Home',        'icon' => 'home'],
    'live-slots'  => ['label' => 'Live Slots',  'icon' => 'live-slots'],
    'live-casino' => ['label' => 'Live Casino', 'icon' => 'live-casino'],
    'e-games'     => ['label' => 'E-Games',     'icon' => 'e-games'],
    'sportsbook'  => ['label' => 'Sportsbook',  'icon' => 'sportsbook'],
];
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
<header class="header-glow relative z-50 bg-surface">
  <div class="mx-auto max-w-shell px-4 pt-4">
    <div class="relative z-10 flex h-[68px] items-center justify-between gap-4 rounded-xl bg-black/35 px-4 backdrop-blur-sm ring-1 ring-white/5">

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
        <?php foreach ($solaire_nav as $key => $item) :
            $active = solaire_nav_active($key);
            $classes = $active
                ? 'text-orange hover:text-orange-bright'
                : 'text-white/90 hover:text-orange';
        ?>
          <a href="<?php echo esc_url(solaire_nav_url($key)); ?>"
             class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition-colors <?php echo esc_attr($classes); ?>"
             <?php echo $active ? 'aria-current="page"' : ''; ?>>
            <?php echo solaire_icon($item['icon']); // phpcs:ignore ?>
            <span><?php echo esc_html($item['label']); ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <!-- Right actions -->
      <div class="flex items-center gap-3">
        <a href="#" class="hidden text-sm font-semibold text-white/80 transition-colors hover:text-white sm:inline"><?php esc_html_e('Sign Up', 'solaire'); ?></a>
        <a href="#" class="btn-press hidden rounded-md bg-brand-orange px-5 py-2 text-sm font-bold text-white shadow-lg shadow-orange/20 sm:inline-block"><?php esc_html_e('Sign In', 'solaire'); ?></a>
        <button id="nav-toggle" aria-label="<?php esc_attr_e('Open menu', 'solaire'); ?>" class="flex h-10 w-10 items-center justify-center rounded-lg text-white lg:hidden">
          <?php echo solaire_icon('menu', 'h-6 w-6'); // phpcs:ignore ?>
        </button>
      </div>
    </div>
  </div>
</header>

<!-- Mobile drawer -->
<div id="nav-overlay" class="fixed inset-0 z-50 hidden bg-black/60 opacity-0 lg:hidden"></div>
<aside id="nav-drawer" class="fixed right-0 top-0 z-[60] flex h-full w-72 max-w-[80vw] flex-col bg-deep p-6 shadow-2xl lg:hidden">
  <div class="mb-8 flex items-center justify-between">
    <span class="font-logo text-xl tracking-[0.3em]">SOLAIRE</span>
    <button id="nav-close" aria-label="<?php esc_attr_e('Close menu', 'solaire'); ?>" class="flex h-9 w-9 items-center justify-center rounded-lg text-white/70 hover:text-white">
      <?php echo solaire_icon('close', 'h-6 w-6'); // phpcs:ignore ?>
    </button>
  </div>
  <nav class="flex flex-col gap-1 text-base font-semibold" aria-label="<?php esc_attr_e('Mobile Menu', 'solaire'); ?>">
    <?php foreach ($solaire_nav as $key => $item) :
        $classes = solaire_nav_active($key) ? 'text-orange' : 'text-white/90';
    ?>
      <a href="<?php echo esc_url(solaire_nav_url($key)); ?>" class="rounded-lg px-3 py-3 hover:bg-white/5 <?php echo esc_attr($classes); ?>"><?php echo esc_html($item['label']); ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="mt-auto flex flex-col gap-3 pt-6">
    <a href="#" class="rounded-md border border-white/15 px-5 py-3 text-center text-sm font-semibold text-white"><?php esc_html_e('Sign Up', 'solaire'); ?></a>
    <a href="#" class="btn-press rounded-md bg-brand-orange px-5 py-3 text-center text-sm font-bold text-white"><?php esc_html_e('Sign In', 'solaire'); ?></a>
  </div>
</aside>
