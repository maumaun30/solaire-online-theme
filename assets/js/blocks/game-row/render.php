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
$count     = max(1, (int) ($attributes['count'] ?? 5));
$view_text = $attributes['viewAllText'] ?? 'View all';

$view_url = $attributes['viewAllUrl'] ?? '';
if (!$view_url) {
    if ($category) {
        $term = get_term_by('slug', $category, 'game_category');
        $view_url = ($term && !is_wp_error($term)) ? get_term_link($term) : get_post_type_archive_link('game');
    } else {
        $view_url = get_post_type_archive_link('game');
    }
    if (is_wp_error($view_url) || !$view_url) {
        $view_url = '#';
    }
}

$card_class = 'w-[30%] shrink-0 sm:w-[22%] lg:w-[15.5%]';
$query = solaire_query_games(['category' => $category, 'count' => $count]);
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative']); ?> data-carousel>
  <div class="mx-auto max-w-shell px-4">
    <div class="relative z-10 mt-8">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="font-display text-lg font-bold sm:text-xl"><?php echo esc_html($title); ?></h2>
        <div class="flex items-center gap-2">
          <a href="<?php echo esc_url($view_url); ?>" class="rounded-md bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/80 transition hover:bg-white/20"><?php echo esc_html($view_text); ?></a>
          <button data-prev aria-label="<?php esc_attr_e('Previous', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-white/80 transition hover:bg-white/20"><?php echo solaire_icon('arrow-left', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
          <button data-next aria-label="<?php esc_attr_e('Next', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-white/80 transition hover:bg-white/20"><?php echo solaire_icon('arrow-right', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
        </div>
      </div>
      <div data-track class="no-scrollbar snap-row flex gap-3 overflow-x-auto pt-3 pb-2 sm:gap-4">
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
