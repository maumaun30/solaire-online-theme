<?php
/**
 * One-time content seeding.
 *
 * Creates the game_category terms and a set of sample Games (using the
 * design artwork bundled in /assets/img) so the homepage rows, category
 * archive and single game page render with real content immediately.
 * Runs once, guarded by the `solaire_seeded` option. Delete that option
 * to re-seed.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Copy a theme image into the media library and return its attachment ID.
 */
function solaire_sideload_image($relative)
{
    $file = get_theme_file_path('/assets/img/' . $relative);
    if (!file_exists($file)) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $uploads = wp_upload_dir();
    $basename = wp_unique_filename($uploads['path'], basename($relative));
    $dest = trailingslashit($uploads['path']) . $basename;

    if (!copy($file, $dest)) {
        return 0;
    }

    $filetype = wp_check_filetype($basename, null);
    $attachment = [
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name(pathinfo($basename, PATHINFO_FILENAME)),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment($attachment, $dest);
    if (is_wp_error($attach_id) || !$attach_id) {
        return 0;
    }
    $meta = wp_generate_attachment_metadata($attach_id, $dest);
    wp_update_attachment_metadata($attach_id, $meta);

    return $attach_id;
}

/**
 * Look up a game by exact title (replacement for deprecated get_page_by_title).
 */
function solaire_find_game_by_title($title)
{
    $q = new WP_Query([
        'post_type'      => 'game',
        'title'          => $title,
        'posts_per_page' => 1,
        'post_status'    => 'any',
        'no_found_rows'  => true,
        'fields'         => 'ids',
    ]);
    return $q->have_posts() ? $q->posts[0] : 0;
}

function solaire_seed_content()
{
    if (get_option('solaire_seeded') === SOLAIRE_VERSION) {
        return;
    }
    if (!post_type_exists('game')) {
        return;
    }

    // Categories
    $terms = [
        'live-slots'  => 'Live Slots',
        'live-casino' => 'Live Casino',
        'e-games'     => 'E-Games',
        'sportsbook'  => 'Sportsbook',
    ];
    foreach ($terms as $slug => $name) {
        if (!term_exists($slug, 'game_category')) {
            wp_insert_term($name, 'game_category', ['slug' => $slug]);
        }
    }

    // Sample games: [title, image, category, rtp, volatility, menu_order]
    $games = [
        ['Coin Combo',      'chips-banner.jpg', 'live-slots',  '96.2%', 'High', 0],
        ['Firebird Spirit', 'game-1.png',       'e-games',     '96.6%', 'High', 5],
        ['Gates of Olympus','game-2.png',       'e-games',     '99.2%', 'High', 6],
        ['Happy Fortune',   'game-3.png',       'e-games',     '97.1%', 'High', 7],
        ['Snakes & Ladders','game-4.png',       'e-games',     '99.8%', 'High', 8],
        ['Fortune Tree',    'slot-1.png',       'live-slots',  '97.3%', 'High', 1],
        ['Lightning Bongs', 'slot-2.png',       'live-slots',  '96.7%', 'High', 2],
        ['Lion Link',       'slot-3.png',       'live-slots',  '97.8%', 'High', 3],
        ['Fu Lai Cai Lai',  'slot-4.png',       'live-slots',  '95.0%', 'High', 4],
        ['Roulette',        'game-5.jpg',       'live-casino', '98.1%', 'High', 1],
        ['Crazy Time',      'game-6.jpg',       'live-casino', '96.7%', 'High', 2],
        ['Blackjack',       'slot-5.jpg',       'live-casino', '98.1%', 'High', 3],
        ['Dream Catcher',   'slot-6.jpg',       'live-casino', '95.7%', 'High', 4],
        ['Mega Ball',       'slot-7.jpg',       'live-casino', '96.2%', 'High', 5],
    ];

    foreach ($games as $g) {
        list($title, $image, $cat, $rtp, $vol, $order) = $g;

        // Skip if a game with this title already exists.
        if (solaire_find_game_by_title($title)) {
            continue;
        }

        $post_id = wp_insert_post([
            'post_type'    => 'game',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'menu_order'   => $order,
            'post_content' => sprintf(
                '%s turns every spin into a chance to collect special symbols, match winning combinations, and trigger exciting rewards.',
                $title
            ),
        ]);

        if (is_wp_error($post_id) || !$post_id) {
            continue;
        }

        wp_set_object_terms($post_id, $cat, 'game_category');

        $attach_id = solaire_sideload_image($image);
        if ($attach_id) {
            set_post_thumbnail($post_id, $attach_id);
        }

        if (function_exists('update_field')) {
            update_field('provider', 'Solaire Online', $post_id);
            update_field('rtp', $rtp, $post_id);
            update_field('volatility', $vol, $post_id);
            update_field('demo_badge', true, $post_id);
            update_field('play_url', '#', $post_id);
            update_field('demo_url', '#', $post_id);
        }
    }

    // Enrich the Coin Combo showcase to match the single design.
    $coin_id = solaire_find_game_by_title('Coin Combo');
    if ($coin_id && function_exists('update_field')) {
        $bg = solaire_sideload_image('coin-combo.png');
        if ($bg) {
            update_field('hero_background', $bg, $coin_id);
        }
        update_field('stats', [
            ['label' => 'RTP', 'value' => '96.2%'],
            ['label' => 'Volatility', 'value' => 'High'],
            ['label' => 'Game Provider', 'value' => 'Solaire Online'],
        ], $coin_id);
        update_field('rules', [
            ['title' => 'Symbols', 'text' => 'The Coin Combo symbols include special coin icons, bonus symbols, and matching game symbols that help create winning combinations and unlock exciting rewards.'],
            ['title' => 'Wilds & Scatters', 'text' => 'Look out for Wild and Scatter symbols, as they can boost your winning chances, unlock bonus features, and create more rewarding gameplay moments.'],
            ['title' => 'Free Spins Feature', 'text' => 'Trigger the Free Spin feature to enjoy bonus spins, collect more coin combinations, and increase your chances of landing bigger rewards.'],
            ['title' => '25 Fixed Paylines', 'text' => 'Coin Combo features 25 fixed paylines, giving players more chances to form winning combinations on every spin.'],
        ], $coin_id);
        wp_update_post([
            'ID' => $coin_id,
            'post_content' => 'Coin Combo turns every spin into a chance to collect special coin symbols, match winning combinations, and trigger exciting rewards. As the action builds, players can unlock bonus prizes, multipliers, and bigger payout opportunities.',
        ]);
    }

    update_option('solaire_seeded', SOLAIRE_VERSION);
}
add_action('admin_init', 'solaire_seed_content');
