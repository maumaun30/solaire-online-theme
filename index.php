<?php
/**
 * Generic fallback template (blog index, search, date archives, etc.).
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="mx-auto max-w-shell px-4 py-12">
  <?php if (have_posts()) : ?>

    <?php if (is_home() && !is_front_page()) : ?>
      <h1 class="mb-8 font-display text-3xl font-extrabold sm:text-4xl"><?php single_post_title(); ?></h1>
    <?php elseif (is_archive()) : ?>
      <h1 class="mb-8 font-display text-3xl font-extrabold sm:text-4xl"><?php the_archive_title(); ?></h1>
    <?php elseif (is_search()) : ?>
      <h1 class="mb-8 font-display text-3xl font-extrabold sm:text-4xl"><?php printf(esc_html__('Search results for: %s', 'solaire'), '<span class="text-orange">' . esc_html(get_search_query()) . '</span>'); ?></h1>
    <?php endif; ?>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('overflow-hidden rounded-2xl bg-white/[0.03] ring-1 ring-white/10 transition hover:bg-white/[0.06]'); ?>>
          <a href="<?php the_permalink(); ?>" class="block">
            <?php if (has_post_thumbnail()) : ?>
              <div class="aspect-[16/10] overflow-hidden"><?php the_post_thumbnail('large', ['class' => 'h-full w-full object-cover']); ?></div>
            <?php endif; ?>
            <div class="p-5">
              <h2 class="font-display text-lg font-bold"><?php the_title(); ?></h2>
              <p class="mt-2 text-sm leading-relaxed text-slatey"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
            </div>
          </a>
        </article>
      <?php endwhile; ?>
    </div>

    <div class="mt-10"><?php the_posts_pagination(['mid_size' => 1]); ?></div>

  <?php else : ?>
    <p class="text-slatey"><?php esc_html_e('Sorry, nothing matched your request.', 'solaire'); ?></p>
  <?php endif; ?>
</main>
<?php
get_footer();
