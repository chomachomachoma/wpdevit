<script>
    import { navigate } from '../lib/stores.js';
    import { fetchProduct } from '../lib/api.js';
    import { customerPortalUrl } from '../lib/freemius.js';
    import BuyButton from '../components/BuyButton.svelte';
    import GFValidationDemo from '../components/GFValidationDemo.svelte';
    import GFRestrictionsDemo from '../components/GFRestrictionsDemo.svelte';

    let { slug } = $props();

    let product = $state(null);
    let loading = $state(true);
    let error = $state('');
    let lightbox = $state(-1); // index of open screenshot, -1 = closed
    let openFaq = $state(0);

    $effect(() => {
        loading = true;
        error = '';
        product = null;
        fetchProduct(slug)
            .then(data => { product = data; })
            .catch(err => { error = err.message; })
            .finally(() => { loading = false; });
    });

    const DemoComponent = $derived(
        product?.demo_type === 'gf-validation' ? GFValidationDemo :
        product?.demo_type === 'gf-restrictions' ? GFRestrictionsDemo : null
    );

    function priceLabel(p) {
        if (!p || p === 'Free' || p === '0') return 'Free';
        return `$${p}`;
    }
    function isPaid(p) { return p && p !== 'Free' && p !== '0'; }

    function scrollTo(id) {
        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
    }

    function openShot(i) { lightbox = i; }
    function closeShot() { lightbox = -1; }
    function shotNav(dir) {
        const n = product.screenshots.length;
        lightbox = (lightbox + dir + n) % n;
    }
    function onKey(e) {
        if (lightbox < 0) return;
        if (e.key === 'Escape') closeShot();
        if (e.key === 'ArrowRight') shotNav(1);
        if (e.key === 'ArrowLeft') shotNav(-1);
    }
</script>

<svelte:window onkeydown={onKey} />

{#if loading}
    <div class="container state"><div class="spinner"></div><p>Loading product…</p></div>
{:else if error || !product}
    <div class="container state">
        <h2>Product not found</h2>
        <p>The plugin you're looking for doesn't exist.</p>
        <a href="/plugins" class="btn btn-primary" onclick={(e) => { e.preventDefault(); navigate('/plugins'); }}>Browse plugins</a>
    </div>
{:else}
    <!-- HERO -->
    <section class="p-hero">
        <div class="container hero-grid">
            <div class="hero-text">
                <a href="/plugins" class="back" onclick={(e) => { e.preventDefault(); navigate('/plugins'); }}>&larr; All plugins</a>
                <h1>{product.title}</h1>
                {#if product.tagline}<p class="tagline">{product.tagline}</p>{/if}
                <div class="hero-meta">
                    <span class="price">{priceLabel(product.price)}{#if isPaid(product.price)}<span class="per">/year</span>{/if}</span>
                    {#if product.version}<span class="ver">v{product.version}</span>{/if}
                    <span class="dot">·</span>
                    <span class="compat">For Gravity Forms</span>
                </div>
                <div class="hero-actions">
                    <BuyButton {product} label={isPaid(product.price) ? 'Get a License' : 'Download Free'} variant="btn-primary btn-lg" />
                    {#if DemoComponent}
                        <button class="btn btn-outline btn-lg" onclick={() => scrollTo('demo')}>Try the live demo</button>
                    {/if}
                </div>
            </div>
            {#if product.featured_image}
                <div class="hero-visual">
                    <img src={product.featured_image} alt={product.title} />
                </div>
            {/if}
        </div>
    </section>

    <!-- HIGHLIGHTS -->
    {#if product.highlights?.length}
        <section class="highlights">
            <div class="container hl-grid">
                {#each product.highlights as h}
                    <div class="hl">
                        <div class="hl-icon">{@html h.icon || '&#9670;'}</div>
                        <h3>{h.title}</h3>
                        <p>{h.text}</p>
                    </div>
                {/each}
            </div>
        </section>
    {/if}

    <!-- SCREENSHOTS -->
    {#if product.screenshots?.length}
        <section class="shots">
            <div class="container">
                <div class="section-head"><h2>See it in action</h2><p>Real screens from inside the WordPress admin.</p></div>
                <div class="shot-grid">
                    {#each product.screenshots as s, i}
                        <figure class="shot" onclick={() => openShot(i)} role="button" tabindex="0"
                                onkeydown={(e) => e.key === 'Enter' && openShot(i)}>
                            <img src={s.src} alt={s.caption || product.title} loading="lazy" />
                            {#if s.caption}<figcaption>{s.caption}</figcaption>{/if}
                        </figure>
                    {/each}
                </div>
            </div>
        </section>
    {/if}

    <!-- BODY + SIDEBAR -->
    <section class="body">
        <div class="container body-grid">
            <div class="content">
                {@html product.content}

                {#if product.features?.length}
                    <h2>Everything included</h2>
                    <ul class="feature-list">
                        {#each product.features as f}<li>{f}</li>{/each}
                    </ul>
                {/if}
            </div>

            <aside class="sidebar">
                <div class="buy-card">
                    <div class="buy-price">{priceLabel(product.price)}{#if isPaid(product.price)}<span class="per">/year</span>{/if}</div>
                    <BuyButton {product} label={isPaid(product.price) ? 'Get a License' : 'Download Free'} variant="btn-primary btn-lg" block={true} />
                    {#if product.demo_url}
                        <a href={product.demo_url} target="_blank" rel="noopener" class="btn btn-outline block">Live demo site</a>
                    {/if}
                    <dl class="specs">
                        {#if product.version}<div><dt>Version</dt><dd>{product.version}</dd></div>{/if}
                        {#if product.requirements?.wp}<div><dt>WordPress</dt><dd>{product.requirements.wp}+</dd></div>{/if}
                        {#if product.requirements?.php}<div><dt>PHP</dt><dd>{product.requirements.php}+</dd></div>{/if}
                        {#if product.requirements?.gf}<div><dt>Gravity Forms</dt><dd>{product.requirements.gf}+</dd></div>{/if}
                    </dl>
                    {#if product.freemius}
                        <a class="manage" href={customerPortalUrl(product.freemius)} target="_blank" rel="noopener">Already a customer? Manage your license &rarr;</a>
                    {/if}
                </div>
            </aside>
        </div>
    </section>

    <!-- DEMO -->
    {#if DemoComponent}
        <section class="demo-sec" id="demo">
            <div class="container">
                <div class="section-head"><h2>Try it yourself</h2><p>No install required — this runs the same rules the plugin enforces.</p></div>
                <DemoComponent />
            </div>
        </section>
    {/if}

    <!-- PRICING -->
    {#if product.pricing?.length}
        <section class="pricing" id="pricing">
            <div class="container">
                <div class="section-head"><h2>Simple, honest pricing</h2><p>One purchase. A year of updates and support. Cancel anytime.</p></div>
                <div class="plan-grid" class:few={product.pricing.length < 3}>
                    {#each product.pricing as plan}
                        <div class="plan" class:highlighted={plan.highlighted}>
                            {#if plan.highlighted}<span class="ribbon">Most popular</span>{/if}
                            <h3>{plan.name}</h3>
                            <div class="plan-price">{priceLabel(plan.price)}{#if isPaid(plan.price)}<span class="per">/{plan.period || 'year'}</span>{/if}</div>
                            {#if plan.tagline}<p class="plan-tag">{plan.tagline}</p>{/if}
                            <ul>
                                {#each plan.features as f}<li>{f}</li>{/each}
                            </ul>
                            <BuyButton {product} {plan} label={plan.cta || 'Choose plan'}
                                       variant={plan.highlighted ? 'btn-primary' : 'btn-outline'} block={true} />
                        </div>
                    {/each}
                </div>
                <p class="guarantee">&#128274; Secure checkout by Freemius · 14-day money-back guarantee</p>
            </div>
        </section>
    {/if}

    <!-- FAQ -->
    {#if product.faq?.length}
        <section class="faq">
            <div class="container narrow">
                <div class="section-head"><h2>Frequently asked questions</h2></div>
                <div class="faq-list">
                    {#each product.faq as item, i}
                        <div class="faq-item" class:open={openFaq === i}>
                            <button class="faq-q" onclick={() => openFaq = openFaq === i ? -1 : i}>
                                <span>{item.q}</span><span class="chev">{openFaq === i ? '−' : '+'}</span>
                            </button>
                            {#if openFaq === i}<div class="faq-a">{@html item.a}</div>{/if}
                        </div>
                    {/each}
                </div>
            </div>
        </section>
    {/if}

    <!-- CLOSING CTA -->
    <section class="closing">
        <div class="container">
            <h2>Ready to get started with {product.title}?</h2>
            <BuyButton {product} label={isPaid(product.price) ? 'Get a License' : 'Download Free'} variant="btn-primary btn-lg" />
        </div>
    </section>

    <!-- LIGHTBOX -->
    {#if lightbox >= 0}
        <div class="lightbox" onclick={closeShot} role="dialog" aria-modal="true">
            <button class="lb-close" onclick={closeShot} aria-label="Close">×</button>
            <button class="lb-nav prev" onclick={(e) => { e.stopPropagation(); shotNav(-1); }} aria-label="Previous">‹</button>
            <figure class="lb-fig" onclick={(e) => e.stopPropagation()}>
                <img src={product.screenshots[lightbox].src} alt={product.screenshots[lightbox].caption || ''} />
                {#if product.screenshots[lightbox].caption}<figcaption>{product.screenshots[lightbox].caption}</figcaption>{/if}
            </figure>
            <button class="lb-nav next" onclick={(e) => { e.stopPropagation(); shotNav(1); }} aria-label="Next">›</button>
        </div>
    {/if}
{/if}

<style>
    .state { text-align: center; padding: var(--spacing-3xl) 0; min-height: 50vh; }
    .state h2 { margin-bottom: var(--spacing-sm); }
    .state p { color: var(--color-text-light); margin-bottom: var(--spacing-lg); }
    .spinner { width: 38px; height: 38px; border: 3px solid var(--color-border); border-top-color: var(--color-accent); border-radius: 50%; margin: 0 auto var(--spacing-md); animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Hero */
    .p-hero { background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%); color: #fff; padding: var(--spacing-3xl) 0; }
    .hero-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-2xl); align-items: center; }
    .back { color: rgba(255,255,255,0.75); font-size: 0.9rem; display: inline-block; margin-bottom: var(--spacing-lg); }
    .back:hover { color: #fff; }
    .hero-text h1 { color: #fff; font-size: 2.75rem; margin-bottom: var(--spacing-sm); }
    .tagline { font-size: 1.25rem; color: rgba(255,255,255,0.85); line-height: 1.5; margin-bottom: var(--spacing-lg); }
    .hero-meta { display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-xl); flex-wrap: wrap; }
    .price { font-size: 1.6rem; font-weight: 800; color: var(--color-warning); }
    .price .per, .plan-price .per, .buy-price .per { font-size: 0.95rem; font-weight: 500; }
    .price .per { color: rgba(255,255,255,0.6); }
    .ver { font-family: var(--font-mono); font-size: 0.85rem; background: rgba(255,255,255,0.12); padding: 3px 10px; border-radius: 20px; }
    .dot { color: rgba(255,255,255,0.4); }
    .compat { color: rgba(255,255,255,0.8); font-size: 0.95rem; }
    .hero-actions { display: flex; gap: var(--spacing-md); flex-wrap: wrap; }
    .p-hero .btn-outline { color: #fff; border-color: rgba(255,255,255,0.5); }
    .p-hero .btn-outline:hover { background: #fff; color: var(--color-primary); }
    .hero-visual img { border-radius: var(--radius-lg); box-shadow: 0 30px 60px rgba(0,0,0,0.35); }

    /* Highlights */
    .highlights { padding: var(--spacing-3xl) 0; }
    .hl-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--spacing-xl); }
    .hl { text-align: center; padding: var(--spacing-lg); }
    .hl-icon { font-size: 2rem; margin-bottom: var(--spacing-sm); }
    .hl h3 { font-size: 1.15rem; margin-bottom: var(--spacing-sm); }
    .hl p { color: var(--color-text-light); line-height: 1.6; }

    /* Sections */
    .section-head { text-align: center; max-width: 640px; margin: 0 auto var(--spacing-2xl); }
    .section-head p { color: var(--color-text-light); font-size: 1.1rem; margin-top: var(--spacing-sm); }

    /* Screenshots */
    .shots { padding: var(--spacing-3xl) 0; background: var(--color-bg-alt); }
    .shot-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--spacing-lg); }
    .shot { margin: 0; cursor: pointer; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); background: var(--color-bg); transition: transform 0.2s, box-shadow 0.2s; }
    .shot:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .shot img { width: 100%; display: block; }
    .shot figcaption { padding: var(--spacing-md); font-size: 0.9rem; color: var(--color-text-light); }

    /* Body + sidebar */
    .body { padding: var(--spacing-3xl) 0; }
    .body-grid { display: grid; grid-template-columns: 1fr 340px; gap: var(--spacing-2xl); align-items: start; }
    .content { line-height: 1.75; font-size: 1.05rem; }
    .content :global(h2) { margin: var(--spacing-2xl) 0 var(--spacing-md); }
    .content :global(h3) { margin: var(--spacing-xl) 0 var(--spacing-sm); }
    .content :global(p) { margin-bottom: var(--spacing-md); }
    .content :global(ul), .content :global(ol) { margin: 0 0 var(--spacing-md) var(--spacing-xl); }
    .content :global(li) { margin-bottom: var(--spacing-xs); }
    .feature-list { list-style: none; padding: 0; columns: 2; column-gap: var(--spacing-2xl); }
    .feature-list li { padding: var(--spacing-xs) 0 var(--spacing-xs) 1.75rem; position: relative; break-inside: avoid; }
    .feature-list li::before { content: '\2713'; position: absolute; left: 0; color: var(--color-success); font-weight: 800; }

    .sidebar { position: sticky; top: calc(var(--header-height) + var(--spacing-lg)); }
    .buy-card { background: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--spacing-xl); }
    .buy-price { font-size: 2rem; font-weight: 800; color: var(--color-primary); text-align: center; margin-bottom: var(--spacing-lg); }
    .buy-price .per { color: var(--color-text-light); }
    .buy-card .block { margin-top: var(--spacing-sm); }
    .specs { margin: var(--spacing-lg) 0 0; border-top: 1px solid var(--color-border); padding-top: var(--spacing-md); }
    .specs div { display: flex; justify-content: space-between; padding: var(--spacing-xs) 0; font-size: 0.9rem; }
    .specs dt { color: var(--color-text-light); }
    .specs dd { font-weight: 600; font-family: var(--font-mono); }
    .manage { display: block; margin-top: var(--spacing-lg); font-size: 0.85rem; text-align: center; }

    /* Demo */
    .demo-sec { padding: var(--spacing-3xl) 0; background: var(--color-bg-alt); }

    /* Pricing */
    .pricing { padding: var(--spacing-3xl) 0; }
    .plan-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: var(--spacing-xl); align-items: start; max-width: 1000px; margin: 0 auto; }
    .plan-grid.few { grid-template-columns: repeat(auto-fit, minmax(260px, 320px)); justify-content: center; }
    .plan { background: var(--color-bg); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--spacing-xl); position: relative; }
    .plan.highlighted { border: 2px solid var(--color-accent); box-shadow: var(--shadow-lg); }
    .ribbon { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--color-accent); color: #fff; font-size: 0.75rem; font-weight: 700; padding: 4px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.04em; }
    .plan h3 { margin-bottom: var(--spacing-sm); }
    .plan-price { font-size: 2rem; font-weight: 800; color: var(--color-primary); margin-bottom: var(--spacing-xs); }
    .plan-price .per { font-size: 0.95rem; font-weight: 500; color: var(--color-text-light); }
    .plan-tag { color: var(--color-text-light); font-size: 0.9rem; margin-bottom: var(--spacing-md); }
    .plan ul { list-style: none; padding: 0; margin: var(--spacing-md) 0 var(--spacing-lg); }
    .plan li { padding: var(--spacing-xs) 0 var(--spacing-xs) 1.6rem; position: relative; font-size: 0.95rem; }
    .plan li::before { content: '\2713'; position: absolute; left: 0; color: var(--color-success); font-weight: 800; }
    .guarantee { text-align: center; color: var(--color-text-light); margin-top: var(--spacing-2xl); font-size: 0.9rem; }

    /* FAQ */
    .faq { padding: var(--spacing-3xl) 0; background: var(--color-bg-alt); }
    .narrow { max-width: 760px; }
    .faq-item { background: var(--color-bg); border: 1px solid var(--color-border); border-radius: var(--radius-md); margin-bottom: var(--spacing-md); overflow: hidden; }
    .faq-q { width: 100%; text-align: left; background: none; border: none; padding: var(--spacing-lg); font-size: 1.05rem; font-weight: 600; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: var(--spacing-md); color: var(--color-text); }
    .chev { color: var(--color-accent); font-size: 1.3rem; flex: 0 0 auto; }
    .faq-a { padding: 0 var(--spacing-lg) var(--spacing-lg); color: var(--color-text-light); line-height: 1.7; }

    /* Closing */
    .closing { padding: var(--spacing-3xl) 0; text-align: center; }
    .closing h2 { margin-bottom: var(--spacing-lg); }

    /* Lightbox */
    .lightbox { position: fixed; inset: 0; background: rgba(10,12,24,0.92); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: var(--spacing-xl); }
    .lb-fig { margin: 0; max-width: 1100px; max-height: 90vh; }
    .lb-fig img { max-width: 100%; max-height: 82vh; border-radius: var(--radius-md); box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
    .lb-fig figcaption { color: rgba(255,255,255,0.85); text-align: center; margin-top: var(--spacing-md); }
    .lb-close { position: absolute; top: 18px; right: 26px; background: none; border: none; color: #fff; font-size: 2.4rem; line-height: 1; cursor: pointer; }
    .lb-nav { background: rgba(255,255,255,0.12); border: none; color: #fff; font-size: 2.4rem; width: 56px; height: 56px; border-radius: 50%; cursor: pointer; flex: 0 0 auto; }
    .lb-nav:hover { background: rgba(255,255,255,0.25); }
    .lb-nav.prev { margin-right: var(--spacing-md); }
    .lb-nav.next { margin-left: var(--spacing-md); }

    @media (max-width: 900px) {
        .hero-grid, .body-grid { grid-template-columns: 1fr; }
        .hero-visual { order: -1; }
        .sidebar { position: static; }
        .feature-list { columns: 1; }
        .lb-nav { width: 44px; height: 44px; font-size: 1.8rem; }
    }
</style>
