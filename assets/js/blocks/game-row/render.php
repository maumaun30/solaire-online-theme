<?php
/**
 * Solaire Game Row — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$title     = $attributes['title'] ?? '';
$category  = $attributes['category'] ?? '';
$count     = max(1, (int) ($attributes['count'] ?? 12));

// Exact cards-per-view per breakpoint with no partial card — sizing handled by
// the `.game-row-track` rule in main.css (2 → 3 → 4 tablet → 5 → 6 desktop).
$card_class = 'shrink-0';
$query = solaire_query_games(['category' => $category, 'count' => $count]);
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative']); ?> data-carousel>
  <div class="mx-auto max-w-shell px-4">
    <div class="relative z-10 mt-8">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="font-display text-lg font-bold sm:text-xl"><?php echo esc_html($title); ?></h2>
        <div class="flex items-center gap-2">
          <button data-prev aria-label="<?php esc_attr_e('Previous', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-orange transition hover:bg-white/20 disabled:cursor-not-allowed"><?php echo solaire_icon('arrow-left', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
          <button data-next aria-label="<?php esc_attr_e('Next', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-orange transition hover:bg-white/20 disabled:cursor-not-allowed"><?php echo solaire_icon('arrow-right', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
        </div>
      </div>
      <div data-track class="game-row-track no-scrollbar snap-row flex gap-3 overflow-x-auto pt-3 pb-2 sm:gap-4">
        <?php
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                echo solaire_game_card(get_the_ID(), ['variant' => 'portrait', 'class' => $card_class]); // phpcs:ignore
            }
            wp_reset_postdata();
        } else {
            echo solaire_placeholder_cards($count, $card_class); // phpcs:ignore
        }
        ?>
      </div>
    </div>
  </div>
</section>
