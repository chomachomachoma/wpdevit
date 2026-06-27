<?php
/**
 * Custom REST API Endpoints
 */

defined('ABSPATH') || exit;

function wpdevit_register_rest_routes() {
    $namespace = 'wpdevit/v1';

    // Site info endpoint
    register_rest_route($namespace, '/site-info', [
        'methods'             => 'GET',
        'callback'            => 'wpdevit_rest_site_info',
        'permission_callback' => '__return_true',
    ]);

    // Contact form endpoint
    register_rest_route($namespace, '/contact', [
        'methods'             => 'POST',
        'callback'            => 'wpdevit_rest_contact',
        'permission_callback' => '__return_true',
        'args'                => [
            'name'    => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'email'   => [
                'required'          => true,
                'validate_callback' => 'is_email',
                'sanitize_callback' => 'sanitize_email',
            ],
            'message' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
        ],
    ]);

    // Menus endpoint
    register_rest_route($namespace, '/menus/(?P<location>[a-zA-Z0-9_-]+)', [
        'methods'             => 'GET',
        'callback'            => 'wpdevit_rest_menu',
        'permission_callback' => '__return_true',
        'args'                => [
            'location' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);

    // Featured plugins endpoint
    register_rest_route($namespace, '/plugins/featured', [
        'methods'             => 'GET',
        'callback'            => 'wpdevit_rest_featured_plugins',
        'permission_callback' => '__return_true',
    ]);

    // Normalized products list (all plugins, fully resolved meta)
    register_rest_route($namespace, '/products', [
        'methods'             => 'GET',
        'callback'            => 'wpdevit_rest_products',
        'permission_callback' => '__return_true',
    ]);

    // Single normalized product by slug
    register_rest_route($namespace, '/product/(?P<slug>[a-zA-Z0-9_-]+)', [
        'methods'             => 'GET',
        'callback'            => 'wpdevit_rest_product',
        'permission_callback' => '__return_true',
        'args'                => [
            'slug' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_title',
            ],
        ],
    ]);
}
add_action('rest_api_init', 'wpdevit_register_rest_routes');

/**
 * Site info callback.
 */
function wpdevit_rest_site_info() {
    return rest_ensure_response([
        'name'        => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url'         => home_url('/'),
    ]);
}

/**
 * Get menu items for a registered menu location.
 */
function wpdevit_rest_menu($request) {
    $location = $request->get_param('location');
    return rest_ensure_response(wpdevit_get_menu_items($location));
}

/**
 * Resolve menu items for a given theme location.
 * Returns a flat array with parent/children structure.
 */
function wpdevit_get_menu_items($location) {
    $locations = get_nav_menu_locations();

    if (empty($locations[$location])) {
        return [];
    }

    $menu  = wp_get_nav_menu_object($locations[$location]);
    $items = wp_get_nav_menu_items($menu->term_id);

    if (!$items) {
        return [];
    }

    $site_url = home_url();
    $result   = [];

    foreach ($items as $item) {
        // Convert absolute URLs to relative paths for SPA routing
        $url = $item->url;
        if (str_starts_with($url, $site_url)) {
            $url = wp_make_link_relative($url);
        }

        $entry = [
            'id'       => $item->ID,
            'title'    => $item->title,
            'url'      => $url,
            'target'   => $item->target ?: '',
            'parent'   => (int) $item->menu_item_parent,
            'children' => [],
        ];

        $result[] = $entry;
    }

    // Build parent/children tree
    $tree    = [];
    $by_id   = [];

    foreach ($result as &$entry) {
        $by_id[$entry['id']] = &$entry;
    }
    unset($entry);

    foreach ($result as &$entry) {
        if ($entry['parent'] && isset($by_id[$entry['parent']])) {
            $by_id[$entry['parent']]['children'][] = &$entry;
        } else {
            $tree[] = &$entry;
        }
    }
    unset($entry);

    return $tree;
}

/**
 * Contact form callback.
 */
function wpdevit_rest_contact($request) {
    $name    = $request->get_param('name');
    $email   = $request->get_param('email');
    $message = $request->get_param('message');

    $to      = get_option('admin_email');
    $subject = sprintf('[WPDevIT] Contact from %s', $name);
    $body    = sprintf(
        "Name: %s\nEmail: %s\n\nMessage:\n%s",
        $name,
        $email,
        $message
    );
    $headers = ['Reply-To: ' . $name . ' <' . $email . '>'];

    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        return rest_ensure_response([
            'success' => true,
            'message' => 'Your message has been sent successfully.',
        ]);
    }

    return new WP_Error(
        'mail_failed',
        'Failed to send message. Please try again later.',
        ['status' => 500]
    );
}

/**
 * Decode a JSON-encoded meta value, returning a fallback on empty/invalid input.
 */
function wpdevit_decode_meta($post_id, $key, $fallback = []) {
    $raw = get_post_meta($post_id, $key, true);
    if (empty($raw)) {
        return $fallback;
    }
    $decoded = json_decode($raw, true);
    return (json_last_error() === JSON_ERROR_NONE && $decoded !== null) ? $decoded : $fallback;
}

/**
 * Normalize a plugin post into a flat product object for the SPA.
 *
 * @param WP_Post $post
 * @param bool    $full Include heavy fields (content, faq, changelog).
 * @return array
 */
function wpdevit_normalize_product($post, $full = true) {
    $id = $post->ID;

    $product = [
        'id'             => $id,
        'title'          => get_the_title($post),
        'slug'           => $post->post_name,
        'excerpt'        => wp_strip_all_tags(get_the_excerpt($post)),
        'tagline'        => get_post_meta($id, '_wpdevit_tagline', true),
        'featured_image' => get_the_post_thumbnail_url($id, 'plugin-hero') ?: get_the_post_thumbnail_url($id, 'full') ?: null,
        'card_image'     => get_the_post_thumbnail_url($id, 'plugin-card') ?: null,
        'price'          => get_post_meta($id, '_wpdevit_price', true),
        'version'        => get_post_meta($id, '_wpdevit_version', true),
        'download_url'   => get_post_meta($id, '_wpdevit_download_url', true),
        'demo_url'       => get_post_meta($id, '_wpdevit_demo_url', true),
        'demo_type'      => get_post_meta($id, '_wpdevit_demo_type', true),
        'is_featured'    => get_post_meta($id, '_wpdevit_is_featured', true) === '1',
        'features'       => wpdevit_decode_meta($id, '_wpdevit_features'),
        'highlights'     => wpdevit_decode_meta($id, '_wpdevit_highlights'),
        'screenshots'    => wpdevit_decode_meta($id, '_wpdevit_screenshots'),
        'pricing'        => wpdevit_decode_meta($id, '_wpdevit_pricing'),
        'freemius'       => wpdevit_decode_meta($id, '_wpdevit_freemius', null),
        'requirements'   => wpdevit_decode_meta($id, '_wpdevit_requirements', null),
    ];

    if ($full) {
        $product['content']   = apply_filters('the_content', $post->post_content);
        $product['faq']       = wpdevit_decode_meta($id, '_wpdevit_faq');
        $product['changelog'] = wpdevit_decode_meta($id, '_wpdevit_changelog');
    }

    return $product;
}

/**
 * Featured plugins callback.
 */
function wpdevit_rest_featured_plugins() {
    $query = new WP_Query([
        'post_type'      => 'wpdevit_plugin',
        'posts_per_page' => 3,
        'meta_key'       => '_wpdevit_is_featured',
        'meta_value'     => '1',
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
    ]);

    $plugins = array_map(
        function ($post) { return wpdevit_normalize_product($post, false); },
        $query->posts
    );

    return rest_ensure_response($plugins);
}

/**
 * All products (normalized) callback.
 */
function wpdevit_rest_products() {
    $query = new WP_Query([
        'post_type'      => 'wpdevit_plugin',
        'posts_per_page' => 100,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
    ]);

    $plugins = array_map(
        function ($post) { return wpdevit_normalize_product($post, false); },
        $query->posts
    );

    return rest_ensure_response($plugins);
}

/**
 * Single product (normalized) callback.
 */
function wpdevit_rest_product($request) {
    $slug = $request->get_param('slug');

    $query = new WP_Query([
        'post_type'      => 'wpdevit_plugin',
        'name'           => $slug,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
    ]);

    if (empty($query->posts)) {
        return new WP_Error('not_found', 'Product not found.', ['status' => 404]);
    }

    return rest_ensure_response(wpdevit_normalize_product($query->posts[0], true));
}
