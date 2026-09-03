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
<section <?php echo get_block_wrapper_attributes(['class' => 'bg-surface py-8 sm:py-16']); ?>>
  <div class="mx-auto max-w-shell px-4">
    <?php if ($heading) : ?>
      <h2 data-anim class="text-center font-display text-2xl font-extrabold sm:text-3xl"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>
    <!-- Flex rather than grid so a partial final row (or a block with only one
         or two benefits) centres instead of hanging off the left edge. The
         basis maths reproduces a 3-column grid: each item takes a third of the
         space left once the two column gaps are removed. -->
    <div class="mt-10 flex flex-wrap justify-center gap-x-3 gap-y-8 text-center md:gap-8">
  <?php foreach ($benefits as $i => $b) :
      $icon  = $b['icon'] ?? 'bolt';
      $title = $b['title'] ?? '';
      $text  = $b['text'] ?? '';
      $delay = $i * 100;
  ?>
    <div data-anim <?php echo $delay ? 'data-anim-delay="' . esc_attr($delay) . '"' : ''; ?> class="flex shrink-0 grow-0 basis-[calc((100%-1.5rem)/3)] flex-col items-center px-1 md:basis-[calc((100%-4rem)/3)] md:px-4">
      <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-orange ring-1 ring-orange/30 md:h-14 md:w-14"><?php echo solaire_icon($icon, 'h-5 w-5 md:h-6 md:w-6'); // phpcs:ignore ?></span>
      <h3 class="mt-3 font-display text-xs font-bold sm:text-sm md:mt-4 md:text-lg"><?php echo esc_html($title); ?></h3>
      <p class="mt-1.5 max-w-[9rem] text-[11px] leading-snug text-slatey md:mt-2 md:max-w-xs md:text-sm md:leading-relaxed"><?php echo esc_html($text); ?></p>
    </div>
  <?php endforeach; ?>
</div>
  </div>
</section>
