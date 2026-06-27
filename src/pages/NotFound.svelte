<script>
    import { navigate } from '../lib/stores.js';

    function handleHome(e) {
        e.preventDefault();
        navigate('/');
    }
</script>

<section class="not-found">
    <div class="container">
        <h1>404</h1>
        <p>The page you're looking for doesn't exist.</p>
        <a href="/" class="btn btn-primary btn-lg" onclick={handleHome}>
            Back to Home
        </a>
    </div>
</section>

<style>
    .not-found {
        padding: var(--spacing-3xl) 0;
        text-align: center;
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .not-found h1 {
        font-size: 6rem;
        color: var(--color-accent);
        margin-bottom: var(--spacing-md);
    }

    .not-found p {
        font-size: 1.25rem;
        color: var(--color-text-light);
        margin-bottom: var(--spacing-2xl);
    }
</style>
