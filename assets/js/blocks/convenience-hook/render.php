<?php
/**
 * Solaire Convenience Hook — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$overline = $attributes['overline'] ?? '';
$heading  = $attributes['heading'] ?? '';
$text     = $attributes['text'] ?? '';
$features = $attributes['features'] ?? [];
$items    = $attributes['items'] ?? [];

$heading_html = nl2br(esc_html($heading));
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'bg-surface py-16']); ?>>
  <div class="mx-auto max-w-shell px-4">
    <?php if ($overline) : ?>
      <p class="text-xs font-bold uppercase tracking-[0.28em] text-orange"><?php echo esc_html($overline); ?></p>
    <?php endif; ?>
    <div class="mt-4 grid gap-10 lg:grid-cols-2">
      <div data-anim>
        <?php if ($heading) : ?>
          <h2 class="font-display text-3xl font-extrabold leading-tight sm:text-4xl"><?php echo $heading_html; // phpcs:ignore ?></h2>
        <?php endif; ?>
        <?php if ($text) : ?>
          <p class="mt-5 max-w-md text-sm leading-relaxed text-slatey sm:text-base"><?php echo esc_html($text); ?></p>
        <?php endif; ?>
        <?php if ($features) : ?>
          <div class="mt-8 grid grid-cols-2 gap-6 sm:grid-cols-4">
            <?php foreach ($features as $f) : ?>
              <div class="flex flex-col items-start gap-2">
                <span class="text-orange"><?php echo solaire_icon($f['icon'] ?? 'phone', 'h-6 w-6', '1.8'); // phpcs:ignore ?></span>
                <span class="text-[11px] font-semibold uppercase leading-tight tracking-wide text-slatey"><?php echo esc_html($f['label'] ?? ''); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div data-anim data-anim-delay="120" data-accordion data-single class="flex flex-col gap-3">
        <?php foreach ($items as $i => $item) :
            $open = ($i === 0);
        ?>
          <div class="acc-item <?php echo $open ? 'open ring-orange/30' : 'ring-white/10'; ?> rounded-xl bg-white/[0.03] ring-1">
            <button data-acc-head class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left">
              <span class="flex items-center gap-3 font-semibold">
                <span class="text-orange"><?php echo solaire_icon($item['icon'] ?? 'phone-plain', 'h-5 w-5'); // phpcs:ignore ?></span>
                <?php echo esc_html($item['title'] ?? ''); ?>
              </span>
              <span class="acc-chevron text-orange"><?php echo solaire_icon('chevron', 'h-5 w-5', '2.5'); // phpcs:ignore ?></span>
            </button>
            <div class="acc-panel"><div class="px-5 pb-5 text-sm leading-relaxed text-slatey"><?php echo esc_html($item['text'] ?? ''); ?></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
