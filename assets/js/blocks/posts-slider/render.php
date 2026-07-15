<?php
/**
 * Posts Slider — front-end render.
 *
 * Shows the featured image of each chosen post; clicking a slide links to that
 * single post. Works with the default `post` type or any public CPT (ACF etc.).
 */

$heading   = $attributes['heading'] ?? '';
$post_type = $attributes['postType'] ?? 'post';
$post_ids  = array_values( array_filter( array_map( 'intval', (array) ( $attributes['postIds'] ?? [] ) ) ) );

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

$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => 'posts-slider' ] );
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="posts-slider__header">
        <?php if ( $heading ) : ?>
            <h2 class="posts-slider__heading"><?php echo esc_html( $heading ); ?></h2>
        <?php endif; ?>

        <div class="posts-slider__nav">
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
                $permalink = get_permalink();
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
                <a class="posts-slider__slide" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
                    <?php if ( $thumb ) : ?>
                        <?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php else : ?>
                        <span class="posts-slider__image posts-slider__image--placeholder"><?php echo esc_html( $title ); ?></span>
                    <?php endif; ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php
wp_reset_postdata();
