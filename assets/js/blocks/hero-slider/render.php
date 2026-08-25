<?php
/**
 * Solaire Hero Slider — render.php
 * Full-width image slider built from the featured images of a chosen post type.
 * Breadcrumb only (Home / Page Title), dot nav only, no arrows.
 * Front-end JS ships via the block's viewScript (view.js).
 */

$post_type       = $attributes['selectedPostType'] ?? 'post';
$slide_count     = intval($attributes['slideCount'] ?? 5);
$breadcrumb_lbl  = $attributes['breadcrumbLabel'] ?? '';
$autoplay_delay  = intval($attributes['autoplayDelay'] ?? 5000);
$slider_height   = esc_attr($attributes['sliderHeight'] ?? '420px');

// Dynamic breadcrumb label — falls back to whatever is being viewed.
if (empty($breadcrumb_lbl)) {
    $queried = get_queried_object();

    if ($queried instanceof WP_Post) {
        $breadcrumb_lbl = get_the_title($queried);
    } elseif ($queried instanceof WP_Term) {
        $breadcrumb_lbl = $queried->name;
    } elseif ($queried instanceof WP_Post_Type) {
        $breadcrumb_lbl = $queried->labels->name;
    } elseif (is_home() || is_front_page()) {
        $breadcrumb_lbl = get_bloginfo('name');
    } else {
        $breadcrumb_lbl = get_the_archive_title();
    }
}
$breadcrumb_lbl = wp_strip_all_tags($breadcrumb_lbl);

// Slides
$slides = [];
if ($post_type && get_post_type_object($post_type)) {
    $q = new WP_Query([
        'post_type'              => $post_type,
        'posts_per_page'         => $slide_count,
        'post_status'            => 'publish',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'orderby'                => 'date',
        'order'                  => 'DESC',
    ]);

    foreach ($q->posts as $p) {
        // Posts without a featured image still get a slide — the branded
        // logo panel stands in for the artwork.
        $slides[] = [
            'url'   => get_the_post_thumbnail_url($p->ID, 'full') ?: '',
            'title' => get_the_title($p),
        ];
    }
    wp_reset_postdata();
}

// Shared by every slide that has no featured image.
$site_logo = function_exists('solaire_site_logo_url') ? solaire_site_logo_url() : '';

if (empty($slides)) {
    echo '<div class="wp-block-solaire-hero-slider solaire-hero-slider solaire-hero-slider--empty">'
        . '<p>' . sprintf(
            /* translators: %s: post type slug */
            esc_html__('No published posts found for post type: %s', 'solaire'),
            '<strong>' . esc_html($post_type) . '</strong>'
        ) . '</p></div>';
    return;
}

$wrapper_attrs = get_block_wrapper_attributes([
    'class'                => 'solaire-hero-slider',
    'data-autoplay'        => $autoplay_delay,
    'aria-roledescription' => 'carousel',
    'aria-label'           => esc_attr($breadcrumb_lbl . ' slideshow'),
    'style'                => "--slider-height:{$slider_height};",
]);
?>

<div <?php echo $wrapper_attrs; ?>>

    <div class="solaire-hero-slider__track" aria-live="off">
        <?php foreach ($slides as $i => $slide) : ?>
        <div
            class="solaire-hero-slider__slide<?php echo $i === 0 ? ' is-active' : ''; ?>"
            aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>"
            role="group"
            aria-roledescription="slide"
            aria-label="<?php echo esc_attr(($i + 1) . ' of ' . count($slides)); ?>"
        >
            <?php if ($slide['url']) : ?>
                <img
                    class="solaire-hero-slider__img"
                    src="<?php echo esc_url($slide['url']); ?>"
                    alt="<?php echo esc_attr($slide['title']); ?>"
                    <?php echo $i === 0 ? '' : 'loading="lazy"'; ?>
                    decoding="async"
                />
            <?php else : ?>
                <?php /* No featured image — site logo on a dark panel, the same
                         fallback single.php and the archive cards use. */ ?>
                <div class="solaire-hero-slider__fallback">
                    <?php if ($site_logo) : ?>
                        <img
                            class="solaire-hero-slider__fallback-logo"
                            src="<?php echo esc_url($site_logo); ?>"
                            alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                            <?php echo $i === 0 ? '' : 'loading="lazy"'; ?>
                            decoding="async"
                        />
                    <?php else : ?>
                        <span class="solaire-hero-slider__wordmark">
                            <span class="solaire-hero-slider__wordmark-main">SOLAIRE</span>
                            <span class="solaire-hero-slider__wordmark-sub">ONLINE</span>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="solaire-hero-slider__nav-container">
        <nav class="solaire-hero-slider__breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'solaire'); ?>">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="breadcrumb-home">
                <?php esc_html_e('Home', 'solaire'); ?>
            </a>
            <svg class="breadcrumb-sep" viewBox="0 0 6 10" width="6" height="10" aria-hidden="true">
                <path d="M3.818 5L0 1.111 1.091 0 6 5l-4.909 5L0 8.889 3.818 5z" fill="currentColor" />
            </svg>
            <span class="breadcrumb-current"><?php echo esc_html($breadcrumb_lbl); ?></span>
        </nav>
    </div>

    <div class="solaire-hero-slider__edge" aria-hidden="true"></div>

    <?php if (count($slides) > 1) : ?>
    <div class="solaire-hero-slider__dots" role="tablist" aria-label="<?php esc_attr_e('Slide navigation', 'solaire'); ?>">
        <?php foreach ($slides as $i => $slide) : ?>
        <button
            class="solaire-hero-slider__dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
            role="tab"
            aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
            aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'solaire'), $i + 1)); ?>"
            data-index="<?php echo $i; ?>"
        ></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
