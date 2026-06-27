// Site-level configuration constants for the SPA.

// Freemius store / product defaults used for checkout and the customer portal.
// The product id + public key below are GF Validation's live Freemius product
// (the same values the plugin ships in gf-validation.php). Per-product overrides
// can be set on each plugin via the `_wpdevit_freemius` meta field.
export const FREEMIUS = {
    plugin_id: '22298',
    public_key: 'pk_e72e2735ce3f291c45adf3214cbfd',
    // Set `store_id` (from Freemius → Stores → Settings → API & Keys) to embed the
    // customer portal in-page on the Account screen. Left blank → we link out to
    // the universal customer portal instead.
    store_id: '',
    portal_url: '',
};

// Universal Freemius customer portal. Customers log in with their purchase email
// and receive a passwordless magic link to manage every Freemius purchase.
export const CUSTOMER_PORTAL_URL = 'https://users.freemius.com';
