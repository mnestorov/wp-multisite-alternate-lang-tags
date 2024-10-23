<?php
/**
 * Plugin Name: MN - Multisite Alternate Language Tags
 * Plugin URI:  https://github.com/mnestorov/wp-multisite-alternate-lang-tags
 * Description: Handles alternate language tags for a WordPress multisite environment.
 * Version:     1.0.0
 * Author:      Martin Nestorov
 * Author URI:  https://github.com/mnestorov
 * License:     GPL2
 * Text Domain: mn-multisite-alternate-lang-tags
 * Network:     true
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure this is a multisite environment
if (!is_multisite()) {
    return;
}

/**
 * Dynamically generate hreflang tags for all sites in a WordPress multisite network.
 */
/* NEED SOME TESTING
function mn_dynamic_hreflang_tags() {
    global $post;
    $sites = get_sites(['public' => 1]); // Fetch all public sites within the network
    $current_url = (is_singular()) ? get_permalink($post->ID) : home_url(add_query_arg(null, null));

    $site_urls = [];
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);
        $language_code = get_bloginfo('language');
        $site_urls[$language_code] = home_url($current_url);
        restore_current_blog();
    }

    $default_language = 'en-US'; // Set the default x-default language code
    foreach ($site_urls as $lang => $url) {
        echo '<link rel="alternate" hreflang="' . esc_attr($lang) . '" href="' . esc_url($url) . '" />' . "\n";
    }

    // x-default should point to the English site or another designated default
    if (isset($site_urls[$default_language])) {
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($site_urls[$default_language]) . '" />' . "\n";
    }
}
add_action('wp_head', 'mn_dynamic_hreflang_tags');
*/


/**
 * Add hreflang tags dynamically to <head>
 */
function mn_add_hreflang_tags() {
    global $post;

    // Define your site IDs (you can also retrieve this dynamically if needed)
    $english_site_id   = 1;  // English site
    $bulgarian_site_id = 2;  // Bulgarian site
    $german_site_id    = 3;  // German site

    // Dynamically determine the base URLs for each language/site based on site ID
    $english_url    = trailingslashit(get_home_url($english_site_id));  // English site URL
    $bulgarian_url  = trailingslashit(get_home_url($bulgarian_site_id));  // Bulgarian site URL
    $german_url     = trailingslashit(get_home_url($german_site_id));  // German site URL

    // Determine the current URL or path
    if (is_home() || is_front_page()) {
        $current_url = get_home_url(null, '/');  // Homepage
    } elseif (function_exists('is_shop') && is_shop()) {
        $current_url = get_permalink(wc_get_page_id('shop'));  // WooCommerce shop page
    } elseif ($post && $post->post_type === 'product') {
        $current_url = get_permalink($post->ID);  // Product page
    } else {
        $current_url = get_permalink(get_queried_object_id());  // Any other page/post
    }

    // Extract the path from the current URL to append it to alternate URLs
    $path = str_replace(trailingslashit(get_site_url()), '', $current_url);

    // Get current site ID
    $current_blog_id = get_current_blog_id();

    // Output hreflang tags based on the current site
    if ($current_blog_id == $bulgarian_site_id) {  // Bulgarian site
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($english_url . $path) . '"/>' . "\n";
        echo '<link rel="alternate" hreflang="bg-BG" href="' . esc_url($bulgarian_url . $path) . '"/>' . "\n";
        echo '<link rel="alternate" hreflang="de-DE" href="' . esc_url($german_url . $path) . '"/>' . "\n";
    } elseif ($current_blog_id == $english_site_id) {  // English site
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($english_url . $path) . '"/>' . "\n";
        echo '<link rel="alternate" hreflang="bg-BG" href="' . esc_url($bulgarian_url . $path) . '"/>' . "\n";
        echo '<link rel="alternate" hreflang="de-DE" href="' . esc_url($german_url . $path) . '"/>' . "\n";
    } elseif ($current_blog_id == $german_site_id) {  // German site
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($english_url . $path) . '"/>' . "\n";
        echo '<link rel="alternate" hreflang="bg-BG" href="' . esc_url($bulgarian_url . $path) . '"/>' . "\n";
        echo '<link rel="alternate" hreflang="de-DE" href="' . esc_url($german_url . $path) . '"/>' . "\n";
    }
}
add_action('wp_head', 'mn_add_hreflang_tags');

/**
 * Enqueue detect-locale.js script.
 */
function mn_enqueue_locale_script() {
    if (!is_user_logged_in() && !is_checkout() && !is_cart()) {
        wp_enqueue_script('detect-locale', 'https://lesthe.com/cdn/plugins/language-selector/js/detect-locale.js', [], null, true);
    }
}
add_action('wp_enqueue_scripts', 'mn_enqueue_locale_script');

/**
 * Find alternate page by SKU or slug.
 */
function mn_find_alternate_page($current_post_id, $site_id) {
    switch_to_blog($site_id);

    // Handle product pages (match by SKU)
    if (get_post_type($current_post_id) === 'product') {
        $sku = get_post_meta($current_post_id, '_sku', true);
        if ($sku) {
            $args = [
                'post_type' => 'product',
                'meta_query' => [
                    [
                        'key'   => '_sku',
                        'value' => $sku,
                        'compare' => '='
                    ]
                ]
            ];
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                $alternate_post = $query->posts[0];
                restore_current_blog();
                return get_permalink($alternate_post->ID);
            }
        }
    }

    // Handle info pages (match by slug)
    $slug = get_post_field('post_name', $current_post_id);
    $alternate_post = get_page_by_path($slug);
    if ($alternate_post) {
        restore_current_blog();
        return get_permalink($alternate_post->ID);
    }

    restore_current_blog();
    return false;
}

/**
 * Admin page for plugin settings.
 */
function mn_create_options_page() {
    add_options_page(
        __('Alternate Language Tags Settings', 'mn-multisite-alternate-lang-tags'),
        __('Alternate Lang Tags', 'mn-multisite-alternate-lang-tags'),
        'manage_options',
        'malt-settings',
        'mn_render_options_page'
    );
}
add_action('admin_menu', 'mn_create_options_page');

/**
 * Network admin page for network-wide settings.
 */
function mn_create_network_settings_page() {
    add_submenu_page(
        'settings.php',
        __('Network Language Settings', 'mn-multisite-alternate-lang-tags'),
        __('Network Lang Tags', 'mn-multisite-alternate-lang-tags'),
        'manage_network_options',
        'network-lang-tags',
        'mn_render_network_settings_page'
    );
}
add_action('network_admin_menu', 'mn_create_network_settings_page');

/**
 * Render the plugin options page.
 */
function mn_render_options_page() {
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Alternate Language Tags Settings', 'mn-multisite-alternate-lang-tags') . '</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('mn_alt_lang_settings');
    do_settings_sections('mn_alt_lang_settings');
    submit_button();
    echo '</form>';
    echo '</div>';
}

/**
 * Render the network settings page.
 */
function mn_render_network_settings_page() {
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Network Alternate Language Tags Settings', 'mn-multisite-alternate-lang-tags') . '</h1>';
    echo '<form method="post" action="edit.php?action=update_network_settings">';
    wp_nonce_field('update_network_settings_nonce');

    // Excluded Sites
    echo '<h2>' . esc_html__('Excluded Sites', 'mn-multisite-alternate-lang-tags') . '</h2>';
    $excluded_sites = get_site_option('mn_excluded_sites', []);
    echo '<input type="text" name="mn_excluded_sites" value="' . esc_attr(implode(',', $excluded_sites)) . '" placeholder="Enter site IDs separated by commas" />';

    // Excluded Languages
    echo '<h2>' . esc_html__('Excluded Languages', 'mn-multisite-alternate-lang-tags') . '</h2>';
    $excluded_languages = get_site_option('mn_excluded_languages', []);
    echo '<input type="text" name="mn_excluded_languages" value="' . esc_attr(implode(',', $excluded_languages)) . '" placeholder="Enter language codes separated by commas (e.g., en-US, bg-BG)" />';

    // Default Site for x-default
    echo '<h2>' . esc_html__('Default Site for x-default', 'mn-multisite-alternate-lang-tags') . '</h2>';
    $default_site = get_site_option('mn_default_site', '');
    echo '<input type="text" name="mn_default_site" value="' . esc_attr($default_site) . '" placeholder="Enter default site ID" />';

    // Enable/Disable Caching
    echo '<h2>' . esc_html__('Enable Caching', 'mn-multisite-alternate-lang-tags') . '</h2>';
    $enable_caching = get_site_option('mn_enable_caching', true);
    echo '<input type="checkbox" name="mn_enable_caching" value="1"' . checked(1, $enable_caching, false) . ' /> Enable caching';

    submit_button(__('Save Network Settings', 'mn-multisite-alternate-lang-tags'));
    echo '</form>';
}

/**
 * Save network settings.
 */
function mn_save_network_settings() {
    if (!current_user_can('manage_network_options') || !check_admin_referer('update_network_settings_nonce')) {
        return;
    }

    if (isset($_POST['mn_excluded_sites'])) {
        $excluded_sites = array_map('intval', explode(',', sanitize_text_field($_POST['mn_excluded_sites'])));
        update_site_option('mn_excluded_sites', $excluded_sites);
    }

    if (isset($_POST['mn_excluded_languages'])) {
        $excluded_languages = array_map('sanitize_text_field', explode(',', sanitize_text_field($_POST['mn_excluded_languages'])));
        update_site_option('mn_excluded_languages', $excluded_languages);
    }

    if (isset($_POST['mn_default_site'])) {
        $default_site = intval(sanitize_text_field($_POST['mn_default_site']));
        update_site_option('mn_default_site', $default_site);
    }

    wp_redirect(add_query_arg(['page' => 'network-lang-tags', 'updated' => 'true'], network_admin_url('settings.php')));
    exit;
}
add_action('network_admin_edit_update_network_settings', 'mn_save_network_settings');

/**
 * Register plugin settings
 */
function mn_register_settings() {
    register_setting('mn_alt_lang_settings', 'mn_excluded_sites');
    register_setting('mn_alt_lang_settings', 'mn_excluded_languages');
    register_setting('mn_alt_lang_settings', 'mn_default_site');
}
add_action('admin_init', 'mn_register_settings');

/**
 * Caching and transients.
 */
function mn_get_cached_hreflang_tags() {
    // Check if caching is enabled in the network settings
    $enable_caching = get_site_option('mn_enable_caching', true);

    // If caching is disabled, return hreflang tags without caching
    if (!$enable_caching) {
        ob_start();
        do_action('mn_add_hreflang_tags');
        return ob_get_clean(); // Return tags directly without caching
    }

    // Otherwise, proceed with caching
    $transient_key = 'hreflang_tags_' . get_current_blog_id();
    $cached_tags = get_transient($transient_key);

    if ($cached_tags === false) {
        ob_start();
        do_action('mn_add_hreflang_tags');
        $cached_tags = ob_get_clean();
        set_transient($transient_key, $cached_tags, 12 * HOUR_IN_SECONDS); // Cache for 12 hours
    }

    return $cached_tags;
}

// Cron job to regenerate the transient
if (!wp_next_scheduled('mn_regenerate_hreflang_transient')) {
    wp_schedule_event(time(), 'hourly', 'mn_regenerate_hreflang_transient');
}
add_action('mn_regenerate_hreflang_transient', 'mn_regenerate_transient_for_all_sites');

/**
 * Regenerate transient for all sites in the network.
 */
function mn_regenerate_transient_for_all_sites() {
    $sites = get_sites(['public' => 1]);
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);

        // Flush the transient for the current site
        delete_transient('hreflang_tags_' . get_current_blog_id());
        set_transient('hreflang_tags_' . get_current_blog_id(), mn_get_cached_hreflang_tags(), 12 * HOUR_IN_SECONDS);
        
        restore_current_blog();
    }
}

/**
 * Clean up scheduled tasks on plugin deactivation.
 */
function mn_deactivate_plugin() {
    $timestamp = wp_next_scheduled('mn_regenerate_hreflang_transient');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'mn_regenerate_hreflang_transient');
    }
}
register_deactivation_hook(__FILE__, 'mn_deactivate_plugin');

/**
 * Install default network settings on plugin activation.
 */
function mn_activate_plugin() {
    if (!get_site_option('mn_excluded_sites')) {
        update_site_option('mn_excluded_sites', []);
    }
    if (!get_site_option('mn_excluded_languages')) {
        update_site_option('mn_excluded_languages', []);
    }
    if (!get_site_option('mn_default_site')) {
        update_site_option('mn_default_site', 1); // Default to site ID 1 (main site)
    }
}
register_activation_hook(__FILE__, 'mn_activate_plugin');
