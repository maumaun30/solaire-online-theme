<?php
/**
 * Solaire Category Tiles — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$tiles = $attributes['tiles'] ?? [];

$gradients = [
    'live-casino' => 'bg-gradient-to-br from-[#1b3a4b] via-[#15171a] to-[#0c2230]',
    'e-games'     => 'bg-gradient-to-br from-[#7a2410] via-[#15171a] to-[#3a1207]',
    'sportsbook'  => 'bg-gradient-to-br from-[#1c5132] via-[#15171a] to-[#0a2417]',
    'live-slots'  => 'bg-gradient-to-br from-[#3a2a10] via-[#15171a] to-[#241807]',
];
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative']); ?>>
  <div class="mx-auto max-w-shell px-4">
    <div class="relative z-10 mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
      <?php foreach ($tiles as $tile) :
          $label = $tile['label'] ?? '';
          $slug  = $tile['slug'] ?? '';
          $icon  = $tile['icon'] ?? 'live-slots';
          // The media picker stores { id, url } under `image`; fall back to the legacy `imageUrl`.
          $image = $tile['image']['url'] ?? ($tile['imageUrl'] ?? '');

          // Default the Live Slots tile to the bundled category artwork.
          if (!$image && $slug === 'live-slots') {
              $image = get_theme_file_uri('/assets/img/cat-live-slots.png');
          }

          $url = '#';
          if ($slug) {
              $term = get_term_by('slug', $slug, 'game_category');
              if ($slug === 'live-slots' && (!$term || is_wp_error($term))) {
                  $url = get_post_type_archive_link('game') ?: '#';
              } elseif ($term && !is_wp_error($term)) {
                  $link = get_term_link($term);
                  $url = is_wp_error($link) ? '#' : $link;
              }
          }
          $gradient = $gradients[$slug] ?? 'bg-gradient-to-br from-panel via-deep to-deep';
      ?>
        <a href="<?php echo esc_url($url); ?>" class="card-lift group relative block aspect-square overflow-hidden rounded-xl">
          <?php if ($image) : ?>
            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($label); ?>" class="absolute inset-0 h-full w-full transition-transform duration-500" loading="lazy" />
          <?php else : ?>
            <div class="absolute inset-0 <?php echo esc_attr($gradient); ?>"></div>
            <div class="ph absolute inset-0"><?php echo esc_html($label); ?></div>
          <?php endif; ?>
          <span class="absolute left-3 top-3 flex h-8 w-8 items-center justify-center rounded-md bg-black/55 text-orange backdrop-blur-sm">
            <?php echo solaire_icon($icon, 'h-4 w-4'); // phpcs:ignore ?>
          </span>
          <div class="absolute inset-x-0 bottom-0 flex items-center bg-black/35 px-4 py-2.5 backdrop-blur-md">
            <span class="font-display text-sm font-bold uppercase tracking-wide sm:text-base"><?php echo esc_html($label); ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
