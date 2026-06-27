<script>
    import { navigate } from '../lib/stores.js';

    let { plugin } = $props();

    function handleClick(e) {
        e.preventDefault();
        navigate(`/plugins/${plugin.slug}`);
    }

    // Handle both REST API formats (custom endpoint vs WP REST)
    $effect(() => {
        if (!plugin.featured_image && plugin._embedded?.['wp:featuredmedia']?.[0]?.source_url) {
            plugin.featured_image = plugin._embedded['wp:featuredmedia'][0].source_url;
        }
    });

    let title = $derived(typeof plugin.title === 'object' ? plugin.title.rendered : plugin.title);
    let tagline = $derived(plugin.tagline || '');
    let excerpt = $derived(typeof plugin.excerpt === 'object' ? plugin.excerpt.rendered : plugin.excerpt);
    let price = $derived(plugin.meta?._wpdevit_price || plugin.price || 'Free');
    let version = $derived(plugin.meta?._wpdevit_version || plugin.version || '');
    let image = $derived(plugin.card_image || plugin.featured_image);
</script>

<article class="plugin-card">
    <a href="/plugins/{plugin.slug}" onclick={handleClick} class="card-link">
        <div class="card-image">
            {#if image}
                <img src={image} alt={title} />
            {:else}
                <div class="card-placeholder">
                    <span>&#9670;</span>
                </div>
            {/if}
            <span class="card-badge">Gravity Forms</span>
        </div>

        <div class="card-body">
            <h3>{title}</h3>

            {#if tagline}
                <p class="card-tagline">{tagline}</p>
            {:else if excerpt}
                <div class="card-excerpt">{@html excerpt}</div>
            {/if}

            <div class="card-meta">
                <span class="card-price">{price === 'Free' ? 'Free' : `$${price}`}</span>
                {#if version}
                    <span class="card-version">v{version}</span>
                {/if}
            </div>
        </div>
    </a>
</article>

<style>
    .plugin-card {
        background: var(--color-bg);
        border-radius: var(--radius-lg);
        overflow: hidden;
        border: 1px solid var(--color-border);
        transition: all 0.3s ease;
    }

    .plugin-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .card-link {
        text-decoration: none;
        color: inherit;
    }

    .card-link:hover {
        color: inherit;
    }

    .card-image {
        aspect-ratio: 3 / 2;
        overflow: hidden;
        background: var(--color-bg-alt);
        position: relative;
    }

    .card-badge {
        position: absolute;
        top: var(--spacing-sm);
        left: var(--spacing-sm);
        background: rgba(26, 26, 46, 0.85);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 4px 10px;
        border-radius: 20px;
        backdrop-filter: blur(2px);
    }

    .card-tagline {
        color: var(--color-text-light);
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: var(--spacing-md);
    }

    .card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .plugin-card:hover .card-image img {
        transform: scale(1.05);
    }

    .card-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
        color: var(--color-accent);
        font-size: 3rem;
    }

    .card-body {
        padding: var(--spacing-lg);
    }

    .card-body h3 {
        font-size: 1.25rem;
        margin-bottom: var(--spacing-sm);
    }

    .card-excerpt {
        color: var(--color-text-light);
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: var(--spacing-md);
    }

    .card-excerpt :global(p) {
        margin: 0;
    }

    .card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: var(--spacing-md);
        border-top: 1px solid var(--color-border);
    }

    .card-price {
        font-weight: 700;
        color: var(--color-accent);
        font-size: 1.1rem;
    }

    .card-version {
        font-size: 0.85rem;
        color: var(--color-text-light);
        font-family: var(--font-mono);
    }
</style>
