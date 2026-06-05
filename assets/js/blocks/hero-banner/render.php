<?php
/**
 * Solaire Hero Banner — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$title    = $attributes['title'] ?? '';
$subtitle = $attributes['subtitle'] ?? '';
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?: get_post_type_archive_link('game');
$image    = $attributes['imageUrl'] ?: get_theme_file_uri('/assets/img/hero-casino.jpg');

// Split the title so the first word stays white and the remainder reads dark.
$title_parts = preg_split('/\s+/', trim($title), 2);
$title_first = $title_parts[0] ?? '';
$title_rest  = $title_parts[1] ?? '';
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative']); ?>>
  <div class="mx-auto max-w-shell px-4 pt-4">
    <div class="relative z-10 overflow-hidden rounded-2xl">
      <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" class="absolute inset-0 h-full w-full object-cover" />
      <!-- Warm brand gradient bleeding from the left over the casino image -->
      <div class="absolute inset-0 bg-gradient-to-r from-brandred via-orange/55 to-transparent"></div>
      <div class="absolute inset-0 bg-gradient-to-t from-black/25 to-transparent"></div>
      <div class="relative z-10 flex min-h-[180px] flex-col justify-center gap-3 px-6 py-8 sm:min-h-[220px] sm:px-10 sm:py-10 lg:min-h-[240px] lg:px-12">
        <?php if ($title) : ?>
          <h1 data-anim class="font-display text-2xl font-extrabold uppercase leading-tight tracking-tight drop-shadow sm:text-4xl lg:text-5xl">
            <span class="text-white"><?php echo esc_html($title_first); ?></span><?php if ($title_rest) : ?> <span class="text-deep"><?php echo esc_html($title_rest); ?></span><?php endif; ?>
          </h1>
        <?php endif; ?>
        <?php if ($subtitle) : ?>
          <p data-anim data-anim-delay="120" class="max-w-md text-sm text-white/90 sm:text-base"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
        <?php if ($btn_text) : ?>
          <a data-anim data-anim-delay="240" href="<?php echo esc_url($btn_url ?: '#'); ?>" class="btn-press mt-2 inline-block w-fit rounded-full bg-white px-7 py-2.5 text-sm font-bold text-deep shadow-lg shadow-black/20 transition-colors hover:bg-white/90"><?php echo esc_html($btn_text); ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
