# unirate-currency-converter

WordPress plugin — live currency conversion in posts, pages, and blocks. Powered by [UniRate API](https://unirateapi.com).

## Features

- **Gutenberg block** — drag-and-drop currency converter with block-editor sidebar controls.
- **Three shortcodes** — `[unirate_rate]`, `[unirate_convert]`, `[unirate_widget]`.
- **API key stays server-side** — requests are proxied through `/wp-json/unirate/v1/`; the key is never exposed to visitors.
- **WP transient caching** — 1-hour cache out of the box, zero extra database writes per page load.
- **Zero PHP runtime dependencies** — uses `wp_remote_get()` only; no Composer packages needed on your server.

## Installation

1. Download the plugin zip from [Releases](https://github.com/UniRate-API/unirate-currency-converter/releases) and upload via **Plugins → Add New → Upload Plugin**, or install directly from the WordPress Plugin Directory.
2. Activate via **Plugins → Installed Plugins**.
3. Go to **Settings → UniRate** and enter your [UniRate API key](https://unirateapi.com).

## Shortcodes

### `[unirate_rate from="USD" to="EUR"]`

Server-rendered exchange rate at page load.

```
1 USD = 0.9235 EUR
```

### `[unirate_convert from="USD" to="EUR" amount="100"]`

Server-rendered converted amount.

```
92.35 EUR
```

### `[unirate_widget from="USD" to="EUR" amount="1"]`

Interactive currency converter. Visitor enters an amount, clicks **Convert**, and the result loads instantly via the local REST proxy — no API key exposed.

## Gutenberg block

Search for **UniRate Currency Converter** in the block inserter (Widgets category). Use the sidebar panel to set:

- **Display style** — widget / rate / converted amount
- **From / To currency** — 3-letter ISO codes
- **Amount**

## REST endpoints

The plugin registers these endpoints on your site (all `GET`, public):

| Endpoint | Params | Response |
|----------|--------|----------|
| `/wp-json/unirate/v1/rate` | `from`, `to` | `{"rate": 0.9235}` |
| `/wp-json/unirate/v1/convert` | `from`, `to`, `amount` | `{"result": 92.35}` |
| `/wp-json/unirate/v1/currencies` | — | `{"currencies": ["USD", "EUR", ...]}` |

Your API key is read from `wp_options` server-side — it never appears in these responses or in page HTML.

## Development

```bash
# Install test dependencies (runs in an isolated container)
dev php composer install

# Run tests
dev php ./vendor/bin/phpunit

# Offline re-run (post-install security check)
dev --no-net php ./vendor/bin/phpunit
```

Tests: **37 tests, 52 assertions** (ClientTest · SettingsTest · RestApiTest · ShortcodeTest via WP_Mock + PHPUnit 9.6).

## Related packages

| Package | Description |
|---------|-------------|
| [`unirate-api`](https://github.com/UniRate-API/unirate-api-nodejs) | Node.js client |
| [`unirate-api`](https://github.com/UniRate-API/unirate-api-python) | Python client |
| [`@unirate/react`](https://github.com/UniRate-API/react-unirate) | React hooks |
| [`@unirate/next`](https://github.com/UniRate-API/next-unirate) | Next.js integration |
| [`@unirate/nuxt`](https://github.com/UniRate-API/nuxt-unirate) | Nuxt module |
| [`@unirate/sveltekit`](https://github.com/UniRate-API/sveltekit-unirate) | SvelteKit integration |
| [`flask-unirate`](https://github.com/UniRate-API/flask-unirate) | Flask extension |
| [`djangorestframework-unirate`](https://github.com/UniRate-API/djangorestframework-unirate) | Django REST Framework |
| [`jekyll-unirate`](https://github.com/UniRate-API/jekyll-unirate) | Jekyll plugin |
| [`hugo-unirate`](https://github.com/UniRate-API/hugo-unirate) | Hugo module |
| [`@unirate/astro`](https://github.com/UniRate-API/astro-unirate) | Astro integration |
| [`@unirate/mcp`](https://github.com/UniRate-API/unirate-mcp) | MCP server |
| [`unirate-cli`](https://github.com/UniRate-API/unirate-cli) | CLI tool |

## License

MIT
