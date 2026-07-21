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
    // Filled, sprite-style icons that need their own viewBox and fill rendering
    // (not the shared 0 0 24 24 stroke template below).
    $filled = [
        // "FOOTBALL" soccer-ball mark used for Sportsbook (matches solaireonline.com).
        'sportsbook' => [
            'viewBox' => '0 0 21 20',
            'path'    => '<path d="M10.6625 1.39999C8.96158 1.39999 7.29886 1.90438 5.8846 2.84936C4.47034 3.79434 3.36805 5.13747 2.71714 6.70892C2.06623 8.28036 1.89592 10.0095 2.22775 11.6778C2.55958 13.346 3.37866 14.8784 4.58139 16.0811C5.78412 17.2838 7.31649 18.1029 8.98473 18.4347C10.653 18.7666 12.3821 18.5963 13.9536 17.9454C15.525 17.2944 16.8682 16.1922 17.8131 14.7779C18.7581 13.3636 19.2625 11.7009 19.2625 9.99999C19.2625 7.71913 18.3564 5.53169 16.7436 3.91888C15.1308 2.30606 12.9434 1.39999 10.6625 1.39999ZM3.37298 9.99999C3.36839 8.49306 3.82575 7.02093 4.68345 5.7819H5.66632L5.46155 7.78857L4.35583 10.5733L3.41394 10.9829C3.37804 10.6565 3.36436 10.3282 3.37298 9.99999ZM16.5187 5.69999C17.3959 6.88783 17.8825 8.31886 17.9111 9.79523L16.5187 10.7781L14.2254 9.50856L14.0206 7.5838L14.9215 5.94571H15.0854L16.5187 5.69999ZM10.4168 6.88761L13.5292 7.74761L13.7339 9.54952L11.7273 11.4333L9.39298 11.0238L8.9425 8.11618L9.18822 7.91142L10.4168 6.88761ZM10.7444 16.1019L7.71393 15.4057L7.09965 12.9486L9.22917 11.4743L11.6044 11.8838L12.2596 14.3819L11.1949 15.6105L10.7444 16.1019ZM4.47869 10.9829L6.64917 12.9486L7.2225 15.4876L6.44441 15.9381C4.93168 14.8591 3.87934 13.2514 3.49584 11.4333L4.47869 10.9829ZM11.072 16.4705L12.6282 14.6686H15.372L15.5358 15.3648C14.3194 16.5038 12.7367 17.1718 11.072 17.2486V16.4705ZM15.8225 14.4638L16.7235 11.2286L17.9111 10.4095C17.8229 12.1237 17.1258 13.7501 15.9454 14.9962L15.8225 14.4638ZM16.1911 5.24952L15.5768 5.37237L14.8396 5.45428L12.9558 3.69333L13.0377 3.11999C14.258 3.5399 15.3457 4.27449 16.1911 5.24952ZM9.96631 4.67618L10.1301 6.51904L8.61489 7.74761L5.95298 7.5838L6.15774 5.61809L7.95964 4.1438L9.96631 4.67618ZM12.5873 2.95619L12.5054 3.61142L10.212 4.22571L8.0825 3.69333L7.9187 3.24285C8.7869 2.8784 9.721 2.69716 10.6625 2.71047C11.312 2.70908 11.9589 2.79167 12.5873 2.95619Z"/>',
        ],
    ];
    if (isset($filled[$name])) {
        return sprintf(
            '<svg viewBox="%s" class="%s" fill="currentColor" aria-hidden="true">%s</svg>',
            esc_attr($filled[$name]['viewBox']),
            esc_attr($class),
            $filled[$name]['path']
        );
    }

    $paths = [
        'home'        => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v9h14v-9"/>',
        'live-slots'  => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 9v6M12 9v6M17 9v6"/>',
        'live-casino' => '<rect x="3" y="5" width="13" height="16" rx="2"/><path d="m9 9 3 3-3 3"/><path d="M16 7h3a2 2 0 0 1 2 2v8"/>',
        'e-games'     => '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>',
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
 * @param array $args  Accepts: category (term slug), tag (game-tag term slug), count, orderby, order.
 * @return WP_Query
 */
function solaire_query_games($args = [])
{
    $defaults = [
        'count'    => 8,
        'category' => '',
        'tag'      => '',
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

    $tax_query = [];
    if ($args['category']) {
        $tax_query[] = [
            'taxonomy' => 'game_category',
            'field'    => 'slug',
            'terms'    => $args['category'],
        ];
    }
    if ($args['tag']) {
        $tax_query[] = [
            'taxonomy' => 'game-tag',
            'field'    => 'slug',
            'terms'    => $args['tag'],
        ];
    }
    if ($tax_query) {
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
        $q['tax_query'] = $tax_query;
    }

    return new WP_Query($q);
}

/**
 * Site logo URL (WordPress Custom Logo), or '' when none is set.
 */
function solaire_site_logo_url()
{
    $logo_id = get_theme_mod('custom_logo');
    if ($logo_id) {
        $url = wp_get_attachment_image_url($logo_id, 'medium');
        if ($url) {
            return $url;
        }
    }
    return '';
}

/**
 * Branded card face — the site logo centered on a dark panel. Used as the
 * visual for every game card (in place of game artwork). Falls back to the
 * "SOLAIRE ONLINE" wordmark when no Custom Logo is configured.
 */
function solaire_card_logo_face()
{
    $logo = solaire_site_logo_url();
    if ($logo) {
        $inner = sprintf(
            '<img src="%s" alt="%s" class="w-3/5 max-w-[130px] object-contain opacity-95 transition duration-300" loading="lazy" />',
            esc_url($logo),
            esc_attr(get_bloginfo('name'))
        );
    } else {
        $inner = '<span class="flex flex-col items-center leading-none text-center">'
            . '<span class="font-logo text-base font-semibold tracking-[0.3em] text-white/90">SOLAIRE</span>'
            . '<span class="font-logo text-[7px] tracking-[0.55em] text-white/60">ONLINE</span>'
            . '</span>';
    }
    return '<div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-panel via-surface to-deep p-4">' . $inner . '</div>';
}

/**
 * Render a single game card.
 *
 * Shows the game's featured image when one exists; otherwise falls back to the
 * site logo centered on a panel with the game name along the bottom.
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
    // Show the "Hot" badge when the game carries the `hot` game-tag term.
    $is_hot  = has_term('hot', 'game-tag', $post->ID);

    $cats = wp_get_post_terms($post->ID, 'game_category', ['fields' => 'slugs']);
    $cat_attr = esc_attr(implode(' ', (array) $cats));

    // Featured image, or the branded logo fallback when none is set.
    $media = $img
        ? sprintf('<img src="%s" alt="%s" class="absolute inset-0 h-full w-full object-cover" loading="lazy" />', esc_url($img), esc_attr($title))
        : solaire_card_logo_face();

    // Demo + Hot badges stack in the top-right corner sharing one pill design:
    // Demo on top, Hot directly below it. The flex wrapper keeps them aligned
    // whether one or both are present.
    // Stretch both pills to the same (widest) width and center their labels so
    // Demo and Hot line up as equal-size badges.
    $badges = '';
    if ($badge || $is_hot) {
        $badges = '<div class="absolute right-2 top-2 z-10 flex flex-col gap-1 text-center">';
        if ($badge) {
            $badges .= '<span class="rounded bg-brand-orange px-1.5 py-0.5 text-[10px] font-bold uppercase">Demo</span>';
        }
        if ($is_hot) {
            $badges .= '<span class="rounded bg-brand-orange px-1.5 py-0.5 text-[10px] font-bold uppercase">Hot</span>';
        }
        $badges .= '</div>';
    }

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
            <div class="game-card relative overflow-hidden">
                <?php echo $media; // phpcs:ignore ?>
                <?php echo $badges; // phpcs:ignore ?>
            </div>
            <div class="px-3 pt-2">
                <h3 class="truncate font-display text-sm font-bold"><?php echo esc_html($title); ?></h3>
            </div>
            <div class="mt-1 flex items-center justify-between border-t border-white/5 px-3 py-2 text-[10px] uppercase tracking-wide text-slatey">
                <span>RTP: <b class="text-gold"><?php echo esc_html($rtp ?: '—'); ?></b></span>
                <span>Vol: <b class="text-orange"><?php echo esc_html($vol ?: '—'); ?></b></span>
            </div>
        </a>
        <?php
        return ob_get_clean();
    }

    // portrait variant (homepage rows + more-games). When there's no featured
    // image, the logo fallback gets the game name along the bottom.
    ob_start(); ?>
    <a href="<?php echo esc_url($url); ?>" data-category="<?php echo $cat_attr; ?>"
       class="card-lift game-card group relative block overflow-hidden rounded-xl <?php echo esc_attr($extra); ?>">
        <?php echo $media; // phpcs:ignore ?>
        <?php echo $badges; // phpcs:ignore ?>
        <?php if (!$img) : ?>
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/45 to-transparent px-2.5 pb-2 pt-7">
                <h3 class="truncate text-center font-display text-xs font-bold text-white sm:text-sm"><?php echo esc_html($title); ?></h3>
            </div>
        <?php endif; ?>
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
    $face = solaire_card_logo_face();
    $out  = '';
    for ($i = 0; $i < $count; $i++) {
        $out .= sprintf(
            '<a href="#" class="card-lift game-card group relative block overflow-hidden rounded-xl %s">%s<span class="absolute right-2 top-2 z-10 rounded bg-brand-orange px-1.5 py-0.5 text-[10px] font-bold uppercase">Demo</span><div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/45 to-transparent px-2.5 pb-2 pt-7"><h3 class="truncate text-center font-display text-xs font-bold text-white sm:text-sm">%s</h3></div></a>',
            esc_attr($extra),
            $face,
            esc_html__('Game', 'solaire')
        );
    }
    return $out;
}

/**
 * Demo trigger button + its lazy game embed.
 *
 * Returns a button that opens the shared demo modal (see solaire_demo_modal),
 * paired with a <template> holding the st8 game iframe. The iframe lives in the
 * template so it isn't loaded until the visitor actually opens the demo.
 *
 * Returns '' when the game has no `so_game_code`, so callers can simply skip
 * rendering a demo control for games without an embed.
 *
 * @param int|WP_Post $post
 * @param string $label  Button text.
 * @param string $class  Button classes.
 */
function solaire_demo_trigger($post, $label, $class)
{
    $post = get_post($post);
    if (!$post) {
        return '';
    }
    $code = trim((string) get_field('so_game_code', $post->ID));
    if ($code === '') {
        return '';
    }

    $device = wp_is_mobile() ? 'MOBILE' : 'DESKTOP';
    $embed  = do_shortcode('[st8_game game="' . esc_attr($code) . '" fun_mode="true" device="' . $device . '"]');
    $tpl_id = 'demo-embed-' . $post->ID . '-' . wp_unique_id();
    $title  = get_the_title($post) . ' — ' . __('Demo', 'solaire');

    return sprintf(
        '<button type="button" data-demo-open data-demo-target="%1$s" data-title="%2$s" class="%3$s">%4$s</button>'
        . '<template id="%1$s">%5$s</template>',
        esc_attr($tpl_id),
        esc_attr($title),
        esc_attr($class),
        esc_html($label),
        $embed // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode markup.
    );
}

/**
 * Shared demo modal markup + script. Outputs once per request (later calls
 * return ''), so it can be invoked from any block/template that uses
 * solaire_demo_trigger(). Mirrors the single-game demo modal: desktop opens the
 * iframe in the modal; mobile opens the embed URL in a new tab.
 */
function solaire_demo_modal()
{
    static $done = false;
    if ($done) {
        return '';
    }
    $done = true;

    ob_start(); ?>
    <div id="solaire-demo-modal" class="fixed inset-0 z-[9997] hidden items-center justify-center bg-black/85 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-hidden="true">
      <div class="relative w-full max-w-5xl overflow-hidden rounded-2xl bg-deep shadow-2xl ring-1 ring-white/15">
        <div class="flex items-center justify-between border-b border-white/10 bg-surface px-5 py-3">
          <h2 data-demo-title class="font-display text-lg font-bold text-white"></h2>
          <button type="button" data-demo-close aria-label="<?php esc_attr_e('Close', 'solaire'); ?>" class="flex h-9 w-9 items-center justify-center rounded-md text-white/70 transition hover:bg-white/10 hover:text-white">
            <?php echo solaire_icon('close', 'h-5 w-5', '2.5'); // phpcs:ignore ?>
          </button>
        </div>
        <div class="relative aspect-video w-full bg-deep">
          <div data-demo-loading class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-deep text-sm text-slatey">
            <span class="h-10 w-10 animate-spin rounded-full border-[3px] border-white/15 border-t-orange"></span>
            <span><?php esc_html_e('Loading game…', 'solaire'); ?></span>
          </div>
          <div data-demo-body class="absolute inset-0 [&_iframe]:absolute [&_iframe]:inset-0 [&_iframe]:h-full [&_iframe]:w-full [&_iframe]:border-0"></div>
        </div>
      </div>
    </div>
    <script>
      (function () {
        'use strict';
        var modal = document.getElementById('solaire-demo-modal');
        if (!modal) return;

        var titleEl = modal.querySelector('[data-demo-title]');
        var bodyEl  = modal.querySelector('[data-demo-body]');
        var loadEl  = modal.querySelector('[data-demo-loading]');

        function showLoading() { if (loadEl) loadEl.style.display = 'flex'; }
        function hideLoading() { if (loadEl) loadEl.style.display = 'none'; }

        function open() {
          modal.classList.remove('hidden');
          modal.classList.add('flex');
          modal.setAttribute('aria-hidden', 'false');
          document.body.style.overflow = 'hidden';
        }
        function close() {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          modal.setAttribute('aria-hidden', 'true');
          document.body.style.overflow = '';
          if (bodyEl) bodyEl.innerHTML = ''; // stop the game / free the iframe
        }

        document.querySelectorAll('[data-demo-open]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var tpl = document.getElementById(btn.dataset.demoTarget);
            if (!tpl || !tpl.content) return;
            var frag    = tpl.content.cloneNode(true);
            var wrapper = frag.querySelector('.st8gl-wrapper');
            var frame   = frag.querySelector('iframe');

            if (titleEl) titleEl.textContent = btn.dataset.title || '';
            bodyEl.innerHTML = '';
            showLoading();
            if (frame) { frame.addEventListener('load', hideLoading); }
            bodyEl.appendChild(frag);

            // The st8 launcher only auto-inits wrappers present at page load;
            // a wrapper cloned from the <template> is inert until we launch it
            // (this fetches the game URL and sets the iframe src).
            if (wrapper && window.St8GL && typeof window.St8GL.launch === 'function') {
              window.St8GL.launch(wrapper);
            } else if (!frame) {
              hideLoading();
            }
            open();
          });
        });

        modal.querySelectorAll('[data-demo-close]').forEach(function (btn) {
          btn.addEventListener('click', close);
        });
        modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });
      })();
    </script>
    <?php
    return ob_get_clean();
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

        $cls       = $active ? 'text-white' : 'text-white/90 hover:text-white';
        $label_cls = 'so-nav-underline' . ($active ? ' is-active' : '');
        $icon      = solaire_nav_icon_html($item, $active);
        $output .= sprintf(
            '<a href="%s" class="group flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition-colors %s"%s><span class="flex shrink-0 text-white">%s</span><span class="%s">%s</span></a>',
            esc_url($url),
            esc_attr($cls),
            $active ? ' aria-current="page"' : '',
            $icon, // phpcs:ignore
            esc_attr($label_cls),
            esc_html($label)
        );
    }
}

/**
 * Render page/post content, keeping default Gutenberg (core/*) blocks inside a
 * width-constrained `.entry-content` box while letting the theme's custom
 * full-bleed section blocks (solaire/* and mytheme/*) render edge-to-edge
 * outside it.
 *
 * Without this, a page that mixes a full-bleed section block (e.g. the Solaire
 * Hero Banner) with normal paragraphs would render those paragraphs edge-to-edge
 * too. Here we walk the top-level blocks, buffer consecutive core blocks and
 * flush them wrapped in `.entry-content`, and emit each solaire/* section block
 * on its own (full-bleed).
 *
 * @param string $content Raw post content (unfiltered), e.g. from get_the_content().
 * @return string Rendered HTML.
 */
function solaire_render_split_content($content)
{
    $blocks = parse_blocks($content);
    $html   = '';
    $buffer = '';

    $flush = function () use (&$buffer, &$html) {
        if (trim($buffer) !== '') {
            // do_shortcode() so shortcodes typed inside core blocks still run.
            $html .= '<div class="entry-content">' . do_shortcode($buffer) . '</div>';
        }
        $buffer = '';
    };

    foreach ($blocks as $block) {
        $name = $block['blockName'] ?? null;

        // Theme section blocks are full-bleed — render them outside the box.
        // Covers both namespaces: original `solaire/*` blocks and the
        // scaffolded/imported `mytheme/*` blocks (e.g. the carousel).
        if ($name && (strpos($name, 'solaire/') === 0 || strpos($name, 'mytheme/') === 0)) {
            $flush();
            $html .= render_block($block);
        } else {
            // core/* blocks (and the whitespace "null" blocks between them).
            $buffer .= render_block($block);
        }
    }
    $flush();

    return $html;
}
