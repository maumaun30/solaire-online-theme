<?php
/**
 * Solaire FAQ Guide — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$heading = $attributes['heading'] ?? '';
$items   = $attributes['items'] ?? [];
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'mt-14']); ?>>
  <div class="mx-auto max-w-shell px-4">
    <?php if ($heading) : ?>
      <h2 data-anim class="text-center font-display text-2xl font-extrabold text-gold sm:text-4xl"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>
    <div data-anim data-accordion data-single class="mx-auto mt-8 flex max-w-4xl flex-col gap-4">
      <?php foreach ($items as $i => $item) :
          $open = ($i === 0);
      ?>
        <div class="acc-item <?php echo $open ? 'open ring-orange/40' : 'ring-white/10'; ?> rounded-xl bg-white/[0.02] ring-1">
          <button data-acc-head class="flex w-full items-center justify-between gap-3 px-6 py-5 text-left">
            <span class="flex items-center gap-3 font-semibold">
              <span class="text-gold"><?php echo solaire_icon($item['icon'] ?? 'help', 'h-5 w-5'); // phpcs:ignore ?></span>
              <?php echo esc_html($item['question'] ?? ''); ?>
            </span>
            <span class="acc-chevron shrink-0 text-gold"><?php echo solaire_icon('chevron', 'h-5 w-5', '2.5'); // phpcs:ignore ?></span>
          </button>
          <div class="acc-panel"><div class="px-6 pb-6 text-sm leading-relaxed text-slatey"><?php echo esc_html($item['answer'] ?? ''); ?></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
