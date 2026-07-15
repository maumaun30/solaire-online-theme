<?php
$section_title    = $attributes['sectionTitle'] ?? '';
$section_subtitle = $attributes['sectionSubtitle'] ?? '';
$section_tagline  = $attributes['sectionTagline'] ?? '';
$features         = $attributes['features'] ?? [];

if ( ! function_exists( 'mytheme_render_payment_logo' ) ) {
    function mytheme_render_payment_logo( int $media_id, string $media_url, string $alt = '' ): string {
        if ( ! $media_id && ! $media_url ) {
            return '';
        }

        if ( $media_id ) {
            $mime = get_post_mime_type( $media_id );

            if ( 'image/svg+xml' === $mime ) {
                $file_path = get_attached_file( $media_id );
                if ( $file_path && file_exists( $file_path ) ) {
                    $svg = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                    $svg = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg );
                    $svg = preg_replace( '/<!DOCTYPE[^>]*>/i', '', $svg );
                    $svg = preg_replace( '/<svg/', '<svg class="mytheme-payment-logo"', $svg, 1 );
                    return trim( $svg );
                }
            }

            $img = wp_get_attachment_image(
                $media_id,
                'medium',
                false,
                [
                    'class'   => 'mytheme-payment-logo',
                    'alt'     => esc_attr( $alt ),
                    'loading' => 'lazy',
                ]
            );
            if ( $img ) {
                return $img;
            }
        }

        if ( $media_url ) {
            return '<img src="' . esc_url( $media_url ) . '" alt="' . esc_attr( $alt ) . '" class="mytheme-payment-logo" loading="lazy" />';
        }

        return '';
    }
}
?>

<section <?php echo get_block_wrapper_attributes( [ 'class' => 'mytheme-payment-bank' ] ); ?>>
    <div class="mytheme-wcu__container">

        <?php if ( $section_title || $section_subtitle ) : ?>
            <div class="mytheme-wcu__heading">
                <?php if ( $section_title ) : ?>
                    <h2 class="mytheme-wcu__title"><?php echo wp_kses_post( $section_title ); ?></h2>
                <?php endif; ?>
                <?php if ( $section_subtitle ) : ?>
                    <p class="mytheme-wcu__subtitle"><?php echo wp_kses_post( $section_subtitle ); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $features ) ) :
            // Build the card markup once, then output the track twice so the
            // CSS marquee can loop seamlessly (the duplicate is hidden from AT).
            ob_start();
            foreach ( $features as $feature ) :
                $media_id  = intval( $feature['svgId'] ?? 0 );
                $media_url = esc_url( $feature['svgUrl'] ?? '' );
                $title     = esc_html( $feature['title'] ?? '' );
                $logo_html = mytheme_render_payment_logo( $media_id, $media_url, $title );
            ?>
                <div class="mytheme-wcu__card">

                    <!-- Icon bg shape with logo inside -->
                    <div class="mytheme-wcu__icon-bg">
                        <?php if ( $logo_html ) : ?>
                            <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php else : ?>
                            <div class="mytheme-wcu__logo-placeholder" aria-hidden="true"></div>
                        <?php endif; ?>
                    </div>

                    <!-- Label inside the card -->
                    <?php if ( $title ) : ?>
                        <span class="mytheme-wcu__card-label"><?php echo $title; ?></span>
                    <?php endif; ?>

                </div>
            <?php endforeach;
            $cards_html = ob_get_clean();

            // Keep a constant scroll speed regardless of item count
            // (~5s of travel per logo).
            $marquee_duration = max( 20, count( $features ) * 5 );
            ?>
            <div class="mytheme-wcu__marquee">
                <div class="mytheme-wcu__track" style="--wcu-marquee-duration: <?php echo esc_attr( $marquee_duration ); ?>s;">
                    <?php echo $cards_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php
                    // Aria-hidden duplicate keeps the loop seamless without
                    // exposing the logos twice to screen readers.
                    ?>
                    <div class="mytheme-wcu__track-dupe" aria-hidden="true">
                        <?php echo $cards_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $section_tagline ) : ?>
            <p class="mytheme-wcu__tagline"><?php echo wp_kses_post( $section_tagline ); ?></p>
        <?php endif; ?>

    </div>
</section>