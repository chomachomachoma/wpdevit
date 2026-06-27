<script>
    import { submitContact } from '../lib/api.js';

    let name = $state('');
    let email = $state('');
    let message = $state('');
    let status = $state('idle'); // idle, sending, success, error
    let errorMessage = $state('');

    async function handleSubmit(e) {
        e.preventDefault();
        status = 'sending';
        errorMessage = '';

        try {
            await submitContact({ name, email, message });
            status = 'success';
            name = '';
            email = '';
            message = '';
        } catch (err) {
            status = 'error';
            errorMessage = err.message || 'Something went wrong. Please try again.';
        }
    }
</script>

<form onsubmit={handleSubmit} class="contact-form">
    {#if status === 'success'}
        <div class="alert alert-success">
            Thanks for reaching out! We'll get back to you soon.
        </div>
    {/if}

    {#if status === 'error'}
        <div class="alert alert-error">
            {errorMessage}
        </div>
    {/if}

    <div class="form-group">
        <label for="name">Name</label>
        <input
            id="name"
            type="text"
            bind:value={name}
            required
            placeholder="Your name"
            disabled={status === 'sending'}
        />
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input
            id="email"
            type="email"
            bind:value={email}
            required
            placeholder="you@example.com"
            disabled={status === 'sending'}
        />
    </div>

    <div class="form-group">
        <label for="message">Message</label>
        <textarea
            id="message"
            bind:value={message}
            required
            rows="6"
            placeholder="Tell us about your project or question..."
            disabled={status === 'sending'}
        ></textarea>
    </div>

    <button type="submit" class="btn btn-primary btn-lg" disabled={status === 'sending'}>
        {status === 'sending' ? 'Sending...' : 'Send Message'}
    </button>
</form>

<style>
    .contact-form {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-lg);
        max-width: 600px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    label {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--color-text);
    }

    input, textarea {
        padding: var(--spacing-sm) var(--spacing-md);
        border: 2px solid var(--color-border);
        border-radius: var(--radius-md);
        font-family: var(--font-sans);
        font-size: 1rem;
        color: var(--color-text);
        background: var(--color-bg);
        transition: border-color 0.2s ease;
    }

    input:focus, textarea:focus {
        outline: none;
        border-color: var(--color-accent);
    }

    input:disabled, textarea:disabled {
        opacity: 0.6;
    }

    textarea {
        resize: vertical;
    }

    .alert {
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        font-weight: 500;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
