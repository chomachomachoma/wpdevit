<script>
    import { navigate } from '../lib/stores.js';
    import { openCheckout, customerPortalUrl, portalIsEmbeddable } from '../lib/freemius.js';
    import { FREEMIUS } from '../lib/config.js';

    const portalUrl = customerPortalUrl(FREEMIUS);
    const embeddable = portalIsEmbeddable(FREEMIUS);

    let email = $state('');
    let checkoutStatus = $state('idle'); // idle | opening | error
    let checkoutError = $state('');

    function openPortal(e) {
        e?.preventDefault();
        window.open(portalUrl, '_blank', 'noopener,noreferrer');
    }

    async function buyLicense(e) {
        e?.preventDefault();
        checkoutStatus = 'opening';
        checkoutError = '';
        try {
            await openCheckout(FREEMIUS, { name: 'GF Validation', licenses: 1 });
            checkoutStatus = 'idle';
        } catch (err) {
            checkoutStatus = 'error';
            checkoutError = err.message;
        }
    }
</script>

<section class="account">
    <div class="container">
        <div class="page-header">
            <span class="eyebrow">Customer Area</span>
            <h1>Account &amp; Subscriptions</h1>
            <p>Manage your licenses, update billing, download the latest version, or cancel &mdash;
               all handled securely through the Freemius customer portal.</p>
        </div>

        <div class="account-grid">
            <!-- Primary: customer portal -->
            <div class="card primary">
                <div class="card-icon">&#128100;</div>
                <h2>Manage your subscription</h2>
                <p class="lead">
                    Open the customer portal and sign in with the email you used at checkout.
                    Freemius emails you a secure, passwordless magic link &mdash; no password to remember.
                </p>

                <ul class="capabilities">
                    <li>View &amp; download your purchases</li>
                    <li>Upgrade, downgrade, or cancel a subscription</li>
                    <li>Update your payment method &amp; billing details</li>
                    <li>Manage site activations and license keys</li>
                    <li>Download invoices and view order history</li>
                </ul>

                <div class="card-actions">
                    <a href={portalUrl} class="btn btn-primary btn-lg" onclick={openPortal}>
                        Open Customer Portal &rarr;
                    </a>
                </div>

                <form class="portal-hint" onsubmit={openPortal}>
                    <label for="acct-email">Your purchase email</label>
                    <div class="inline">
                        <input id="acct-email" type="email" bind:value={email} placeholder="you@example.com" />
                        <button type="submit" class="btn btn-outline">Send magic link</button>
                    </div>
                    <small>You'll finish signing in on the secure Freemius portal.</small>
                </form>

                {#if embeddable}
                    <div class="portal-embed">
                        <iframe title="Customer Portal" src={portalUrl} loading="lazy"></iframe>
                    </div>
                {/if}
            </div>

            <!-- Side column -->
            <div class="side">
                <div class="card">
                    <div class="card-icon sm">&#128273;</div>
                    <h3>Activate a license</h3>
                    <p>After purchase, activate GF Validation on your site:</p>
                    <ol class="steps">
                        <li>Install &amp; activate the plugin you downloaded.</li>
                        <li>Go to <strong>Plugins &rarr; GF Validation</strong>.</li>
                        <li>Click <strong>Activate License</strong> and paste your key.</li>
                    </ol>
                    <p class="muted">Your license key is shown in the customer portal under <em>Licenses</em>.</p>
                </div>

                <div class="card buy">
                    <div class="card-icon sm">&#128722;</div>
                    <h3>Need a license?</h3>
                    <p>Purchase GF Validation and start validating in minutes.</p>
                    <button class="btn btn-primary" onclick={buyLicense} disabled={checkoutStatus === 'opening'}>
                        {checkoutStatus === 'opening' ? 'Opening checkout…' : 'Buy GF Validation'}
                    </button>
                    <a href="/plugins/gf-validation" class="text-link"
                       onclick={(e) => { e.preventDefault(); navigate('/plugins/gf-validation'); }}>
                        View pricing &amp; plans &rarr;
                    </a>
                    {#if checkoutStatus === 'error'}
                        <p class="err">{checkoutError}</p>
                    {/if}
                </div>

                <div class="card support">
                    <div class="card-icon sm">&#128172;</div>
                    <h3>Need a hand?</h3>
                    <p>Questions about billing or licensing? We're happy to help.</p>
                    <a href="/contact" class="text-link"
                       onclick={(e) => { e.preventDefault(); navigate('/contact'); }}>Contact support &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .account { padding: var(--spacing-3xl) 0; }
    .page-header { max-width: 720px; margin-bottom: var(--spacing-2xl); }
    .eyebrow {
        display: inline-block; text-transform: uppercase; letter-spacing: 0.08em;
        font-size: 0.8rem; font-weight: 700; color: var(--color-accent);
        background: var(--color-accent-light); padding: 4px 12px; border-radius: 20px;
        margin-bottom: var(--spacing-md);
    }
    .page-header p { color: var(--color-text-light); font-size: 1.15rem; margin-top: var(--spacing-sm); line-height: 1.6; }

    .account-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: var(--spacing-xl); align-items: start; }

    .card {
        background: var(--color-bg); border: 1px solid var(--color-border);
        border-radius: var(--radius-lg); padding: var(--spacing-xl);
    }
    .card.primary {
        background: linear-gradient(180deg, #fbfcff 0%, var(--color-bg) 60%);
        border-color: #d7defc;
    }
    .card-icon { font-size: 2rem; margin-bottom: var(--spacing-sm); }
    .card-icon.sm { font-size: 1.5rem; }
    .card h2 { margin-bottom: var(--spacing-sm); }
    .card .lead { color: var(--color-text-light); line-height: 1.6; margin-bottom: var(--spacing-lg); }

    .capabilities { list-style: none; padding: 0; margin: 0 0 var(--spacing-lg); display: grid; gap: var(--spacing-xs); }
    .capabilities li { padding: var(--spacing-xs) 0; padding-left: 1.75rem; position: relative; color: var(--color-text); }
    .capabilities li::before {
        content: '\2713'; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
        width: 20px; height: 20px; border-radius: 50%; background: var(--color-accent-light);
        color: var(--color-accent); font-size: 0.75rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
    }

    .card-actions { margin-bottom: var(--spacing-lg); }

    .portal-hint {
        border-top: 1px solid var(--color-border); padding-top: var(--spacing-lg);
        display: flex; flex-direction: column; gap: var(--spacing-xs);
    }
    .portal-hint label { font-weight: 600; font-size: 0.9rem; }
    .portal-hint .inline { display: flex; gap: var(--spacing-sm); }
    .portal-hint input {
        flex: 1; padding: var(--spacing-sm) var(--spacing-md);
        border: 2px solid var(--color-border); border-radius: var(--radius-md); font-size: 1rem;
    }
    .portal-hint input:focus { outline: none; border-color: var(--color-accent); }
    .portal-hint small { color: var(--color-text-light); }

    .portal-embed { margin-top: var(--spacing-lg); border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-border); }
    .portal-embed iframe { width: 100%; height: 620px; border: 0; display: block; }

    .side { display: flex; flex-direction: column; gap: var(--spacing-lg); }
    .side h3 { margin-bottom: var(--spacing-sm); font-size: 1.15rem; }
    .side p { color: var(--color-text-light); line-height: 1.6; margin-bottom: var(--spacing-md); }
    .steps { margin: 0 0 var(--spacing-md) 1.1rem; color: var(--color-text); }
    .steps li { padding: 2px 0; }
    .muted { font-size: 0.85rem; color: var(--color-text-light); }
    .card.buy { background: var(--color-bg-alt); }
    .text-link { display: inline-block; margin-top: var(--spacing-sm); font-weight: 600; font-size: 0.95rem; }
    .err { color: var(--color-danger); font-size: 0.9rem; margin-top: var(--spacing-sm); }

    @media (max-width: 900px) {
        .account-grid { grid-template-columns: 1fr; }
    }
</style>
