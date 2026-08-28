<?php
/**
 * Solaire Read More — render.
 *
 * Collapsing is handled by the theme's global `[data-readmore]` behaviour in
 * assets/js/solaire.js (the same one the game category template uses), so this
 * block ships no view script of its own.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks.
 * @var WP_Block $block      Block instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

$heading          = trim((string) ($attributes['heading'] ?? ''));
$show_icon        = (bool) ($attributes['showIcon'] ?? true);
$collapsible      = (bool) ($attributes['collapsible'] ?? true);
$collapsed_height = max(80, (int) ($attributes['collapsedHeight'] ?? 200));

// Nothing to show when the body is empty (an empty <p> from the editor counts).
if ('' === trim(wp_strip_all_tags($content))) {
    return;
}

// Typography for the editor's inner blocks, matching the Read More section on
// the game category template.
$body_classes = 'text-sm leading-relaxed text-slatey sm:text-base'
    . ' [&_a]:text-orange hover:[&_a]:underline'
    . ' [&_h2]:mb-3 [&_h2]:mt-8 [&_h2:first-child]:mt-0 [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-extrabold [&_h2]:text-white sm:[&_h2]:text-2xl'
    . ' [&_h3]:mb-2 [&_h3]:mt-4 [&_h3]:font-display [&_h3]:font-semibold [&_h3]:text-white'
    . ' [&_h4]:mb-2 [&_h4]:mt-4 [&_h4]:font-display [&_h4]:font-semibold [&_h4]:text-white'
    . ' [&_li]:mb-1 [&_ol]:my-4 [&_ol]:list-decimal [&_ol]:pl-5'
    . ' [&_p]:mb-4 [&_p:last-child]:mb-0'
    . ' [&_ul]:my-4 [&_ul]:list-disc [&_ul]:pl-5 [&_ul_li]:marker:text-orange';
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'mx-auto max-w-shell px-4']); // phpcs:ignore ?>>
  <div data-anim class="title-bar mt-6 bg-white/[0.02] p-6 sm:mt-12 sm:p-8">

    <?php if ($heading) : ?>
      <?php
        // The heading wraps to two or three lines on phones, where centring
        // the icon against the whole block leaves it floating beside the middle
        // line. Align it to the first line there and re-centre from md up,
        // where the heading is a single line.
        ?>
        <div class="mb-6 flex items-start gap-3 border-b border-white/10 pb-4 md:items-center">
        <?php if ($show_icon) : ?>
          <span class="shrink-0 text-orange"><?php echo solaire_icon('help', 'h-6 w-6'); // phpcs:ignore ?></span>
        <?php endif; ?>
        <h2 class="font-display text-xl font-extrabold text-gold sm:text-2xl"><?php echo esc_html($heading); ?></h2>
      </div>
    <?php endif; ?>

    <?php if ($collapsible) : ?>
      <div data-readmore data-readmore-collapsed-height="<?php echo esc_attr($collapsed_height); ?>">
        <div class="relative">
          <div data-readmore-body class="<?php echo esc_attr($body_classes); ?>">
            <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — already-rendered inner blocks. ?>
          </div>
        </div>
        <button type="button" data-readmore-toggle
          data-more="<?php esc_attr_e('Read More', 'solaire'); ?>"
          data-less="<?php esc_attr_e('Read Less', 'solaire'); ?>"
          class="mt-4 font-display text-sm font-semibold text-orange transition hover:text-white"><?php esc_html_e('Read More', 'solaire'); ?></button>
      </div>
    <?php else : ?>
      <div class="<?php echo esc_attr($body_classes); ?>">
        <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </div>
    <?php endif; ?>

  </div>
</section>
