<?php
/**
 * Front page — the Solaire homepage.
 *
 * Renders the static front page content if one is set (so it stays editable
 * in the block editor), otherwise falls back to the default homepage block
 * composition so the homepage always renders.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="site-main">
<?php
$front_id = (int) get_option('page_on_front');
if ($front_id && ($front = get_post($front_id)) && trim($front->post_content) !== '') {
    while (have_posts()) {
        the_post();
        the_content();
    }
} else {
    echo do_blocks(solaire_homepage_blocks()); // phpcs:ignore WordPress.Security.EscapeOutput
}
?>
</main>
<?php
get_footer();
