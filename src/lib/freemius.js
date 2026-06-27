// Freemius Checkout integration.
//
// Loads the hosted Checkout script on demand and opens the overlay configured
// for a given product. Used to sell licenses directly from the product pages,
// and to send customers to the Freemius customer portal to manage subscriptions.

import { FREEMIUS, CUSTOMER_PORTAL_URL } from './config.js';

const CHECKOUT_SRC = 'https://checkout.freemius.com/checkout.min.js';

let loadPromise = null;

/** Inject a <script> and resolve on load. */
function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load ' + src));
        document.head.appendChild(script);
    });
}

/**
 * Freemius Checkout depends on jQuery, which this Svelte SPA does not otherwise
 * load. Ensure it's present — preferring the copy WordPress already ships — before
 * the checkout script runs, otherwise FS.Checkout never attaches to the window.
 */
function ensureJQuery() {
    if (typeof window !== 'undefined' && window.jQuery) return Promise.resolve();
    const siteUrl = (window.wpdevitData && window.wpdevitData.siteUrl) || '/';
    const wpJq = siteUrl.replace(/\/$/, '') + '/wp-includes/js/jquery/jquery.min.js';
    return loadScript(wpJq).catch(() => loadScript('https://code.jquery.com/jquery-3.7.1.min.js'));
}

/**
 * Lazily load jQuery + the Freemius Checkout script. Resolves once
 * `FS.Checkout` is available on the window.
 */
function loadCheckout() {
    if (typeof window !== 'undefined' && window.FS && window.FS.Checkout) {
        return Promise.resolve();
    }
    if (loadPromise) return loadPromise;

    loadPromise = ensureJQuery()
        .then(() => loadScript(CHECKOUT_SRC))
        .then(() => {
            if (!(window.FS && window.FS.Checkout)) {
                throw new Error('FS.Checkout is unavailable after loading.');
            }
        })
        .catch((err) => {
            loadPromise = null; // allow a retry
            throw new Error('Could not load the Freemius checkout. ' + err.message);
        });

    return loadPromise;
}

/**
 * Merge a per-product Freemius config (from plugin meta) over the site default.
 * Returns null when no usable product id is available.
 */
export function resolveFreemius(productFreemius) {
    const merged = { ...FREEMIUS, ...(productFreemius || {}) };
    if (!merged.plugin_id) return null;
    return merged;
}

/**
 * Open the Freemius checkout overlay.
 *
 * @param {object} freemius  Resolved freemius config ({ plugin_id, public_key, plan_id, pricing_id }).
 * @param {object} opts      { name, title, licenses, plan_id, pricing_id, billingCycle, onComplete }
 * @returns {Promise<{status:'success'|'cancel', data?:object}>}
 */
export async function openCheckout(freemius, opts = {}) {
    const cfg = resolveFreemius(freemius);
    if (!cfg) {
        throw new Error('This product is not available for purchase yet.');
    }

    await loadCheckout();

    // FS.Checkout is a singleton: configure() sets the product, open() launches
    // the dialog. (Not a constructor — `new FS.Checkout` does not exist.)
    const handler = window.FS.Checkout.configure({
        plugin_id: String(cfg.plugin_id),
        ...(cfg.public_key ? { public_key: cfg.public_key } : {}),
        ...(opts.plan_id || cfg.plan_id ? { plan_id: String(opts.plan_id || cfg.plan_id) } : {}),
    });

    const checkout = handler && typeof handler.open === 'function' ? handler : window.FS.Checkout;

    return new Promise((resolve, reject) => {
        try {
            checkout.open({
                name: opts.name || undefined,
                title: opts.title || undefined,
                licenses: opts.licenses || 1,
                ...(opts.pricing_id ? { pricing_id: String(opts.pricing_id) } : {}),
                ...(opts.billingCycle ? { billing_cycle: opts.billingCycle } : {}),
                purchaseCompleted: (data) => {
                    if (typeof opts.onComplete === 'function') opts.onComplete(data);
                },
                success: (data) => resolve({ status: 'success', data }),
                cancel: () => resolve({ status: 'cancel' }),
            });
        } catch (err) {
            reject(err);
        }
    });
}

/**
 * Resolve the customer portal URL for managing subscriptions. Prefers an
 * explicit per-store portal (embeddable) and falls back to the universal portal.
 */
export function customerPortalUrl(freemius) {
    const cfg = { ...FREEMIUS, ...(freemius || {}) };
    if (cfg.portal_url) return cfg.portal_url;
    if (cfg.store_id) return `https://customers.freemius.com/store/${cfg.store_id}`;
    return CUSTOMER_PORTAL_URL;
}

/**
 * Whether the customer portal can be safely embedded in an <iframe> (only the
 * per-store portal supports embedding; the universal portal must open in a tab).
 */
export function portalIsEmbeddable(freemius) {
    const cfg = { ...FREEMIUS, ...(freemius || {}) };
    return Boolean(cfg.portal_url || cfg.store_id);
}
