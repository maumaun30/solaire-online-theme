<?php
/**
 * Solaire Benefits Row ("Why Play") — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$heading  = $attributes['heading'] ?? '';
$benefits = $attributes['benefits'] ?? [];
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'bg-surface py-16']); ?>>
  <div class="mx-auto max-w-shell px-4">
    <?php if ($heading) : ?>
      <h2 data-anim class="text-center font-display text-2xl font-extrabold sm:text-3xl"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>
    <div class="mt-10 grid gap-8 text-center md:grid-cols-3">
      <?php foreach ($benefits as $i => $b) :
          $icon  = $b['icon'] ?? 'bolt';
          $title = $b['title'] ?? '';
          $text  = $b['text'] ?? '';
          $delay = $i * 100;
      ?>
        <div data-anim <?php echo $delay ? 'data-anim-delay="' . esc_attr($delay) . '"' : ''; ?> class="flex flex-col items-center px-4">
          <span class="flex h-14 w-14 items-center justify-center rounded-full bg-white/5 text-orange ring-1 ring-orange/30"><?php echo solaire_icon($icon, 'h-6 w-6'); // phpcs:ignore ?></span>
          <h3 class="mt-4 font-display text-lg font-bold"><?php echo esc_html($title); ?></h3>
          <p class="mt-2 max-w-xs text-sm leading-relaxed text-slatey"><?php echo esc_html($text); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
