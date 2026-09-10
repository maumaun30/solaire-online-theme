<?php

/**
 * Template: search.php
 * Search results across games, promos, blog posts and pages.
 *
 * Layout mirrors the category archive (same shell width, same grid
 * breakpoints and the same 111:140 card ratio) so a result tile is
 * visually identical to a game tile elsewhere on the site.
 */

if (!defined('ABSPATH')) {
  exit;
}

get_header();

$search_query = get_search_query();
$found        = (int) $GLOBALS['wp_query']->found_posts;

/**
 * Human-readable badge label for a result's post type.
 */
function solaire_search_type_label($post_type)
{
  $labels = [
    'game'  => __('Game', 'solaire'),
    'promo' => __('Promo', 'solaire'),
    'post'  => __('Blog', 'solaire'),
    'page'  => __('Page', 'solaire'),
  ];
  return $labels[$post_type] ?? ucfirst($post_type);
}
?>

<!-- ============================ BANNER ============================ -->
<section class="relative overflow-hidden bg-surface">
  <div class="absolute inset-0 bg-gradient-to-r from-deep via-deep/85 to-deep/30"></div>
  <div class="relative z-10 mx-auto max-w-shell px-4 py-12 sm:py-16">
    <div class="border-l-4 border-orange pl-5">
      <nav class="mb-2 flex items-center gap-2 text-sm">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="text-white/70 transition hover:text-white"><?php esc_html_e('Home', 'solaire'); ?></a>
        <span class="text-white/30">|</span>
        <span class="font-semibold text-orange"><?php esc_html_e('Search Results', 'solaire'); ?></span>
      </nav>
      <h1 data-anim class="font-display text-3xl font-extrabold uppercase tracking-tight sm:text-5xl">
        <?php if ($search_query) : ?>
          <?php esc_html_e('Results for', 'solaire'); ?>
          <span class="text-orange">&ldquo;<?php echo esc_html($search_query); ?>&rdquo;</span>
        <?php else : ?>
          <?php esc_html_e('Search Solaire Online', 'solaire'); ?>
        <?php endif; ?>
      </h1>
      <?php if ($search_query) : ?>
        <p data-anim data-anim-delay="120" class="mt-3 text-sm text-white/85 sm:text-base">
          <?php
          printf(
            /* translators: %s: number of search results. */
            esc_html(_n('%s result found', '%s results found', $found, 'solaire')),
            '<b class="text-gold">' . esc_html(number_format_i18n($found)) . '</b>'
          );
          ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
</section>

<main class="mx-auto max-w-shell px-4 pb-16">

  <?php if (have_posts()) : ?>

    <!-- ===================== RESULTS GRID ===================== -->
    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
      <?php while (have_posts()) : the_post();
        $post_type = get_post_type();
        $thumb     = get_the_post_thumbnail_url(get_the_ID(), 'large');
      ?>
        <a href="<?php the_permalink(); ?>" class="card-lift group block overflow-hidden rounded-xl bg-panel ring-1 ring-white/5">
          <div class="game-card relative overflow-hidden">
            <?php if ($thumb) : ?>
              <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" class="absolute inset-0 h-full w-full object-cover" loading="lazy" />
            <?php else : ?>
              <?php echo solaire_card_logo_face(); // phpcs:ignore 
              ?>
            <?php endif; ?>
            <div class="absolute right-2 top-2 z-10">
              <span class="rounded bg-brand-orange px-1.5 py-0.5 text-[10px] font-bold uppercase"><?php echo esc_html(solaire_search_type_label($post_type)); ?></span>
            </div>
          </div>
          <div class="px-3 py-2">
            <h3 class="truncate font-display text-sm font-bold"><?php the_title(); ?></h3>
          </div>
        </a>
      <?php endwhile; ?>
    </div>

    <!-- ===================== PAGINATION ===================== -->
    <?php
    $links = paginate_links([
      'prev_text' => __('&lsaquo; Prev', 'solaire'),
      'next_text' => __('Next &rsaquo;', 'solaire'),
      'type'      => 'array',
      'end_size'  => 1,
      'mid_size'  => 2,
    ]);
    if ($links) : ?>
      <nav class="so-pagination mt-10 flex flex-wrap justify-center gap-2" aria-label="<?php esc_attr_e('Search pagination', 'solaire'); ?>">
        <?php foreach ($links as $link) {
          echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } ?>
      </nav>
    <?php endif; ?>

  <?php else : ?>

    <!-- ===================== EMPTY STATE ===================== -->
    <div class="title-bar mt-8 bg-white/[0.02] px-6 py-16 text-center">
      <h2 class="font-display text-xl font-extrabold text-white sm:text-2xl">
        <?php if ($search_query) : ?>
          <?php esc_html_e('No results match', 'solaire'); ?>
          &ldquo;<?php echo esc_html($search_query); ?>&rdquo;
        <?php else : ?>
          <?php esc_html_e('Nothing to search for yet', 'solaire'); ?>
        <?php endif; ?>
      </h2>
      <p class="mt-2 text-sm text-slatey"><?php esc_html_e('Try a different keyword or check the spelling.', 'solaire'); ?></p>
    </div>

  <?php endif; ?>

</main>

<?php get_footer(); ?>
