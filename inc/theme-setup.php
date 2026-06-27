<?php
/**
 * Theme Setup
 */

defined('ABSPATH') || exit;

function wpdevit_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    add_image_size('plugin-card', 600, 400, true);
    add_image_size('plugin-hero', 1200, 600, true);

    register_nav_menus([
        'primary' => __('Primary Menu', 'wpdevit'),
        'footer'  => __('Footer Menu', 'wpdevit'),
    ]);
}
add_action('after_setup_theme', 'wpdevit_theme_setup');
