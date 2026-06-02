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
                'choices' => ['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High'],
                'default_value' => 'Medium',
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
