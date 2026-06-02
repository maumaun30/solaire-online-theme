<?php
/**
 * Games archive (and game_category archive) — the "Live Slots" category layout.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$is_tax     = is_tax('game_category');
$page_title = $is_tax ? single_term_title('', false) : post_type_archive_title('', false);
$crumb      = $page_title;

// Banner background — term thumbnail if available, else a bundled slot image.
$banner = get_theme_file_uri('/assets/img/slot-2.png');

$blurb = '';
if ($is_tax) {
    $blurb = term_description();
}
if (!$blurb) {
    $blurb = "Spin the Reels and Try Your Luck Playing Live Slots on Solaire Online Philippines!";
}

$terms = get_terms(['taxonomy' => 'game_category', 'hide_empty' => true]);
?>

<!-- ============================ BANNER ============================ -->
<section class="relative mt-1 overflow-hidden">
  <img src="<?php echo esc_url($banner); ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-70" />
  <div class="absolute inset-0 bg-gradient-to-r from-deep via-deep/85 to-deep/30"></div>
  <div class="relative z-10 mx-auto max-w-shell px-4 py-12 sm:py-16">
    <div class="border-l-4 border-orange pl-5">
      <nav class="mb-2 flex items-center gap-2 text-sm">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="text-white/70 transition hover:text-white"><?php esc_html_e('Home', 'solaire'); ?></a>
        <span class="text-white/30">|</span>
        <span class="font-semibold text-orange"><?php echo esc_html($crumb); ?></span>
      </nav>
      <h1 data-anim class="font-display text-4xl font-extrabold uppercase tracking-tight sm:text-6xl"><?php echo esc_html($page_title); ?></h1>
      <p data-anim data-anim-delay="120" class="mt-3 max-w-xl text-sm text-white/85 sm:text-base"><?php echo esc_html(wp_strip_all_tags($blurb)); ?></p>
    </div>
  </div>
</section>

<main class="mx-auto max-w-shell px-4">

  <!-- ===================== FILTERS ===================== -->
  <div class="mt-8 flex flex-wrap items-center gap-3" data-filter-group data-filter-target="#games-grid">
    <button data-filter="all" class="solaire-chip btn-press is-active"><?php esc_html_e('Popular Games', 'solaire'); ?></button>
    <?php foreach ($terms as $term) : ?>
      <button data-filter="<?php echo esc_attr($term->slug); ?>" class="solaire-chip btn-press"><?php echo esc_html($term->name); ?></button>
    <?php endforeach; ?>
  </div>

  <!-- ===================== GAME GRID ===================== -->
  <div id="games-grid" data-grid data-step="12" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            echo solaire_game_card(get_the_ID(), ['variant' => 'grid']); // phpcs:ignore
        endwhile;
    else :
        echo '<p class="col-span-full rounded-xl bg-white/[0.03] p-6 text-sm text-slatey ring-1 ring-white/5">' . esc_html__('No games found.', 'solaire') . '</p>';
    endif;
    ?>
  </div>

  <!-- LOAD MORE -->
  <div class="mt-8 flex justify-center">
    <button data-load-more data-load-target="#games-grid" data-step="12" class="btn-press rounded-lg border border-orange/70 bg-orange/10 px-8 py-3 text-sm font-bold uppercase tracking-wide text-orange transition hover:bg-orange hover:text-white"><?php esc_html_e('Load More Games', 'solaire'); ?></button>
  </div>

  <!-- ===================== CONTENT BLOCK ===================== -->
  <section data-anim class="title-bar mt-12 bg-white/[0.02] p-6 sm:p-8">
    <h2 class="title-tab pl-4 font-display text-xl font-extrabold sm:text-2xl"><?php esc_html_e('Enjoy Online Casino Games at Solaire Online', 'solaire'); ?></h2>
    <p class="mt-5 text-sm leading-relaxed text-slatey sm:text-base"><?php esc_html_e('Solaire Online offers a variety of highly engaging live slots and online casino games that allow you to enjoy an unparalleled gaming experience. Experience the thrill of traditional slots with the sophistication of a VIP room, blending and creating a harmony that you will tune in to. See the meticulous design of these live slots where players can immerse themselves and pull the virtual lever with a tap of their finger to make every spin.', 'solaire'); ?></p>
    <a href="#" class="mt-4 inline-block text-sm font-bold text-white transition hover:text-orange"><?php esc_html_e('Read More', 'solaire'); ?></a>
  </section>

  <!-- ===================== FAQ ===================== -->
  <?php echo do_blocks('<!-- wp:solaire/faq-guide /-->'); // phpcs:ignore ?>

  <div class="h-16"></div>
</main>

<?php
get_footer();
