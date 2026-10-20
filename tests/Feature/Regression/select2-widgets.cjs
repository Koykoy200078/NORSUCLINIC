// Standalone browser regression: use PLAYWRIGHT_MODULE and installed Chrome, as for the medicine picker.
const fs = require('fs');
const assert = require('assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

(async () => {
    const browser = await chromium.launch({channel: 'chrome', headless: true});
    try {
        const page = await browser.newPage();
        page.setDefaultTimeout(4000);
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.setContent('<select id="outside" data-control="select2"><option>Outside</option></select><div id="component"><select id="inside" data-control="select2"><option value="1">First</option><option value="2" selected>Second</option></select></div>');
        await page.addStyleTag({path: 'public/assets/css/third-party.css'});
        await page.addScriptTag({path: 'resources/theme/js/vendor.js'});
        await page.evaluate(() => {
            window.hooks = {};
            window.Livewire = {hook(name, callback) { (window.hooks[name] ||= []).push(callback); }};
        });
        if (process.argv.includes('--before')) {
            const source = fs.readFileSync('resources/assets/js/custom/custom.js', 'utf8');
            const initial = source.slice(source.indexOf('function select2initialize()'), source.indexOf('document.addEventListener("click"'));
            const legacy = source.slice(source.indexOf('document.addEventListener("livewire:load"'), source.indexOf('const inputFocus'));
            await page.addScriptTag({content: initial + legacy + '\ndocument.addEventListener("DOMContentLoaded", select2initialize);'});
        } else {
            await page.addScriptTag({path: process.env.SELECT2_SCRIPT || 'resources/assets/js/custom/select2-widgets.js'});
        }
        await page.evaluate(() => {
            document.dispatchEvent(new Event('DOMContentLoaded'));
            document.dispatchEvent(new Event('livewire:init'));
            window.outsideContainer = $('#outside').data('select2').$container[0];
        });
        assert.equal(await page.locator('.select2-container').count(), 2, 'Initial widgets');
        await page.evaluate(() => {
            document.getElementById('component').innerHTML = '<select id="inside" data-control="select2"><option value="1">First</option><option value="2" selected>Second</option></select>';
            for (const callback of window.hooks.morphed || []) callback({el: document.getElementById('component')});
        });
        assert.equal(await page.locator('#inside').evaluate(el => el.classList.contains('select2-hidden-accessible')), true, 'A morphed control must become searchable');
        await page.locator('#component .select2-selection').click();
        await page.locator('.select2-search__field').fill('First');
        await page.locator('.select2-results__option').filter({hasText: 'First'}).click();
        assert.equal(await page.locator('#inside').inputValue(), '1');
        await page.evaluate(() => {
            window.insideContainer = $('#inside').data('select2').$container[0];
            const unrelated = document.createElement('select');
            unrelated.id = 'unrelated'; unrelated.dataset.control = 'select2';
            document.body.append(unrelated);
            for (let i = 0; i < 3; i++) for (const callback of window.hooks.morphed || []) callback({el: document.getElementById('component')});
        });
        assert.equal(await page.locator('.select2-container').count(), 2, 'No duplicate widgets');
        assert.equal(await page.evaluate(() => $('#inside').data('select2').$container[0] === window.insideContainer), true, 'Repeated morphs keep an intact widget');
        assert.equal(await page.locator('#unrelated').evaluate(el => el.classList.contains('select2-hidden-accessible')), false, 'Only controls in the morphed component are initialized');
        assert.equal(await page.evaluate(() => $('#outside').data('select2').$container[0] === window.outsideContainer), true, 'Unrelated widgets keep their instance');
        await page.evaluate(() => {
            $('#inside').data('select2').$container.remove();
            for (const callback of window.hooks.morphed || []) callback({el: document.getElementById('component')});
        });
        assert.equal(await page.locator('#component .select2-container').count(), 1, 'A removed container is repaired');
        assert.equal(await page.locator('#inside').inputValue(), '1', 'Selection survives repair');
        // A component root may itself be a select.
        await page.evaluate(() => {
            const root = document.createElement('select');
            root.id = 'root'; root.dataset.control = 'select2'; root.innerHTML = '<option>Root</option>';
            document.body.append(root);
            for (const callback of window.hooks.morphed || []) callback({el: root});
        });
        assert.equal(await page.locator('#root').evaluate(el => el.classList.contains('select2-hidden-accessible')), true);
        assert.deepEqual(errors, []);
        console.log('Select2 initial, morph, search, repeat, scope, repair and root checks passed.');
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
