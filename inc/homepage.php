<?php
/**
 * Homepage composition.
 *
 * The homepage is a stack of Solaire blocks. The same block markup is used
 * three ways: (1) registered as an editor pattern, (2) seeded into a "Home"
 * page set as the static front page, and (3) as the front-page.php fallback
 * so the homepage always renders even before the page is created.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The ordered homepage block markup (block-comment serialised).
 */
function solaire_homepage_blocks()
{
    return implode("\n", [
        '<!-- wp:solaire/hero-banner /-->',
        '<!-- wp:solaire/category-tiles /-->',
        '<!-- wp:solaire/game-row {"title":"E-Games","category":"e-games","count":12} /-->',
        '<!-- wp:solaire/game-row {"title":"Live-Slots","category":"live-slots","count":12} /-->',
        '<!-- wp:solaire/game-row {"title":"Live Casino","category":"live-casino","count":12} /-->',
        '<!-- wp:solaire/ranking-list {"title":"Local Region Ranking","count":7} /-->',
        '<!-- wp:solaire/feature-cards /-->',
        '<!-- wp:solaire/benefits-row /-->',
        '<!-- wp:solaire/convenience-hook /-->',
    ]);
}

/**
 * Register the homepage pattern so editors can insert/edit the full layout.
 */
add_action('init', function () {
    if (!function_exists('register_block_pattern')) {
        return;
    }
    register_block_pattern('solaire/homepage', [
        'title'      => __('Solaire Homepage', 'solaire'),
        'categories' => ['featured'],
        'content'    => solaire_homepage_blocks(),
    ]);
});

/**
 * On first run, create a "Home" page from the homepage blocks and set it as
 * the static front page. Guarded by an option so it only happens once.
 */
function solaire_create_home_page()
{
    if (get_option('solaire_home_created')) {
        return;
    }

    $existing = get_page_by_path('home');
    if (!$existing) {
        $home_id = wp_insert_post([
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => 'Home',
            'post_name'    => 'home',
            'post_content' => solaire_homepage_blocks(),
        ]);
    } else {
        $home_id = $existing->ID;
    }

    if ($home_id && !is_wp_error($home_id)) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
    }

    update_option('solaire_home_created', SOLAIRE_VERSION);
}
add_action('admin_init', 'solaire_create_home_page');

/**
 * One-time migration: the game-row carousels were originally seeded with
 * count:5, which fits within the desktop viewport and leaves nothing to
 * scroll. Bump the saved front-page block attributes to count:12 so the
 * carousels actually overflow and the prev/next arrows engage. Guarded by an
 * option so it only runs once.
 */
function solaire_migrate_game_row_counts()
{
    if (get_option('solaire_game_row_count_migrated')) {
        return;
    }

    $front_id = (int) get_option('page_on_front');
    if ($front_id) {
        $front = get_post($front_id);
        if ($front && strpos($front->post_content, 'wp:solaire/game-row') !== false) {
            $updated = preg_replace_callback(
                '/(<!--\s*wp:solaire\/game-row\s*)(\{.*?\})(\s*\/-->)/s',
                function ($m) {
                    $attrs = json_decode($m[2], true);
                    if (is_array($attrs) && isset($attrs['count']) && (int) $attrs['count'] < 12) {
                        $attrs['count'] = 12;
                        return $m[1] . wp_json_encode($attrs) . $m[3];
                    }
                    return $m[0];
                },
                $front->post_content
            );

            if ($updated && $updated !== $front->post_content) {
                wp_update_post(['ID' => $front_id, 'post_content' => $updated]);
            }
        }
    }

    update_option('solaire_game_row_count_migrated', SOLAIRE_VERSION);
}
add_action('admin_init', 'solaire_migrate_game_row_counts');
