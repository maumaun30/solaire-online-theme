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
    // "Select Volatility" is the unset/placeholder choice — treat it as empty.
    $vol       = get_field('volatility');
    if ($vol === 'Select Volatility') {
        $vol = '';
    }
    $play_url  = get_field('play_url') ?: '#';
    // Hero short description — ACF text field only (no excerpt fallback).
    $short_desc = get_field('so_game_short_description', $id);

    // Demo embed — driven by the `so_game_code` ACF field. Opens in the modal
    // on both desktop and mobile. wp_is_mobile() reads the UA so the embed
    // requests the matching device build.
    $game_code  = get_field('so_game_code', $id);
    $device     = wp_is_mobile() ? 'MOBILE' : 'DESKTOP';
    $game_embed = $game_code
        ? do_shortcode('[st8_game game="' . esc_attr($game_code) . '" fun_mode="true" device="' . $device . '"]')
        : '';

    $cats      = wp_get_post_terms($id, 'game_category', ['fields' => 'all']);
    $cat_name  = (!is_wp_error($cats) && $cats) ? $cats[0]->name : __('Featured Game', 'solaire');
    $cat_slug  = (!is_wp_error($cats) && $cats) ? $cats[0]->slug : '';

    // Stats — ACF repeater, falling back to provider/RTP/volatility.
    $stats = get_field('stats');
    if (!$stats) {
        $stats = [['label' => 'RTP', 'value' => $rtp]];
        // Skip the Volatility stat entirely when none is selected.
        if ($vol !== '') {
            $stats[] = ['label' => 'Volatility', 'value' => $vol];
        }
        $stats[] = ['label' => 'Game Provider', 'value' => $provider];
    }
    $stat_icons = ['chart', 'bolt', 'grid-rows'];

    $rules       = get_field('rules') ?: [];
    $rule_icons  = ['squares', 'star', 'refresh', 'paylines'];
?>

<main class="mx-auto max-w-shell px-4 pt-6">

  <!-- ===================== GAME HERO ===================== -->
  <section class="relative overflow-hidden rounded-[20px] ring-1 ring-orange/70">
    <img src="<?php echo esc_url($hero_bg); ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-right" />
    <div class="absolute inset-0 bg-gradient-to-r from-deep via-deep/90 to-deep/75"></div>
    <!-- Warm glow bleeding in from the top-left, behind the thumbnail. -->
    <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-orange/45 blur-[90px]"></div>
    <div class="relative z-10 grid items-center gap-6 p-6 sm:p-10 md:grid-cols-[260px_1fr]">
      <?php if ($art) : ?>
        <img data-anim src="<?php echo esc_url($art); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="game-art" />
      <?php elseif ($logo = solaire_site_logo_url()) : ?>
        <div data-anim class="game-art mx-auto flex aspect-[3/4] w-44 items-center justify-center rounded-xl bg-gradient-to-br from-panel via-surface to-deep p-6 md:w-full">
          <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="w-3/5 max-w-[130px] object-contain opacity-95" />
        </div>
      <?php else : ?>
        <div data-anim class="ph mx-auto aspect-[3/4] w-44 rounded-xl md:w-full"></div>
      <?php endif; ?>
      <div class="text-center md:text-left">
        <nav data-anim class="flex flex-wrap items-center justify-center gap-2 text-sm font-semibold text-white/70 md:justify-start" aria-label="<?php esc_attr_e('Breadcrumb', 'solaire'); ?>">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="transition hover:text-white"><?php esc_html_e('Home', 'solaire'); ?></a>
          <?php if ($cat_name) :
              $cat_link = (!is_wp_error($cats) && $cats) ? get_term_link($cats[0]) : '';
          ?>
            <span class="text-white/30">|</span>
            <?php if ($cat_link && !is_wp_error($cat_link)) : ?>
              <a href="<?php echo esc_url($cat_link); ?>" class="transition hover:text-white"><?php echo esc_html($cat_name); ?></a>
            <?php else : ?>
              <span><?php echo esc_html($cat_name); ?></span>
            <?php endif; ?>
          <?php endif; ?>
          <span class="text-white/30">|</span>
          <span class="text-orange"><?php the_title(); ?></span>
        </nav>
        <h1 data-anim data-anim-delay="100" class="mt-4 font-display text-3xl font-extrabold leading-tight text-orange sm:text-5xl"><?php the_title(); ?></h1>
        <?php if ($short_desc) : ?>
          <p data-anim data-anim-delay="180" class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-white/85 sm:text-lg md:mx-0"><?php echo esc_html($short_desc); ?></p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ===================== STATS BAR ===================== -->
  <?php $stat_cols = count($stats) <= 2 ? 'sm:grid-cols-2' : 'sm:grid-cols-3'; ?>
  <div class="mt-5 grid gap-px overflow-hidden rounded-xl bg-white/10 ring-1 ring-white/10 <?php echo esc_attr($stat_cols); ?>">
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
  <div class="mt-8 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center">
    <a href="https://www.solaireonline.com/login" class="bg-brand-orange text-white btn-press inline-flex w-full items-center justify-center rounded-xl px-8 py-4 text-center font-display text-lg sm:w-auto sm:text-xl"><?php esc_html_e('Play for Real - Sign Up Now', 'solaire'); ?></a>
    <?php if ($game_code) : ?>
      <button type="button" data-demo-open
        data-title="<?php echo esc_attr(get_the_title()); ?> — <?php esc_attr_e('Demo', 'solaire'); ?>"
        class="btn-press inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-8 py-4 text-center font-display text-lg text-white ring-1 ring-white/20 transition hover:bg-white/15 sm:w-auto sm:text-xl">
        <?php esc_html_e('Try Demo', 'solaire'); ?>
      </button>
    <?php endif; ?>
  </div>

  <!-- ===================== MORE GAMES ===================== -->
  <?php
  $more_q = solaire_query_games(['category' => $cat_slug, 'count' => 10, 'exclude' => [$id]]);
  if ($more_q->have_posts()) :
      // "View All" points to the top-level parent category page.
      $view_all_url = get_post_type_archive_link('game') ?: '#';
      if (!is_wp_error($cats) && $cats) {
          $primary  = $cats[0];
          $anc      = get_ancestors($primary->term_id, 'game_category', 'taxonomy');
          $top_id   = !empty($anc) ? end($anc) : $primary->term_id;
          $top_link = get_term_link($top_id, 'game_category');
          if (!is_wp_error($top_link)) {
              $view_all_url = $top_link;
          }
      }
  ?>
  <section class="mt-12" data-carousel>
    <div class="mb-3 flex items-center justify-between">
      <h2 class="font-display text-lg font-bold sm:text-xl"><?php esc_html_e('More Games', 'solaire'); ?></h2>
      <div class="flex items-center gap-2">
        <a href="<?php echo esc_url($view_all_url); ?>" class="rounded-md bg-white/10 px-3 py-1.5 text-xs text-secondary transition"><?php esc_html_e('View All', 'solaire'); ?></a>
        <button data-prev aria-label="<?php esc_attr_e('Previous', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-secondary transition hover:bg-white/20 disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-white/25 disabled:hover:bg-white/5"><?php echo solaire_icon('arrow-left', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
        <button data-next aria-label="<?php esc_attr_e('Next', 'solaire'); ?>" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-secondary transition hover:bg-white/20 disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-white/25 disabled:hover:bg-white/5"><?php echo solaire_icon('arrow-right', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
      </div>
    </div>
    <div data-track class="game-row-track no-scrollbar snap-row flex gap-3 overflow-x-auto pb-2 pt-3 sm:gap-4">
      <?php
      while ($more_q->have_posts()) {
          $more_q->the_post();
          echo solaire_game_card(get_the_ID(), ['variant' => 'grid', 'class' => 'shrink-0']); // phpcs:ignore
      }
      wp_reset_postdata();
      ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== ABOUT ===================== -->
  <?php if (trim(strip_tags(get_the_content())) !== '') : ?>
  <section data-anim class="title-bar mt-12 bg-white/[0.02] p-6 sm:p-8">
    <h2 class="title-tab pl-4 font-display text-xl font-extrabold sm:text-2xl"><?php printf(esc_html__('About %s', 'solaire'), esc_html(get_the_title())); ?></h2>
    <div data-readmore data-readmore-blocks="1" class="mt-5">
      <div class="relative">
        <div data-readmore-body class="text-sm leading-relaxed text-slatey sm:text-base [&_a]:text-orange hover:[&_a]:underline [&_h2]:mb-2 [&_h2]:mt-6 [&_h2]:font-display [&_h2]:text-lg [&_h2]:font-bold [&_h2]:text-white [&_h3]:mb-2 [&_h3]:mt-4 [&_h3]:font-display [&_h3]:font-semibold [&_h3]:text-white [&_li]:mb-1 [&_ol]:my-4 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_p:last-child]:mb-0 [&_ul]:my-4 [&_ul]:list-disc [&_ul]:pl-5"><?php the_content(); ?></div>
        <div data-readmore-fade class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-[#272a2d] transition-opacity duration-300"></div>
      </div>
      <button type="button" data-readmore-toggle data-more="<?php esc_attr_e('Read More', 'solaire'); ?>" data-less="<?php esc_attr_e('Read Less', 'solaire'); ?>" class="mt-4 font-display text-sm font-semibold text-orange transition hover:text-white"><?php esc_html_e('Read More', 'solaire'); ?></button>
    </div>
  </section>
  <?php endif; ?>

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

<?php if ($game_code) : ?>
  <!-- ===================== DEMO MODAL ===================== -->
  <div id="demo-modal" class="fixed inset-0 z-[9997] hidden items-center justify-center bg-black/85 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="relative w-full max-w-5xl overflow-hidden rounded-2xl bg-deep ring-1 ring-white/15 shadow-2xl">
      <div class="flex items-center justify-between border-b border-white/10 bg-surface px-5 py-3">
        <h2 id="demo-modal-title" class="font-display text-lg font-bold text-white"></h2>
        <button type="button" data-demo-close aria-label="<?php esc_attr_e('Close', 'solaire'); ?>" class="flex h-9 w-9 items-center justify-center rounded-md text-white/70 transition hover:bg-white/10 hover:text-white">
          <?php echo solaire_icon('close', 'h-5 w-5', '2.5'); // phpcs:ignore ?>
        </button>
      </div>
      <div class="relative h-[75vh] w-full bg-deep sm:aspect-video sm:h-auto [&_iframe]:absolute [&_iframe]:inset-0 [&_iframe]:h-full [&_iframe]:w-full [&_iframe]:border-0">
        <div data-demo-loading class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-deep text-sm text-slatey">
          <span class="h-10 w-10 animate-spin rounded-full border-[3px] border-white/15 border-t-orange"></span>
          <span><?php esc_html_e('Loading game…', 'solaire'); ?></span>
        </div>
        <?php echo $game_embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </div>
    </div>
  </div>

  <script>
    (function () {
      'use strict';
      var modal = document.getElementById('demo-modal');
      if (!modal) return;

      var titleEl = document.getElementById('demo-modal-title');
      var loadEl  = modal.querySelector('[data-demo-loading]');
      var frame   = modal.querySelector('iframe');

      function hideLoading() { if (loadEl) loadEl.style.display = 'none'; }
      if (frame) { frame.addEventListener('load', hideLoading); } else { hideLoading(); }

      function open(title) {
        if (titleEl) titleEl.textContent = title || '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        hideLoading();
      }
      function close() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      }

      document.querySelectorAll('[data-demo-open]').forEach(function (btn) {
        // Buttons with a target belong to the shared modal (solaire_demo_modal).
        if (btn.dataset.demoTarget) return;
        btn.addEventListener('click', function () {
          open(this.dataset.title);
        });
      });

      modal.querySelectorAll('[data-demo-close]').forEach(function (btn) {
        btn.addEventListener('click', close);
      });
      modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
      });
    })();
  </script>
<?php endif; ?>

<?php
endwhile;
get_footer();
