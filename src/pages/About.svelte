<script>
    import { navigate } from '../lib/stores.js';

    function handleNav(e, path) {
        e.preventDefault();
        navigate(path);
    }
</script>

<section class="about-page">
    <div class="container">
        <div class="page-header">
            <h1>About WPDevIT</h1>
            <p class="lead">We build WordPress plugins that developers actually want to use.</p>
        </div>

        <div class="about-content">
            <div class="about-section">
                <h2>Our Mission</h2>
                <p>
                    WPDevIT was founded with a simple belief: WordPress plugins should be fast,
                    reliable, and well-coded. Too many plugins slow down sites, conflict with others,
                    or become abandoned. We're here to change that.
                </p>
                <p>
                    Every plugin we build follows WordPress coding standards, uses modern PHP practices,
                    and is optimized for performance from day one. We don't ship bloat.
                </p>
            </div>

            <div class="about-section">
                <h2>What We Do</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <h3>Custom Plugin Development</h3>
                        <p>Purpose-built plugins that solve real problems without the feature bloat of all-in-one solutions.</p>
                    </div>
                    <div class="value-card">
                        <h3>Performance Optimization</h3>
                        <p>Every query is optimized, every asset is minimal, and every feature earns its place.</p>
                    </div>
                    <div class="value-card">
                        <h3>Ongoing Support</h3>
                        <p>Regular updates, security patches, and compatibility testing with every major WordPress release.</p>
                    </div>
                    <div class="value-card">
                        <h3>Developer-First Design</h3>
                        <p>Hooks, filters, and clean APIs so you can customize and extend everything we build.</p>
                    </div>
                </div>
            </div>

            <div class="about-cta">
                <h2>Ready to get started?</h2>
                <p>Check out our plugins or reach out to discuss a custom solution for your project.</p>
                <div class="cta-actions">
                    <a href="/plugins" class="btn btn-primary btn-lg" onclick={(e) => handleNav(e, '/plugins')}>
                        Browse Plugins
                    </a>
                    <a href="/contact" class="btn btn-outline btn-lg" onclick={(e) => handleNav(e, '/contact')}>
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .about-page {
        padding: var(--spacing-3xl) 0;
    }

    .page-header {
        margin-bottom: var(--spacing-2xl);
    }

    .lead {
        font-size: 1.25rem;
        color: var(--color-text-light);
        margin-top: var(--spacing-sm);
    }

    .about-section {
        margin-bottom: var(--spacing-3xl);
    }

    .about-section h2 {
        margin-bottom: var(--spacing-lg);
    }

    .about-section p {
        font-size: 1.05rem;
        line-height: 1.7;
        color: var(--color-text-light);
        margin-bottom: var(--spacing-md);
        max-width: 700px;
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: var(--spacing-lg);
        margin-top: var(--spacing-lg);
    }

    .value-card {
        background: var(--color-bg-alt);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: var(--spacing-xl);
    }

    .value-card h3 {
        font-size: 1.15rem;
        margin-bottom: var(--spacing-sm);
    }

    .value-card p {
        color: var(--color-text-light);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .about-cta {
        background: var(--color-bg-alt);
        border-radius: var(--radius-xl);
        padding: var(--spacing-2xl);
        text-align: center;
    }

    .about-cta h2 {
        margin-bottom: var(--spacing-sm);
    }

    .about-cta p {
        color: var(--color-text-light);
        font-size: 1.05rem;
        margin-bottom: var(--spacing-xl);
    }

    .cta-actions {
        display: flex;
        gap: var(--spacing-md);
        justify-content: center;
        flex-wrap: wrap;
    }
</style>
