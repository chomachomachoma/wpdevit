<script>
    import PluginCard from '../components/PluginCard.svelte';
    import { fetchProducts } from '../lib/api.js';

    let plugins = $state([]);
    let loading = $state(true);
    let error = $state('');

    $effect(() => {
        fetchProducts()
            .then(data => { plugins = data; })
            .catch(err => { error = err.message; })
            .finally(() => { loading = false; });
    });
</script>

<section class="plugins-page">
    <div class="container">
        <div class="page-header">
            <h1>Gravity Forms Add-ons</h1>
            <p>Purpose-built extensions that make Gravity Forms do more — validation and access control, done right.</p>
        </div>

        {#if loading}
            <div class="loading">Loading plugins...</div>
        {:else if error}
            <div class="error-state">
                <p>Failed to load plugins. Please try again later.</p>
            </div>
        {:else if plugins.length > 0}
            <div class="plugin-grid">
                {#each plugins as plugin}
                    <PluginCard {plugin} />
                {/each}
            </div>
        {:else}
            <div class="empty-state">
                <p>No plugins available yet. Check back soon!</p>
            </div>
        {/if}
    </div>
</section>

<style>
    .plugins-page {
        padding: var(--spacing-3xl) 0;
    }

    .page-header {
        margin-bottom: var(--spacing-2xl);
    }

    .page-header p {
        color: var(--color-text-light);
        font-size: 1.15rem;
        margin-top: var(--spacing-sm);
    }

    .plugin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--spacing-xl);
    }

    .loading, .empty-state, .error-state {
        text-align: center;
        padding: var(--spacing-3xl);
        color: var(--color-text-light);
        font-size: 1.1rem;
    }

    .error-state {
        color: var(--color-danger);
    }

    @media (max-width: 768px) {
        .plugin-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
