<?php
/**
 * Solaire Ranking List — render.
 *
 * Renders two carousels from the same set of games: a desktop one (two-column
 * slides, hidden below sm) and a mobile one (single-column slides, hidden at
 * sm+). Each has its own "number of rows" and "rows per slide" so the mobile
 * layout can differ from desktop. Only one is ever visible per breakpoint.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$title      = $attributes['title'] ?? '';
$source     = $attributes['source'] ?? 'auto';
$games      = array_values(array_filter(array_map('intval', (array) ($attributes['games'] ?? []))));
$category   = $attributes['category'] ?? '';
$count      = max(1, (int) ($attributes['count'] ?? 7));
$per_page   = max(1, (int) ($attributes['perPage'] ?? 7));
$count_m    = max(1, (int) ($attributes['countMobile'] ?? $count));
$per_page_m = max(1, (int) ($attributes['perPageMobile'] ?? $per_page));

// Build the ordered game IDs. In manual mode the editor's hand-picked list
// (in its chosen order) is authoritative; otherwise fall back to the automatic
// category query. Both are then sliced/grouped by the layout controls below.
$ids = [];
if ($source === 'manual' && $games) {
    $query = new WP_Query([
        'post_type'           => 'game',
        'post_status'         => 'publish',
        'post__in'            => $games,
        'orderby'             => 'post__in', // preserve the editor's order
        'posts_per_page'      => count($games),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ]);
} else {
    // Fetch enough games to satisfy the larger of the two layouts.
    $query = solaire_query_games(['category' => $category, 'count' => max($count, $count_m)]);
}
if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $ids[] = get_the_ID();
    }
    wp_reset_postdata();
}

/**
 * One ranking row: rank number (crown on #1) + thumbnail + title/blurb +
 * Play / Demo actions. Uses explicit post IDs so it needs no global loop.
 */
if (!function_exists('solaire_ranking_article')) {
    function solaire_ranking_article($post_id, $rank)
    {
        $thumb = get_the_post_thumbnail_url($post_id, 'medium');
        $play  = get_field('play_url', $post_id) ?: get_permalink($post_id);
        $title = get_the_title($post_id);
        ob_start(); ?>
        <article data-anim class="group flex items-center gap-3 rounded-xl bg-white/[0.03] p-3 ring-1 ring-white/5 transition hover:bg-white/[0.06] sm:gap-5 sm:p-4">
          <div class="relative flex h-12 w-9 shrink-0 items-center justify-center sm:h-16 sm:w-12">
            <?php if ($rank === 1) : ?>
              <svg viewBox="0 0 24 24" class="absolute -top-2 left-1/2 h-4 w-4 -translate-x-1/2 text-gold" fill="currentColor"><path d="M3 7l4 4 5-7 5 7 4-4-2 12H5z"/></svg>
            <?php endif; ?>
            <span class="font-display text-2xl font-extrabold sm:text-3xl"><?php echo esc_html($rank); ?></span>
          </div>
          <?php if ($thumb) : ?>
            <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>" class="h-14 w-14 shrink-0 rounded-lg object-cover sm:h-16 sm:w-16" loading="lazy" />
          <?php else : $logo = solaire_site_logo_url(); ?>
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-panel via-surface to-deep p-2 sm:h-16 sm:w-16">
              <?php if ($logo) : ?>
                <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($title); ?>" class="max-h-full max-w-full object-contain opacity-90" loading="lazy" />
              <?php else : ?>
                <span class="font-logo text-[8px] tracking-[0.3em] text-white/80">SOLAIRE</span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <div class="min-w-0 flex-1">
            <h3 class="font-display text-base font-bold sm:text-lg"><?php echo esc_html($title); ?></h3>
            <p class="mt-0.5 hidden text-xs leading-relaxed text-slatey lg:block"><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 38)); ?></p>
          </div>
          <div class="flex shrink-0 flex-col gap-2">
            <a href="<?php echo esc_url($play); ?>" class="btn-press rounded-md bg-brand-orange px-4 py-2 text-center text-xs text-white sm:text-sm"><?php esc_html_e('Play now', 'solaire'); ?></a>
            <?php
              // Demo button only renders when the game has an `so_game_code`.
              echo solaire_demo_trigger(
                  $post_id,
                  __('Demo', 'solaire'),
                  'rounded-md bg-white/10 px-4 py-2 text-center text-xs font-semibold text-white/80 transition hover:bg-white/20 sm:text-sm'
              ); // phpcs:ignore
            ?>
          </div>
        </article>
        <?php
        return ob_get_clean();
    }
}

/**
 * One carousel: title + nav + snap-scrolling slides, grouped `per_page` rows
 * per slide. `$cols` sets how many columns each slide splits into (2 desktop,
 * 1 mobile); `$wrapper_class` handles per-breakpoint visibility.
 */
if (!function_exists('solaire_ranking_carousel')) {
    function solaire_ranking_carousel($ids, $limit, $per_page, $cols, $title, $wrapper_class)
    {
        $ids   = array_slice($ids, 0, $limit);
        $total = count($ids);
        if (!$total) {
            return '';
        }
        $slides = (int) ceil($total / $per_page);
        // Rows per slide column: with two columns the rank numbers fill
        // top-to-bottom, so each column is half the per-slide count.
        $rows = ($cols > 1) ? (int) ceil($per_page / $cols) : $per_page;

        ob_start(); ?>
        <div class="relative z-10 mt-12 <?php echo esc_attr($wrapper_class); ?>" data-carousel>
          <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-bold sm:text-xl"><?php echo esc_html($title); ?></h2>
            <div class="flex items-center gap-2">
              <?php if ($slides > 1) : ?>
                <button data-prev aria-label="<?php esc_attr_e('Previous', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-secondary transition hover:bg-white/20 disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-white/25 disabled:hover:bg-white/5"><?php echo solaire_icon('arrow-left', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
                <button data-next aria-label="<?php esc_attr_e('Next', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-secondary transition hover:bg-white/20 disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-white/25 disabled:hover:bg-white/5"><?php echo solaire_icon('arrow-right', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
              <?php endif; ?>
            </div>
          </div>

          <div data-track class="no-scrollbar flex snap-x snap-mandatory gap-4 overflow-x-auto">
            <?php
            $rank = 0;
            foreach ($ids as $pid) {
                $rank++;
                // Open a new full-width slide at the start of each page.
                if (($rank - 1) % $per_page === 0) {
                    printf('<div class="rank-grid w-full shrink-0 snap-start" style="--rank-rows:%d">', $rows);
                }
                echo solaire_ranking_article($pid, $rank); // phpcs:ignore
                // Close the slide after a full page, or on the final item.
                if ($rank % $per_page === 0 || $rank === $total) {
                    echo '</div>';
                }
            }
            ?>
          </div>

          <?php if ($slides > 1) : ?>
            <div data-dots class="mt-5 flex items-center justify-center gap-2" aria-label="<?php esc_attr_e('Slides', 'solaire'); ?>"></div>
          <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative']); ?>>
  <div class="mx-auto max-w-shell px-4">
    <?php if (!$ids) : ?>
      <div class="relative z-10 mt-12">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="font-display text-lg font-bold sm:text-xl"><?php echo esc_html($title); ?></h2>
        </div>
        <p class="rounded-xl bg-white/[0.03] p-6 text-sm text-slatey ring-1 ring-white/5"><?php esc_html_e('No games found yet. Add games to populate the ranking.', 'solaire'); ?></p>
      </div>
    <?php else :
        // Desktop: two-column slides. Mobile: single-column slides.
        echo solaire_ranking_carousel($ids, $count,   $per_page,   2, $title, 'hidden sm:block'); // phpcs:ignore
        echo solaire_ranking_carousel($ids, $count_m, $per_page_m, 1, $title, 'sm:hidden');        // phpcs:ignore
    endif; ?>
  </div>
  <?php echo solaire_demo_modal(); // phpcs:ignore ?>
</section>
