<?php
/**
 * Solaire Post Archive — helper functions.
 *
 * Required from functions.php. Holds the post-type resolver, the shared card
 * template (used by both render.php and the AJAX handler) and the Load More
 * AJAX endpoint.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Resolve a REST base (e.g. "posts") to a real post type slug (e.g. "post").
 * The editor stores REST bases because that is what the REST routes use.
 */
if (!function_exists('solaire_resolve_post_type')) {
    function solaire_resolve_post_type(string $rest_or_slug): string
    {
        if (get_post_type_object($rest_or_slug)) {
            return $rest_or_slug;
        }

        foreach (get_post_types(['show_in_rest' => true], 'objects') as $type) {
            $rest_base = !empty($type->rest_base) ? $type->rest_base : $type->name;
            if ($rest_base === $rest_or_slug) {
                return $type->name;
            }
        }

        return 'post';
    }
}

/**
 * One archive card. Shared by the initial render and the Load More response so
 * appended cards are identical to the ones already on the page.
 */
if (!function_exists('solaire_archive_card_template')) {
    function solaire_archive_card_template(int $post_id): void
    {
        $thumb_url = get_the_post_thumbnail_url($post_id, 'large');
        $title     = get_the_title($post_id);
        $excerpt   = wp_trim_words(get_the_excerpt($post_id), 18, '…');
        $link      = get_permalink($post_id);
        ?>
        <article class="solaire-archive-card">
            <div class="solaire-archive-card__thumb">
                <?php if ($thumb_url) : ?>
                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" />
                <?php else : ?>
                    <?php /* No featured image — site logo on a dark panel, the same
                             fallback single.php uses for its hero and related cards. */ ?>
                    <div class="solaire-archive-card__thumb-placeholder">
                        <?php $logo = function_exists('solaire_site_logo_url') ? solaire_site_logo_url() : ''; ?>
                        <?php if ($logo) : ?>
                            <img
                                class="solaire-archive-card__thumb-logo"
                                src="<?php echo esc_url($logo); ?>"
                                alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                                loading="lazy"
                            />
                        <?php else : ?>
                            <span class="solaire-archive-card__wordmark">
                                <span class="solaire-archive-card__wordmark-main">SOLAIRE</span>
                                <span class="solaire-archive-card__wordmark-sub">ONLINE</span>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="solaire-archive-card__overlay"></div>
            </div>

            <div class="solaire-archive-card__content">
                <h3 class="solaire-archive-card__title"><?php echo esc_html($title); ?></h3>
                <?php if ($excerpt) : ?>
                    <p class="solaire-archive-card__excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
                <a href="<?php echo esc_url($link); ?>" class="solaire-archive-card__btn">
                    <?php esc_html_e('Read More', 'solaire'); ?>
                </a>
            </div>
        </article>
        <?php
    }
}

/**
 * AJAX: append the next page of cards.
 */
if (!function_exists('solaire_ajax_load_more_posts')) {
    function solaire_ajax_load_more_posts(): void
    {
        check_ajax_referer('solaire_archive_load_more', 'nonce');

        $post_type = solaire_resolve_post_type(sanitize_key($_POST['post_type'] ?? 'post'));
        $per_page  = min(48, max(1, (int) ($_POST['per_page'] ?? 4)));
        $page      = max(1, (int) ($_POST['page'] ?? 2));

        $query = new WP_Query([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
        ]);

        if (!$query->have_posts()) {
            wp_send_json_success(['html' => '', 'has_more' => false]);
        }

        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            solaire_archive_card_template(get_the_ID());
        }
        wp_reset_postdata();
        $html = ob_get_clean();

        $has_more = $page < (int) $query->max_num_pages;

        wp_send_json_success(compact('html', 'has_more'));
    }

    add_action('wp_ajax_solaire_archive_load_more', 'solaire_ajax_load_more_posts');
    add_action('wp_ajax_nopriv_solaire_archive_load_more', 'solaire_ajax_load_more_posts');
}
