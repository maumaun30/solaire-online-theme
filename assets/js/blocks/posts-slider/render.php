<?php
/**
 * Posts Slider — front-end render.
 *
 * Shows the featured image of each chosen post. Slides are currently
 * non-interactive — the link through to the single post is commented out in the
 * loop below. Works with the default `post` type or any public CPT (ACF etc.).
 */

$heading   = $attributes['heading'] ?? '';
$post_type = $attributes['postType'] ?? 'post';
$post_ids  = array_values( array_filter( array_map( 'intval', (array) ( $attributes['postIds'] ?? [] ) ) ) );

// Cards per view, per breakpoint. Clamped to 1–3 so a bad attribute value can
// never produce a track the slide maths cannot lay out.
$clamp_per = static function ( $value, $fallback = 1 ) {
    $value = (int) $value;
    return $value >= 1 && $value <= 3 ? $value : $fallback;
};
$per_desktop = $clamp_per( $attributes['slidesPerView'] ?? 1 );
$per_tablet  = $clamp_per( $attributes['slidesPerViewTablet'] ?? 1 );
$per_mobile  = $clamp_per( $attributes['slidesPerViewMobile'] ?? 1 );
$slide_gap   = max( 0, min( 48, (int) ( $attributes['slideGap'] ?? 16 ) ) );

// Card proportions follow the card count. A single card is a full-width banner
// (16/5); once two or three share the row each one is far narrower, and holding
// 16/5 would squash them into thin letterbox strips — so the cards get taller
// as they get narrower.
$ratios     = [ 1 => '16 / 5', 2 => '16 / 7', 3 => '16 / 9' ];
$ratio_for  = static function ( $per ) use ( $ratios ) {
    return $ratios[ $per ] ?? '16 / 5';
};

// "View All" button, mirroring the game-row block: shown only when the editor
// turned it on AND gave it a URL, so it never renders as a dead link.
$show_view_all = ! empty( $attributes['showViewAll'] );
$view_all_text = trim( (string) ( $attributes['viewAllText'] ?? '' ) );
$view_all_url  = trim( (string) ( $attributes['viewAllUrl'] ?? '' ) );
if ( '' === $view_all_text ) {
    $view_all_text = __( 'View All', 'solaire' );
}
$show_view_all = $show_view_all && '' !== $view_all_url;

// Nothing chosen yet — render only a hint for logged-in editors.
if ( empty( $post_ids ) ) {
    if ( current_user_can( 'edit_posts' ) ) {
        echo '<section ' . get_block_wrapper_attributes( [ 'class' => 'posts-slider' ] ) . '>';
        echo '<p style="opacity:.6;text-align:center;padding:24px;">' . esc_html__( 'Posts Slider: select posts in the block settings.', 'solaire' ) . '</p>';
        echo '</section>';
    }
    return;
}

// Fetch the selected posts, preserving the editor's chosen order.
$query = new WP_Query( [
    'post_type'           => $post_type,
    'post__in'            => $post_ids,
    'orderby'             => 'post__in',
    'posts_per_page'      => count( $post_ids ),
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
] );

if ( ! $query->have_posts() ) {
    wp_reset_postdata();
    return;
}

// The per-breakpoint counts ride along as custom properties: style.css picks
// the right one per media query, and view.js reads the resolved value back so
// the arrows step by however many cards are actually visible.
$wrapper_attributes = get_block_wrapper_attributes( [
    'class' => 'posts-slider',
    'style' => sprintf(
        '--ps-per:%d;--ps-per-tablet:%d;--ps-per-mobile:%d;--ps-gap:%dpx;'
            . '--ps-ratio-desktop:%s;--ps-ratio-tablet:%s;--ps-ratio-mobile:%s;',
        $per_desktop,
        $per_tablet,
        $per_mobile,
        $slide_gap,
        $ratio_for( $per_desktop ),
        $ratio_for( $per_tablet ),
        $ratio_for( $per_mobile )
    ),
] );
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="posts-slider__header">
        <?php if ( $heading ) : ?>
            <h2 class="posts-slider__heading"><?php echo esc_html( $heading ); ?></h2>
        <?php endif; ?>

        <div class="posts-slider__nav">
            <?php if ( $show_view_all ) : ?>
                <a class="posts-slider__viewall" href="<?php echo esc_url( $view_all_url ); ?>"><?php echo esc_html( $view_all_text ); ?></a>
            <?php endif; ?>
            <button type="button" class="posts-slider__arrow" data-dir="prev" aria-label="<?php esc_attr_e( 'Previous slide', 'solaire' ); ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="m15 5-7 7 7 7"/></svg>
            </button>
            <button type="button" class="posts-slider__arrow" data-dir="next" aria-label="<?php esc_attr_e( 'Next slide', 'solaire' ); ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    <div class="posts-slider__viewport">
        <div class="posts-slider__track">
            <?php
            while ( $query->have_posts() ) :
                $query->the_post();
                $permalink = get_permalink(); // Used by the commented-out link markup below.
                $title     = get_the_title();
                $thumb     = get_the_post_thumbnail(
                    get_the_ID(),
                    'large',
                    [
                        'class'   => 'posts-slider__image',
                        'alt'     => esc_attr( $title ),
                        'loading' => 'lazy',
                    ]
                );
                ?>
                <?php
                // Slides are non-interactive for now — the link to the single
                // post is commented out below. Restore the <a> version and drop
                // the <div> to make the slides clickable again.
                ?>
                <?php /*
                <a class="posts-slider__slide" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
                    <?php if ( $thumb ) : ?>
                        <?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php else : ?>
                        <span class="posts-slider__image posts-slider__image--placeholder"><?php echo esc_html( $title ); ?></span>
                    <?php endif; ?>
                </a>
                */ ?>
                <div class="posts-slider__slide">
                    <?php if ( $thumb ) : ?>
                        <?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php else : ?>
                        <span class="posts-slider__image posts-slider__image--placeholder"><?php echo esc_html( $title ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php
wp_reset_postdata();
