<script>
    import { openCheckout, resolveFreemius } from '../lib/freemius.js';

    let {
        product,
        plan = null,
        label = 'Buy Now',
        licenses = 1,
        variant = 'btn-primary',
        block = false,
        onpurchase = null,
    } = $props();

    let status = $state('idle'); // idle | opening | error | done
    let message = $state('');

    // Only treat the product as purchasable via Freemius when it carries its own
    // explicit config. Free products (no freemius) fall back to download_url.
    const freemius = $derived(product?.freemius?.plugin_id ? resolveFreemius(product.freemius) : null);

    async function handleClick(e) {
        e.preventDefault();
        message = '';

        // If there's no Freemius config but a direct download/checkout URL exists, use it.
        if (!freemius) {
            if (product?.download_url) {
                window.open(product.download_url, '_blank', 'noopener,noreferrer');
                return;
            }
            status = 'error';
            message = 'This product is not available for purchase yet.';
            return;
        }

        status = 'opening';
        try {
            const result = await openCheckout(freemius, {
                name: product?.title,
                licenses,
                plan_id: plan?.plan_id,
                pricing_id: plan?.pricing_id,
                billingCycle: plan?.period === 'lifetime' ? undefined : plan?.billingCycle,
                onComplete: (data) => { if (onpurchase) onpurchase(data); },
            });
            status = result.status === 'success' ? 'done' : 'idle';
            if (result.status === 'success') message = 'Thanks for your purchase! Check your email for your license.';
        } catch (err) {
            status = 'error';
            message = err.message;
        }
    }
</script>

<button class="btn {variant}" class:block onclick={handleClick} disabled={status === 'opening'}>
    {status === 'opening' ? 'Opening checkout…' : label}
</button>
{#if message}
    <p class="buy-msg" class:err={status === 'error'} class:ok={status === 'done'}>{message}</p>
{/if}

<style>
    .block { width: 100%; }
    .buy-msg { font-size: 0.85rem; margin-top: var(--spacing-sm); }
    .buy-msg.err { color: var(--color-danger); }
    .buy-msg.ok { color: var(--color-success); }
</style>
