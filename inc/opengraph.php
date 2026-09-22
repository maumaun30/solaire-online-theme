<?php
/**
 * Open Graph fixes on top of Yoast.
 *
 * Yoast only emits og:image when the view has one of its own (a featured
 * image, or an image set in the Yoast social tab). Game archives
 * (game_category / provider) never do, and any game without artwork doesn't
 * either, so those pages shipped og tags without og:image and were flagged as
 * incomplete Open Graph. Fall back to the game artwork, then the site-wide
 * share image used by the Organization schema node.
 */

function solaire_default_og_image()
{
    $details = solaire_organization_details();
    return $details['image'] ?? '';
}

add_action('wpseo_add_opengraph_additional_images', function ($image_container) {
    if ($image_container->has_images()) {
        return;
    }

    if (is_singular() && has_post_thumbnail()) {
        $image_container->add_image_by_id(get_post_thumbnail_id());
        return;
    }

    $fallback = solaire_default_og_image();
    if ($fallback) {
        $image_container->add_image_by_url($fallback);
    }
});
