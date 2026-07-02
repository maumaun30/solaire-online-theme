<?php
/**
 * game_category taxonomy archive — the "Live Slots" category layout.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$page_title = single_term_title('', false);
$crumb      = $page_title;

// Banner background — bundled slot image.
$banner = get_theme_file_uri('/assets/img/slot-2.png');

// Hero description comes from the category's Description field.
$blurb = trim(wp_strip_all_tags(term_description()));

// Filter chips = direct child categories of the category being viewed.
$current_term = get_queried_object();
$terms = get_terms([
    'taxonomy'   => 'game_category',
    'hide_empty' => true,
    'parent'     => isset($current_term->term_id) ? $current_term->term_id : 0,
]);
?>

<!-- ============================ BANNER ============================ -->
<section class="relative overflow-hidden">
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
      <?php if ($blurb) : ?>
        <p data-anim data-anim-delay="120" class="mt-3 max-w-xl text-sm text-white/85 sm:text-base"><?php echo esc_html($blurb); ?></p>
      <?php endif; ?>
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
  <?php
  $grid_step     = defined('SOLAIRE_GAMES_PER_PAGE') ? SOLAIRE_GAMES_PER_PAGE : 12;
  $grid_has_more = ($GLOBALS['wp_query']->max_num_pages ?? 1) > 1;
  ?>
  <div id="games-grid" data-grid
       data-step="<?php echo esc_attr($grid_step); ?>"
       data-parent="<?php echo esc_attr(isset($current_term->slug) ? $current_term->slug : ''); ?>"
       data-page="1"
       data-has-more="<?php echo $grid_has_more ? '1' : '0'; ?>"
       class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
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
    <button data-load-more data-load-target="#games-grid" data-step="<?php echo esc_attr($grid_step); ?>" class="btn-press rounded-lg border border-orange/70 bg-orange/10 px-8 py-3 text-sm uppercase tracking-wide transition hover:bg-orange hover:text-white disabled:cursor-not-allowed<?php echo $grid_has_more ? '' : ' hidden'; ?>"><?php esc_html_e('Load More Games', 'solaire'); ?></button>
  </div>

  <!-- ===================== CONTENT BLOCK ===================== -->
  <?php
  // SEO/intro content — ACF WYSIWYG field on the category term.
  $cat_content = get_field('so_game_category_contents', $current_term);
  if ($cat_content) : ?>
  <section data-anim class="title-bar mt-12 bg-white/[0.02] p-6 sm:p-8">
    <div data-readmore data-readmore-collapsed-height="200">
      <div class="relative">
        <div data-readmore-body class="text-sm leading-relaxed text-slatey sm:text-base [&_a]:text-orange hover:[&_a]:underline [&_h2]:mb-3 [&_h2]:mt-8 [&_h2:first-child]:mt-0 [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-extrabold [&_h2]:text-white sm:[&_h2]:text-2xl [&_h3]:mb-2 [&_h3]:mt-4 [&_h3]:font-display [&_h3]:font-semibold [&_h3]:text-white [&_li]:mb-1 [&_ol]:my-4 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_p:last-child]:mb-0 [&_ul]:my-4 [&_ul]:list-disc [&_ul]:pl-5"><?php echo $cat_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
        <div data-readmore-fade class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-[#272a2d] transition-opacity duration-300"></div>
      </div>
      <button type="button" data-readmore-toggle data-more="<?php esc_attr_e('Read More', 'solaire'); ?>" data-less="<?php esc_attr_e('Read Less', 'solaire'); ?>" class="mt-4 font-display text-sm font-semibold text-orange transition hover:text-white"><?php esc_html_e('Read More', 'solaire'); ?></button>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== FAQ ===================== -->
  <?php
  // FAQ items — ACF repeater on the category term.
  $faq_items = get_field('so_game_category_faq_wrapper', $current_term);
  if ($faq_items) : ?>
  <section class="mt-14">
    <h2 data-anim class="text-center font-display text-2xl font-extrabold text-gold sm:text-4xl"><?php printf(esc_html__('A Quick Guide to %s Games', 'solaire'), esc_html($page_title)); ?></h2>
    <div data-anim data-accordion data-single class="mt-8 flex flex-col gap-4">
      <?php foreach ($faq_items as $i => $faq) :
          $open     = ($i === 0);
          $question = $faq['so_game_category_faq_question'] ?? '';
          $answer   = $faq['so_game_category_faq_answer'] ?? '';
          // Icon image — handle array / id / url return formats.
          $icon = $faq['so_game_category_faq_icon'] ?? '';
          if (is_array($icon)) {
              $icon_url = $icon['url'] ?? '';
              $icon_alt = $icon['alt'] ?? '';
          } elseif (is_numeric($icon)) {
              $icon_url = wp_get_attachment_image_url($icon, 'thumbnail');
              $icon_alt = get_post_meta($icon, '_wp_attachment_image_alt', true);
          } else {
              $icon_url = $icon;
              $icon_alt = '';
          }
      ?>
        <div class="acc-item <?php echo $open ? 'open ring-orange/40' : 'ring-white/10'; ?> rounded-xl bg-white/[0.02] ring-1">
          <button data-acc-head class="flex w-full items-center justify-between gap-3 px-6 py-5 text-left">
            <span class="flex items-center gap-3 font-semibold">
              <?php if ($icon_url) : ?>
                <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($icon_alt); ?>" class="h-6 w-6 shrink-0 object-contain" />
              <?php endif; ?>
              <?php echo esc_html($question); ?>
            </span>
            <span class="acc-chevron shrink-0 text-gold"><?php echo solaire_icon('chevron', 'h-5 w-5', '2.5'); // phpcs:ignore ?></span>
          </button>
          <div class="acc-panel"><div class="px-6 pb-6 text-sm leading-relaxed text-slatey"><?php echo nl2br(esc_html($answer)); ?></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <div class="h-16"></div>
</main>

<?php
get_footer();
