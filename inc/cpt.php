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
 * Default the Games archive to a comfortable per-page count.
 */
function solaire_game_archive_query($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if ($query->is_post_type_archive('game') || $query->is_tax('game_category')) {
        $query->set('posts_per_page', 24);
    }
}
add_action('pre_get_posts', 'solaire_game_archive_query');
