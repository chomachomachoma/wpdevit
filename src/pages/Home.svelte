<script>
    import Hero from '../components/Hero.svelte';
    import PluginCard from '../components/PluginCard.svelte';
    import { navigate } from '../lib/stores.js';
    import { fetchFeaturedPlugins } from '../lib/api.js';

    let plugins = $state([]);
    let loading = $state(true);

    $effect(() => {
        fetchFeaturedPlugins()
            .then(data => { plugins = data; })
            .catch(() => { plugins = []; })
            .finally(() => { loading = false; });
    });

    function handleNav(e, path) {
        e.preventDefault();
        navigate(path);
    }
</script>

<Hero />

<section class="featured-plugins">
    <div class="container">
        <div class="section-header">
            <h2>Featured Plugins</h2>
            <p>Our most popular WordPress plugins, trusted by developers worldwide.</p>
        </div>

        {#if loading}
            <div class="loading">Loading plugins...</div>
        {:else if plugins.length > 0}
            <div class="plugin-grid">
                {#each plugins as plugin}
                    <PluginCard {plugin} />
                {/each}
            </div>
        {:else}
            <div class="empty-state">
                <p>No featured plugins yet. Check back soon!</p>
            </div>
        {/if}

        <div class="section-cta">
            <a href="/plugins" class="btn btn-outline" onclick={(e) => handleNav(e, '/plugins')}>
                View All Plugins
            </a>
        </div>
    </div>
</section>

<section class="why-section">
    <div class="container">
        <div class="section-header">
            <h2>Why WPDevIT?</h2>
        </div>

        <div class="features-grid">
            <div class="feature">
                <div class="feature-icon">&#9889;</div>
                <h3>Performance First</h3>
                <p>Every plugin is optimized for speed. No bloat, no unnecessary queries, no slowdowns.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">&#128736;</div>
                <h3>Developer Friendly</h3>
                <p>Clean code, hooks, filters, and comprehensive documentation for easy customization.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">&#128274;</div>
                <h3>Secure by Design</h3>
                <p>Built following WordPress security best practices with regular audits and updates.</p>
            </div>
        </div>
    </div>
</section>

<style>
    .featured-plugins {
        padding: var(--spacing-3xl) 0;
    }

    .section-header {
        text-align: center;
        margin-bottom: var(--spacing-2xl);
    }

    .section-header p {
        color: var(--color-text-light);
        font-size: 1.15rem;
        margin-top: var(--spacing-sm);
    }

    .plugin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--spacing-xl);
    }

    .loading, .empty-state {
        text-align: center;
        padding: var(--spacing-3xl);
        color: var(--color-text-light);
        font-size: 1.1rem;
    }

    .section-cta {
        text-align: center;
        margin-top: var(--spacing-2xl);
    }

    .why-section {
        padding: var(--spacing-3xl) 0;
        background: var(--color-bg-alt);
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-xl);
    }

    .feature {
        text-align: center;
        padding: var(--spacing-xl);
        background: var(--color-bg);
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-border);
    }

    .feature-icon {
        font-size: 2.5rem;
        margin-bottom: var(--spacing-md);
    }

    .feature h3 {
        margin-bottom: var(--spacing-sm);
    }

    .feature p {
        color: var(--color-text-light);
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .plugin-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
