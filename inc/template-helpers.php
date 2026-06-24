<?php
/**
 * Shared rendering helpers used by both block render.php files and the
 * CPT templates (archive-game.php / single-game.php). Centralising the
 * icon set and the game-card markup keeps the design 1:1 everywhere.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Inline SVG icon set (stroke-based, inherits currentColor).
 *
 * @param string $name  Icon key.
 * @param string $class Extra classes for the <svg>.
 * @param string $sw    Stroke width.
 */
function solaire_icon($name, $class = 'h-[18px] w-[18px]', $sw = '2')
{
    $paths = [
        'home'        => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v9h14v-9"/>',
        'live-slots'  => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 9v6M12 9v6M17 9v6"/>',
        'live-casino' => '<rect x="3" y="5" width="13" height="16" rx="2"/><path d="m9 9 3 3-3 3"/><path d="M16 7h3a2 2 0 0 1 2 2v8"/>',
        'e-games'     => '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>',
        'sportsbook'  => '<circle cx="12" cy="12" r="9"/><path d="M12 3a14 14 0 0 0 0 18M3 12h18"/>',
        'arrow-left'  => '<path d="m15 5-7 7 7 7"/>',
        'arrow-right' => '<path d="m9 5 7 7-7 7"/>',
        'chevron'     => '<path d="m6 9 6 6 6-6"/>',
        'bolt'        => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
        'shield'      => '<path d="M12 2 4 5v6c0 5 3.4 8.5 8 11 4.6-2.5 8-6 8-11V5z"/><path d="m9 12 2 2 4-4"/>',
        'hybrid'      => '<rect x="2" y="6" width="20" height="12" rx="3"/><path d="M7 12h3M8.5 10.5v3M15 11h.01M18 13h.01"/>',
        'crown'       => '<path d="M3 7l4 4 5-7 5 7 4-4-2 12H5z"/>',
        'menu'        => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'       => '<path d="M6 6l12 12M18 6 6 18"/>',
        'phone'       => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
        'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'shield-plain'=> '<path d="M12 2 4 5v6c0 5 3.4 8.5 8 11 4.6-2.5 8-6 8-11V5z"/>',
        'peso'        => '<path d="M12 2v20M5 6h9a3 3 0 0 1 0 6H7a3 3 0 0 0 0 6h10"/>',
        'card'        => '<rect x="2" y="6" width="20" height="12" rx="2"/><path d="M2 10h20"/>',
        'dual'        => '<circle cx="8" cy="12" r="3"/><circle cx="16" cy="12" r="3"/>',
        'phone-plain' => '<rect x="7" y="2" width="10" height="20" rx="2"/>',
        'help'        => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.7.4-1 .9-1 1.7M12 17h.01"/>',
        'squares'     => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'star'        => '<circle cx="12" cy="12" r="9"/><path d="m12 8 1.3 2.6 2.9.4-2.1 2 .5 2.9L12 16.5 9.5 17.9l.5-2.9-2.1-2 2.9-.4z"/>',
        'refresh'     => '<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
        'paylines'    => '<circle cx="7" cy="12" r="3"/><path d="M10 12h11M18 9l3 3-3 3"/>',
        'chart'       => '<path d="M3 17l6-6 4 4 7-7"/><path d="M17 7h4v4"/>',
        'grid-rows'   => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18"/>',
    ];

    if (!isset($paths[$name])) {
        return '';
    }

    $fill = ($name === 'crown') ? 'currentColor' : 'none';

    return sprintf(
        '<svg viewBox="0 0 24 24" class="%s" fill="%s" stroke="currentColor" stroke-width="%s" stroke-linecap="round" stroke-linejoin="round">%s</svg>',
        esc_attr($class),
        esc_attr($fill),
        esc_attr($sw),
        $paths[$name]
    );
}

/**
 * Map a nav/category label to its taxonomy term slug + icon key.
 */
function solaire_categories()
{
    return [
        'live-slots'  => ['label' => 'Live Slots',  'icon' => 'live-slots'],
        'live-casino' => ['label' => 'Live Casino', 'icon' => 'live-casino'],
        'e-games'     => ['label' => 'E-Games',     'icon' => 'e-games'],
        'sportsbook'  => ['label' => 'Sportsbook',  'icon' => 'sportsbook'],
    ];
}

/**
 * Query game posts.
 *
 * @param array $args  Accepts: category (term slug), count, orderby, order.
 * @return WP_Query
 */
function solaire_query_games($args = [])
{
    $defaults = [
        'count'    => 8,
        'category' => '',
        'orderby'  => 'menu_order date',
        'order'    => 'ASC',
        'exclude'  => [],
    ];
    $args = wp_parse_args($args, $defaults);

    $q = [
        'post_type'      => 'game',
        'posts_per_page' => (int) $args['count'],
        'orderby'        => $args['orderby'],
        'order'          => $args['order'],
        'post__not_in'   => (array) $args['exclude'],
        'no_found_rows'  => true,
    ];

    if ($args['category']) {
        $q['tax_query'] = [[
            'taxonomy' => 'game_category',
            'field'    => 'slug',
            'terms'    => $args['category'],
        ]];
    }

    return new WP_Query($q);
}

/**
 * Render a single game card.
 *
 * @param int|WP_Post $post
 * @param array $args  variant: 'portrait' (row/more-games) | 'grid' (square + RTP/Vol).
 *                     class: extra wrapper classes (e.g. row width steps).
 */
function solaire_game_card($post, $args = [])
{
    $post = get_post($post);
    if (!$post) {
        return '';
    }

    $variant = $args['variant'] ?? 'portrait';
    $extra   = $args['class'] ?? '';
    $url     = get_permalink($post);
    $title   = get_the_title($post);
    $img     = get_the_post_thumbnail_url($post, 'large');
    // Show the "Demo" badge only when the game has a playable demo code.
    $badge   = trim((string) get_field('so_game_code', $post->ID)) !== '';

    $cats = wp_get_post_terms($post->ID, 'game_category', ['fields' => 'slugs']);
    $cat_attr = esc_attr(implode(' ', (array) $cats));

    $media = $img
        ? sprintf('<img src="%s" alt="%s" class="absolute inset-0 h-full w-full object-cover" loading="lazy" />', esc_url($img), esc_attr($title))
        : sprintf('<div class="ph absolute inset-0">%s</div>', esc_html($title));

    $badge_html = $badge
        ? '<span class="absolute right-2 top-2 rounded bg-brand-orange px-1.5 py-0.5 text-[10px] font-bold uppercase">Demo</span>'
        : '';

    if ($variant === 'grid') {
        $rtp = get_field('rtp', $post->ID);
        $vol = get_field('volatility', $post->ID);
        // "Select Volatility" is the placeholder default — show "—" like RTP.
        if ($vol === 'Select Volatility') {
            $vol = '';
        }
        ob_start(); ?>
        <a href="<?php echo esc_url($url); ?>" data-grid-item data-category="<?php echo $cat_attr; ?>"
           class="card-lift group block overflow-hidden rounded-xl bg-panel ring-1 ring-white/5 <?php echo esc_attr($extra); ?>">
            <div class="relative aspect-square overflow-hidden">
                <?php echo $media; // phpcs:ignore ?>
                <?php echo $badge_html; // phpcs:ignore ?>
            </div>
            <div class="px-3 pt-2">
                <h3 class="truncate font-display text-sm font-bold"><?php echo esc_html($title); ?></h3>
            </div>
            <div class="mt-1 flex items-center justify-between border-t border-white/5 px-3 py-2 text-[10px] uppercase tracking-wide text-slatey">
                <span>RTP <b class="text-gold"><?php echo esc_html($rtp ?: '—'); ?></b></span>
                <span>Vol <b class="text-orange"><?php echo esc_html($vol ?: '—'); ?></b></span>
            </div>
        </a>
        <?php
        return ob_get_clean();
    }

    // portrait variant (homepage rows + more-games)
    ob_start(); ?>
    <a href="<?php echo esc_url($url); ?>" data-category="<?php echo $cat_attr; ?>"
       class="card-lift game-card group relative block overflow-hidden rounded-xl <?php echo esc_attr($extra); ?>">
        <?php echo $media; // phpcs:ignore ?>
        <?php echo $badge_html; // phpcs:ignore ?>
    </a>
    <?php
    return ob_get_clean();
}

/**
 * Placeholder portrait cards for previews when no games exist yet
 * (keeps homepage game rows looking populated out of the box).
 */
function solaire_placeholder_cards($count = 5, $extra = '')
{
    $out = '';
    for ($i = 0; $i < $count; $i++) {
        $out .= sprintf(
            '<a href="#" class="card-lift game-card group relative block overflow-hidden rounded-xl %s"><div class="ph absolute inset-0">game art</div><span class="absolute right-2 top-2 rounded bg-brand-orange px-1.5 py-0.5 text-[10px] font-bold uppercase">Demo</span></a>',
            esc_attr($extra)
        );
    }
    return $out;
}

/**
 * Resolve an image value to a usable URL.
 * Full URLs pass through; bare filenames resolve against /assets/img.
 */
function solaire_img_src($value)
{
    if (!$value) {
        return '';
    }
    if (preg_match('#^https?://#', $value) || strpos($value, '/') === 0) {
        return $value;
    }
    return get_theme_file_uri('/assets/img/' . ltrim($value, '/'));
}

/**
 * Active-state helper for the header nav.
 */
function solaire_nav_active($key)
{
    if ($key === 'home') {
        return is_front_page() || is_home();
    }
    if ($key === 'live-slots') {
        if (is_singular('game')) {
            $terms = wp_get_post_terms(get_queried_object_id(), 'game_category', ['fields' => 'slugs']);
            return in_array('live-slots', (array) $terms, true)
                || is_post_type_archive('game');
        }
        return is_post_type_archive('game') || is_tax('game_category', 'live-slots');
    }
    return is_tax('game_category', $key);
}

/**
 * Resolve the icon key for a nav menu item (matches solaire_icon() names).
 * Home links map to "home"; game_category items use their term slug.
 */
function solaire_nav_icon_key($item)
{
    if (untrailingslashit($item->url) === untrailingslashit(home_url('/'))) {
        return 'home';
    }
    if ($item->object === 'game_category') {
        $term = get_term($item->object_id, 'game_category');
        if ($term && !is_wp_error($term)) {
            return $term->slug;
        }
    }
    return sanitize_title($item->title);
}

/**
 * Resolve an ACF image field value (array / attachment ID / URL) to a URL.
 */
function solaire_acf_image_url($value, $size = 'thumbnail')
{
    if (is_array($value)) {
        if (!empty($value['sizes'][$size])) {
            return $value['sizes'][$size];
        }
        return $value['url'] ?? '';
    }
    if (is_numeric($value)) {
        return wp_get_attachment_image_url($value, $size) ?: '';
    }
    return is_string($value) ? $value : '';
}

/**
 * Icon HTML for a header nav item.
 *
 * game_category items use their custom two-tone ACF image icons
 * (so_game_ctg_icon when idle, so_game_category_active_icon when active or
 * hovered). Falls back to the built-in SVG icon when no image is set.
 */
function solaire_nav_icon_html($item, $active)
{
    if ($item->object === 'game_category' && function_exists('get_field')) {
        $term_ref     = 'game_category_' . $item->object_id;
        $inactive_url = solaire_acf_image_url(get_field('so_game_ctg_icon', $term_ref));
        $active_url   = solaire_acf_image_url(get_field('so_game_category_active_icon', $term_ref));

        if ($inactive_url || $active_url) {
            // If only one icon is set, use it for both states.
            $inactive_url = $inactive_url ?: $active_url;
            $active_url   = $active_url ?: $inactive_url;

            return sprintf(
                '<span class="relative inline-flex h-5 w-5 shrink-0 items-center justify-center">'
                . '<img src="%1$s" alt="" aria-hidden="true" class="h-5 w-5 object-contain transition-opacity %3$s" />'
                . '<img src="%2$s" alt="" aria-hidden="true" class="absolute inset-0 h-5 w-5 object-contain transition-opacity %4$s" />'
                . '</span>',
                esc_url($inactive_url),
                esc_url($active_url),
                $active ? 'opacity-0' : 'opacity-100 group-hover:opacity-0',
                $active ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'
            );
        }
    }

    return solaire_icon(solaire_nav_icon_key($item));
}

/**
 * Walker for the header navigation. Renders bare <a> elements (no <ul>/<li>)
 * with icons and active styling, in either the "desktop" or "mobile" variant.
 */
class Solaire_Nav_Walker extends Walker_Nav_Menu
{
    protected $variant;

    public function __construct($variant = 'desktop')
    {
        $this->variant = $variant;
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}
    public function end_el(&$output, $item, $depth = 0, $args = null) {}

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = (array) $item->classes;
        $active  = in_array('current-menu-item', $classes, true)
            || in_array('current-menu-parent', $classes, true)
            || in_array('current-menu-ancestor', $classes, true);

        $url   = $item->url ?: '#';
        $label = $item->title;

        if ($this->variant === 'mobile') {
            $cls = $active ? 'text-orange' : 'text-white/90';
            $output .= sprintf(
                '<a href="%s" class="rounded-lg px-3 py-3 hover:bg-white/5 %s">%s</a>',
                esc_url($url),
                esc_attr($cls),
                esc_html($label)
            );
            return;
        }

        $cls  = $active ? 'text-orange hover:text-orange-bright' : 'text-white/90 hover:text-orange';
        $icon = solaire_nav_icon_html($item, $active);
        $output .= sprintf(
            '<a href="%s" class="group flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition-colors %s"%s>%s<span>%s</span></a>',
            esc_url($url),
            esc_attr($cls),
            $active ? ' aria-current="page"' : '',
            $icon, // phpcs:ignore
            esc_html($label)
        );
    }
}
