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
$tag       = $attributes['tag'] ?? '';
$count     = max(1, (int) ($attributes['count'] ?? 12));

// Exact cards-per-view per breakpoint with no partial card — sizing handled by
// the `.game-row-track` rule in main.css (2 → 3 → 4 tablet → 5 → 6 desktop).
$card_class = 'shrink-0';
$query = solaire_query_games(['category' => $category, 'tag' => $tag, 'count' => $count]);

// When an explicit category/tag filter is set but nothing matches, render
// nothing at all (no empty titled carousel). The unfiltered "latest games"
// row still falls back to branded placeholders below.
if (!$query->have_posts() && ($category || $tag)) {
    return;
}

// "View All" points to the category archive when a category is set, otherwise
// the game post-type archive.
$view_all_url = get_post_type_archive_link('game') ?: '#';
if ($category) {
    $term = get_term_by('slug', $category, 'game_category');
    if ($term && !is_wp_error($term)) {
        $term_link = get_term_link($term);
        if (!is_wp_error($term_link)) {
            $view_all_url = $term_link;
        }
    }
}
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative']); ?> data-carousel>
  <div class="mx-auto max-w-shell px-4">
    <div class="relative z-10 mt-8">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="font-display text-lg font-medium sm:text-xl"><?php echo esc_html($title); ?></h2>
        <div class="flex items-center gap-2">
          <a href="<?php echo esc_url($view_all_url); ?>" class="rounded-md bg-white/10 px-3 py-1.5 text-xs text-secondary transition"><?php esc_html_e('View All', 'solaire'); ?></a>
          <button data-prev aria-label="<?php esc_attr_e('Previous', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-secondary transition hover:bg-white/20 disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-white/25 disabled:hover:bg-white/5"><?php echo solaire_icon('arrow-left', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
          <button data-next aria-label="<?php esc_attr_e('Next', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-secondary transition hover:bg-white/20 disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-white/25 disabled:hover:bg-white/5"><?php echo solaire_icon('arrow-right', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
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
            // Reached only for the unfiltered "latest games" row (filtered rows
            // with no matches return early above) — fill with placeholders.
            echo solaire_placeholder_cards($count, $card_class); // phpcs:ignore
        }
        ?>
      </div>
    </div>
  </div>
</section>
