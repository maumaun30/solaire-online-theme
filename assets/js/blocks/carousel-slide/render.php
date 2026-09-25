<?php
$image_url            = $attributes['imageUrl'] ?? '';
$image_id             = $attributes['imageId'] ?? 0;
$image_alt            = $attributes['imageAlt'] ?? '';
$title                = $attributes['title'] ?? '';
$title_highlight = $attributes['titleHighlight'] ?? '';
$title_end            = $attributes['titleEnd'] ?? '';

// SEO: only the first slide rendered on the page is an <h1>; every following
// slide is an <h2>, so the page never has more than one H1.
global $mytheme_carousel_slide_index;
$mytheme_carousel_slide_index = isset( $mytheme_carousel_slide_index ) ? $mytheme_carousel_slide_index + 1 : 1;
$title_tag = ( 1 === $mytheme_carousel_slide_index ) ? 'h1' : 'h2';
$subtitle             = $attributes['subtitle'] ?? '';
$primary_btn_text     = $attributes['primaryButtonText'] ?? '';
$show_primary_btn     = $attributes['showPrimaryButton'] ?? true;
$secondary_btn_text   = $attributes['secondaryButtonText'] ?? '';
$secondary_btn_url    = $attributes['secondaryButtonUrl'] ?? '';
$overlay_opacity      = $attributes['overlayOpacity'] ?? 85;

// Secondary button: fall back to scrolling to the games listing section when
// no URL (or just the placeholder "#") is set in the block settings.
if ( '' === trim( (string) $secondary_btn_url ) || '#' === $secondary_btn_url ) {
    $secondary_btn_url = '#games-listing';
}

$overlay_full  = round( $overlay_opacity / 100, 2 );
$overlay_light = round( $overlay_full * 0.6, 2 );

?>

<div <?php echo get_block_wrapper_attributes(['class' => 'swiper-slide mytheme-carousel-slide']); ?>>

  <?php if ( $image_id || $image_url ) : ?>
    <?php
    // The background is a real <img> (not a CSS background-image) so the browser
    // can serve responsive srcset sizes and lazy-load the images. object-fit:
    // cover in the CSS makes it read like a background. The carousel sits below
    // the hero-banner section (not the page's LCP), so every slide lazy-loads.
    $img_attr = [
        'class'    => 'mytheme-carousel-slide__bg',
        'alt'      => $image_alt,
        'sizes'    => '100vw',
        'decoding' => 'async',
        'loading'  => 'lazy',
    ];
    if ( $image_id ) {
        // Responsive srcset/sizes + dimensions straight from the media library.
        echo wp_get_attachment_image( (int) $image_id, 'full', false, $img_attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    } else {
        printf(
            '<img class="mytheme-carousel-slide__bg" src="%1$s" alt="%2$s" sizes="100vw" decoding="async" loading="lazy" />',
            esc_url( $image_url ),
            esc_attr( $image_alt )
        );
    }
    ?>
  <?php endif; ?>

  <div
    class="mytheme-carousel-slide__overlay"
    style="background: linear-gradient(to right, rgba(10,10,11,<?php echo $overlay_full; ?>), rgba(10,10,11,<?php echo $overlay_light; ?>));"
    aria-hidden="true"
  ></div>

  <div class="mytheme-carousel-slide__content">
    <div class="mytheme-carousel-slide__inner">

      <?php if ( $title || $title_highlight || $title_end ) : ?>
  <<?php echo $title_tag; ?> class="mytheme-carousel-slide__title">

    <?php if ( $title ) : ?>
      <span>
        <?php echo wp_kses_post( $title ); ?>
      </span>
    <?php endif; ?>

    <?php if ( $title_highlight ) : ?>
      <span class="highlight-text">
        <?php echo wp_kses_post( $title_highlight ); ?>
      </span>
    <?php endif; ?>

    <?php if ( $title_end ) : ?>
      <span class="mytheme-carousel-slide__title-end">
        <?php echo wp_kses_post( $title_end ); ?>
      </span>
    <?php endif; ?>

  </<?php echo $title_tag; ?>>
<?php endif; ?>

      <?php if ( $subtitle ) : ?>
        <p class="mytheme-carousel-slide__subtitle">
          <?php echo wp_kses_post( $subtitle ); ?>
        </p>
      <?php endif; ?>

      <?php if ( ( $show_primary_btn && $primary_btn_text ) || $secondary_btn_text ) : ?>
        <div class="mytheme-carousel-slide__buttons">
          <?php if ( $show_primary_btn && $primary_btn_text ) : ?>
            <?php // Opens the login modal (.so-open-register); falls back to a
                  // link to solaireonline.com when the modal is disabled. ?>
            <?php if ( ! function_exists( 'solaire_login_modal_enabled' ) || solaire_login_modal_enabled() ) : ?>
              <button
                type="button"
                class="so-open-register mytheme-carousel-slide__btn mytheme-carousel-slide__btn--primary"
              >
                <span class="mytheme-carousel-slide__btn-label"><?php echo esc_html( $primary_btn_text ); ?></span>
              </button>
            <?php else : ?>
              <a
                href="<?php echo esc_url( 'https://www.solaireonline.com/en' ); ?>"
                class="mytheme-carousel-slide__btn mytheme-carousel-slide__btn--primary"
              >
                <span class="mytheme-carousel-slide__btn-label"><?php echo esc_html( $primary_btn_text ); ?></span>
              </a>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ( $secondary_btn_text ) : ?>
            <a
              href="<?php echo esc_url( $secondary_btn_url ); ?>"
              class="mytheme-carousel-slide__btn mytheme-carousel-slide__btn--secondary"
            >
              <span class="mytheme-carousel-slide__btn-label"><?php echo esc_html( $secondary_btn_text ); ?></span>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>