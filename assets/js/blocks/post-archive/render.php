<?php
/**
 * Solaire Post Archive — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

// Safety net: the theme loads this on `init`, but if that ever does not happen
// (e.g. an older functions.php on the server) render the block instead of
// fataling on an undefined function.
if (!function_exists('solaire_resolve_post_type')) {
    require_once __DIR__ . '/functions-helpers.php';
}

$section_label  = $attributes['sectionLabel'] ?? 'All Blogs';
$post_type_raw  = $attributes['postType'] ?? 'posts';
$posts_per_page = (int) ($attributes['postsPerPage'] ?? 4);
$show_load_more = (bool) ($attributes['showLoadMore'] ?? true);
$columns        = max(1, min(4, (int) ($attributes['columns'] ?? 2)));

// The editor stores a REST base; the query needs the post type slug.
$resolved_type = solaire_resolve_post_type($post_type_raw);

$query = new WP_Query([
    'post_type'      => $resolved_type,
    'post_status'    => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged'          => 1,
]);

$has_more = (int) $query->found_posts > $posts_per_page;
$block_id = 'solaire-archive-' . wp_unique_id();

$wrapper = get_block_wrapper_attributes([
    'class'             => 'solaire-post-archive',
    'data-block-id'     => $block_id,
    'data-post-type'    => esc_attr($resolved_type),
    'data-per-page'     => $posts_per_page,
    'data-current-page' => '1',
]);
?>

<div <?php echo $wrapper; ?>>
  <div class="solaire-post-archive__container">

    <?php if ($section_label) : ?>
        <p class="solaire-post-archive__label"><?php echo esc_html($section_label); ?></p>
    <?php endif; ?>

    <div
        class="solaire-post-archive__grid"
        style="--archive-cols: <?php echo $columns; ?>;"
        id="<?php echo esc_attr($block_id . '-grid'); ?>"
    >
        <?php
        if ($query->have_posts()) :
            while ($query->have_posts()) :
                $query->the_post();
                solaire_archive_card_template(get_the_ID());
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p class="solaire-post-archive__empty">' . esc_html__('No posts found.', 'solaire') . '</p>';
        endif;
        ?>
    </div>

    <?php if ($show_load_more && $has_more) : ?>
        <div class="solaire-post-archive__load-more-wrap">
            <button
                type="button"
                class="solaire-post-archive__load-more"
                data-grid="<?php echo esc_attr($block_id . '-grid'); ?>"
                data-block="<?php echo esc_attr($block_id); ?>"
                data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                data-action="solaire_archive_load_more"
                data-nonce="<?php echo esc_attr(wp_create_nonce('solaire_archive_load_more')); ?>"
            >
                <?php esc_html_e('Load More', 'solaire'); ?>
            </button>
        </div>
    <?php endif; ?>

  </div>
</div>
