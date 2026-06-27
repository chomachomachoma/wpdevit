import { writable, derived } from 'svelte/store';

// Current URL path for SPA routing
export const currentPath = writable(window.location.pathname);

// Listen for browser back/forward
window.addEventListener('popstate', () => {
    currentPath.set(window.location.pathname);
});

/**
 * Navigate to a path without full page reload.
 */
export function navigate(path) {
    if (path === window.location.pathname) return;
    history.pushState({}, '', path);
    currentPath.set(path);
    window.scrollTo(0, 0);
}

// Route params extracted from current path
export const routeParams = derived(currentPath, ($path) => {
    const segments = $path.replace(/^\/|\/$/g, '').split('/');
    return { segments };
});

// Site data store
export const siteInfo = writable({
    name: window.wpdevitData?.siteName || 'WPDevIT',
    restUrl: window.wpdevitData?.restUrl || '/wp-json/',
    nonce: window.wpdevitData?.nonce || '',
    siteUrl: window.wpdevitData?.siteUrl || '/',
});

// Navigation menus from WordPress (inlined on page load, can be refreshed via API)
const wpMenus = window.wpdevitData?.menus || {};

const defaultPrimary = [
    { id: 0, title: 'Home', url: '/', target: '', children: [] },
    { id: 1, title: 'Plugins', url: '/plugins', target: '', children: [] },
    { id: 2, title: 'About', url: '/about', target: '', children: [] },
    { id: 3, title: 'Contact', url: '/contact', target: '', children: [] },
];

export const primaryMenu = writable(
    wpMenus.primary?.length ? wpMenus.primary : defaultPrimary
);
export const footerMenu = writable(
    wpMenus.footer?.length ? wpMenus.footer : defaultPrimary
);
