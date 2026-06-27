<script>
    // Interactive demo mirroring GF Restrictions: a form is gated by role and/or
    // user ID. Pick who's "visiting" and watch the form show or get replaced by
    // your custom message — the same behaviour the plugin applies via the
    // `gform_get_form_filter`.

    let mode = $state('role'); // role | user

    // Simulated visitors
    const visitors = [
        { id: 1, label: 'Admin (Jane, ID 1)', role: 'administrator', loggedIn: true },
        { id: 14, label: 'Editor (Mark, ID 14)', role: 'editor', loggedIn: true },
        { id: 33, label: 'Author (Priya, ID 33)', role: 'author', loggedIn: true },
        { id: 102, label: 'Subscriber (Sam, ID 102)', role: 'subscriber', loggedIn: true },
        { id: 0, label: 'Logged-out visitor', role: null, loggedIn: false },
    ];
    let visitorId = $state(102);
    let visitor = $derived(visitors.find(v => v.id === visitorId));

    // Role gate config
    const allRoles = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
    let allowedRoles = $state(['administrator', 'editor']);
    function toggleRole(r) {
        allowedRoles = allowedRoles.includes(r) ? allowedRoles.filter(x => x !== r) : [...allowedRoles, r];
    }

    // User gate config
    let allowedIds = $state('1, 14, 27');
    let parsedIds = $derived(allowedIds.split(',').map(s => parseInt(s.trim(), 10)).filter(n => !isNaN(n)));

    const roleMessage = 'Sorry — this form is available to staff and members only.';
    const userMessage = 'This beta form is limited to invited testers. Contact us to request access.';

    let access = $derived.by(() => {
        if (mode === 'role') {
            if (!visitor.loggedIn) return { allowed: false, msg: roleMessage };
            return { allowed: allowedRoles.includes(visitor.role), msg: roleMessage };
        }
        // user-id mode
        if (!visitor.loggedIn) return { allowed: false, msg: userMessage };
        return { allowed: parsedIds.includes(visitor.id), msg: userMessage };
    });
</script>

<div class="demo">
    <div class="demo-head">
        <span class="badge">Live demo</span>
        <p>Pick who's visiting and how the form is gated. The page updates just like it would on a real site.</p>
    </div>

    <div class="demo-body">
        <div class="controls">
            <div class="ctl">
                <span class="ctl-label">Restrict access by</span>
                <div class="seg">
                    <button class:on={mode === 'role'} onclick={() => mode = 'role'}>User role</button>
                    <button class:on={mode === 'user'} onclick={() => mode = 'user'}>User ID</button>
                </div>
            </div>

            <div class="ctl">
                <label for="visitor">Viewing as</label>
                <select id="visitor" bind:value={visitorId}>
                    {#each visitors as v}<option value={v.id}>{v.label}</option>{/each}
                </select>
            </div>

            {#if mode === 'role'}
                <div class="ctl">
                    <span class="ctl-label">Permitted roles</span>
                    <div class="rolechecks">
                        {#each allRoles as r}
                            <label class="check"><input type="checkbox" checked={allowedRoles.includes(r)} onchange={() => toggleRole(r)} />
                                <span class="cap">{r}</span></label>
                        {/each}
                    </div>
                </div>
            {:else}
                <div class="ctl">
                    <label for="ids">Permitted user IDs</label>
                    <input id="ids" type="text" bind:value={allowedIds} />
                    <small>Comma-separated. Current visitor ID: <strong>{visitor.loggedIn ? visitor.id : '—'}</strong></small>
                </div>
            {/if}
        </div>

        <div class="preview">
            <div class="browser">
                <div class="bar"><span class="d"></span><span class="d"></span><span class="d"></span>
                    <span class="addr">yoursite.com/submit-a-request</span></div>
                <div class="page">
                    <h3>Submit a Request</h3>
                    {#if access.allowed}
                        <div class="gform">
                            <span class="flabel">Name</span><div class="inp"></div>
                            <span class="flabel">Email</span><div class="inp"></div>
                            <span class="flabel">Message</span><div class="inp tall"></div>
                            <div class="submit">Submit</div>
                            <p class="granted">&#10003; Access granted — form rendered for this visitor.</p>
                        </div>
                    {:else}
                        <div class="blocked">
                            <div class="lock">&#128274;</div>
                            <p>{access.msg}</p>
                        </div>
                        <p class="note">&#10003; The form markup is never output for unauthorized visitors.</p>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .demo { border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden; background: var(--color-bg); }
    .demo-head { padding: var(--spacing-lg) var(--spacing-xl) 0; }
    .badge {
        display: inline-block; background: var(--color-success); color: #04352a;
        font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em;
        padding: 3px 10px; border-radius: 20px; margin-bottom: var(--spacing-sm);
    }
    .demo-head p { color: var(--color-text-light); margin: 0; font-size: 0.95rem; }

    .demo-body { display: grid; grid-template-columns: 1fr 1.1fr; gap: var(--spacing-xl); padding: var(--spacing-xl); }

    .controls { display: flex; flex-direction: column; gap: var(--spacing-lg); }
    .ctl { display: flex; flex-direction: column; gap: var(--spacing-sm); }
    .ctl-label, .ctl label { font-weight: 600; font-size: 0.9rem; }
    .ctl small { color: var(--color-text-light); font-size: 0.82rem; }
    select, input[type=text] {
        padding: var(--spacing-sm) var(--spacing-md); border: 2px solid var(--color-border);
        border-radius: var(--radius-md); font-size: 1rem; font-family: var(--font-sans);
    }
    select:focus, input:focus { outline: none; border-color: var(--color-accent); }

    .seg { display: inline-flex; border: 2px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden; width: fit-content; }
    .seg button { background: var(--color-bg); border: none; padding: var(--spacing-sm) var(--spacing-lg); cursor: pointer; font-weight: 600; color: var(--color-text-light); }
    .seg button.on { background: var(--color-accent); color: #fff; }

    .rolechecks { display: grid; gap: var(--spacing-xs); }
    .check { display: flex; align-items: center; gap: var(--spacing-sm); font-weight: 500; }
    .check input { width: 16px; height: 16px; accent-color: var(--color-accent); }
    .cap { text-transform: capitalize; }

    .preview { display: flex; }
    .browser { width: 100%; border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-md); align-self: start; }
    .bar { background: #e9edf3; padding: 8px 12px; display: flex; align-items: center; gap: 6px; border-bottom: 1px solid var(--color-border); }
    .bar .d { width: 9px; height: 9px; border-radius: 50%; background: #c3c9d4; }
    .addr { margin-left: 10px; font-size: 0.75rem; color: var(--color-text-light); background: #fff; padding: 3px 10px; border-radius: 5px; flex: 1; }
    .page { padding: var(--spacing-lg) var(--spacing-xl); background: #fff; min-height: 280px; }
    .page h3 { margin-bottom: var(--spacing-md); }

    .gform .flabel { display: block; font-size: 0.85rem; font-weight: 600; margin: var(--spacing-md) 0 4px; }
    .gform .inp { height: 34px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); background: var(--color-bg-alt); }
    .gform .inp.tall { height: 64px; }
    .gform .submit { margin-top: var(--spacing-lg); display: inline-block; background: var(--color-accent); color: #fff; padding: 8px 22px; border-radius: var(--radius-md); font-weight: 600; font-size: 0.9rem; }
    .granted { color: #04694e; font-size: 0.85rem; margin-top: var(--spacing-md); font-weight: 600; }

    .blocked {
        background: #fff8f1; border: 1px solid #f3c9a8; border-left: 4px solid #ec5e2a;
        border-radius: var(--radius-md); padding: var(--spacing-lg); display: flex; gap: var(--spacing-md); align-items: center;
    }
    .blocked .lock { font-size: 1.6rem; }
    .blocked p { color: #6b4a37; margin: 0; }
    .note { color: var(--color-text-light); font-size: 0.82rem; margin-top: var(--spacing-md); }

    @media (max-width: 760px) {
        .demo-body { grid-template-columns: 1fr; }
    }
</style>
