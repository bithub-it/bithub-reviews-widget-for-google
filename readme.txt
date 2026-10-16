=== BitHub Reviews Widget for Google ===
Tags: reviews, google maps, testimonials, local business, shortcode
Requires at least: 6.5
Tested up to: 7.1
Stable tag: 1.0.0
Requires PHP: 7.4
Contributors: bithub
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect a Google Maps business to the BitHub Reviews service and display its review widget with a shortcode or Gutenberg block.

== Description ==

BitHub Reviews Widget for Google connects WordPress to the BitHub Reviews service. An administrator explicitly connects a Google Maps business, configures how its reviews are displayed, and then inserts the result with the **BitHub Reviews Widget** block or the `[bithub_reviews]` shortcode.

The plugin itself does not query Google Places directly from WordPress and does not store the Google API key or the management token in the WordPress database. The BitHub Reviews service performs the Google Places requests and serves the public widget.

Features:

* Connect a business using its Google Maps share link.
* Connect an existing BitHub Reviews configuration using a Place ID or saved Maps URL.
* Slider, grid, list and compact rating layouts.
* Minimum-rating and maximum-review filters.
* Responsive desktop, tablet and mobile settings.
* Review text, author, header and slider controls.
* Theme, colors, border, shadow and corner-radius settings.
* Manual review refresh while the management session is active.
* Gutenberg block and `[bithub_reviews]` shortcode.
* No telemetry or analytics added by this WordPress plugin.
* English interface with included Italian translation.

Documentation and other BitHub WordPress plugins:
https://bithub.it/plugin-wordpress/

Web configurator and service documentation:
https://bithub.it/strumenti/google-reviews-widget/

= External service =

This plugin relies on the external **BitHub Reviews** service hosted at `https://extractor.able2code.com`.

The service is an essential part of the plugin: it resolves the Google Maps business, communicates with Google Places API (New) using the API key supplied by the administrator, stores the service-side widget configuration, refreshes review data and serves the public review widget JavaScript.

The plugin contacts the BitHub Reviews service only when an administrator explicitly creates, loads, edits, refreshes or manages a widget. After a widget has been inserted on a public page, the visitor's browser loads that widget from `extractor.able2code.com` as part of the requested service.

During initial setup the following data can be sent to the service:

* Google Maps business URL or Place ID.
* Administrator-provided email address.
* Administrator-provided Google Places API key.
* Site language (`it` or `en`).
* Widget display configuration.

The WordPress plugin does **not** save the Google Places API key, email address or management token in the WordPress database. The remote service stores the information required to operate and manage the widget, including the email address and the Google API credential, according to the service implementation and privacy information.

When a visitor views a page containing the widget, normal HTTP connection information such as the visitor IP address and user agent can be received by the BitHub service server as part of serving the widget resource.

Service documentation:
https://bithub.it/strumenti/google-reviews-widget/

BitHub privacy and cookie information:
https://bithub.it/privacy-cookie/

Google Places Platform terms and policies apply to the Google Places data used by the service. Google and Google Maps are trademarks of Google LLC. This plugin is not affiliated with or endorsed by Google LLC.

== Installation ==

1. Upload the plugin or install it from the WordPress Plugin Directory.
2. Activate **BitHub Reviews Widget for Google**.
3. Open **Tools > BitHub Reviews**.
4. Connect a Google Maps business using its share link, email address and a Google Places API key with Places API (New) enabled.
5. Configure and save the widget.
6. Add the **BitHub Reviews Widget** block or the `[bithub_reviews]` shortcode to a page.

For an already configured business, use the existing-widget form with the Place ID or original Maps URL and the original Google Places API key.

== Frequently Asked Questions ==

= Does this plugin store my Google API key in WordPress? =

No. The API key is sent to the BitHub Reviews service only when it is entered for setup or management. The WordPress plugin does not save it in its local option.

= Does the plugin store my email address in WordPress? =

No. The email address is sent during initial service setup and is not stored by this WordPress plugin. The BitHub Reviews service uses the email for management and recovery flows.

= Why is an external service required? =

The BitHub service performs the Google Places requests, manages the review data and widget configuration, and serves the final widget. This is the core service functionality rather than a license-validation service.

= How do I add the widget to a page? =

Use the **BitHub Reviews Widget** Gutenberg block or add `[bithub_reviews]` to the page content.

= Can I manage an existing widget? =

Yes. Enter the Place ID or the Maps URL originally used for the configuration and the original Google Places API key. If the widget is already connected locally, use **Unlock editor**.

= What happens if I uninstall the plugin? =

The local WordPress connection is removed. Remote BitHub Reviews data is not deleted automatically because uninstalling a WordPress plugin should not silently destroy a separately managed service configuration.

= Does the plugin add tracking? =

No telemetry, analytics or advertising tracking is added by this WordPress plugin. The public widget is loaded from the BitHub Reviews service because that remote service provides the widget functionality.

= Is this an official Google plugin? =

No. Google and Google Maps are trademarks of Google LLC. BitHub Reviews Widget for Google is developed independently by BitHub and is not affiliated with or endorsed by Google LLC.

== Privacy ==

The plugin adds suggested privacy-policy text to the WordPress privacy guide. Site owners should review and adapt that text to their actual use of the widget and their legal requirements.

The BitHub privacy information is available at:
https://bithub.it/privacy-cookie/

== External resources ==

Plugin documentation and additional WordPress tools:
https://bithub.it/plugin-wordpress/

BitHub Reviews web configurator:
https://bithub.it/strumenti/google-reviews-widget/

BitHub website:
https://bithub.it/

== Changelog ==

= 1.0.0 =

* First public release.
* Connection to the BitHub Reviews service.
* Create and load existing business configurations.
* Remote widget configuration and manual refresh.
* Gutenberg block and shortcode output.
* English interface and bundled Italian translation.
