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
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative']); ?>>
  <div class="mx-auto max-w-shell px-4 pt-4">
    <div class="relative z-10 overflow-hidden rounded-2xl">
      <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" class="absolute inset-0 h-full w-full object-cover" />
      <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/50 to-transparent"></div>
      <div class="relative z-10 flex min-h-[240px] flex-col justify-center gap-4 px-6 py-12 sm:min-h-[300px] sm:px-12 lg:min-h-[340px]">
        <?php if ($title) : ?>
          <h1 data-anim class="font-display text-3xl font-extrabold uppercase tracking-tight drop-shadow sm:text-5xl lg:text-6xl"><?php echo esc_html($title); ?></h1>
        <?php endif; ?>
        <?php if ($subtitle) : ?>
          <p data-anim data-anim-delay="120" class="max-w-md text-base text-white/85 sm:text-lg"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
        <?php if ($btn_text) : ?>
          <a data-anim data-anim-delay="240" href="<?php echo esc_url($btn_url ?: '#'); ?>" class="btn-press mt-2 inline-block w-fit rounded-lg bg-brand-orange px-7 py-3 text-sm font-bold text-white shadow-lg shadow-orange/30"><?php echo esc_html($btn_text); ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
