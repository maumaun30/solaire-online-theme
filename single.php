<?php

/**
 * Template: single.php
 * Default single post template (post type: post)
 *
 * Layout:
 *  1. Hero banner       — featured image background w/ title overlay
 *  2. Main 2-col        — left: title, taxonomy chips, content, share | right: Related Blogs sidebar
 *  3. Featured Games    — 6 cards from CPT 'game' tagged 'Featured Games'
 *  4. CTA               — [fnlmx_cta] shortcode
 */

get_header();

if (! have_posts()) {
  echo '<p style="color:#fff;text-align:center;padding:4rem;">Post not found.</p>';
  get_footer();
  exit;
}
the_post();

$post_id   = get_the_ID();
$title     = get_the_title();
$content   = get_the_content();
$permalink = get_permalink();
$hero_img  = get_the_post_thumbnail_url($post_id, 'full');

$cats = get_the_category();
$tags = get_the_tags();

/* Related blog posts — same category, exclude current */
$related_posts = [];
if (! empty($cats)) {
  $cat_ids = wp_list_pluck($cats, 'term_id');
  $rq = new WP_Query([
    'post_type'      => 'post',
    'category__in'   => $cat_ids,
    'post__not_in'   => [$post_id],
    'posts_per_page' => 3,
    'orderby'        => 'modified',
    'order'          => 'DESC',
  ]);
  if ($rq->have_posts()) {
    while ($rq->have_posts()) {
      $rq->the_post();
      $related_posts[] = [
        'title'     => get_the_title(),
        'permalink' => get_permalink(),
        'thumb'     => get_the_post_thumbnail_url(get_the_ID(), 'medium_large'),
        'excerpt'   => wp_trim_words(get_the_excerpt(), 18, '…'),
      ];
    }
    wp_reset_postdata();
  }
}

/* Featured Games — CPT 'game' tagged "Featured Games"
   We don't know which tag-taxonomy the 'game' CPT uses (post_tag, game_tag,
   etc.), so we resolve it dynamically: find every non-hierarchical taxonomy
   attached to 'game' that contains a term whose NAME matches "Featured Games"
   (case-insensitive), then OR them together in the tax_query. */
$featured_games   = [];
$fg_term_ids_by_tax = [];

$game_taxes = get_object_taxonomies('game', 'objects');
foreach ($game_taxes as $tax) {
  // Tag-like taxonomies are non-hierarchical; skip categories like game_category.
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
    $tax_query[] = [
      'taxonomy' => $tax_name,
      'field'    => 'term_id',
      'terms'    => $term_id,
    ];
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

/* Admin-only debug hint — visible only to logged-in admins, helps if list is empty */
$fg_debug = '';
if (current_user_can('manage_options') && empty($featured_games)) {
  $tax_names = array_keys($game_taxes);
  $fg_debug = 'No Featured Games found. Taxonomies on "game": '
    . (empty($tax_names) ? '(none)' : implode(', ', $tax_names))
    . '. Tag-taxes with a "Featured Games" term: '
    . (empty($fg_term_ids_by_tax) ? '(none — create a term named exactly "Featured Games" and assign it to game posts)' : implode(', ', array_keys($fg_term_ids_by_tax)));
}

$share_url   = rawurlencode($permalink);
$share_title = rawurlencode($title);
?>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap');

  :root {
    /* Site palette (theme.json): orange accent, deep/surface/panel darks */
    --color-primary: #df6a2e;
    --color-amber: #f5b335;
    --bg-dark-1: #101214;
    --bg-dark-2: #232629;
    --bg-dark-3: #15171a;
    --bg-dark-4: rgb(35 38 41 / 50%);
    --border: rgba(255, 255, 255, .08);
    --border-strong: rgba(255, 255, 255, .15);
    --radius-lg: 1.25rem;
    --radius-md: .875rem;
    --section-py: clamp(2rem, 5vw, 4rem);
  }

  .sp-page {
    background: var(--bg-dark-3);
    min-height: 100vh;
    color: #fff;
    font-family: 'Montserrat', sans-serif;
  }

  /* ── BREADCRUMB ── */
  .sp-bc {
    max-width: 80rem;
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

  .sp-bc a {
    color: #fff;
    text-decoration: none;
    font-weight: 400;
    transition: color .2s;
  }

  .sp-bc a:hover {
    color: var(--color-primary);
  }

  .sp-bc svg {
    color: #fff;
    flex-shrink: 0;
  }

  .sp-bc__cur {
    color: var(--color-primary);
  }

  /* ── HERO BANNER ── */
  .sp-hero {
    max-width: 80rem;
    margin: .5rem auto 0;
    padding: 0 1.5rem;
  }

  .sp-hero__inner {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    height: 320px;
    display: flex;
    align-items: center;
    background:
      <?php if ($hero_img) : ?> linear-gradient(90deg, rgba(0, 0, 0, .55) 0%, rgba(0, 0, 0, .15) 70%),
      url('<?php echo esc_url($hero_img); ?>') center/cover no-repeat;
      <?php else : ?> linear-gradient(135deg, #2b2e31 0%, #232629 50%, #15171a 100%);
      <?php endif; ?>
  }

  /* Fallback when no featured image — site logo centered on a dark panel,
     matching single-game.php's fallback. */
  .sp-hero__logo {
    margin: 0 auto;
    width: 40%;
    max-width: 190px;
    object-fit: contain;
    opacity: .95;
  }

  .sp-hero__title {
    font-size: clamp(2.25rem, 6vw, 4.5rem);
    font-weight: 900;
    line-height: 1.02;
    text-transform: uppercase;
    color: #fff;
    margin: 0;
    padding: 2.5rem 2rem;
    max-width: 720px;
    text-shadow: 0 4px 28px rgba(0, 0, 0, .45);
    letter-spacing: -.01em;
    display: none;
  }

  /* ── MAIN GRID (content + sidebar) ── */
  .sp-main {
    max-width: 80rem;
    margin: 0 auto;
    padding: var(--section-py) 1.5rem 0;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 2rem;
  }

  /* No related blogs — content spans the full width (no empty 320px column
     where Related Blogs would be). */
  .sp-main--full {
    grid-template-columns: 1fr;
  }

  @media (max-width: 899px) {
    .sp-main {
      grid-template-columns: 1fr;
    }
  }

  /* ── CONTENT CARD ── */
  .sp-content {
    background: var(--bg-dark-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 2rem;
  }

  .sp-content__title {
    font-size: clamp(1.4rem, 2.6vw, 2rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 1rem;
    text-transform: uppercase;
    letter-spacing: -.01em;
    line-height: 1.2;
  }

  /* List markers — ul: primary bullet, ol: numbers */
  .sp-content__body ul {
    list-style: disc;
  }

  .sp-content__body ol {
    list-style: decimal;
  }

  .sp-content__body ul li::marker {
    color: var(--color-primary);
  }

  .sp-terms {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 1.5rem;
  }

  .sp-chip {
    display: inline-flex;
    align-items: center;
    padding: .35rem .85rem;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 400;
    color: #fff;
    background: var(--bg-dark-3);
    border: 1px solid var(--color-primary);
    cursor: default;
    user-select: none;
  }

  .sp-content__body {
    font-size: .95rem;
    line-height: 1.75;
    color: rgba(255, 255, 255, .78);
  }

  .sp-content__body p {
    margin: 0 0 1.1rem;
  }

  .sp-content__body h2,
  .sp-content__body h3 {
    color: #fff;
    margin: 2rem 0 .75rem;
    font-weight: 700;
  }

  .sp-content__body h2 {
    font-size: 1.25rem;
  }

  .sp-content__body h3 {
    font-size: 1.05rem;
  }

  .sp-content__body ul,
  .sp-content__body ol {
    margin: 0 0 1.1rem;
    padding-left: 1.5rem;
  }

  .sp-content__body li {
    margin-bottom: .35rem;
  }

  .sp-content__body a {
    color: var(--color-primary);
  }

  .sp-content__body img {
    max-width: 100%;
    height: auto;
    border-radius: .5rem;
    margin: 1rem 0;
  }

  /* ── SHARE ── */
  .sp-share {
    margin-top: 2rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--border);
  }

  .sp-share__label {
    font-size: .8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #fff;
    margin: 0 0 .75rem;
  }

  .sp-share__row {
    display: flex;
    gap: .6rem;
  }

  .sp-share__btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .06);
    border: 1px solid var(--border-strong);
    color: #fff;
    text-decoration: none;
    transition: background .2s, transform .2s;
  }

  .sp-share__btn:hover {
    background: var(--color-primary);
    transform: translateY(-2px);
  }

  a.sp-share__btn:hover {
    color: unset;
  }

  .sp-share__btn svg {
    width: 16px;
    height: 16px;
  }

  /* ── DATE ── */
  .sp-date {
    margin-top: 1.5rem;
    font-size: .8rem;
    font-weight: 500;
    color: rgba(255, 255, 255, .55);
    letter-spacing: .04em;
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .sp-date svg {
    width: 14px;
    height: 14px;
    color: var(--color-primary);
  }

  /* ── SIDEBAR ── */
  .sp-side__hd {
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #fff;
    margin: 0 0 1rem;
  }

  .sp-side__list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }

  .sp-rel {
    background: var(--bg-dark-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
    text-decoration: none;
    display: block;
    transition: transform .3s, border-color .3s;
  }

  .sp-rel:hover {
    transform: translateY(-4px);
  }

  .sp-rel__thumb {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
    display: block;
    background: var(--bg-dark-4);
    /*border-radius: 8px;*/
  }

  /* Fallback thumb — site logo centered on a dark panel (matches single-game.php). */
  .sp-rel__thumb--logo {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2b2e31 0%, #232629 50%, #15171a 100%);
  }

  .sp-rel__thumb--logo img {
    width: 45%;
    max-width: 120px;
    object-fit: contain;
    opacity: .95;
  }

  .sp-rel__body {
    padding: 1.1rem 1.1rem 1.25rem;
  }

  .sp-rel__title {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.3;
    color: #fff;
    margin: 0 0 .55rem;
  }

  .sp-rel__excerpt {
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

  .sp-rel__more {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--color-primary);
  }

  /* ── FEATURED GAMES (SWIPER) ── */
  .sp-fg {
    max-width: 80rem;
    margin: 0 auto;
    padding: var(--section-py) 1.5rem;
  }

  .sp-fg__hd-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
  }

  .sp-fg__hd {
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #fff;
    margin: 0;
  }

  .sp-fg__nav {
    display: flex;
    gap: .5rem;
  }

  .sp-fg__btn {
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

  .sp-fg__btn:hover {
    background: var(--color-primary);
    border-color: var(--color-primary);
  }

  .sp-fg__btn:disabled,
  .sp-fg__btn.swiper-button-disabled {
    opacity: .35;
    cursor: not-allowed;
  }

  .sp-fg__btn svg {
    width: 16px;
    height: 16px;
  }

  .sp-fg .swiper {
    width: 100%;
    padding-bottom: .5rem;
  }

  .sp-fg .swiper-slide {
    height: auto;
  }

  .sp-fg__card {
    border-radius: var(--radius-md);
    aspect-ratio: 111 / 140;
    overflow: hidden;
    background: var(--bg-dark-4);
    border: 1px solid var(--border);
    text-decoration: none;
    display: block;
    transition: transform .35s, border-color .35s;
  }

  .sp-fg__card:hover {
    transform: translateY(-4px);
  }

  /* Toast for copy-link feedback */
  .sp-toast {
    position: fixed;
    left: 50%;
    bottom: 2rem;
    transform: translateX(-50%) translateY(20px);
    background: var(--bg-dark-2);
    color: #fff;
    border: 1px solid var(--border-strong);
    padding: .65rem 1rem;
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 600;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s, transform .25s;
    z-index: 9999;
  }

  .sp-toast.is-shown {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }

  .sp-fg__card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  /* CTA spacing */
  .sp-cta-wrap {
    margin-top: var(--section-py);
  }

  @media(max-width: 600px) {
    .sp-hero__inner {
      height: 150px;
    }

    .sp-content__title {
      text-align: center;
    }

    .sp-terms {
      justify-content: center;
    }
  }
</style>

<div class="sp-page">

  <!-- BREADCRUMB -->
  <nav class="sp-bc" aria-label="Breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
    <svg viewBox="0 0 6 10" width="6" height="10" aria-hidden="true">
      <path d="M3.818 5L0 1.111 1.091 0 6 5l-4.909 5L0 8.889 3.818 5z" fill="currentColor" />
    </svg>
    <a href="<?php echo esc_url(home_url('/blog/')); ?>">Blogs</a>
    <svg viewBox="0 0 6 10" width="6" height="10" aria-hidden="true">
      <path d="M3.818 5L0 1.111 1.091 0 6 5l-4.909 5L0 8.889 3.818 5z" fill="currentColor" />
    </svg>
    <span class="sp-bc__cur"><?php echo esc_html($title); ?></span>
  </nav>

  <!-- HERO BANNER -->
  <section class="sp-hero">
    <div class="sp-hero__inner">
      <?php if (! $hero_img && ($sp_logo = solaire_site_logo_url())) : ?>
        <img class="sp-hero__logo" src="<?php echo esc_url($sp_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" />
      <?php endif; ?>
      <h1 class="sp-hero__title"><?php echo esc_html($title); ?></h1>
    </div>
  </section>

  <!-- MAIN: content + sidebar -->
  <div class="sp-main<?php echo empty($related_posts) ? ' sp-main--full' : ''; ?>">

    <!-- LEFT: article -->
    <article class="sp-content">
      <h2 class="sp-content__title"><?php echo esc_html($title); ?></h2>

      <?php if ($cats || $tags) : ?>
        <div class="sp-terms">
          <?php if ($cats) : foreach ($cats as $c) : ?>
              <span class="sp-chip"><?php echo esc_html($c->name); ?></span>
          <?php endforeach;
          endif; ?>
          <?php if ($tags) : foreach ($tags as $t) : ?>
              <span class="sp-chip">#<?php echo esc_html($t->name); ?></span>
          <?php endforeach;
          endif; ?>
        </div>
      <?php endif; ?>

      <div class="sp-content__body">
        <?php echo apply_filters('the_content', $content); ?>
      </div>

      <!-- PUBLISHED DATE -->
      <div class="sp-date">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="3" y="4" width="18" height="18" rx="2" />
          <line x1="16" y1="2" x2="16" y2="6" />
          <line x1="8" y1="2" x2="8" y2="6" />
          <line x1="3" y1="10" x2="21" y2="10" />
        </svg>
        <span>Published <?php echo esc_html(get_the_date('F j, Y')); ?></span>
      </div>

      <!-- SHARE -->
      <div class="sp-share">
        <p class="sp-share__label">Share</p>
        <div class="sp-share__row">
          <a class="sp-share__btn" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener" aria-label="Share on Facebook">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.3-1.5 1.6-1.5h1.6V3.6c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.4H8v3.1h2.6V21h2.9z" />
            </svg>
          </a>
          <a class="sp-share__btn" href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener" aria-label="Share on X">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M18.244 2H21.5l-7.5 8.57L22.75 22H16.1l-5.2-6.79L4.94 22H1.68l8.02-9.17L1.25 2H8.06l4.71 6.23L18.24 2zm-2.34 18h1.81L8.18 4H6.25l9.66 16z" />
            </svg>
          </a>
          <a class="sp-share__btn" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url; ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.22 8h4.56V22H.22V8zm7.18 0h4.37v1.92h.06c.61-1.15 2.1-2.36 4.33-2.36 4.63 0 5.49 3.05 5.49 7.01V22H17.1v-6.13c0-1.46-.03-3.34-2.04-3.34-2.04 0-2.35 1.59-2.35 3.24V22H8.4V8z" />
            </svg>
          </a>
          <button type="button" class="sp-share__btn js-copy-link"
            data-url="<?php echo esc_attr($permalink); ?>"
            aria-label="Copy link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 13a5 5 0 0 0 7.07 0l3-3a5 5 0 0 0-7.07-7.07l-1.5 1.5" />
              <path d="M14 11a5 5 0 0 0-7.07 0l-3 3a5 5 0 0 0 7.07 7.07l1.5-1.5" />
            </svg>
          </button>
        </div>
      </div>
    </article>

    <!-- RIGHT: sidebar -->
    <?php if (! empty($related_posts)) : ?>
      <aside class="sp-side">
        <h2 class="sp-side__hd">Related Blogs</h2>
        <div class="sp-side__list">
          <?php foreach ($related_posts as $rp) : ?>
            <a class="sp-rel" href="<?php echo esc_url($rp['permalink']); ?>">
              <?php if ($rp['thumb']) : ?>
                <img class="sp-rel__thumb" src="<?php echo esc_url($rp['thumb']); ?>" alt="<?php echo esc_attr($rp['title']); ?>" loading="lazy">
              <?php elseif ($rp_logo = solaire_site_logo_url()) : ?>
                <div class="sp-rel__thumb sp-rel__thumb--logo">
                  <img src="<?php echo esc_url($rp_logo); ?>" alt="<?php echo esc_attr($rp['title']); ?>" loading="lazy">
                </div>
              <?php else : ?>
                <div class="sp-rel__thumb"></div>
              <?php endif; ?>
              <div class="sp-rel__body">
                <h3 class="sp-rel__title"><?php echo esc_html($rp['title']); ?></h3>
                <?php if ($rp['excerpt']) : ?>
                  <p class="sp-rel__excerpt"><?php echo esc_html($rp['excerpt']); ?></p>
                <?php endif; ?>
                <span class="sp-rel__more">Read More
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

  <!-- FEATURED GAMES -->
  <?php if (! empty($featured_games)) : ?>
    <section class="sp-fg" data-carousel>
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

  <!-- Toast for copy-link feedback -->
  <div class="sp-toast" id="sp-toast" role="status" aria-live="polite">Link copied!</div>

  <script>
    (function() {
      'use strict';

      /* Featured Games Swiper is initialized by assets/js/single-featured-games.js,
         enqueued in functions.php with `swiper` as a dependency. */

      /* Copy-link button */
      var toast = document.getElementById('sp-toast');

      function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add('is-shown');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(function() {
          toast.classList.remove('is-shown');
        }, 1800);
      }
      document.querySelectorAll('.js-copy-link').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var url = this.dataset.url || window.location.href;
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(
              function() {
                showToast('Link copied!');
              },
              function() {
                showToast('Copy failed');
              }
            );
          } else {
            var ta = document.createElement('textarea');
            ta.value = url;
            document.body.appendChild(ta);
            ta.select();
            try {
              document.execCommand('copy');
              showToast('Link copied!');
            } catch (e) {
              showToast('Copy failed');
            }
            document.body.removeChild(ta);
          }
        });
      });
    })();
  </script>

</div>

<?php get_footer(); ?>