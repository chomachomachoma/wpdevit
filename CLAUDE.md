# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

WPDevIT is a WordPress theme that runs a Svelte 5 single-page app on top of WordPress acting as a headless CMS. WordPress serves a shell (`index.php`) and data (REST API); all routing and rendering happen client-side in Svelte.

`README.md` is the authoritative reference for project structure, REST endpoint shapes, plugin meta fields, and step-by-step recipes (adding a page, adding an endpoint). Read it before making changes. This file covers only what isn't obvious from a single file.

## Commands

```bash
npm install        # install deps (run from this theme directory)
npm run dev        # Vite dev server with HMR at localhost:5173 (requires WPDEVIT_DEV_MODE)
npm run build      # production build to dist/ — REQUIRED to see src/ changes on the real site
npm run preview    # preview the production build
```

There is no test suite, linter, or CI configured. "Building" the theme means running `npm run build`.

## Critical workflow facts

- **`npm run build` is mandatory after editing anything in `src/`.** The live site loads compiled assets from `dist/`, not source. Editing a `.svelte` file alone changes nothing on the site until you rebuild — unless dev mode is active.
- **Dev mode is opt-in via PHP.** `functions.php` only loads from the Vite dev server when `define('WPDEVIT_DEV_MODE', true)` is set in `wp-config.php`. Without it, even `npm run dev` has no effect on the site.
- **`functions.php` reads `dist/.vite/manifest.json`** to resolve hashed filenames. The manifest entry key is hardcoded as `src/main.js` (the single Rollup input). If you rename the entry or change `vite.config.js` input, update `wpdevit_enqueue_assets()` to match, or assets silently stop loading.

## WP CLI
The wp-cli utility is installed on this system and can be used to interact with WordPress and the application database.

## Architecture: how the pieces connect

The SPA boundary spans PHP and JS — changes often need edits on both sides.

- **PHP → JS handoff:** `functions.php` injects `window.wpdevitData` via `wp_localize_script` (REST URL, nonce, site name, and pre-resolved `primary`/`footer` menus). `src/lib/stores.js` and `src/lib/api.js` read this global; both fall back to defaults if it's absent, so they don't hard-crash outside WordPress.
- **Catch-all routing:** `wpdevit_spa_template()` (`template_include` filter, priority 99) forces `index.php` for every non-admin, non-REST frontend request. `redirect_canonical` is disabled so WordPress doesn't bounce SPA URLs. Net effect: any path renders the SPA, and Svelte decides what to show.
- **Client router:** `src/App.svelte` maps `$currentPath` → a page component in a single `$derived.by` block (Svelte 5 runes). `navigate()` in `src/lib/stores.js` does `history.pushState` + updates the `currentPath` store; a `popstate` listener handles back/forward.
- **Adding a route touches multiple files:** route condition in `App.svelte`, plus nav entry in `Header.svelte`/menu data. See README "Adding a New Page."

### Data layer

- **Two REST surfaces.** Core CPT data comes from `wp/v2/wpdevit-plugins` (registered in `inc/cpt-plugin.php` with `show_in_rest`). Custom endpoints live under the `wpdevit/v1` namespace in `inc/rest-api.php` (`site-info`, `plugins/featured`, `contact`, `menus/<location>`). All custom endpoints use `permission_callback => '__return_true'` (public).
- **`src/lib/api.js` is the only place that calls `fetch`.** Every new endpoint needs a wrapper here; components never fetch directly.
- **Plugin meta fields are private (`_wpdevit_*` prefix)** and registered in `inc/cpt-plugin.php` with an `auth_callback` requiring `edit_posts` to write. `_wpdevit_features` is a JSON-encoded string array, decoded server-side in the featured endpoint. The featured endpoint flattens meta into top-level keys and gates on `_wpdevit_is_featured == '1'` (stored as string `'1'`, not a PHP boolean), capped at 3 posts.
- **Menus are resolved server-side** by `wpdevit_get_menu_items()` (`inc/rest-api.php`): it converts absolute URLs to relative paths (so SPA routing works) and builds a parent/children tree. Menus are both inlined into `wpdevitData` and available via the `menus/<location>` endpoint.

## Conventions

- PHP functions/hooks are prefixed `wpdevit_`; meta keys `_wpdevit_`; the CPT is `wpdevit_plugin` with REST base `wpdevit-plugins`.
- This is Svelte 5 — use runes (`$state`, `$derived`, `$props`), not the Svelte 4 store-auto-subscribe-in-markup-only style. `mount()` (not `new App()`) bootstraps the app in `src/main.js`.
- Global design tokens are CSS custom properties in `src/styles/global.css`; component styles rely on Svelte's scoped `<style>`.
