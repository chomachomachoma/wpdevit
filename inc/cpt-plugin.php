<?php
/**
 * Custom Post Type: Plugin Products
 */

defined('ABSPATH') || exit;

function wpdevit_register_plugin_cpt() {
    $labels = [
        'name'               => __('Plugins', 'wpdevit'),
        'singular_name'      => __('Plugin', 'wpdevit'),
        'add_new'            => __('Add New Plugin', 'wpdevit'),
        'add_new_item'       => __('Add New Plugin', 'wpdevit'),
        'edit_item'          => __('Edit Plugin', 'wpdevit'),
        'new_item'           => __('New Plugin', 'wpdevit'),
        'view_item'          => __('View Plugin', 'wpdevit'),
        'search_items'       => __('Search Plugins', 'wpdevit'),
        'not_found'          => __('No plugins found', 'wpdevit'),
        'not_found_in_trash' => __('No plugins found in Trash', 'wpdevit'),
        'menu_name'          => __('Plugins', 'wpdevit'),
    ];

    register_post_type('wpdevit_plugin', [
        'labels'       => $labels,
        'public'       => true,
        'has_archive'  => false,
        'show_in_rest' => true,
        'rest_base'    => 'wpdevit-plugins',
        'menu_icon'    => 'dashicons-admin-plugins',
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'rewrite'      => ['slug' => 'plugins', 'with_front' => false],
    ]);

    // Custom taxonomy for plugin categories
    register_taxonomy('plugin_category', 'wpdevit_plugin', [
        'labels'       => [
            'name'          => __('Plugin Categories', 'wpdevit'),
            'singular_name' => __('Plugin Category', 'wpdevit'),
        ],
        'public'       => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rest_base'    => 'plugin-categories',
        'rewrite'      => ['slug' => 'plugin-category'],
    ]);

    // Register meta fields exposed to REST API
    $meta_fields = [
        '_wpdevit_price'        => 'string',
        '_wpdevit_version'      => 'string',
        '_wpdevit_download_url' => 'string',
        '_wpdevit_demo_url'     => 'string',
        '_wpdevit_features'     => 'string', // JSON array
        '_wpdevit_is_featured'  => 'boolean',
        // Extended product fields (most are JSON-encoded strings, decoded by the
        // normalized /product endpoint in inc/rest-api.php).
        '_wpdevit_tagline'      => 'string', // short subtitle under the title
        '_wpdevit_screenshots'  => 'string', // JSON: [{ "src", "caption" }]
        '_wpdevit_pricing'      => 'string', // JSON: [{ name, price, period, plan_id, pricing_id, features[], highlighted, cta }]
        '_wpdevit_freemius'     => 'string', // JSON: { plugin_id, public_key, plan_id, pricing_id }
        '_wpdevit_demo_type'    => 'string', // 'gf-validation' | 'gf-restrictions' | ''
        '_wpdevit_faq'          => 'string', // JSON: [{ q, a }]
        '_wpdevit_changelog'    => 'string', // JSON: [{ version, date, notes[] }]
        '_wpdevit_requirements' => 'string', // JSON: { wp, php, gf }
        '_wpdevit_highlights'   => 'string', // JSON: [{ icon, title, text }]
    ];

    foreach ($meta_fields as $key => $type) {
        register_post_meta('wpdevit_plugin', $key, [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => $type,
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
}
add_action('init', 'wpdevit_register_plugin_cpt');
