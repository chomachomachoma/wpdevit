<script>
    import { currentPath } from './lib/stores.js';
    import Header from './components/Header.svelte';
    import Footer from './components/Footer.svelte';
    import Home from './pages/Home.svelte';
    import Plugins from './pages/Plugins.svelte';
    import PluginSingle from './pages/PluginSingle.svelte';
    import About from './pages/About.svelte';
    import Contact from './pages/Contact.svelte';
    import Account from './pages/Account.svelte';
    import NotFound from './pages/NotFound.svelte';

    let path = $derived($currentPath);

    let route = $derived.by(() => {
        const clean = path.replace(/\/+$/, '') || '/';

        if (clean === '/') return { component: Home, props: {} };
        if (clean === '/plugins') return { component: Plugins, props: {} };
        if (clean.startsWith('/plugins/')) {
            const slug = clean.replace('/plugins/', '');
            return { component: PluginSingle, props: { slug } };
        }
        if (clean === '/about') return { component: About, props: {} };
        if (clean === '/contact') return { component: Contact, props: {} };
        if (clean === '/account') return { component: Account, props: {} };
        return { component: NotFound, props: {} };
    });
</script>

<Header />

<main>
    {#key path}
        <route.component {...route.props} />
    {/key}
</main>

<Footer />

<style>
    main {
        flex: 1;
    }
</style>
