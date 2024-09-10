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
