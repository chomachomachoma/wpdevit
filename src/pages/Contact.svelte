<script>
    import ContactForm from '../components/ContactForm.svelte';
</script>

<section class="contact-page">
    <div class="container">
        <div class="contact-layout">
            <div class="contact-info">
                <h1>Get in Touch</h1>
                <p>
                    Have a question about one of our plugins? Need a custom solution?
                    We'd love to hear from you.
                </p>

                <div class="info-items">
                    <div class="info-item">
                        <h3>Email</h3>
                        <p>hello@wpdevit.local</p>
                    </div>
                    <div class="info-item">
                        <h3>Response Time</h3>
                        <p>We typically respond within 24 hours on business days.</p>
                    </div>
                </div>
            </div>

            <div class="contact-form-wrapper">
                <ContactForm />
            </div>
        </div>
    </div>
</section>

<style>
    .contact-page {
        padding: var(--spacing-3xl) 0;
    }

    .contact-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-3xl);
        align-items: start;
    }

    .contact-info h1 {
        margin-bottom: var(--spacing-lg);
    }

    .contact-info > p {
        font-size: 1.1rem;
        color: var(--color-text-light);
        line-height: 1.6;
        margin-bottom: var(--spacing-2xl);
    }

    .info-items {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-lg);
    }

    .info-item h3 {
        font-size: 1rem;
        margin-bottom: var(--spacing-xs);
    }

    .info-item p {
        color: var(--color-text-light);
    }

    .contact-form-wrapper {
        background: var(--color-bg-alt);
        border-radius: var(--radius-xl);
        padding: var(--spacing-2xl);
        border: 1px solid var(--color-border);
    }

    @media (max-width: 768px) {
        .contact-layout {
            grid-template-columns: 1fr;
            gap: var(--spacing-xl);
        }
    }
</style>
