<?php
/**
 * Games custom post type + category taxonomy.
 *
 * Post type key:  game   (archive slug: /games/)
 * Taxonomy key:   game_category   (slug: /game-category/)
 */

if (!defined('ABSPATH')) {
    exit;
}

function solaire_register_game_cpt()
{
    $labels = [
        'name'                  => __('Games', 'solaire'),
        'singular_name'         => __('Game', 'solaire'),
        'menu_name'             => __('Games', 'solaire'),
        'add_new'               => __('Add Game', 'solaire'),
        'add_new_item'          => __('Add New Game', 'solaire'),
        'edit_item'             => __('Edit Game', 'solaire'),
        'new_item'              => __('New Game', 'solaire'),
        'view_item'             => __('View Game', 'solaire'),
        'view_items'            => __('View Games', 'solaire'),
        'search_items'          => __('Search Games', 'solaire'),
        'not_found'             => __('No games found', 'solaire'),
        'not_found_in_trash'    => __('No games found in Trash', 'solaire'),
        'all_items'             => __('All Games', 'solaire'),
        'archives'              => __('Game Archives', 'solaire'),
        'featured_image'        => __('Game Artwork', 'solaire'),
        'set_featured_image'    => __('Set game artwork', 'solaire'),
        'use_featured_image'    => __('Use as game artwork', 'solaire'),
    ];

    register_post_type('game', [
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => 'games',
        'rewrite'       => ['slug' => 'games', 'with_front' => false],
        'menu_icon'     => 'dashicons-games',
        'menu_position' => 5,
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'show_in_rest'  => true,
        'taxonomies'    => ['game_category'],
    ]);
}
add_action('init', 'solaire_register_game_cpt');

function solaire_register_game_taxonomy()
{
    $labels = [
        'name'              => __('Game Categories', 'solaire'),
        'singular_name'     => __('Game Category', 'solaire'),
        'search_items'      => __('Search Categories', 'solaire'),
        'all_items'         => __('All Categories', 'solaire'),
        'edit_item'         => __('Edit Category', 'solaire'),
        'update_item'       => __('Update Category', 'solaire'),
        'add_new_item'      => __('Add New Category', 'solaire'),
        'new_item_name'     => __('New Category Name', 'solaire'),
        'menu_name'         => __('Categories', 'solaire'),
    ];

    register_taxonomy('game_category', ['game'], [
        'labels'            => $labels,
        'public'            => true,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'game-category', 'with_front' => false],
    ]);
}
add_action('init', 'solaire_register_game_taxonomy');

/**
 * Flush rewrite rules once, after the CPT/taxonomy are first registered.
 */
function solaire_maybe_flush_rewrites()
{
    if (get_option('solaire_rewrites_flushed') !== SOLAIRE_VERSION) {
        solaire_register_game_cpt();
        solaire_register_game_taxonomy();
        flush_rewrite_rules();
        update_option('solaire_rewrites_flushed', SOLAIRE_VERSION);
    }
}
add_action('init', 'solaire_maybe_flush_rewrites', 99);

/**
 * How many game cards the category/archive grid loads per page. Shared by the
 * initial server render (pre_get_posts below) and the AJAX "Load More" handler
 * so pagination offsets always line up.
 */
if (!defined('SOLAIRE_GAMES_PER_PAGE')) {
    define('SOLAIRE_GAMES_PER_PAGE', 12);
}

/**
 * Tune the front-end Games archive + category queries: a comfortable per-page
 * count, and order the cards oldest → newest by publish date.
 */
function solaire_game_archive_query($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if ($query->is_post_type_archive('game') || $query->is_tax('game_category')) {
        $query->set('posts_per_page', SOLAIRE_GAMES_PER_PAGE);
        $query->set('orderby', 'date');
        $query->set('order', 'ASC'); // oldest first
    }
}
add_action('pre_get_posts', 'solaire_game_archive_query');

/**
 * AJAX: paginated + category-filtered game cards for the category archive grid.
 *
 * The grid is server-rendered with page 1; this endpoint powers both the
 * "Load More Games" button (append the next page) and the child-category filter
 * chips (replace the grid with page 1 of the chosen child). Filtering happens
 * on the server so games beyond the first page are reachable — the previous
 * behaviour only revealed cards already in the DOM, so with 1000+ games most
 * were never loaded and child filters matched nothing past the first page.
 *
 * POST params: parent (archive term slug), filter (child slug | 'all'),
 * paged, per_page.
 */
function solaire_ajax_load_games()
{
    check_ajax_referer('solaire_games', 'nonce');

    $parent = isset($_POST['parent']) ? sanitize_title(wp_unslash($_POST['parent'])) : '';
    $filter = isset($_POST['filter']) ? sanitize_title(wp_unslash($_POST['filter'])) : 'all';
    $paged  = max(1, (int) ($_POST['paged'] ?? 1));
    $per    = min(48, max(1, (int) ($_POST['per_page'] ?? SOLAIRE_GAMES_PER_PAGE)));

    // Scope to the active child filter when set, otherwise the whole parent
    // category (its descendants included).
    $term_slug = ($filter && $filter !== 'all') ? $filter : $parent;

    $args = [
        'post_type'           => 'game',
        'post_status'         => 'publish',
        'posts_per_page'      => $per,
        'paged'               => $paged,
        'orderby'             => 'date',
        'order'               => 'ASC',
        'ignore_sticky_posts' => true,
    ];
    if ($term_slug) {
        $args['tax_query'] = [[
            'taxonomy'         => 'game_category',
            'field'            => 'slug',
            'terms'            => $term_slug,
            'include_children' => true,
        ]];
    }

    $q = new WP_Query($args);

    ob_start();
    while ($q->have_posts()) {
        $q->the_post();
        echo solaire_game_card(get_the_ID(), ['variant' => 'grid']); // phpcs:ignore
    }
    wp_reset_postdata();
    $html = ob_get_clean();

    wp_send_json_success([
        'html'    => $html,
        'hasMore' => $paged < (int) $q->max_num_pages,
        'found'   => (int) $q->found_posts,
    ]);
}
add_action('wp_ajax_solaire_load_games', 'solaire_ajax_load_games');
add_action('wp_ajax_nopriv_solaire_load_games', 'solaire_ajax_load_games');
