<?php
/**
 * Default page template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="site-main">
  <?php
  while (have_posts()) :
      the_post();
      // Core blocks render inside a constrained `.entry-content` box; the
      // theme's full-bleed solaire/* section blocks render edge-to-edge.
      echo solaire_render_split_content(get_the_content()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  endwhile;
  ?>
</main>
<?php
get_footer();
