<?php
/**
 * WPDevIT Theme Functions
 */

defined('ABSPATH') || exit;

// Theme includes
require_once get_stylesheet_directory() . '/inc/theme-setup.php';
require_once get_stylesheet_directory() . '/inc/cpt-plugin.php';
require_once get_stylesheet_directory() . '/inc/rest-api.php';

/**
 * Enqueue Svelte app assets (dev or production).
 */
function wpdevit_enqueue_assets() {
    $manifest_path = get_stylesheet_directory() . '/dist/.vite/manifest.json';

    if (defined('WPDEVIT_DEV_MODE') && WPDEVIT_DEV_MODE) {
        // Dev mode: load from Vite dev server
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_enqueue_script('vite-client', 'http://localhost:5173/@vite/client', [], null, false);
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_enqueue_script('wpdevit-app', 'http://localhost:5173/src/main.js', [], null, true);
    } elseif (file_exists($manifest_path)) {
        // Production: read Vite manifest
        $manifest = json_decode(file_get_contents($manifest_path), true);

        if (!isset($manifest['src/main.js'])) {
            return;
        }

        $entry = $manifest['src/main.js'];
        $theme_uri = get_stylesheet_directory_uri();

        // Enqueue CSS files
        if (!empty($entry['css'])) {
            foreach ($entry['css'] as $i => $css_file) {
                wp_enqueue_style("wpdevit-app-{$i}", $theme_uri . '/dist/' . $css_file, [], null);
            }
        }

        // Enqueue JS entry
        wp_enqueue_script('wpdevit-app', $theme_uri . '/dist/' . $entry['file'], [], null, true);
    } else {
        return;
    }

    // Pass WordPress data to the Svelte app
    wp_localize_script('wpdevit-app', 'wpdevitData', [
        'restUrl'  => esc_url_raw(rest_url()),
        'nonce'    => wp_create_nonce('wp_rest'),
        'siteUrl'  => home_url('/'),
        'siteName' => get_bloginfo('name'),
        'menus'    => [
            'primary' => wpdevit_get_menu_items('primary'),
            'footer'  => wpdevit_get_menu_items('footer'),
        ],
    ]);
}
add_action('wp_enqueue_scripts', 'wpdevit_enqueue_assets');

/**
 * Add type="module" to Svelte script tags.
 */
function wpdevit_script_type_module($tag, $handle) {
    if (in_array($handle, ['wpdevit-app', 'vite-client'], true)) {
        $tag = str_replace(' src', ' type="module" src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'wpdevit_script_type_module', 10, 2);

/**
 * SPA catch-all: always serve index.php for frontend requests.
 */
function wpdevit_spa_template($template) {
    if (is_admin() || wp_doing_ajax()) {
        return $template;
    }

    // Don't intercept REST API requests
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return $template;
    }

    // Check if this is a REST API URL path
    $rest_prefix = rest_get_url_prefix();
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($request_uri, "/{$rest_prefix}/") !== false) {
        return $template;
    }

    return get_stylesheet_directory() . '/index.php';
}
add_filter('template_include', 'wpdevit_spa_template', 99);

/**
 * Disable canonical redirects for SPA routes.
 */
function wpdevit_disable_canonical_redirect($redirect_url) {
    if (is_admin() || wp_doing_ajax()) {
        return $redirect_url;
    }
    return false;
}
add_filter('redirect_canonical', 'wpdevit_disable_canonical_redirect');

/**
 * Remove admin bar spacing for SPA.
 */
function wpdevit_remove_admin_bar_bump() {
    remove_action('wp_head', '_admin_bar_bump_cb');
}
add_action('get_header', 'wpdevit_remove_admin_bar_bump');
