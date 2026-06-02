<?php
/**
 * Single Game — the "Coin Combo" layout.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $id        = get_the_ID();
    $art       = get_the_post_thumbnail_url($id, 'large');
    $hero_bg   = get_field('hero_background');
    if (!$hero_bg) {
        $hero_bg = get_theme_file_uri('/assets/img/coin-combo.png');
    }
    $provider  = get_field('provider') ?: 'Solaire Online';
    $rtp       = get_field('rtp') ?: '96.2%';
    $vol       = get_field('volatility') ?: 'High';
    $play_url  = get_field('play_url') ?: '#';

    $cats      = wp_get_post_terms($id, 'game_category', ['fields' => 'all']);
    $cat_name  = (!is_wp_error($cats) && $cats) ? $cats[0]->name : __('Featured Game', 'solaire');
    $cat_slug  = (!is_wp_error($cats) && $cats) ? $cats[0]->slug : '';

    // Stats — ACF repeater, falling back to provider/RTP/volatility.
    $stats = get_field('stats');
    if (!$stats) {
        $stats = [
            ['label' => 'RTP', 'value' => $rtp],
            ['label' => 'Volatility', 'value' => $vol],
            ['label' => 'Game Provider', 'value' => $provider],
        ];
    }
    $stat_icons = ['chart', 'bolt', 'grid-rows'];

    $rules       = get_field('rules') ?: [];
    $rule_icons  = ['squares', 'star', 'refresh', 'paylines'];
?>

<main class="mx-auto max-w-shell px-4 pt-6">

  <!-- ===================== GAME HERO ===================== -->
  <section class="relative overflow-hidden rounded-2xl ring-1 ring-white/10">
    <img src="<?php echo esc_url($hero_bg); ?>" alt="" class="absolute inset-0 h-full w-full object-cover" />
    <div class="absolute inset-0 bg-gradient-to-r from-deep via-deep/85 to-deep/55"></div>
    <div class="relative z-10 grid items-center gap-6 p-6 sm:p-10 md:grid-cols-[260px_1fr]">
      <?php if ($art) : ?>
        <img data-anim src="<?php echo esc_url($art); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="mx-auto w-44 rounded-xl shadow-2xl shadow-black/50 ring-1 ring-gold/30 md:w-full" />
      <?php else : ?>
        <div data-anim class="ph mx-auto aspect-[3/4] w-44 rounded-xl md:w-full"></div>
      <?php endif; ?>
      <div>
        <span data-anim class="inline-block rounded-md bg-black/40 px-4 py-1.5 text-sm font-semibold ring-1 ring-white/20"><?php echo esc_html($cat_name); ?></span>
        <h1 data-anim data-anim-delay="100" class="mt-4 font-display text-3xl font-extrabold leading-tight text-orange sm:text-5xl"><?php the_title(); ?></h1>
        <p data-anim data-anim-delay="180" class="mt-4 max-w-xl text-sm leading-relaxed text-white/85 sm:text-lg"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 40)); ?></p>
      </div>
    </div>
  </section>

  <!-- ===================== STATS BAR ===================== -->
  <div class="mt-5 grid gap-px overflow-hidden rounded-xl bg-white/10 ring-1 ring-white/10 sm:grid-cols-3">
    <?php foreach (array_values($stats) as $i => $stat) :
        $icon = $stat_icons[$i % count($stat_icons)];
    ?>
      <div class="flex items-center gap-4 bg-surface px-6 py-5">
        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange/15 text-orange"><?php echo solaire_icon($icon, 'h-5 w-5'); // phpcs:ignore ?></span>
        <span class="text-sm text-slatey"><?php echo esc_html($stat['label'] ?? ''); ?>: <b class="text-white"><?php echo esc_html($stat['value'] ?? ''); ?></b></span>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- ===================== CTA ===================== -->
  <div class="mt-8 flex justify-center">
    <a href="<?php echo esc_url($play_url); ?>" class="bg-gold-cta btn-press w-full max-w-xl rounded-xl px-8 py-4 text-center font-display text-lg font-bold text-[#3a2606] shadow-lg shadow-gold/20 sm:text-xl"><?php esc_html_e('Play for Real - Sign Up Now', 'solaire'); ?></a>
  </div>

  <!-- ===================== MORE GAMES ===================== -->
  <?php
  $more = solaire_query_games(['category' => $cat_slug, 'count' => 10, 'exclude' => [$id]]);
  if ($more->have_posts()) :
  ?>
  <section class="mt-12" data-carousel>
    <div class="mb-3 flex items-center justify-between">
      <h2 class="font-display text-lg font-bold sm:text-xl"><?php esc_html_e('More Games', 'solaire'); ?></h2>
      <div class="flex items-center gap-2">
        <a href="<?php echo esc_url(get_post_type_archive_link('game') ?: '#'); ?>" class="rounded-md bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/80 transition hover:bg-white/20"><?php esc_html_e('View All', 'solaire'); ?></a>
        <button data-prev aria-label="<?php esc_attr_e('Previous', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-white/80 transition hover:bg-white/20"><?php echo solaire_icon('arrow-left', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
        <button data-next aria-label="<?php esc_attr_e('Next', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-white/80 transition hover:bg-white/20"><?php echo solaire_icon('arrow-right', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
      </div>
    </div>
    <div data-track class="no-scrollbar snap-row flex gap-3 overflow-x-auto pb-2 sm:gap-4">
      <?php
      while ($more->have_posts()) {
          $more->the_post();
          echo solaire_game_card(get_the_ID(), ['variant' => 'grid', 'class' => 'w-[40%] shrink-0 sm:w-[28%] md:w-[16%]']); // phpcs:ignore
      }
      wp_reset_postdata();
      ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== ABOUT ===================== -->
  <section data-anim class="title-bar mt-12 bg-white/[0.02] p-6 sm:p-8">
    <h2 class="title-tab pl-4 font-display text-xl font-extrabold sm:text-2xl"><?php printf(esc_html__('About %s', 'solaire'), esc_html(get_the_title())); ?></h2>
    <div class="mt-5 text-sm leading-relaxed text-slatey sm:text-base"><?php the_content(); ?></div>
  </section>

  <!-- ===================== RULES ===================== -->
  <?php if ($rules) : ?>
  <section class="mt-14">
    <h2 data-anim class="text-center font-display text-2xl font-extrabold sm:text-4xl"><?php esc_html_e('Game Rules & Mechanics', 'solaire'); ?></h2>
    <div class="mt-8 grid gap-5 md:grid-cols-2">
      <?php foreach ($rules as $i => $rule) :
          $icon  = $rule_icons[$i % count($rule_icons)];
          $delay = ($i % 2) ? 80 : 0;
      ?>
        <article data-anim <?php echo $delay ? 'data-anim-delay="' . esc_attr($delay) . '"' : ''; ?> class="flex gap-4 rounded-xl bg-white/[0.03] p-6 ring-1 ring-white/10">
          <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-orange/15 text-orange"><?php echo solaire_icon($icon, 'h-5 w-5'); // phpcs:ignore ?></span>
          <div>
            <h3 class="font-display text-lg font-bold"><?php echo esc_html($rule['title'] ?? ''); ?></h3>
            <p class="mt-2 text-sm leading-relaxed text-slatey"><?php echo esc_html($rule['text'] ?? ''); ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <div class="h-16"></div>
</main>

<?php
endwhile;
get_footer();
