<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/github/explore/80688e429a7d4ef2fca1e82350fe8e3517d3494d/topics/wordpress/wordpress.png" width="100" alt="WordPress Logo"></a></p>


# WordPress - Multisite Alternate Language Tags

[![Licence](https://img.shields.io/badge/LICENSE-GPL2.0+-blue)](./LICENSE)

- Developed by: [Martin Nestorov](https://github.com/mnestorov)
- Plugin URI: https://github.dev/mnestorov/wp-multisite-alternate-lang-tags

## Overview

**WordPress - Multisite Alternate Language Tags** is a plugin designed to handle alternate language tags (`hreflang`) for WordPress multisite environments. This helps with SEO and multilingual site detection, making sure search engines know the language versions of each page across your network.

## Features

- Automatically generates `hreflang` tags for pages, posts, and products across different sites in the multisite network.
- Matches product pages using SKUs and info pages using slugs.
- Caches the generated `hreflang` tags for improved performance, using WordPress transients.
- Adds a network-wide settings page for excluding specific sites and languages.
- Adds support for `x-default` language tags for default pages.
- Includes an option to periodically refresh the cached data using WordPress cron jobs.
- Fully compatible with WooCommerce and its product pages.
- Excludes dynamic WooCommerce pages (e.g., checkout, cart, thank you pages) from alternate language tag generation.

## Installation

1. Download the plugin and upload it to your WordPress multisite environment.
2. Activate the plugin via the Network Admin under "Plugins".
3. Once activated, navigate to the Network Admin settings to configure the language tag settings:
   - Exclude specific sites (such as development or non-production sites).
   - Exclude specific languages.
   - Set a default site for `x-default` language tags.

## Usage

Once installed and configured, the plugin will automatically generate `hreflang` tags for each post, page, product, or shop page in your multisite environment. The tags will indicate the alternate language versions of a page, improving SEO and multilingual compatibility.

### Admin Panel

You can configure the plugin under:
- **Network Settings**: Set global options such as excluded sites, languages, and the default site for the `x-default` tag.
- **Site Settings**: Customize site-specific language tag behavior.

## Function Descriptions

### `mn_add_hreflang_tags()`

Adds `hreflang` tags to the `<head>` of the website for alternate language versions of pages, posts, and products across the multisite network.

- Retrieves the current blog ID and a list of all public sites in the multisite network.
- Generates the appropriate `hreflang` tags by matching posts using slugs or SKUs (for WooCommerce products).
- Outputs the `hreflang` tags in the page header for SEO purposes.

### `mn_enqueue_locale_script()`

Enqueues a script (`detect-locale.js`) to detect the user's locale unless the user is logged in or viewing a checkout/cart page.

- Only enqueues the script for non-logged-in users.
- Ensures the script is not loaded on WooCommerce checkout or cart pages.

### `mn_find_alternate_page($current_post_id, $site_id)`

Finds the alternate version of a page or product on another site in the multisite network.

- For product pages, it matches based on SKU.
- For info pages (like About, Contact), it matches based on the slug.

### `mn_create_options_page()`

Creates an options page for the plugin under the WordPress admin dashboard. The settings page allows administrators to manage the plugin's behavior.

- Registers the settings page for site-specific options.
- The page allows administrators to configure language settings for the current site.

### `mn_create_network_settings_page()`

Creates a settings page in the WordPress Network Admin dashboard. This page is used to manage global settings for the entire multisite network.

- Allows network admins to configure excluded sites, excluded languages, and the default site for `x-default` hreflang tags.

### `mn_render_options_page()`

Renders the plugin's options page for site-specific settings.

- Displays the form and fields for site administrators to configure site-specific language tag settings.

### `mn_render_network_settings_page()`

Renders the network-wide settings page.

- Displays input fields to exclude sites or languages, and to set a default site for the `x-default` hreflang tag.
- Processes form submissions to update the network settings.

### `mn_save_network_settings()`

Handles saving network-wide settings for excluded sites, excluded languages, and the default site.

- Validates the settings and updates the network options.
- Ensures the user has the proper permissions to modify network settings.

### `mn_register_settings()`

Registers the settings fields for site-specific configurations.

- Registers settings for excluding specific sites or languages and defining a default site for the `x-default` tag.

### `mn_get_cached_hreflang_tags()`

Retrieves the cached `hreflang` tags using WordPress transients to improve performance.

- If the tags are not cached, it regenerates them and stores them in the transient cache for 12 hours.
- Ensures hreflang tags are served efficiently without regenerating them on every request.

### `mn_regenerate_transient_for_all_sites()`

Regenerates the cached `hreflang` tags for all sites in the network.

- Iterates through each public site in the network.
- Deletes the existing transient for each site and regenerates the `hreflang` tags.
- This function is called by a cron job to keep the cached data fresh.

### `mn_deactivate_plugin()`

Cleans up scheduled tasks when the plugin is deactivated.

- Removes the scheduled cron job for regenerating the transient cache when the plugin is deactivated.

### `mn_activate_plugin()`

Sets up default network settings when the plugin is activated.

- Initializes default values for excluded sites, excluded languages, and the default site for the `x-default` tag.

## Hooks and Customization

### Hooks

- **`mn_add_hreflang_tags`**: Allows you to add custom logic or modify the hreflang generation behavior.
- **`mn_enqueue_locale_script`**: Lets you enqueue the locale detection script conditionally.
- **`mn_regenerate_transient_for_all_sites`**: Hook for regenerating the cached hreflang tags across all sites in the network.

### Transients

The plugin caches the generated `hreflang` tags using WordPress transients for faster page loading. These transients are refreshed automatically every 12 hours (or based on the scheduled cron job).


## Requirements

- WordPress 4.7+ or higher.
- WooCommerce 5.1.0 or higher (optional, but recommended for product sites).
- PHP 7.2+

## Changelog

For a detailed list of changes and updates made to this project, please refer to our [Changelog](./CHANGELOG.md).

---

## License

This project is released under the [GPL-2.0+ License](http://www.gnu.org/licenses/gpl-2.0.txt).
