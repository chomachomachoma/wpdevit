const data = window.wpdevitData || {};
const REST_URL = data.restUrl || '/wp-json/';
const NONCE = data.nonce || '';

async function apiFetch(endpoint, options = {}) {
    const url = endpoint.startsWith('http') ? endpoint : `${REST_URL}${endpoint}`;

    const headers = {
        'Content-Type': 'application/json',
        ...(NONCE ? { 'X-WP-Nonce': NONCE } : {}),
        ...options.headers,
    };

    const response = await fetch(url, { ...options, headers });

    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        throw new Error(error.message || `API error: ${response.status}`);
    }

    return response.json();
}

export async function fetchPlugins() {
    return apiFetch('wp/v2/wpdevit-plugins?_embed&per_page=100');
}

export async function fetchPlugin(slug) {
    const results = await apiFetch(`wp/v2/wpdevit-plugins?slug=${encodeURIComponent(slug)}&_embed`);
    return results.length > 0 ? results[0] : null;
}

export async function fetchFeaturedPlugins() {
    return apiFetch('wpdevit/v1/plugins/featured');
}

// Normalized product endpoints (flattened meta: screenshots, pricing, freemius, etc.)
export async function fetchProducts() {
    return apiFetch('wpdevit/v1/products');
}

export async function fetchProduct(slug) {
    return apiFetch(`wpdevit/v1/product/${encodeURIComponent(slug)}`);
}

export async function fetchSiteInfo() {
    return apiFetch('wpdevit/v1/site-info');
}

export async function fetchMenu(location) {
    return apiFetch(`wpdevit/v1/menus/${encodeURIComponent(location)}`);
}

export async function submitContact({ name, email, message }) {
    return apiFetch('wpdevit/v1/contact', {
        method: 'POST',
        body: JSON.stringify({ name, email, message }),
    });
}
