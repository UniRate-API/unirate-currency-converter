=== UniRate Currency Converter ===
Contributors: robbrowncc
Tags: currency converter, exchange rates, forex, widget, block
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Live currency conversion in posts, pages, and blocks. Powered by UniRate API. API key stays server-side.

== Description ==

**UniRate Currency Converter** brings live exchange rates to your WordPress site using the [UniRate API](https://unirateapi.com) — 170+ currencies, updated daily.

= Features =

* **Gutenberg block** — drag-and-drop currency converter widget or inline rate/conversion display.
* **Shortcodes** — `[unirate_rate]`, `[unirate_convert]`, and `[unirate_widget]` for use in posts, pages, and classic-editor content.
* **API key stays server-side** — the plugin proxies requests through your WordPress REST API; your key is never exposed to visitors.
* **Built-in caching** — responses cached for 1 hour via WordPress transients (no database writes per page load).
* **Zero PHP runtime dependencies** — uses the WordPress HTTP API (`wp_remote_get`), no Composer packages needed on your server.

= Shortcode reference =

**`[unirate_rate from="USD" to="EUR"]`**
Outputs `1 USD = 0.9235 EUR` (server-rendered at page load).

**`[unirate_convert from="USD" to="EUR" amount="100"]`**
Outputs `92.35 EUR` (server-rendered at page load).

**`[unirate_widget from="USD" to="EUR" amount="1"]`**
Renders an interactive converter form — visitors type an amount, hit Convert, and the result appears instantly via the local REST endpoint.

= Privacy =

The plugin sends the visitor's chosen currency codes and amount to `api.unirateapi.com` to fetch the rate. No visitor personal data is transmitted. See the [UniRate privacy policy](https://unirateapi.com/privacy) for details.

== Installation ==

1. Upload the `unirate-currency-converter` folder to `/wp-content/plugins/`.
2. Activate the plugin via **Plugins → Installed Plugins**.
3. Go to **Settings → UniRate** and enter your [UniRate API key](https://unirateapi.com).
4. Add the **UniRate Currency Converter** block in the block editor, or use a shortcode in any post or page.

== Frequently Asked Questions ==

= Where do I get an API key? =

Sign up for free at [unirateapi.com](https://unirateapi.com). The free tier covers all shortcodes and the widget.

= How often are rates updated? =

Rates are fetched from UniRate API, which updates daily. The plugin caches the response for one hour.

= Is the API key secure? =

Yes — the plugin never sends your API key to the visitor's browser. All calls to the UniRate API are made server-side (PHP) or through your WordPress REST endpoint (`/wp-json/unirate/v1/`).

= Does this work with page caching plugins? =

Yes for server-rendered shortcodes (`[unirate_rate]`, `[unirate_convert]`). The `[unirate_widget]` and the Gutenberg block widget call the local REST endpoint from JavaScript, so rates stay fresh even on cached pages.

= Which currencies are supported? =

170+ currencies. Call `[unirate_rate from="USD" to="EUR"]` or visit your site's `/wp-json/unirate/v1/currencies` to see the full list.

== Screenshots ==

1. Block editor — UniRate Currency Converter block with sidebar settings.
2. Frontend — interactive widget.
3. Settings page.

== Changelog ==

= 0.1.0 =
* Initial release: Gutenberg block, `[unirate_rate]` / `[unirate_convert]` / `[unirate_widget]` shortcodes, REST proxy endpoint.

== Upgrade Notice ==

= 0.1.0 =
Initial release.
