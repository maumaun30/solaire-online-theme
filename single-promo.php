<?php

/**
 * Template: single-promo.php
 * CPT:       promo
 *
 * Layout:
 *  1. Hero banner       — featured image full-bleed
 *  2. Main 2-col        — left: title, status pills, content, Deposit Now CTA | right: Recent Promos
 *  3. Featured Games    — Swiper slider from CPT 'game' tagged "Featured Games"
 *
 * ACF fields used:
 *   - so_promo_duration  (date_time_picker — END/expiry datetime)
 *   - so_deposit_now     (text — URL for the Deposit Now button; hides if empty)
 */

get_header();

if (! have_posts()) {
  echo '<p style="color:#fff;text-align:center;padding:4rem;">Promo not found.</p>';
  get_footer();
  exit;
}
the_post();

$post_id   = get_the_ID();
$title     = get_the_title();
$content   = get_the_content();
$permalink = get_permalink();
$hero_img  = get_the_post_thumbnail_url($post_id, 'full');

function sp_promo_acf($key, $id)
{
  return function_exists('get_field') ? get_field($key, $id) : get_post_meta($id, $key, true);
}

/* Promo duration (end date). ACF date_time_picker's default Return Format is
   `d/m/Y g:i a` (day-first), which strtotime() misreads as US m/d/y and rejects
   for values like "31/12/2026". Try ACF's known formats first, then fall back. */
$raw_duration = sp_promo_acf('so_promo_duration', $post_id);
$end_ts       = 0;
if ($raw_duration) {
  $candidates = [
    'd/m/Y g:i a',  // 31/12/2026 11:59 pm  ← ACF default
    'd/m/Y G:i',
    'm/d/Y g:i a',
    'F j, Y g:i a',
    'Y-m-d H:i:s',  // 2026-12-31 23:59:00
    'YmdHis',       // ACF raw DB format
    'Y-m-d',
  ];
  foreach ($candidates as $fmt) {
    $dt = DateTime::createFromFormat($fmt, $raw_duration);
    if ($dt) {
      $end_ts = $dt->getTimestamp();
      break;
    }
  }
  if (! $end_ts) {
    $end_ts = strtotime($raw_duration);   // last resort
    if ($end_ts === false) $end_ts = 0;
  }
}
$is_ongoing    = $end_ts && $end_ts > current_time('timestamp');
$end_formatted = $end_ts ? date_i18n('M j, Y', $end_ts) : '';

$cta_label = 'Deposit Now';
// Deposit Now always points to the main site.
$deposit_url = 'https://www.solaireonline.com/';

/* Recent Promos — same CPT, exclude current, latest 3 */
$recent_promos = [];
$rq = new WP_Query([
  'post_type'      => 'promo',
  'post__not_in'   => [$post_id],
  'posts_per_page' => 3,
  'orderby'        => 'modified',
  'order'          => 'DESC',
]);
if ($rq->have_posts()) {
  while ($rq->have_posts()) {
    $rq->the_post();
    $recent_promos[] = [
      'title'     => get_the_title(),
      'permalink' => get_permalink(),
      'thumb'     => get_the_post_thumbnail_url(get_the_ID(), 'medium_large'),
      'excerpt'   => wp_trim_words(get_the_excerpt(), 16, '…'),
    ];
  }
  wp_reset_postdata();
}

/* Featured Games — CPT 'game' tagged "Featured Games" (same logic as single.php) */
$featured_games     = [];
$fg_term_ids_by_tax = [];

$game_taxes = get_object_taxonomies('game', 'objects');
foreach ($game_taxes as $tax) {
  if (! empty($tax->hierarchical)) continue;
  $term = get_terms([
    'taxonomy'   => $tax->name,
    'name'       => 'Featured Games',
    'hide_empty' => false,
    'number'     => 1,
  ]);
  if (! is_wp_error($term) && ! empty($term)) {
    $fg_term_ids_by_tax[$tax->name] = (int) $term[0]->term_id;
  }
}
if (! empty($fg_term_ids_by_tax)) {
  $tax_query = ['relation' => 'OR'];
  foreach ($fg_term_ids_by_tax as $tax_name => $term_id) {
    $tax_query[] = ['taxonomy' => $tax_name, 'field' => 'term_id', 'terms' => $term_id];
  }
  $fq = new WP_Query([
    'post_type'      => 'game',
    'posts_per_page' => -1,
    'tax_query'      => $tax_query,
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);
  if ($fq->have_posts()) {
    while ($fq->have_posts()) {
      $fq->the_post();
      $featured_games[] = [
        'id'        => get_the_ID(),
        'title'     => get_the_title(),
        'permalink' => get_permalink(),
        'thumb'     => get_the_post_thumbnail_url(get_the_ID(), 'large'),
      ];
    }
    wp_reset_postdata();
  }
}
?>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap');

  :root {
    --color-primary: #df6a2e;
    --color-amber: #f5a623;
    --bg-dark-1: #0a0a0b;
    --bg-dark-2: #1E1E1E;
    --bg-dark-3: #000000;
    --bg-dark-4: rgb(30 30 30 / 50%);
    --border: rgba(255, 255, 255, .08);
    --border-strong: rgba(255, 255, 255, .15);
    --radius-lg: 1.25rem;
    --radius-md: .875rem;
    --section-py: clamp(2rem, 5vw, 4rem);
  }

  .pr-page {
    background: var(--bg-dark-3);
    min-height: 100vh;
    color: #fff;
    font-family: 'Montserrat', sans-serif;
  }

  /* ── BREADCRUMB ── */
  .pr-bc {
    max-width: 1440px;
    margin: 0 auto;
    padding: 1.25rem 1.5rem .5rem;
    display: flex;
    align-items: center;
    gap: .55rem;
    flex-wrap: wrap;
    font-size: .78rem;
    font-weight: 600;
    letter-spacing: .04em;
  }

  .pr-bc a {
    color: #fff;
    text-decoration: none;
    font-weight: 400;
    transition: color .2s;
  }

  .pr-bc a:hover {
    color: var(--color-primary);
  }

  .pr-bc svg {
    color: #fff;
    flex-shrink: 0;
  }

  .pr-bc__cur {
    color: var(--color-primary);
  }

  /* ── HERO BANNER ── */
  .pr-hero {
    max-width: 1440px;
    margin: .5rem auto 0;
    padding: 0 1.5rem;
  }

  .pr-hero__inner {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    aspect-ratio: 1280 / 360;
    background: linear-gradient(135deg, #2b2e31 0%, #232629 50%, #15171a 100%);
  }

  /* Fallback when no featured image — site logo centered on a dark panel,
     matching single-game.php's fallback. */
  .pr-hero__logo {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 30%;
    max-width: 190px;
    object-fit: contain;
    opacity: .95;
  }

  .pr-hero__img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  @media (max-width: 600px) {
    .pr-hero__inner {
      aspect-ratio: 16 / 5;
    }

    .pr-content__title {
      text-align: center;
    }

    .pr-status {
      justify-content: center;
    }

    .pr-cta-wrap {
      display: flex;
      justify-content: center;
    }
  }

  /* ── MAIN GRID ── */
  .pr-main {
    max-width: 1440px;
    margin: 0 auto;
    padding: var(--section-py) 1.5rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 2rem;
  }

  /* No other promos published — content spans the full width (no empty
     320px column where Recent Promos would be). */
  .pr-main--full {
    grid-template-columns: 1fr;
  }

  @media (max-width: 899px) {
    .pr-main {
      grid-template-columns: 1fr;
    }
  }

  /* ── CONTENT CARD ── */
  .pr-content {
    background: var(--bg-dark-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 2rem;
  }

  .pr-content__title {
    font-size: clamp(1.4rem, 2.6vw, 2rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 1rem;
    text-transform: uppercase;
    letter-spacing: -.01em;
    line-height: 1.2;
  }

  /* ── STATUS PILLS (match design: dark with red border) ── */
  .pr-status {
    display: flex;
    flex-wrap: wrap;
    gap: .6rem;
    margin-bottom: 1.5rem;
  }

  .pr-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem 1rem;
    border-radius: 6px;
    font-size: .8rem;
    font-weight: 600;
    color: #fff;
    background: rgba(0, 0, 0, .4);
    border: 1px solid var(--color-primary);
    letter-spacing: .01em;
  }

  .pr-pill--expired {
    background: rgba(186, 0, 29, .15);
    color: #ff5a72;
    border-color: rgba(255, 90, 114, .5);
  }

  /* ── CONTENT BODY ── */
  .pr-content__body {
    font-size: .9rem;
    line-height: 1.75;
    color: rgba(255, 255, 255, .78);
  }

  .pr-content__body p {
    margin: 0 0 1rem;
  }

  .pr-content__body h2,
  .pr-content__body h3,
  .pr-content__body h4 {
    color: #fff;
    margin: 1.75rem 0 .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
  }

  .pr-content__body h2 {
    font-size: .95rem;
  }

  .pr-content__body h3 {
    font-size: .85rem;
    color: var(--color-primary);
  }

  .pr-content__body h4 {
    font-size: .8rem;
  }

  .pr-content__body ul,
  .pr-content__body ol {
    margin: 0 0 1rem;
    padding-left: 1.5rem;
  }

  .pr-content__body li {
    margin-bottom: .35rem;
  }

  .pr-content__body a {
    color: var(--color-primary);
  }

  .pr-content__body strong {
    color: #fff;
  }

  .pr-content__body img {
    max-width: 100%;
    height: auto;
    border-radius: .5rem;
    margin: 1rem 0;
  }

  /* List markers — ul: primary bullet, ol: numbers */
  .pr-content__body ul {
    list-style: disc;
  }

  .pr-content__body ol {
    list-style: decimal;
  }

  .pr-content__body ul li::marker {
    color: var(--color-primary);
  }

  /* ── DEPOSIT NOW CTA ── */
  .pr-cta-wrap {
    margin-top: 25px;
  }

  .pr-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: 14px 28px;
    border-radius: 8px;
    background: var(--color-primary);
    color: #fff;
    font-family: inherit;
    font-size: .85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: filter .2s, transform .2s;
  }

  .pr-cta-btn:hover {
    filter: brightness(1.1);
    transform: translateY(-2px);
    color: #fff;
  }

  /* ── SIDEBAR: RECENT PROMOS ── */
  .pr-side__hd {
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #fff;
    margin: 0 0 1rem;
  }

  .pr-side__list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }

  .pr-rel {
    background: var(--bg-dark-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
    text-decoration: none;
    display: block;
    transition: transform .3s, border-color .3s;
  }

  .pr-rel:hover {
    transform: translateY(-4px);
  }

  .pr-rel__img {
    width: 100%;
    aspect-ratio: 16 / 9;
    object-fit: cover;
    display: block;
    background: var(--bg-dark-4);
  }

  /* Fallback thumb — site logo centered on a dark panel (matches single-game.php). */
  .pr-rel__img--logo {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2b2e31 0%, #232629 50%, #15171a 100%);
  }

  .pr-rel__img--logo img {
    width: 45%;
    max-width: 120px;
    object-fit: contain;
    opacity: .95;
  }

  .pr-rel__body {
    padding: 1rem 1.1rem 1.15rem;
  }

  .pr-rel__title {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.3;
    color: #fff;
    margin: 0 0 .5rem;
    text-transform: uppercase;
    letter-spacing: .02em;
  }

  .pr-rel__excerpt {
    font-size: .8rem;
    line-height: 1.5;
    color: rgba(255, 255, 255, .55);
    margin: 0 0 .85rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .pr-rel__more {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--color-primary);
  }

  /* ── FEATURED GAMES (Swiper — same as single.php) ── */
  .pr-fg {
    max-width: 1440px;
    margin: 0 auto;
    padding: var(--section-py) 1.5rem;
  }

  .pr-fg__hd-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
  }

  .pr-fg__hd {
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #fff;
    margin: 0;
  }

  .pr-fg__nav {
    display: flex;
    gap: .5rem;
  }

  .pr-fg__btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--bg-dark-2);
    border: 1px solid var(--border-strong);
    color: #fff;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background .2s, color .2s, border-color .2s;
  }

  .pr-fg__btn:hover {
    background: var(--color-primary);
    border-color: var(--color-primary);
  }

  .pr-fg__btn.swiper-button-disabled {
    opacity: .35;
    cursor: not-allowed;
  }

  .pr-fg__btn svg {
    width: 16px;
    height: 16px;
  }

  .pr-fg .swiper {
    width: 100%;
    padding-bottom: .5rem;
  }

  .pr-fg .swiper-slide {
    height: auto;
  }

  .pr-fg__card {
    border-radius: var(--radius-md);
    aspect-ratio: 111 / 140;
    overflow: hidden;
    background: var(--bg-dark-4);
    border: 1px solid var(--border);
    text-decoration: none;
    display: block;
    transition: transform .35s, border-color .35s;
  }

  .pr-fg__card:hover {
    transform: translateY(-4px);
  }

  .pr-fg__card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  /* CTA shortcode spacing */
  .pr-cta-global {
    margin-top: var(--section-py);
  }
</style>

<div class="pr-page">

  <!-- BREADCRUMB -->
  <nav class="pr-bc" aria-label="Breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
    <svg viewBox="0 0 6 10" width="6" height="10" aria-hidden="true">
      <path d="M3.818 5L0 1.111 1.091 0 6 5l-4.909 5L0 8.889 3.818 5z" fill="currentColor" />
    </svg>
    <a href="<?php echo esc_url(home_url('/promos/')); ?>">Promos</a>
    <svg viewBox="0 0 6 10" width="6" height="10" aria-hidden="true">
      <path d="M3.818 5L0 1.111 1.091 0 6 5l-4.909 5L0 8.889 3.818 5z" fill="currentColor" />
    </svg>
    <span class="pr-bc__cur"><?php echo esc_html($title); ?></span>
  </nav>

  <!-- HERO BANNER -->
  <section class="pr-hero">
    <div class="pr-hero__inner">
      <?php if ($hero_img) : ?>
        <img class="pr-hero__img" src="<?php echo esc_url($hero_img); ?>" alt="<?php echo esc_attr($title); ?>">
      <?php elseif ($pr_logo = solaire_site_logo_url()) : ?>
        <img class="pr-hero__logo" src="<?php echo esc_url($pr_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
      <?php endif; ?>
    </div>
  </section>

  <!-- MAIN -->
  <div class="pr-main<?php echo empty($recent_promos) ? ' pr-main--full' : ''; ?>">

    <!-- LEFT: article -->
    <article class="pr-content">
      <h1 class="pr-content__title"><?php echo esc_html($title); ?></h1>

      <?php if ($end_ts) : ?>
        <div class="pr-status">
          <?php if ($is_ongoing) : ?>
            <span class="pr-pill">Ongoing</span>
          <?php else : ?>
            <span class="pr-pill pr-pill--expired">Ended</span>
          <?php endif; ?>
          <span class="pr-pill"><?php echo esc_html($end_formatted); ?></span>
        </div>
      <?php endif; ?>

      <div class="pr-content__body">
        <?php echo apply_filters('the_content', $content); ?>
      </div>

      <div class="pr-cta-wrap">
        <a href="<?php echo esc_url($deposit_url); ?>" class="pr-cta-btn" target="_blank" rel="noopener">
          <?php echo esc_html($cta_label); ?>
        </a>
      </div>
    </article>

    <!-- RIGHT: recent promos -->
    <?php if (! empty($recent_promos)) : ?>
      <aside class="pr-side">
        <h2 class="pr-side__hd">Recent Promos</h2>
        <div class="pr-side__list">
          <?php foreach ($recent_promos as $rp) : ?>
            <a class="pr-rel" href="<?php echo esc_url($rp['permalink']); ?>">
              <?php if ($rp['thumb']) : ?>
                <img class="pr-rel__img" src="<?php echo esc_url($rp['thumb']); ?>" alt="<?php echo esc_attr($rp['title']); ?>" loading="lazy">
              <?php elseif ($rp_logo = solaire_site_logo_url()) : ?>
                <div class="pr-rel__img pr-rel__img--logo">
                  <img src="<?php echo esc_url($rp_logo); ?>" alt="<?php echo esc_attr($rp['title']); ?>" loading="lazy">
                </div>
              <?php else : ?>
                <div class="pr-rel__img"></div>
              <?php endif; ?>
              <div class="pr-rel__body">
                <h3 class="pr-rel__title"><?php echo esc_html($rp['title']); ?></h3>
                <?php if ($rp['excerpt']) : ?>
                  <p class="pr-rel__excerpt"><?php echo esc_html($rp['excerpt']); ?></p>
                <?php endif; ?>
                <span class="pr-rel__more">Read More
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6" />
                  </svg>
                </span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </aside>
    <?php endif; ?>

  </div>

  <!-- FEATURED GAMES (Swiper) -->
  <?php if (! empty($featured_games)) : ?>
    <section class="pr-fg" data-carousel>
      <div class="mb-3 flex items-center justify-between">
        <h2 class="font-display text-lg font-bold sm:text-xl">Featured Games</h2>
        <div class="flex items-center gap-2">
          <button data-prev aria-label="Previous" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-secondary transition hover:bg-white/20 disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-white/25 disabled:hover:bg-white/5"><?php echo solaire_icon('arrow-left', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
          <button data-next aria-label="Next" class="flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-secondary transition hover:bg-white/20 disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-white/25 disabled:hover:bg-white/5"><?php echo solaire_icon('arrow-right', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
        </div>
      </div>
      <div data-track class="game-row-track no-scrollbar snap-row flex gap-3 overflow-x-auto pb-2 pt-3 sm:gap-4">
        <?php foreach ($featured_games as $g) : ?>
          <?php echo solaire_game_card($g['id'], ['variant' => 'grid', 'class' => 'shrink-0']); // phpcs:ignore ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</div>

<?php get_footer(); ?>