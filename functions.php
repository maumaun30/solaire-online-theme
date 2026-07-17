<?php

if (!defined('ABSPATH')) {
    exit;
}

define('SOLAIRE_VERSION', '1.0.1');

/* ============================================================
   Advanced Custom Fields (bundled)
   ============================================================ */
add_action('after_setup_theme', function () {
    if (!class_exists('ACF')) {
        include_once get_stylesheet_directory() . '/acf/acf.php';
    }
});

add_filter('acf/settings/path', function ($path) {
    return get_stylesheet_directory() . '/acf/';
});

add_filter('acf/settings/dir', function ($dir) {
    return get_stylesheet_directory_uri() . '/acf/';
});

/* ============================================================
   Theme support
   ============================================================ */
function solaire_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    add_theme_support('custom-logo');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary'        => __('Primary Menu', 'solaire'),
        'footer-legal'   => __('Footer — Legal', 'solaire'),
        'footer-support' => __('Footer — Support', 'solaire'),
    ]);
}
add_action('after_setup_theme', 'solaire_setup');

/* ============================================================
   Front-end assets
   ============================================================ */
function solaire_fonts_url()
{
    return 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap';
}

function solaire_enqueue_assets()
{
    $critical_css = get_theme_file_path('/assets/css/critical.min.css');
    $main_css     = get_theme_file_path('/assets/css/main.min.css');
    $solaire_js   = get_theme_file_path('/assets/js/solaire.js');

    // Google Fonts: Montserrat (single typeface for the whole theme).
    wp_enqueue_style('solaire-fonts', solaire_fonts_url(), [], null);

    if (file_exists($critical_css)) {
        wp_enqueue_style(
            'solaire-critical',
            get_theme_file_uri('/assets/css/critical.min.css'),
            [],
            filemtime($critical_css)
        );
    }

    if (file_exists($main_css)) {
        wp_enqueue_style(
            'solaire-main',
            get_theme_file_uri('/assets/css/main.min.css'),
            ['solaire-fonts', 'solaire-critical'],
            filemtime($main_css)
        );
    }

    wp_enqueue_style(
        'solaire-style',
        get_stylesheet_uri(),
        ['solaire-main'],
        filemtime(get_theme_file_path('/style.css'))
    );

    if (file_exists($solaire_js)) {
        wp_enqueue_script(
            'solaire-main',
            get_theme_file_uri('/assets/js/solaire.js'),
            [],
            filemtime($solaire_js),
            true
        );
        // AJAX endpoint + nonce for the category grid "Load More" / filtering.
        wp_localize_script('solaire-main', 'SolaireAjax', [
            'url'   => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('solaire_games'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'solaire_enqueue_assets');

/* ============================================================
   Editor assets — preview blocks with real fonts + utilities
   ============================================================ */
function solaire_editor_assets()
{
    if (file_exists(get_theme_file_path('/assets/css/main.min.css'))) {
        add_editor_style('assets/css/main.min.css');
    }
}
add_action('after_setup_theme', 'solaire_editor_assets');

function solaire_editor_fonts()
{
    wp_enqueue_style('solaire-fonts', solaire_fonts_url(), [], null);
}
add_action('enqueue_block_editor_assets', 'solaire_editor_fonts');

/* ============================================================
   Auto-register dynamic blocks in /assets/js/blocks
   ============================================================ */
function solaire_register_blocks()
{
    $blocks_dir = get_theme_file_path('/assets/js/blocks');

    if (!is_dir($blocks_dir)) {
        return;
    }

    foreach (scandir($blocks_dir) as $folder) {
        if ($folder === '.' || $folder === '..') {
            continue;
        }

        $block_path = $blocks_dir . '/' . $folder;

        if (is_dir($block_path) && file_exists($block_path . '/block.json')) {
            register_block_type($block_path);
        }
    }
}
add_action('init', 'solaire_register_blocks');

// Allow SVG upload
add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {

  global $wp_version;
  if ( $wp_version !== '4.7.1' ) {
     return $data;
  }

  $filetype = wp_check_filetype( $filename, $mimes );

  return [
      'ext'             => $filetype['ext'],
      'type'            => $filetype['type'],
      'proper_filename' => $data['proper_filename']
  ];

}, 10, 4 );

function cc_mime_types( $mimes ){
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter( 'upload_mimes', 'cc_mime_types' );

function fix_svg() {
  echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}
add_action( 'admin_head', 'fix_svg' );

function so_enqueue_single_post_slider() {
    if ( ! is_singular( array( 'post', 'promo' ) ) ) {
        return;
    }

    wp_enqueue_style(
        'swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        [],
        '11'
    );
    wp_enqueue_script(
        'swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        [],
        '11',
        true
    );

    $fg_js = get_template_directory() . '/assets/js/single-featured-games.js';
    wp_enqueue_script(
        'sp-featured-games',
        get_template_directory_uri() . '/assets/js/single-featured-games.js',
        [ 'swiper' ],
        file_exists( $fg_js ) ? filemtime( $fg_js ) : wp_get_theme()->get( 'Version' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'so_enqueue_single_post_slider' );

/* ============================================================
   Theme modules
   ============================================================ */
require_once get_theme_file_path('/inc/cpt.php');
require_once get_theme_file_path('/inc/acf-fields.php');
require_once get_theme_file_path('/inc/template-helpers.php');
require_once get_theme_file_path('/inc/homepage.php');
require_once get_theme_file_path('/inc/seed.php');
