<?php
/**
 * Solaire Steps — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$heading     = $attributes['heading'] ?? '';
$subheading  = $attributes['subheading'] ?? '';
$footer_text = $attributes['footerText'] ?? '';
$steps       = $attributes['steps'] ?? [];
$total       = count($steps);

// The section describes an ordered procedure, so it also feeds a HowTo node
// (printed in wp_footer by inc/schema.php). Emits nothing until two steps
// carry copy.
if ($steps && function_exists('solaire_collect_howto')) {
    solaire_collect_howto($heading, $subheading, array_map(function ($step) {
        return [
            'title'       => $step['title'] ?? '',
            'description' => $step['text'] ?? '',
        ];
    }, $steps));
}
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative mt-14 overflow-hidden bg-gradient-to-b from-[#2a1410] via-deep to-deep py-8 sm:py-16']); ?>>
  <div class="mx-auto max-w-shell px-4 text-center">

    <?php if ($heading) : ?>
      <h2 data-anim class="mx-auto max-w-3xl font-display text-2xl font-extrabold uppercase leading-tight text-gold sm:text-4xl"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <?php if ($subheading) : ?>
      <p data-anim data-anim-delay="80" class="mx-auto mt-4 max-w-2xl text-sm text-slatey sm:text-base"><?php echo esc_html($subheading); ?></p>
    <?php endif; ?>

    <div class="mt-10 grid grid-cols-2 gap-5 lg:grid-cols-4">
      <?php foreach ($steps as $i => $step) :
          // A blank number falls back to the step's position, so editors only
          // set it when they want something other than 1, 2, 3...
          $number = trim((string) ($step['number'] ?? ''));
          if ('' === $number) {
              $number = (string) ($i + 1);
          }
          $title    = $step['title'] ?? '';
          $text     = $step['text'] ?? '';
          $icon_url = is_array($step['icon'] ?? null) ? ($step['icon']['url'] ?? '') : '';
          $delay    = $i * 100;
      ?>
        <div data-anim <?php echo $delay ? 'data-anim-delay="' . esc_attr($delay) . '"' : ''; ?> class="relative flex flex-col">

          <?php // Badge sits over the card's top-left corner, hiding the left
                // end of the card's accent border. ?>
          <span class="absolute -left-1 -top-1 z-20 flex h-10 w-10 items-center justify-center rounded-lg bg-orange font-display text-sm font-bold text-white max-[499px]:h-[35px] max-[499px]:w-[35px] max-[499px]:text-xs">
            <?php echo esc_html($number); ?>
          </span>

          <?php // The accent line is this border, not a connector between
                // cards: along the top on wider screens, down the left edge
                // once the cards get narrow. ?>
          <div class="group relative mt-5 flex-1 overflow-hidden rounded-xl border-l-2 border-orange bg-white/[0.03] px-6 pb-7 pt-8 text-center min-[600px]:border-l-0 min-[600px]:border-t-2 max-[499px]:px-4 max-[499px]:py-5">

            <span aria-hidden="true" class="pointer-events-none absolute inset-0 rounded-xl bg-[radial-gradient(circle_at_center,rgba(223,106,46,0.30),transparent_70%)] opacity-0 transition-opacity duration-500 group-hover:opacity-100"></span>

            <div class="relative">
              <?php if ($icon_url) : ?>
                <img src="<?php echo esc_url($icon_url); ?>" alt="" class="mx-auto mb-5 h-[50px] w-[50px] object-contain max-[499px]:mb-3 max-[499px]:h-[30px] max-[499px]:w-[30px]" loading="lazy" />
              <?php else : ?>
                <span class="mx-auto mb-5 flex h-[50px] w-[50px] items-center justify-center text-orange max-[499px]:mb-3 max-[499px]:h-[30px] max-[499px]:w-[30px]"><?php echo solaire_icon('help', 'h-8 w-8'); // phpcs:ignore ?></span>
              <?php endif; ?>

              <?php if ($title) : ?>
                <h3 class="font-display text-sm font-bold uppercase tracking-[0.1em] text-white max-[499px]:text-xs"><?php echo esc_html($title); ?></h3>
              <?php endif; ?>

              <?php if ($text) : ?>
                <p class="mt-2.5 text-[0.8125rem] leading-relaxed text-slatey max-[499px]:text-xs"><?php echo esc_html($text); ?></p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($footer_text) : ?>
      <p data-anim class="mx-auto mt-10 max-w-3xl text-sm text-slatey"><?php echo esc_html($footer_text); ?></p>
    <?php endif; ?>

  </div>
</section>
