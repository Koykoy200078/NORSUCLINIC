// Real-app browser check. Supply a private synthetic accounts JSON with accounts[role].email/password.
const fs = require('fs');
const assert = require('assert/strict');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.QA_BASE_URL || 'http://127.0.0.1:8016';
const {accounts} = JSON.parse(fs.readFileSync(process.env.QA_ACCOUNTS_FILE || 'storage/framework/testing/phase6-browser-access.json'));

(async () => {
    const browser = await chromium.launch({channel: 'chrome', headless: true});
    const errors = [], external = [], failures = [], checked = [];
    try {
        for (const [role, prefix] of [['clinic_admin', 'admin'], ['staff', 'staff'], ['doctor', 'doctors']]) {
            const context = await browser.newContext({viewport: {width: role === 'doctor' ? 440 : 1100, height: 900}});
            context.on('request', request => {
                const url = new URL(request.url());
                if (/^https?:$/.test(url.protocol) && url.origin !== new URL(base).origin) external.push(url.origin);
            });
            const page = await context.newPage();
            page.setDefaultTimeout(6000);
            page.on('pageerror', error => errors.push(error.message));
            page.on('response', response => { if (response.status() >= 500) failures.push(response.status()); });
            await page.goto(base + '/login');
            await page.getByLabel('Email', {exact: true}).fill(accounts[role].email);
            await page.getByLabel('Password', {exact: true}).fill(accounts[role].password);
            await Promise.all([page.waitForURL(url => url.pathname !== '/login'), page.getByRole('button', {name: 'Login', exact: true}).click()]);

            const filters = [['categories', '#medicineCategoryHead', '#categoryResetFilter']];
            if (role !== 'doctor') filters.unshift(['doctors', '#doctorStatus', '#doctorResetFilter']);
            for (const [path, selector, reset] of filters) {
                assert.equal((await page.goto(base + '/' + prefix + '/' + path)).status(), 200);
                await page.waitForFunction(selector => !!window.$(selector).data('select2'), selector);
                const option = await page.locator(selector).evaluate(el => [...el.options].find(option => option.value === '1').text);
                await page.evaluate(selector => {
                    window.savedWidget = $(selector).data('select2').$container[0];
                    window.filterMorphs = 0;
                    Livewire.hook('morphed', ({el}) => {
                        if (el.contains(document.querySelector(selector))) window.filterMorphs++;
                    });
                }, selector);
                const refresh = page.waitForResponse(response => response.url().includes('/livewire/update') && response.request().method() === 'POST');
                await page.evaluate(() => Livewire.dispatch('refresh'));
                assert.equal((await refresh).status(), 200);
                await page.waitForFunction(() => window.filterMorphs > 0);
                assert.equal(await page.evaluate(selector => $(selector).data('select2').$container[0] === window.savedWidget, selector), true, path + ' filter survives refresh');

                const dropdown = page.locator(selector).locator('xpath=ancestor::div[contains(@class,"dropdown-menu")]');
                const toggle = page.locator('#' + await dropdown.getAttribute('aria-labelledby'));
                await toggle.click();
                await dropdown.locator('.select2-selection').click();
                await page.locator('.select2-search__field').fill(option);
                const beforeSelection = await page.evaluate(() => window.filterMorphs);
                const update = page.waitForResponse(response => response.url().includes('/livewire/update') && response.request().method() === 'POST');
                await page.locator('.select2-results__option').filter({hasText: option}).first().click();
                assert.equal((await update).status(), 200);
                await page.waitForFunction(before => window.filterMorphs > before, beforeSelection);
                assert.equal(await page.locator(selector).inputValue(), '1');
                if (path === 'categories') {
                    await dropdown.waitFor({state: 'hidden'});
                    assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
                }
                if (await dropdown.isVisible()) await toggle.click();
                await dropdown.waitFor({state: 'hidden'});

                const menu = page.locator('[data-row-actions-toggle]').first();
                if (await menu.count()) {
                    await menu.click();
                    await page.locator('.dropdown-menu.show[role="menu"] [role="menuitem"]').first().waitFor({state: 'visible'});
                    await page.keyboard.press('Escape');
                }
                await toggle.click();
                const beforeReset = await page.evaluate(() => window.filterMorphs);
                const resetUpdate = page.waitForResponse(response => response.url().includes('/livewire/update') && response.request().method() === 'POST');
                await page.locator(reset).click();
                assert.equal((await resetUpdate).status(), 200);
                await page.waitForFunction(before => window.filterMorphs > before, beforeReset);
                assert.equal(await page.locator(selector).inputValue(), path === 'doctors' ? '2' : '0');
                assert.equal(await page.evaluate(() => addCommas(1234567)), '1,234,567');
                checked.push(prefix + '/' + path);
            }
            await context.close();
        }
        assert.deepEqual(errors, []);
        assert.deepEqual(external, []);
        assert.deepEqual(failures, []);
        console.log(JSON.stringify({checked, errors, external, failures}));
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
