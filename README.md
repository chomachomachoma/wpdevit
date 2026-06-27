# WPDevIT Theme

A WordPress SPA theme built with Svelte 5 and Vite for selling custom WordPress plugins. All frontend routing and rendering is handled client-side by Svelte, with WordPress serving as a headless CMS via the REST API.

## Requirements

- WordPress 6.5+
- PHP 8.1+
- Node.js 20+
- npm 10+

## Project Structure

```
wpdevit/
├── style.css                    # WP theme metadata (no styles)
├── index.php                    # SPA shell template (<div id="app">)
├── functions.php                # Asset enqueuing, SPA routing, includes
├── inc/
│   ├── theme-setup.php          # Theme supports, image sizes, nav menus
│   ├── cpt-plugin.php           # Custom post type + taxonomy + meta fields
│   └── rest-api.php             # Custom REST API endpoints
├── vite.config.js               # Vite build config
├── svelte.config.js             # Svelte preprocessor config
├── package.json
├── src/                         # Svelte application source
│   ├── main.js                  # Entry point, mounts App to #app
│   ├── App.svelte               # Root component with client-side router
│   ├── lib/
│   │   ├── api.js               # REST API client (fetch wrappers)
│   │   └── stores.js            # Svelte stores, navigate(), currentPath
│   ├── components/
│   │   ├── Header.svelte        # Sticky header with responsive nav
│   │   ├── Footer.svelte        # Site footer
│   │   ├── Hero.svelte          # Homepage hero banner
│   │   ├── PluginCard.svelte    # Plugin product card
│   │   └── ContactForm.svelte   # Contact form with validation
│   ├── pages/
│   │   ├── Home.svelte          # / — hero, featured plugins, value props
│   │   ├── Plugins.svelte       # /plugins — plugin grid listing
│   │   ├── PluginSingle.svelte  # /plugins/:slug — plugin detail + sidebar
│   │   ├── About.svelte         # /about — company info
│   │   ├── Contact.svelte       # /contact — contact form
│   │   └── NotFound.svelte      # 404 fallback
│   └── styles/
│       └── global.css           # CSS custom properties, reset, utilities
└── dist/                        # Vite build output (production assets)
    ├── assets/
    │   ├── main-[hash].js
    │   └── main-[hash].css
    └── .vite/
        └── manifest.json        # Asset manifest read by functions.php
```

## Getting Started

### 1. Install dependencies

```bash
cd wp-content/themes/wpdevit
npm install
```

### 2. Build for production

```bash
npm run build
```

This outputs hashed assets to `dist/` and generates the Vite manifest that `functions.php` reads to enqueue the correct files.

### 3. Activate the theme

Go to **WP Admin > Appearance > Themes** and activate **WPDevIT**.

### 4. Add plugin products

A custom post type **Plugins** appears in the admin sidebar. Create entries there with the following meta fields (available in the editor sidebar under Custom Fields):

| Meta Key | Type | Description |
|---|---|---|
| `_wpdevit_price` | string | Price in dollars, e.g. `"49.99"`. Leave empty for free. |
| `_wpdevit_version` | string | Current version, e.g. `"2.1.0"` |
| `_wpdevit_download_url` | string | Purchase or download link |
| `_wpdevit_demo_url` | string | Live demo URL |
| `_wpdevit_features` | string | JSON array of feature strings, e.g. `["Fast", "Secure"]` |
| `_wpdevit_is_featured` | boolean | Set to `true` to show on the homepage |

You can also categorize plugins using the **Plugin Categories** taxonomy.

## Development

### Dev mode with HMR

Add this to your `wp-config.php`:

```php
define('WPDEVIT_DEV_MODE', true);
```

Then start the Vite dev server:

```bash
npm run dev
```

Visit `https://wpdevit.local` — WordPress loads the SPA shell and pulls JS/CSS from the Vite dev server at `localhost:5173` with hot module replacement enabled.

Remove the `WPDEVIT_DEV_MODE` constant (or set to `false`) and run `npm run build` for production.

### npm scripts

| Command | Description |
|---|---|
| `npm run dev` | Start Vite dev server with HMR |
| `npm run build` | Production build to `dist/` |
| `npm run preview` | Preview production build locally |

## Architecture

### How the SPA works

1. WordPress receives every frontend request and always renders `index.php` (via the `template_include` filter in `functions.php`).
2. `index.php` outputs a minimal HTML shell with `<div id="app">` and calls `wp_head()`/`wp_footer()` to load the compiled Svelte bundle.
3. `functions.php` reads `dist/.vite/manifest.json` to resolve hashed asset filenames, enqueues them as ES modules, and injects `wpdevitData` (REST URL, nonce, site URL) via `wp_localize_script`.
4. Svelte mounts into `#app`, reads `window.location.pathname`, and renders the matching page component.
5. Navigation uses `history.pushState` — no full page reloads. The `navigate()` function in `src/lib/stores.js` updates the `currentPath` store, which the router in `App.svelte` reacts to.
6. Canonical redirects are disabled via the `redirect_canonical` filter so WordPress doesn't interfere with SPA routes.

### Client-side routing

Routes are defined in `src/App.svelte`:

| Path | Component | Description |
|---|---|---|
| `/` | `Home.svelte` | Homepage with hero, featured plugins, value props |
| `/plugins` | `Plugins.svelte` | Grid of all plugins from REST API |
| `/plugins/:slug` | `PluginSingle.svelte` | Single plugin detail page |
| `/about` | `About.svelte` | About page |
| `/contact` | `Contact.svelte` | Contact form |
| `*` | `NotFound.svelte` | 404 fallback |

To add a new route, import the page component in `App.svelte` and add a condition to the `route` derived block.

### SPA link handling

All internal links use standard `<a>` tags with an `onclick` handler that calls `navigate(path)` from `src/lib/stores.js`. This ensures:
- No full page reloads (true SPA behavior)
- Browser back/forward works (`popstate` listener updates the store)
- Links are crawlable (real `href` attributes)

## REST API

### WordPress core endpoints

The custom post type is exposed at:

```
GET /wp-json/wp/v2/wpdevit-plugins          # List all plugins
GET /wp-json/wp/v2/wpdevit-plugins?slug=foo  # Get plugin by slug
GET /wp-json/wp/v2/wpdevit-plugins?_embed    # Include featured images
```

### Custom endpoints

Registered under the `wpdevit/v1` namespace in `inc/rest-api.php`:

#### `GET /wp-json/wpdevit/v1/site-info`

Returns site name, description, and URL. No authentication required.

```json
{
  "name": "WPDevIT",
  "description": "Custom WordPress Plugins",
  "url": "https://wpdevit.local/"
}
```

#### `GET /wp-json/wpdevit/v1/plugins/featured`

Returns plugins where `_wpdevit_is_featured` is `true` (max 3). No authentication required. Returns an array of plugin objects with all meta fields resolved.

#### `POST /wp-json/wpdevit/v1/contact`

Sends a contact email via `wp_mail()`. No authentication required.

**Request body:**

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "message": "I'd like to know more about your plugins."
}
```

**Response:**

```json
{
  "success": true,
  "message": "Your message has been sent successfully."
}
```

### API client

`src/lib/api.js` exports these functions that all page components use:

```js
fetchPlugins()                    // GET all plugins with embeds
fetchPlugin(slug)                 // GET single plugin by slug
fetchFeaturedPlugins()            // GET featured plugins
fetchSiteInfo()                   // GET site info
submitContact({ name, email, message })  // POST contact form
```

All requests include the `X-WP-Nonce` header from `wpdevitData.nonce` for authenticated operations.

## Custom Post Type

Registered in `inc/cpt-plugin.php` as `wpdevit_plugin`:

- **REST base:** `wpdevit-plugins`
- **Supports:** title, editor, thumbnail, excerpt, custom-fields
- **Taxonomy:** `plugin_category` (hierarchical, REST-enabled at `plugin-categories`)
- **Meta fields:** All registered with `show_in_rest: true` and listed in the table above

## CSS Architecture

All styles use CSS custom properties defined in `src/styles/global.css`. Component styles are scoped via Svelte's built-in style encapsulation.

Key design tokens:

```css
--color-primary: #1a1a2e       /* Dark navy */
--color-accent: #4361ee        /* Blue */
--color-bg: #ffffff            /* White */
--color-bg-alt: #f8f9fa        /* Light gray */
--color-text: #2d3436          /* Near-black */
```

The theme uses a system font stack and is fully responsive with a mobile breakpoint at `768px`.

## Adding a New Page

1. Create `src/pages/MyPage.svelte`
2. Import it in `src/App.svelte`
3. Add a route condition in the `route` derived block:
   ```js
   if (clean === '/my-page') return { component: MyPage, props: {} };
   ```
4. Add a nav link in `src/components/Header.svelte` (in the `navItems` array)
5. Rebuild: `npm run build`

## Adding a New REST Endpoint

1. Add a `register_rest_route()` call in `inc/rest-api.php` inside the `wpdevit_register_rest_routes` function
2. Create the callback function in the same file
3. Add a fetch wrapper in `src/lib/api.js`
4. Call it from your Svelte component
