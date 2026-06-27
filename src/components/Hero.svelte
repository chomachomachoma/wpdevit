<script>
    import { navigate } from '../lib/stores.js';

    function handleNav(e, path) {
        e.preventDefault();
        navigate(path);
    }
</script>

<section class="hero">
    <div class="container hero-inner">
        <span class="hero-eyebrow">Gravity Forms Add-ons</span>
        <h1>Supercharge Your<br>Gravity Forms</h1>
        <p class="hero-subtitle">
            Add advanced field validation and role-based access control to Gravity Forms —
            no code required. Lightweight, secure, and built by developers who live in WordPress.
        </p>
        <div class="hero-actions">
            <a href="/plugins" class="btn btn-primary btn-lg" onclick={(e) => handleNav(e, '/plugins')}>
                Explore the Add-ons
            </a>
            <a href="/plugins/gf-validation" class="btn btn-outline btn-lg" onclick={(e) => handleNav(e, '/plugins/gf-validation')}>
                Try a Live Demo
            </a>
        </div>
    </div>
</section>

<style>
    .hero {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
        color: var(--color-text-inverse);
        padding: var(--spacing-3xl) 0;
        min-height: 60vh;
        display: flex;
        align-items: center;
    }

    .hero-inner {
        text-align: center;
        max-width: 820px;
    }

    .hero-eyebrow {
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-size: 0.8rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.85);
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        padding: 6px 16px;
        border-radius: 30px;
        margin-bottom: var(--spacing-lg);
    }

    .hero h1 {
        color: var(--color-text-inverse);
        font-size: 3rem;
        margin-bottom: var(--spacing-lg);
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: var(--spacing-2xl);
        line-height: 1.6;
    }

    .hero-actions {
        display: flex;
        gap: var(--spacing-md);
        justify-content: center;
        flex-wrap: wrap;
    }

    .hero .btn-outline {
        color: var(--color-text-inverse);
        border-color: var(--color-text-inverse);
    }

    .hero .btn-outline:hover {
        background: var(--color-text-inverse);
        color: var(--color-primary);
    }

    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2rem;
        }

        .hero-subtitle {
            font-size: 1rem;
        }

        .hero {
            min-height: auto;
            padding: var(--spacing-2xl) 0;
        }
    }
</style>
