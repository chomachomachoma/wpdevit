<script>
    import { currentPath, navigate, primaryMenu } from '../lib/stores.js';

    let mobileOpen = $state(false);

    function isInternal(url) {
        return url.startsWith('/') && !url.startsWith('//');
    }

    function handleNav(e, item) {
        if (isInternal(item.url) && !item.target) {
            e.preventDefault();
            navigate(item.url);
            mobileOpen = false;
        }
    }

    function isActive(path, itemUrl) {
        const clean = itemUrl.replace(/\/+$/, '') || '/';
        return path === clean || (path.startsWith(clean) && clean !== '/');
    }
</script>

<header class="site-header">
    <div class="container header-inner">
        <a href="/" class="logo" onclick={(e) => { e.preventDefault(); navigate('/'); mobileOpen = false; }}>
            <span class="logo-icon">&#9670;</span>
            <span class="logo-text">WPDevIT</span>
        </a>

        <button
            class="mobile-toggle"
            onclick={() => mobileOpen = !mobileOpen}
            aria-label="Toggle menu"
            aria-expanded={mobileOpen}
        >
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <nav class:open={mobileOpen}>
            {#each $primaryMenu as item}
                <a
                    href={item.url}
                    target={item.target || undefined}
                    rel={item.target ? 'noopener noreferrer' : undefined}
                    class:active={isActive($currentPath, item.url)}
                    onclick={(e) => handleNav(e, item)}
                >
                    {item.title}
                </a>
            {/each}
        </nav>
    </div>
</header>

<style>
    .site-header {
        position: sticky;
        top: 0;
        z-index: 100;
        background: var(--color-bg);
        border-bottom: 1px solid var(--color-border);
        height: var(--header-height);
        display: flex;
        align-items: center;
    }

    .header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--color-primary);
        text-decoration: none;
    }

    .logo:hover {
        color: var(--color-accent);
    }

    .logo-icon {
        color: var(--color-accent);
        font-size: 1.25rem;
    }

    nav {
        display: flex;
        gap: var(--spacing-lg);
    }

    nav a {
        color: var(--color-text);
        font-weight: 500;
        font-size: 0.95rem;
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius-sm);
        transition: all 0.2s ease;
        text-decoration: none;
    }

    nav a:hover,
    nav a.active {
        color: var(--color-accent);
    }

    nav a.active {
        background: var(--color-accent-light);
    }

    .mobile-toggle {
        display: none;
        flex-direction: column;
        gap: 4px;
        background: none;
        border: none;
        cursor: pointer;
        padding: var(--spacing-sm);
    }

    .bar {
        width: 24px;
        height: 2px;
        background: var(--color-text);
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    @media (max-width: 768px) {
        .mobile-toggle {
            display: flex;
        }

        nav {
            display: none;
            position: absolute;
            top: var(--header-height);
            left: 0;
            right: 0;
            background: var(--color-bg);
            flex-direction: column;
            padding: var(--spacing-md);
            border-bottom: 1px solid var(--color-border);
            box-shadow: var(--shadow-md);
        }

        nav.open {
            display: flex;
        }
    }
</style>
