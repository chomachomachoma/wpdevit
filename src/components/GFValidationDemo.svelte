<script>
    // Interactive demo that mirrors GF Validation's server-side rules so visitors
    // can feel exactly how the plugin behaves before buying. Pure client-side —
    // the same logic the plugin enforces via the `gform_field_validation` filter.

    let active = $state('text'); // text | number | date | selection

    const tabs = [
        { id: 'text', label: 'Text', icon: '✎' },
        { id: 'number', label: 'Number', icon: '#' },
        { id: 'date', label: 'Date', icon: '\u{1F4C5}' },
        { id: 'selection', label: 'Selection', icon: '☑' },
    ];

    // ---- Text validator ----
    let textRules = $state({ minLength: 40, prohibited: 'lorem ipsum, test test', required: 'budget, timeline' });
    let textValue = $state('I have a budget of $5k and need this delivered on a 6 week timeline.');

    function validateText(value, rules) {
        const errors = [];
        const v = value.trim();
        if (rules.minLength && v.length < Number(rules.minLength)) {
            errors.push(`Must be at least ${rules.minLength} characters (currently ${v.length}).`);
        }
        const lower = v.toLowerCase();
        const prohibited = rules.prohibited.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
        const hit = prohibited.find(w => lower.includes(w));
        if (hit) errors.push(`Contains a prohibited phrase: “${hit}”.`);
        const required = rules.required.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
        const missing = required.filter(w => !lower.includes(w));
        if (missing.length) errors.push(`Missing required word(s): ${missing.join(', ')}.`);
        return errors;
    }

    // ---- Number validator ----
    let numberRules = $state({ operator: '<=', compare: 50, outcome: 'invalid' });
    let numberValue = $state(64);
    const operators = [
        { id: '>', label: 'greater than' },
        { id: '>=', label: 'greater than or equal' },
        { id: '<', label: 'less than' },
        { id: '<=', label: 'less than or equal' },
        { id: '==', label: 'equal to' },
        { id: '!=', label: 'not equal to' },
    ];
    function compareNumbers(a, op, b) {
        a = Number(a); b = Number(b);
        switch (op) {
            case '>': return a > b;
            case '>=': return a >= b;
            case '<': return a < b;
            case '<=': return a <= b;
            case '==': return a === b;
            case '!=': return a !== b;
        }
        return false;
    }
    function validateNumber(value, rules) {
        const conditionMet = compareNumbers(value, rules.operator, rules.compare);
        // Field is "invalid when" / "valid when" the condition is met.
        const isInvalid = rules.outcome === 'invalid' ? conditionMet : !conditionMet;
        const opLabel = operators.find(o => o.id === rules.operator)?.label;
        return isInvalid ? [`Value must not be ${opLabel} ${rules.compare}.`] : [];
    }

    // ---- Date validator ----
    let dateRules = $state({ min: '2026-06-26', max: '2026-12-31' });
    let dateValue = $state('2027-02-10');
    function validateDate(value, rules) {
        const errors = [];
        if (!value) return ['Please choose a date.'];
        if (rules.min && value < rules.min) errors.push(`Date must be on or after ${formatDate(rules.min)}.`);
        if (rules.max && value > rules.max) errors.push(`Date must be on or before ${formatDate(rules.max)}.`);
        return errors;
    }
    function formatDate(iso) {
        const [y, m, d] = iso.split('-');
        return `${m}/${d}/${y}`;
    }

    // ---- Selection validator ----
    let selectionRules = $state({ min: 1, max: 2 });
    const addons = ['Airport pickup', 'Breakfast', 'Late checkout', 'Spa access'];
    let selected = $state(['Airport pickup', 'Breakfast', 'Late checkout']);
    function toggleAddon(name) {
        selected = selected.includes(name) ? selected.filter(n => n !== name) : [...selected, name];
    }
    function validateSelection(chosen, rules) {
        const errors = [];
        if (rules.min && chosen.length < Number(rules.min)) errors.push(`Select at least ${rules.min} option(s).`);
        if (rules.max && chosen.length > Number(rules.max)) errors.push(`Select no more than ${rules.max} option(s).`);
        return errors;
    }

    let errors = $derived.by(() => {
        switch (active) {
            case 'text': return validateText(textValue, textRules);
            case 'number': return validateNumber(numberValue, numberRules);
            case 'date': return validateDate(dateValue, dateRules);
            case 'selection': return validateSelection(selected, selectionRules);
        }
        return [];
    });
    let valid = $derived(errors.length === 0);
</script>

<div class="demo">
    <div class="demo-head">
        <span class="badge">Live demo</span>
        <p>Change the input below. Validation runs instantly — exactly like the plugin does server-side.</p>
    </div>

    <div class="demo-tabs" role="tablist">
        {#each tabs as t}
            <button role="tab" class:active={active === t.id} aria-selected={active === t.id}
                    onclick={() => active = t.id}>
                <span class="tab-icon">{t.icon}</span> {t.label}
            </button>
        {/each}
    </div>

    <div class="demo-body">
        <div class="field-col">
            {#if active === 'text'}
                <label for="d-text">Project Details <span class="req">*</span></label>
                <textarea id="d-text" rows="4" bind:value={textValue}></textarea>
                <p class="counter">{textValue.trim().length} characters</p>
            {:else if active === 'number'}
                <label for="d-num">Tickets Requested <span class="req">*</span></label>
                <input id="d-num" type="number" bind:value={numberValue} />
            {:else if active === 'date'}
                <label for="d-date">Preferred Date <span class="req">*</span></label>
                <input id="d-date" type="date" bind:value={dateValue} min={dateRules.min} max={dateRules.max} />
                <p class="counter">Picker is also limited to the allowed range.</p>
            {:else if active === 'selection'}
                <span class="block-label">Add-on Services <span class="req">*</span></span>
                <div class="checks">
                    {#each addons as a}
                        <label class="check"><input type="checkbox" checked={selected.includes(a)} onchange={() => toggleAddon(a)} /> {a}</label>
                    {/each}
                </div>
            {/if}

            <div class="result" class:ok={valid} class:bad={!valid} aria-live="polite">
                {#if valid}
                    <strong>&#10003; Passes validation</strong>
                    <span>This submission would be accepted.</span>
                {:else}
                    <strong>&#10007; Blocked</strong>
                    <ul>{#each errors as e}<li>{e}</li>{/each}</ul>
                {/if}
            </div>
        </div>

        <div class="rules-col">
            <h4>Rules in effect</h4>
            {#if active === 'text'}
                <div class="rule"><span>Minimum length</span>
                    <input type="number" bind:value={textRules.minLength} /></div>
                <div class="rule"><span>Prohibited phrases</span>
                    <input type="text" bind:value={textRules.prohibited} /></div>
                <div class="rule"><span>Required words</span>
                    <input type="text" bind:value={textRules.required} /></div>
            {:else if active === 'number'}
                <div class="rule"><span>Field is</span>
                    <select bind:value={numberRules.outcome}><option value="invalid">invalid</option><option value="valid">valid</option></select></div>
                <div class="rule"><span>when value is</span>
                    <select bind:value={numberRules.operator}>{#each operators as o}<option value={o.id}>{o.label}</option>{/each}</select></div>
                <div class="rule"><span>compared to</span>
                    <input type="number" bind:value={numberRules.compare} /></div>
            {:else if active === 'date'}
                <div class="rule"><span>Minimum date</span>
                    <input type="date" bind:value={dateRules.min} /></div>
                <div class="rule"><span>Maximum date</span>
                    <input type="date" bind:value={dateRules.max} /></div>
            {:else if active === 'selection'}
                <div class="rule"><span>Minimum selections</span>
                    <input type="number" bind:value={selectionRules.min} /></div>
                <div class="rule"><span>Maximum selections</span>
                    <input type="number" bind:value={selectionRules.max} /></div>
                <p class="hint">{selected.length} selected</p>
            {/if}
            <p class="enforced">&#9670; Enforced server-side — can't be bypassed.</p>
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

    .demo-tabs { display: flex; gap: 4px; padding: var(--spacing-md) var(--spacing-xl) 0; flex-wrap: wrap; }
    .demo-tabs button {
        background: var(--color-bg-alt); border: 1px solid var(--color-border); border-bottom: none;
        padding: var(--spacing-sm) var(--spacing-md); border-radius: var(--radius-md) var(--radius-md) 0 0;
        cursor: pointer; font-weight: 600; font-size: 0.9rem; color: var(--color-text-light);
    }
    .demo-tabs button.active { background: var(--color-bg); color: var(--color-accent); border-color: var(--color-border); position: relative; top: 1px; }
    .tab-icon { margin-right: 4px; }

    .demo-body { display: grid; grid-template-columns: 1.2fr 1fr; gap: var(--spacing-xl); padding: var(--spacing-xl); border-top: 1px solid var(--color-border); }

    label, .block-label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: var(--spacing-sm); }
    .req { color: var(--color-danger); }
    textarea, .field-col input[type=number], .field-col input[type=date] {
        width: 100%; padding: var(--spacing-sm) var(--spacing-md); border: 2px solid var(--color-border);
        border-radius: var(--radius-md); font-size: 1rem; font-family: var(--font-sans); resize: vertical;
    }
    textarea:focus, .field-col input:focus { outline: none; border-color: var(--color-accent); }
    .counter { font-size: 0.8rem; color: var(--color-text-light); margin-top: var(--spacing-xs); }
    .checks { display: grid; gap: var(--spacing-xs); }
    .check { display: flex; align-items: center; gap: var(--spacing-sm); font-weight: 500; }
    .check input { width: 16px; height: 16px; accent-color: var(--color-accent); }

    .result { margin-top: var(--spacing-lg); padding: var(--spacing-md) var(--spacing-lg); border-radius: var(--radius-md); font-size: 0.95rem; }
    .result.ok { background: #e6f9f2; border: 1px solid #9fe7cf; color: #04694e; }
    .result.bad { background: #fdeaef; border: 1px solid #f5b9c8; color: #8a1c34; }
    .result strong { display: block; margin-bottom: 4px; }
    .result ul { margin: 4px 0 0 1.1rem; }
    .result li { margin: 2px 0; }

    .rules-col { background: var(--color-bg-alt); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--spacing-lg); }
    .rules-col h4 { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-text-light); margin-bottom: var(--spacing-md); }
    .rule { display: flex; flex-direction: column; gap: 4px; margin-bottom: var(--spacing-md); }
    .rule span { font-size: 0.85rem; font-weight: 600; color: var(--color-text); }
    .rule input, .rule select { padding: 6px 10px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9rem; font-family: var(--font-sans); }
    .rule input:focus, .rule select:focus { outline: none; border-color: var(--color-accent); }
    .hint { font-size: 0.85rem; color: var(--color-text-light); }
    .enforced { font-size: 0.8rem; color: var(--color-accent); margin-top: var(--spacing-md); font-weight: 600; }

    @media (max-width: 760px) {
        .demo-body { grid-template-columns: 1fr; }
    }
</style>
