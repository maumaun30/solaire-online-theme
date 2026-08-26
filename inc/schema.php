<?php
/**
 * Structured data.
 *
 * The site ships no SEO plugin, so this file owns the @graph and prints it in
 * wp_head. If Yoast is ever activated it takes over the graph, and everything
 * here switches to enriching Yoast's Organization node instead of printing a
 * second one -- the site must never publish two entities for one business.
 *
 * @package Solaire_Online_Theme
 */

defined('ABSPATH') || exit;

/* -------------------------------------------------------------------------
 * 1 + 2. Organization / Casino
 *
 * One node, typed as both Organization and Casino (a LocalBusiness subtype),
 * carrying the PAGCOR accreditation listing via subjectOf.
 * ---------------------------------------------------------------------- */

/**
 * Is Yoast SEO active and building the schema graph?
 *
 * @return bool
 */
function solaire_schema_has_yoast()
{
    return defined('WPSEO_VERSION');
}

/**
 * Canonical @id for the Organization / Casino node.
 *
 * @return string
 */
function solaire_organization_id()
{
    return home_url('/') . '#organization';
}

/**
 * Organisation details.
 *
 * Filterable so a child theme or plugin can correct any of it without an edit
 * here.
 *
 * @return array
 */
function solaire_organization_details()
{
    return apply_filters(
        'solaire_organization_details',
        [
            'name'               => 'Solaire Online',
            'legalName'          => 'Bloomberry Resorts and Hotels, Inc. (Solaire Resorts and Casino)',
            'description'        => 'Solaire Online is your premier online gaming casino offering a wide variety of casino games, live dealers, and slots. We bring the excitement of Solaire Resort to your fingertips.',
            'foundingDate'       => '2013-03-16',
            'email'              => 'solaireonline@solaireresort.com',
            'telephone'          => '+632 8888-8888',
            'customerSupport'    => [
                'telephone'         => '+632 8888-8888',
                'email'             => 'solaireonline@solaireresort.com',
                'availableLanguage' => ['en', 'fil'],
            ],
            'priceRange'         => '₱₱',
            'image'              => 'https://games.solaireonline.com/wp-content/uploads/2026/08/SO-og-image.webp',
            'logo'               => 'https://games.solaireonline.com/wp-content/uploads/2026/08/Solaire-Online-logo-white.svg',
            'sameAs'             => [
                'https://x.com/solaireresort',
                'https://www.facebook.com/SolaireResort/',
                'https://www.youtube.com/@solaireresort',
                'https://www.instagram.com/solaireresort/',
                'https://www.linkedin.com/company/solaire-resort/',
            ],
            // No parentOrganization on purpose. PAGCOR's accreditation list files
            // the brand under "Main Brand" with Bloomberry Resorts and Hotels,
            // Inc. as the one accredited administrator -- a single company
            // trading under a brand, not a parent and a subsidiary. Naming
            // Bloomberry as parent while legalName is also Bloomberry would make
            // the entity its own parent.
            'isCasino'           => true,
            'areaServed'         => 'Philippines',
            'currenciesAccepted' => 'PHP',
            'address'            => [
                'streetAddress'   => '1 Asean Avenue, Entertainment City, Tambo',
                'addressLocality' => 'Parañaque City',
                'addressRegion'   => 'Metro Manila',
                'postalCode'      => '1701',
                'addressCountry'  => 'PH',
            ],
        ]
    );
}

/**
 * Regulatory documents that list this brand, as subjectOf nodes.
 *
 * Only list a document that actually names this site's brand or domain --
 * citing a register the brand does not appear in is a false claim.
 *
 * @return array
 */
function solaire_regulatory_listings()
{
    $pagcor = [
        '@type'         => 'GovernmentOrganization',
        'name'          => 'Philippine Amusement and Gaming Corporation',
        'alternateName' => 'PAGCOR',
        'url'           => 'https://www.pagcor.ph/',
    ];

    $documents = apply_filters(
        'solaire_regulatory_listings',
        [
            [
                'name' => 'List of PAGCOR-Approved Registered Brand and Domain Names/URLs of Licensed Casinos',
                'url'  => 'https://www.pagcor.ph/regulatory/pdf/App%20Kits/List-of-Registered-Brands-and-Domain-Names-URLs-of-Licensed-Casinos.pdf',
            ],
        ]
    );

    $nodes = [];
    foreach ($documents as $document) {
        if (empty($document['url']) || empty($document['name'])) {
            continue;
        }
        $nodes[] = [
            '@type'     => 'DigitalDocument',
            'name'      => $document['name'],
            'url'       => $document['url'],
            'publisher' => $pagcor,
        ];
    }

    return $nodes;
}

/**
 * Build the Organization / Casino node.
 *
 * Every property is conditional: an unset detail is omitted rather than
 * emitted empty. Existing keys on $data (Yoast's, when it is active) win for
 * anything Yoast already models more richly.
 *
 * @param array $data Existing Organization node, or [] when we own the graph.
 * @return array
 */
function solaire_build_organization_node($data = [])
{
    $details = solaire_organization_details();

    if (empty($data['@id'])) {
        $data['@id'] = solaire_organization_id();
    }
    if (empty($data['@type'])) {
        $data['@type'] = 'Organization';
    }
    if (empty($data['name'])) {
        $data['name'] = $details['name'];
    }
    if (empty($data['url'])) {
        $data['url'] = home_url('/');
    }

    foreach (['legalName', 'description', 'foundingDate', 'email', 'telephone'] as $key) {
        if (!empty($details[$key])) {
            $data[$key] = $details[$key];
        }
    }

    // addressCountry alone is a default, not a real address.
    $address = array_filter($details['address']);
    if (count($address) > 1) {
        $data['address'] = ['@type' => 'PostalAddress'] + $address;
    }

    $support = array_filter($details['customerSupport']);
    if (!empty($support['telephone']) || !empty($support['email'])) {
        $data['contactPoint'] = [
            '@type'       => 'ContactPoint',
            'contactType' => 'customer support',
        ] + $support;
    }

    if (!empty($details['priceRange'])) {
        $data['priceRange'] = $details['priceRange'];
    }

    if (!empty($details['image'])) {
        $data['image'] = $details['image'];
    }

    // Never clobber a richer ImageObject built from a plugin setting.
    if (!empty($details['logo']) && empty($data['logo'])) {
        $data['logo'] = $details['logo'];
    }

    if (!empty($details['sameAs'])) {
        $same_as = array_merge(
            isset($data['sameAs']) ? (array) $data['sameAs'] : [],
            (array) $details['sameAs']
        );
        $data['sameAs'] = array_values(array_unique(array_filter($same_as)));
    }

    if (!empty($details['areaServed'])) {
        $data['areaServed'] = [
            '@type' => 'Country',
            'name'  => $details['areaServed'],
        ];
    }

    if (!empty($details['currenciesAccepted'])) {
        $data['currenciesAccepted'] = $details['currenciesAccepted'];
    }

    // Casino is a LocalBusiness subtype: merged into the same node so the site
    // never publishes a second entity for the same business.
    if (!empty($details['isCasino'])) {
        $types = (array) $data['@type'];
        if (!in_array('Casino', $types, true)) {
            $types[] = 'Casino';
        }
        $data['@type'] = count($types) > 1 ? array_values($types) : reset($types);
    }

    $listings = solaire_regulatory_listings();
    if ($listings) {
        $existing = isset($data['subjectOf']) ? (array) $data['subjectOf'] : [];
        $data['subjectOf'] = array_merge($existing, $listings);
    }

    return $data;
}

/**
 * Enrich Yoast's Organization node when Yoast owns the graph.
 *
 * @param array $data Organization schema node.
 * @return array
 */
function solaire_filter_schema_organization($data)
{
    return solaire_build_organization_node((array) $data);
}
add_filter('wpseo_schema_organization', 'solaire_filter_schema_organization', 10, 1);

/**
 * Print the theme's own @graph when no SEO plugin is building one.
 *
 * WebSite is included so the Organization has a site node to be publisher of,
 * matching the shape Yoast would otherwise emit.
 */
function solaire_print_schema_graph()
{
    if (solaire_schema_has_yoast()) {
        return;
    }

    $graph = [
        solaire_build_organization_node(),
        [
            '@type'     => 'WebSite',
            '@id'       => home_url('/') . '#website',
            'url'       => home_url('/'),
            'name'      => get_bloginfo('name'),
            'publisher' => ['@id' => solaire_organization_id()],
            'inLanguage' => get_bloginfo('language'),
        ],
    ];

    /**
     * Nodes appended by the rest of the schema layer (game pages, FAQ, HowTo,
     * articles) when Yoast is not present.
     */
    $graph = apply_filters('solaire_schema_graph', $graph);

    $schema = [
        '@context' => 'https://schema.org',
        '@graph'   => array_values($graph),
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . '</script>' . "\n";
}
add_action('wp_head', 'solaire_print_schema_graph', 20);

/* -------------------------------------------------------------------------
 * 4. FAQPage
 *
 * FAQs come from two places -- the ACF repeater on a game_category term and
 * the solaire/faq-guide block -- and a block's answers are not known until the
 * body renders, well after wp_head has printed the graph. So each renderer
 * registers its pairs as it draws them and one FAQPage node is printed in
 * wp_footer. Google reads JSON-LD anywhere in the document.
 *
 * Google also wants at most one FAQPage per URL, so every source on a page
 * feeds a single shared node rather than printing one apiece.
 * ---------------------------------------------------------------------- */

/**
 * Collect Q/A pairs for this request's FAQPage node.
 *
 * Call with no argument to read what has been collected.
 *
 * @param array $items Rows with 'question' and 'answer' keys.
 * @return array Everything collected so far.
 */
function solaire_collect_faq_items($items = [])
{
    static $collected = [];

    foreach ((array) $items as $item) {
        $question = trim(wp_strip_all_tags((string) ($item['question'] ?? '')));
        // Answers may hold block-level markup; a newline keeps sentences from
        // running together once the tags are stripped.
        $answer = trim(wp_strip_all_tags((string) ($item['answer'] ?? ''), true));

        // A pair missing either half is not a FAQ, and Google rejects it.
        if ('' === $question || '' === $answer) {
            continue;
        }

        // Two blocks on one page can repeat a question; keyed by question so
        // the node never carries a duplicate.
        $collected[md5(strtolower($question))] = [
            'question' => $question,
            'answer'   => $answer,
        ];
    }

    return $collected;
}

/**
 * Print the FAQPage node for whatever was collected during this render.
 */
function solaire_print_faq_schema()
{
    // Yoast builds a FAQPage from its own FAQ block. Our sources -- the ACF
    // term repeater, the faq-guide block, prose in game copy -- are invisible
    // to it, so we normally still print. Standing down on every singular view
    // would suppress exactly the FAQs Yoast cannot see; instead we check
    // whether it actually emitted a competing node for this URL.
    if (solaire_yoast_emitted_faqpage()) {
        return;
    }

    $items = solaire_collect_faq_items();
    if (!$items) {
        return;
    }

    $main_entity = [];
    foreach ($items as $item) {
        $main_entity[] = [
            '@type'          => 'Question',
            'name'           => $item['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $item['answer'],
            ],
        ];
    }

    $url = solaire_current_url();

    $schema = [
        '@context'         => 'https://schema.org',
        '@type'            => 'FAQPage',
        '@id'              => $url . '#faq',
        'mainEntityOfPage' => $url,
        'mainEntity'       => $main_entity,
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . '</script>' . "\n";
}
add_action('wp_footer', 'solaire_print_faq_schema', 20);

/**
 * Did Yoast put a FAQPage in its graph for this request?
 *
 * Only one FAQPage per URL is valid, so this decides whether ours stands down.
 * Recorded from the graph filter, which runs in wp_head -- well before the
 * footer printer needs the answer.
 *
 * @param array|null $graph Yoast graph, when called as a filter.
 * @return bool|array
 */
function solaire_yoast_emitted_faqpage($graph = null)
{
    static $emitted = false;

    if (null === $graph) {
        return $emitted;
    }

    foreach ($graph as $node) {
        if (!empty($node['@type']) && in_array('FAQPage', (array) $node['@type'], true)) {
            $emitted = true;
            break;
        }
    }

    return $graph;
}
add_filter('wpseo_schema_graph', 'solaire_yoast_emitted_faqpage', 20, 1);

/**
 * Canonical URL of the view being rendered, for schema @id values.
 *
 * @return string
 */
function solaire_current_url()
{
    if (is_singular()) {
        $link = get_permalink();
    } elseif (is_tax() || is_category() || is_tag()) {
        $link = get_term_link(get_queried_object());
    } elseif (is_post_type_archive()) {
        $link = get_post_type_archive_link(get_post_type());
    } else {
        $link = home_url('/');
    }

    return (!$link || is_wp_error($link)) ? home_url('/') : $link;
}

/**
 * Pull Q/A pairs out of free-form editor copy.
 *
 * Game "About" copy has no structured FAQ field -- editors write it as plain
 * paragraphs: a "Frequently Asked Questions" marker, then alternating
 * "1. Question?" / answer blocks. So the markup has to be walked.
 *
 * Returns [] whenever the content has no FAQ section, which is the common case.
 *
 * @param string $rendered_html Post content after the_content filters.
 * @return array Rows shaped for solaire_collect_faq_items().
 */
function solaire_parse_faq_pairs($rendered_html)
{
    if (!is_string($rendered_html) || '' === trim($rendered_html)) {
        return [];
    }

    // Cheap bail-out so content without an FAQ never pays for DOM parsing.
    if (!preg_match('/frequently\s+asked\s+questions|\bFAQs?\b/i', $rendered_html)) {
        return [];
    }

    /* Editors format the FAQ two ways: one paragraph per block, or a single
       paragraph with <br><br> between entries. Promoting every <br> to a
       paragraph break normalises both into the same flat list of blocks. */
    $normalised = preg_replace('/<br\s*\/?>/i', '</p><p>', $rendered_html);

    $dom  = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    // Force UTF-8: without the encoding hint DOMDocument assumes ISO-8859-1 and
    // mangles the curly quotes editor content is full of.
    $dom->loadHTML(
        '<?xml encoding="utf-8" ?><div>' . $normalised . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $xpath  = new DOMXPath($dom);
    $blocks = $xpath->query('//p | //h1 | //h2 | //h3 | //h4 | //h5 | //h6');
    if (!$blocks || 0 === $blocks->length) {
        return [];
    }

    $pairs    = [];
    $started  = false;
    $question = '';
    $answer   = [];

    foreach ($blocks as $block) {
        $text = trim(preg_replace('/\s+/u', ' ', $block->textContent));
        if ('' === $text) {
            continue;
        }

        // Everything before the "Frequently Asked Questions" marker is intro copy.
        if (!$started) {
            if (preg_match('/^(frequently\s+asked\s+questions|FAQs?)\b[:\s]*$/i', $text)) {
                $started = true;
            }
            continue;
        }

        // A question is either numbered ("1. What is X?") or a heading ending in "?".
        $is_heading = (bool) preg_match('/^h[1-6]$/i', $block->nodeName);
        $numbered   = preg_match('/^\d+\s*[\.\)]\s*(.+)$/u', $text, $m);

        if ($numbered || ($is_heading && '?' === substr($text, -1))) {
            // Flush the previous pair before starting the next one.
            if ('' !== $question && $answer) {
                $pairs[] = [
                    'question' => $question,
                    'answer'   => implode(' ', $answer),
                ];
            }
            $question = $numbered ? trim($m[1]) : $text;
            $answer   = [];
            continue;
        }

        if ('' !== $question) {
            $answer[] = $text;
        }
    }

    if ('' !== $question && $answer) {
        $pairs[] = [
            'question' => $question,
            'answer'   => implode(' ', $answer),
        ];
    }

    return $pairs;
}

/* -------------------------------------------------------------------------
 * 3. SoftwareApplication
 *
 * One node per single game page, built from the game's ACF fields and
 * taxonomies. Joins the graph so publisher can reference the Organization
 * node by @id rather than restating the company.
 * ---------------------------------------------------------------------- */

/**
 * Build the SoftwareApplication node for the game being viewed.
 *
 * Reads the raw ACF fields, never the display defaults single-game.php falls
 * back to -- an invented RTP or provider is a false claim in structured data
 * even though it is harmless as page copy.
 *
 * @return array Empty when this is not a single game page.
 */
function solaire_game_software_application_node()
{
    if (!is_singular('game')) {
        return [];
    }

    $post_id = get_the_ID();
    if (!$post_id) {
        return [];
    }

    $get = static function ($key) use ($post_id) {
        $value = function_exists('get_field')
            ? get_field($key, $post_id)
            : get_post_meta($post_id, $key, true);

        return trim(wp_strip_all_tags((string) $value));
    };

    $permalink = get_permalink($post_id);

    $node = [
        '@type'               => 'SoftwareApplication',
        '@id'                 => $permalink . '#game',
        'name'                => get_the_title($post_id),
        'url'                 => $permalink,
        'applicationCategory' => 'GameApplication',
        'operatingSystem'     => 'Web browser',
        'publisher'           => ['@id' => solaire_organization_id()],
    ];

    // No offers / aggregateRating node here, and that is deliberate: without
    // them Google will not render a Software App rich result. These are
    // real-money games with a free demo, so a zero-price Offer would surface a
    // "Free" label that misdescribes the product. Ratings wait until real
    // user ones exist -- inventing them is exactly what Google penalises.

    $description = $get('so_game_short_description');
    if ('' === $description) {
        $description = trim(wp_strip_all_tags(get_the_excerpt($post_id)));
    }
    if ('' !== $description) {
        $node['description'] = $description;
    }

    $image = get_the_post_thumbnail_url($post_id, 'large');
    if ($image) {
        $node['image'] = $image;
    }

    // The studio that made the game, as distinct from the site operating it.
    // Prefer the taxonomy term (canonical, shared across games) over the free
    // text field, and never fall back to the "Solaire Online" display default.
    $provider = '';
    $provider_terms = get_the_terms($post_id, 'provider');
    if ($provider_terms && !is_wp_error($provider_terms)) {
        $provider = $provider_terms[0]->name;
    }
    if ('' === $provider) {
        $provider = $get('provider');
    }
    if ('' !== $provider) {
        $node['author'] = [
            '@type' => 'Organization',
            'name'  => $provider,
        ];
    }

    $terms = get_the_terms($post_id, 'game_category');
    if ($terms && !is_wp_error($terms)) {
        $node['applicationSubCategory'] = $terms[0]->name;
        $node['genre']                  = array_values(wp_list_pluck($terms, 'name'));
    }

    // RTP and volatility have no schema.org equivalents, so they ride along as
    // additionalProperty rather than being forced into an unrelated field.
    $extras = [];

    $rtp = $get('rtp');
    if ('' !== $rtp) {
        $extras[] = [
            '@type' => 'PropertyValue',
            'name'  => 'RTP',
            // The field holds either "96.2" or "96.2%"; normalise to one form.
            'value' => rtrim($rtp, '% ') . '%',
        ];
    }

    $volatility = $get('volatility');
    // "Select Volatility" is the ACF placeholder choice, not a real value.
    if ('' !== $volatility && 'Select Volatility' !== $volatility) {
        $extras[] = [
            '@type' => 'PropertyValue',
            'name'  => 'Volatility',
            'value' => $volatility,
        ];
    }

    if ($extras) {
        $node['additionalProperty'] = $extras;
    }

    return $node;
}

/**
 * Append the game node to the theme's own graph.
 *
 * @param array $graph Schema graph.
 * @return array
 */
function solaire_add_game_to_graph($graph)
{
    $node = solaire_game_software_application_node();
    if ($node) {
        $graph[] = $node;
    }

    return $graph;
}
add_filter('solaire_schema_graph', 'solaire_add_game_to_graph', 10, 1);

/**
 * Append the game node to Yoast's graph, pointing publisher at Yoast's
 * Organization node instead of ours.
 *
 * @param array $graph Yoast schema graph.
 * @return array
 */
function solaire_add_game_to_yoast_graph($graph)
{
    $node = solaire_game_software_application_node();
    if (!$node) {
        return $graph;
    }

    foreach ($graph as $existing) {
        if (!empty($existing['@id']) && !empty($existing['@type'])
            && in_array('Organization', (array) $existing['@type'], true)) {
            $node['publisher'] = ['@id' => $existing['@id']];
            break;
        }
    }

    $graph[] = $node;

    return $graph;
}
add_filter('wpseo_schema_graph', 'solaire_add_game_to_yoast_graph', 11, 1);

/* -------------------------------------------------------------------------
 * 5. HowTo
 *
 * Currently fed by the "Game Rules & Mechanics" repeater on single-game.php,
 * but built as a collector so any future how-to-play section -- a block, a
 * page template, another repeater -- can register steps without touching this
 * file's printer.
 *
 * Unlike FAQPage, several HowTo nodes on one page are valid, so each section
 * gets its own @id rather than being merged.
 * ---------------------------------------------------------------------- */

/**
 * Register a how-to section for this request.
 *
 * Call with no arguments to read what has been collected.
 *
 * @param string $name        Heading for the instructions.
 * @param string $description Optional intro copy.
 * @param array  $steps       Rows with 'title' and/or 'description' keys.
 * @return array Everything collected so far.
 */
function solaire_collect_howto($name = '', $description = '', $steps = [])
{
    static $collected = [];

    $name = trim(wp_strip_all_tags((string) $name));
    if ('' !== $name && $steps && is_array($steps)) {
        $collected[] = [
            'name'        => $name,
            'description' => trim(wp_strip_all_tags((string) $description)),
            'steps'       => $steps,
        ];
    }

    return $collected;
}

/**
 * Turn a collected section into a HowTo node.
 *
 * @param array $section Row from solaire_collect_howto().
 * @param int   $index   Position on the page, for a unique @id.
 * @return array Empty when the steps do not describe a real procedure.
 */
function solaire_build_howto_node($section, $index)
{
    $how_to_steps = [];
    $position     = 0;

    foreach ($section['steps'] as $step) {
        $step_name = trim(wp_strip_all_tags((string) ($step['title'] ?? '')));
        $step_text = trim(wp_strip_all_tags((string) ($step['description'] ?? ''), true));

        if ('' === $step_name && '' === $step_text) {
            continue;
        }

        ++$position;

        $how_to_step = [
            '@type'    => 'HowToStep',
            'position' => $position,
        ];
        if ('' !== $step_name) {
            $how_to_step['name'] = $step_name;
        }
        // text is required, so fall back to the title when the editor filled
        // only one of the two fields in.
        $how_to_step['text'] = '' !== $step_text ? $step_text : $step_name;

        $how_to_steps[] = $how_to_step;
    }

    // A single step is not a procedure worth describing.
    if (count($how_to_steps) < 2) {
        return [];
    }

    $node = [
        '@type' => 'HowTo',
        '@id'   => solaire_current_url() . '#howto-' . $index,
        'name'  => $section['name'],
        'step'  => $how_to_steps,
    ];

    if ('' !== $section['description']) {
        $node['description'] = $section['description'];
    }

    return $node;
}

/**
 * Register the game rules repeater as a how-to section.
 *
 * Runs before the printer so the steps are collected in time. Emits nothing
 * until the repeater carries at least two rules with copy.
 */
function solaire_register_game_rules_howto()
{
    if (!is_singular('game')) {
        return;
    }

    $post_id = get_the_ID();
    $rules   = function_exists('get_field') ? get_field('rules', $post_id) : [];
    if (!$rules || !is_array($rules)) {
        return;
    }

    // The repeater uses its own field names, so map them onto the shape
    // solaire_build_howto_node() expects.
    $steps = [];
    foreach ($rules as $rule) {
        $steps[] = [
            'title'       => $rule['title'] ?? '',
            'description' => $rule['text'] ?? '',
        ];
    }

    $short_description = function_exists('get_field')
        ? (string) get_field('so_game_short_description', $post_id)
        : '';

    solaire_collect_howto(
        /* translators: %s: game title. */
        sprintf(__('How to Play %s', 'solaire'), get_the_title($post_id)),
        $short_description,
        $steps
    );
}
add_action('wp_footer', 'solaire_register_game_rules_howto', 5);

/**
 * Print a HowTo node for every collected section.
 */
function solaire_print_howto_schema()
{
    $sections = solaire_collect_howto();
    if (!$sections) {
        return;
    }

    $index = 0;
    foreach ($sections as $section) {
        ++$index;
        $node = solaire_build_howto_node($section, $index);
        if (!$node) {
            continue;
        }

        $node = ['@context' => 'https://schema.org'] + $node;

        echo '<script type="application/ld+json">'
            . wp_json_encode($node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            . '</script>' . "\n";
    }
}
add_action('wp_footer', 'solaire_print_howto_schema', 20);
