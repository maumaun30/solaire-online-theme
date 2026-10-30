<?php
/**
 * ACF field group for the Games CPT.
 *
 * Registered in PHP so the fields ship with the theme (no DB import).
 * Featured image = game artwork. These fields carry the metadata shown
 * on the category grid (RTP / Volatility) and the single game page
 * (stats bar, gold CTA links, rules grid).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Populate the Single Game custom-filter Select fields from the first assigned
 * game category's custom-filter template. The category remains the source of
 * truth; editors only select values that apply to the game.
 */
function solaire_load_game_custom_filter_choices($field)
{
    $post_id = (int) ($_GET['post'] ?? 0);
    if (!$post_id && function_exists('acf_get_form_data')) {
        $post_id = (int) acf_get_form_data('post_id');
    }
    if (!$post_id) {
        return $field;
    }

    $terms = wp_get_post_terms($post_id, 'game_category');
    if (is_wp_error($terms) || !$terms) {
        return $field;
    }

    foreach ($terms as $term) {
        $template = get_field('so_custom_filter_template', $term);
        if (empty($template['so_custom_game_filter']) || empty($template['so_custom_filter_repeater'])) {
            continue;
        }

        $field_map = [
            'so_first_custom_filter_values'  => 0,
            'so_second_custom_filter_values' => 1,
            'so_third_custom_filter_values'  => 2,
        ];
        $row_index = $field_map[$field['name']] ?? null;
        if ($row_index === null) {
            return $field;
        }

        $rows = array_values($template['so_custom_filter_repeater']);
        $row = $rows[$row_index] ?? null;
        if (!$row) {
            return $field;
        }

        $options = $row['so_filter_option_repeater'] ?? [];
        foreach ((array) $options as $option) {
            $value = is_array($option) ? ($option['so_filter_option_items'] ?? '') : $option;
            $value = trim((string) $value);
            if ($value !== '') {
                $field['choices'][$value] = $value;
            }
        }
        $field['multiple'] = 1;
        $field['ui'] = 1;
        return $field;
    }

    return $field;
}
add_filter('acf/load_field/name=so_first_custom_filter_values', 'solaire_load_game_custom_filter_choices');
add_filter('acf/load_field/name=so_second_custom_filter_values', 'solaire_load_game_custom_filter_choices');
add_filter('acf/load_field/name=so_third_custom_filter_values', 'solaire_load_game_custom_filter_choices');

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_solaire_game',
        'title'    => 'Game Details',
        'location' => [[[
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'game',
        ]]],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'fields'   => [
            [
                'key'   => 'field_solaire_provider',
                'label' => 'Provider',
                'name'  => 'provider',
                'type'  => 'text',
                'placeholder' => 'e.g. Solaire Studios',
            ],
            [
                'key'   => 'field_solaire_rtp',
                'label' => 'RTP',
                'name'  => 'rtp',
                'type'  => 'text',
                'placeholder' => 'e.g. 96.5%',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key'     => 'field_solaire_volatility',
                'label'   => 'Volatility',
                'name'    => 'volatility',
                'type'    => 'select',
                'choices' => ['Select Volatility' => 'Select Volatility', 'Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High'],
                'default_value' => 'Select Volatility',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key'           => 'field_solaire_demo_badge',
                'label'         => 'Show "Demo" badge',
                'name'          => 'demo_badge',
                'type'          => 'true_false',
                'default_value' => 1,
                'ui'            => 1,
            ],
            [
                'key'     => 'field_solaire_play_url',
                'label'   => 'Play now URL',
                'name'    => 'play_url',
                'type'    => 'url',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key'     => 'field_solaire_demo_url',
                'label'   => 'Demo URL',
                'name'    => 'demo_url',
                'type'    => 'url',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key'           => 'field_solaire_hero_bg',
                'label'         => 'Hero background (single page)',
                'name'          => 'hero_background',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
                'instructions'  => 'Optional textured background behind the game art on the single page. Falls back to the poker-chips banner.',
            ],
            [
                'key'          => 'field_solaire_stats',
                'label'        => 'Stats bar',
                'name'         => 'stats',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Add stat',
                'min'          => 0,
                'sub_fields'   => [
                    [
                        'key'   => 'field_solaire_stat_label',
                        'label' => 'Label',
                        'name'  => 'label',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_solaire_stat_value',
                        'label' => 'Value',
                        'name'  => 'value',
                        'type'  => 'text',
                    ],
                ],
            ],
            [
                'key'          => 'field_solaire_rules',
                'label'        => 'Game rules & mechanics',
                'name'         => 'rules',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Add rule',
                'min'          => 0,
                'sub_fields'   => [
                    [
                        'key'   => 'field_solaire_rule_title',
                        'label' => 'Title',
                        'name'  => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_solaire_rule_text',
                        'label' => 'Description',
                        'name'  => 'text',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                ],
            ],
        ],
    ]);
});

/**
 * Site Popups — cookie consent + responsible gaming notices.
 *
 * Registered in PHP so the popups ship with the theme. Content is editable on
 * a dedicated "Site Popups" options page; the partial in
 * template-parts/site-popups.php renders the markup and assets/js/solaire.js
 * handles first-visit display + dismissal (stored in localStorage).
 */
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Site Popups',
            'menu_title' => 'Site Popups',
            'menu_slug'  => 'solaire-site-popups',
            'capability' => 'manage_options',
            'icon_url'   => 'dashicons-megaphone',
            'position'   => 59,
            'redirect'   => false,
        ]);
    }

    acf_add_local_field_group([
        'key'      => 'group_solaire_popups',
        'title'    => 'Site Popups',
        'location' => [[[
            'param'    => 'options_page',
            'operator' => '==',
            'value'    => 'solaire-site-popups',
        ]]],
        'menu_order' => 0,
        'style'      => 'default',
        'fields'   => [
            /* ---- Cookie consent ---- */
            [
                'key'   => 'field_so_cookie_tab',
                'label' => 'Cookie Policy',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_so_cookie_enabled',
                'label'         => 'Enable cookie popup',
                'name'          => 'so_cookie_enabled',
                'type'          => 'true_false',
                'default_value' => 1,
                'ui'            => 1,
            ],
            [
                'key'           => 'field_so_cookie_title',
                'label'         => 'Title',
                'name'          => 'so_cookie_title',
                'type'          => 'text',
                'default_value' => 'Cookie Policy',
            ],
            [
                'key'           => 'field_so_cookie_message',
                'label'         => 'Message',
                'name'          => 'so_cookie_message',
                'type'          => 'textarea',
                'rows'          => 3,
                'instructions'  => 'Plain intro text. The policy links below are appended after this sentence.',
                'default_value' => 'Our website uses cookies to ensure you get the best experience. By continuing to browse this website, you are agreeing to our use of cookies. Please read our updated',
            ],
            [
                'key'           => 'field_so_cookie_link_text',
                'label'         => 'Policy link text',
                'name'          => 'so_cookie_link_text',
                'type'          => 'text',
                'default_value' => 'Privacy Policy',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'     => 'field_so_cookie_link_url',
                'label'   => 'Policy link URL',
                'name'    => 'so_cookie_link_url',
                'type'    => 'url',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key'           => 'field_so_cookie_link2_text',
                'label'         => 'Second link text',
                'name'          => 'so_cookie_link2_text',
                'type'          => 'text',
                'default_value' => 'Cookies Policy',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'     => 'field_so_cookie_link2_url',
                'label'   => 'Second link URL',
                'name'    => 'so_cookie_link2_url',
                'type'    => 'url',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key'           => 'field_so_cookie_button_text',
                'label'         => 'Accept button text',
                'name'          => 'so_cookie_button_text',
                'type'          => 'text',
                'default_value' => 'I Accept',
            ],
            /* ---- Responsible gaming ---- */
            [
                'key'   => 'field_so_rg_tab',
                'label' => 'Responsible Gaming',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_so_rg_enabled',
                'label'         => 'Enable responsible gaming popup',
                'name'          => 'so_rg_enabled',
                'type'          => 'true_false',
                'default_value' => 1,
                'ui'            => 1,
            ],
            [
                'key'           => 'field_so_rg_heading',
                'label'         => 'Heading',
                'name'          => 'so_rg_heading',
                'type'          => 'text',
                'default_value' => 'Responsible Gaming Guidelines',
            ],
            [
                'key'           => 'field_so_rg_intro',
                'label'         => 'Intro text',
                'name'          => 'so_rg_intro',
                'type'          => 'textarea',
                'rows'          => 2,
                'instructions'  => 'Wrap the emphasised phrase in *asterisks* to highlight it in orange (e.g. *NOT ALLOWED*).',
                'default_value' => 'The following persons are *NOT ALLOWED* to register and/or play in the online gaming website:',
            ],
            [
                'key'          => 'field_so_rg_items',
                'label'        => 'Restricted persons list',
                'name'         => 'so_rg_items',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Add item',
                'min'          => 0,
                'instructions' => 'Leave empty to use the built-in PAGCOR defaults. Add an optional URL to render the item as a link.',
                'sub_fields'   => [
                    [
                        'key'   => 'field_so_rg_item_text',
                        'label' => 'Text',
                        'name'  => 'text',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_so_rg_item_url',
                        'label' => 'Link URL (optional)',
                        'name'  => 'url',
                        'type'  => 'url',
                    ],
                ],
            ],
            [
                'key'           => 'field_so_rg_pagcor_image',
                'label'         => 'PAGCOR badge image',
                'name'          => 'so_rg_pagcor_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_so_rg_age_image',
                'label'         => '21+ badge image',
                'name'          => 'so_rg_age_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_so_rg_footer',
                'label'         => 'Footer text',
                'name'          => 'so_rg_footer',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'It is an offence in this jurisdiction or program with a found ineligible to play. Solaire Online cannot be held responsible to any of this requirement. Solaire Online gaming entrance. Playing in open and public places is prohibited.',
            ],
            [
                'key'           => 'field_so_rg_terms_text',
                'label'         => 'Terms link text',
                'name'          => 'so_rg_terms_text',
                'type'          => 'text',
                'default_value' => 'Terms & Conditions',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'     => 'field_so_rg_terms_url',
                'label'   => 'Terms link URL',
                'name'    => 'so_rg_terms_url',
                'type'    => 'url',
                'wrapper' => ['width' => '25'],
            ],
            [
                'key'           => 'field_so_rg_privacy_text',
                'label'         => 'Privacy link text',
                'name'          => 'so_rg_privacy_text',
                'type'          => 'text',
                'default_value' => 'Privacy Policy',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'     => 'field_so_rg_privacy_url',
                'label'   => 'Privacy link URL',
                'name'    => 'so_rg_privacy_url',
                'type'    => 'url',
                'wrapper' => ['width' => '25'],
            ],
            [
                'key'           => 'field_so_rg_accept_text',
                'label'         => 'Accept button text',
                'name'          => 'so_rg_accept_text',
                'type'          => 'text',
                'default_value' => 'Accept',
                'wrapper'       => ['width' => '33'],
            ],
            [
                'key'           => 'field_so_rg_decline_text',
                'label'         => 'Decline button text',
                'name'          => 'so_rg_decline_text',
                'type'          => 'text',
                'default_value' => 'Exit',
                'wrapper'       => ['width' => '33'],
            ],
            [
                'key'           => 'field_so_rg_decline_url',
                'label'         => 'Decline redirect URL',
                'name'          => 'so_rg_decline_url',
                'type'          => 'url',
                'instructions'  => 'Where visitors who do not accept are sent.',
                'default_value' => 'https://www.google.com',
                'wrapper'       => ['width' => '34'],
            ],
        ],
    ]);
});
