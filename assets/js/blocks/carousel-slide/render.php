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

// Unique suffix so each slide's SVG clipPath ids don't collide on the page.
$slide_uid = uniqid();

if ( ! function_exists( 'mytheme_carousel_btn_shape' ) ) {
    function mytheme_carousel_btn_shape( $uid ) { ?>
      <svg aria-hidden="true" class="mytheme-carousel-slide__btn-shape" viewBox="0 0 148 42" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g clip-path="url(#mytheme-carousel-btn-<?php echo esc_attr( $uid ); ?>)">
          <path d="M148 30.4 L136.4 42 H0 V7 L7 0 H148 V30.4 Z" fill="currentColor"></path>
          <path d="M148 34 V42 H140 L148 34 Z" fill="var(--decoration, currentColor)"></path>
        </g>
        <defs>
          <clipPath id="mytheme-carousel-btn-<?php echo esc_attr( $uid ); ?>">
            <rect width="148" height="42" fill="white"></rect>
          </clipPath>
        </defs>
      </svg>
    <?php }
}
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
            <button
              type="button"
              class="mytheme-carousel-slide__btn mytheme-carousel-slide__btn--primary"
              id="fnlmx-rg-proceed"
            >
              <?php mytheme_carousel_btn_shape( 'primary-' . $slide_uid ); ?>
              <span class="mytheme-carousel-slide__btn-label"><?php echo esc_html( $primary_btn_text ); ?></span>
            </button>
          <?php endif; ?>

          <?php if ( $secondary_btn_text ) : ?>
            <a
              href="<?php echo esc_url( $secondary_btn_url ); ?>"
              class="mytheme-carousel-slide__btn mytheme-carousel-slide__btn--secondary"
            >
              <?php mytheme_carousel_btn_shape( 'secondary-' . $slide_uid ); ?>
              <span class="mytheme-carousel-slide__btn-label"><?php echo esc_html( $secondary_btn_text ); ?></span>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>