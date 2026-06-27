<script>
    import { navigate, footerMenu } from '../lib/stores.js';

    function isInternal(url) {
        return url.startsWith('/') && !url.startsWith('//');
    }

    function handleNav(e, item) {
        if (isInternal(item.url) && !item.target) {
            e.preventDefault();
            navigate(item.url);
        }
    }

    const year = new Date().getFullYear();
</script>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <span class="logo-icon">&#9670;</span>
            <span class="logo-text">WPDevIT</span>
            <p>Custom WordPress plugins built for performance and reliability.</p>
        </div>

        <div class="footer-links">
            <h4>Navigation</h4>
            {#each $footerMenu as item}
                <a
                    href={item.url}
                    target={item.target || undefined}
                    rel={item.target ? 'noopener noreferrer' : undefined}
                    onclick={(e) => handleNav(e, item)}
                >
                    {item.title}
                </a>
            {/each}
        </div>

        <div class="footer-links">
            <h4>Support</h4>
            <a href="/contact" onclick={(e) => { e.preventDefault(); navigate('/contact'); }}>Get in Touch</a>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; {year} WPDevIT. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
    .site-footer {
        background: var(--color-bg-dark);
        color: var(--color-text-inverse);
        margin-top: auto;
    }

    .footer-inner {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: var(--spacing-2xl);
        padding: var(--spacing-3xl) var(--spacing-lg);
    }

    .footer-brand {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .footer-brand .logo-icon {
        color: var(--color-accent);
        font-size: 1.25rem;
    }

    .footer-brand .logo-text {
        font-size: 1.5rem;
        font-weight: 800;
    }

    .footer-brand p {
        color: rgba(255, 255, 255, 0.7);
        max-width: 300px;
        line-height: 1.6;
    }

    .footer-links {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .footer-links h4 {
        color: var(--color-text-inverse);
        margin-bottom: var(--spacing-sm);
        font-size: 1rem;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.2s;
    }

    .footer-links a:hover {
        color: var(--color-accent);
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: var(--spacing-lg) 0;
        text-align: center;
    }

    .footer-bottom p {
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .footer-inner {
            grid-template-columns: 1fr;
            gap: var(--spacing-xl);
        }
    }
</style>
