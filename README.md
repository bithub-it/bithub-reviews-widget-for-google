# BitHub Reviews Widget for Google

WordPress plugin that connects a Google Maps business to the [BitHub Reviews](https://bithub.it/en/tools/google-reviews-widget/) service and displays its Google reviews with a Gutenberg block or the `[bithub_reviews]` shortcode.

- **WordPress.org:** https://wordpress.org/plugins/bithub-reviews-widget-for-google/
- **Web configurator (no WordPress needed):** https://bithub.it/en/tools/google-reviews-widget/
- **Plugin documentation:** https://bithub.it/plugin-wordpress/
- **Developed by:** [BitHub](https://bithub.it/), Jesi, Italy

## Features

- Connect a business using its Google Maps share link.
- Connect an existing BitHub Reviews configuration using a Place ID or saved Maps URL.
- Slider, grid, list and compact rating layouts.
- Minimum-rating and maximum-review filters.
- Responsive desktop, tablet and mobile settings.
- Theme, colors, border, shadow and corner-radius settings.
- Manual review refresh while the management session is active.
- Gutenberg block and `[bithub_reviews]` shortcode.
- No telemetry or analytics added by the plugin.
- English interface with bundled Italian translation.

## Requirements

- WordPress 6.5 or later (tested up to 7.1)
- PHP 7.4 or later
- A Google Places API key with Places API (New) enabled. Google Maps Platform billing must be active and Google usage charges may apply.

## Installation

Install it from the [WordPress Plugin Directory](https://wordpress.org/plugins/bithub-reviews-widget-for-google/), or copy this repository into `wp-content/plugins/bithub-reviews-widget-for-google/`, then:

1. Activate **BitHub Reviews Widget for Google**.
2. Open **Tools > BitHub Reviews**.
3. Connect a Google Maps business with its share link, an email address and your Google Places API key.
4. Configure and save the widget.
5. Add the **BitHub Reviews Widget** block or the `[bithub_reviews]` shortcode to a page.

## External service

The plugin relies on the BitHub Reviews service hosted at `https://extractor.able2code.com`, which resolves the business, calls Google Places API (New) with the administrator's key, stores the widget configuration and serves the public widget. The plugin does not store the Google API key, email address or management token in the WordPress database. See `readme.txt` for the full list of data sent and [BitHub privacy information](https://bithub.it/privacy-cookie/).

## License

GPLv2 or later. See [LICENSE.txt](LICENSE.txt).

Google and Google Maps are trademarks of Google LLC. This plugin is developed independently by BitHub and is not affiliated with or endorsed by Google LLC.
