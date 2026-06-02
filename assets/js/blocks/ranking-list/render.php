<?php
/**
 * Solaire Ranking List — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$title    = $attributes['title'] ?? '';
$category = $attributes['category'] ?? '';
$count    = max(1, (int) ($attributes['count'] ?? 7));
$view_url = $attributes['viewAllUrl'] ?: (get_post_type_archive_link('game') ?: '#');

$query = solaire_query_games(['category' => $category, 'count' => $count]);
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative']); ?>>
  <div class="mx-auto max-w-shell px-4">
    <div class="relative z-10 mt-12">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="font-display text-lg font-bold sm:text-xl"><?php echo esc_html($title); ?></h2>
        <div class="flex items-center gap-2">
          <a href="<?php echo esc_url($view_url); ?>" class="rounded-md bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/80 transition hover:bg-white/20"><?php esc_html_e('View all', 'solaire'); ?></a>
          <button aria-label="<?php esc_attr_e('Previous', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-white/80 transition hover:bg-white/20"><?php echo solaire_icon('arrow-left', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
          <button aria-label="<?php esc_attr_e('Next', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-white/80 transition hover:bg-white/20"><?php echo solaire_icon('arrow-right', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
        </div>
      </div>

      <div class="flex flex-col gap-3">
        <?php
        if ($query->have_posts()) :
            $rank = 0;
            while ($query->have_posts()) :
                $query->the_post();
                $rank++;
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                $play  = get_field('play_url') ?: get_permalink();
                $demo  = get_field('demo_url') ?: get_permalink();
        ?>
          <article data-anim class="group flex items-center gap-3 rounded-xl bg-white/[0.03] p-3 ring-1 ring-white/5 transition hover:bg-white/[0.06] sm:gap-5 sm:p-4">
            <div class="relative flex h-12 w-9 shrink-0 items-center justify-center sm:h-16 sm:w-12">
              <?php if ($rank === 1) : ?>
                <svg viewBox="0 0 24 24" class="absolute -top-2 left-1/2 h-4 w-4 -translate-x-1/2 text-gold" fill="currentColor"><path d="M3 7l4 4 5-7 5 7 4-4-2 12H5z"/></svg>
              <?php endif; ?>
              <span class="font-display text-2xl font-extrabold sm:text-3xl"><?php echo esc_html($rank); ?></span>
            </div>
            <?php if ($thumb) : ?>
              <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="h-14 w-14 shrink-0 rounded-lg object-cover sm:h-16 sm:w-16" loading="lazy" />
            <?php else : ?>
              <div class="ph h-14 w-14 shrink-0 rounded-lg sm:h-16 sm:w-16"></div>
            <?php endif; ?>
            <div class="min-w-0 flex-1">
              <h3 class="font-display text-base font-bold sm:text-lg"><?php echo esc_html(get_the_title()); ?></h3>
              <p class="mt-0.5 hidden text-xs leading-relaxed text-slatey sm:block"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 38)); ?></p>
            </div>
            <div class="flex shrink-0 flex-col gap-2">
              <a href="<?php echo esc_url($play); ?>" class="btn-press rounded-md bg-brand-orange px-4 py-2 text-center text-xs font-bold text-white sm:text-sm"><?php esc_html_e('Play now', 'solaire'); ?></a>
              <a href="<?php echo esc_url($demo); ?>" class="rounded-md bg-white/10 px-4 py-2 text-center text-xs font-semibold text-white/80 transition hover:bg-white/20 sm:text-sm"><?php esc_html_e('Demo', 'solaire'); ?></a>
            </div>
          </article>
        <?php
            endwhile;
            wp_reset_postdata();
        else :
        ?>
          <p class="rounded-xl bg-white/[0.03] p-6 text-sm text-slatey ring-1 ring-white/5"><?php esc_html_e('No games found yet. Add games to populate the ranking.', 'solaire'); ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
