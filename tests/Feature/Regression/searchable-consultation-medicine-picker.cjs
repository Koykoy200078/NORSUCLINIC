// Run with PLAYWRIGHT_MODULE pointing to the installed Playwright module; uses bundled local widgets and Chrome.
const fs = require('fs');
const assert = require('assert/strict');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const catalog = [{name:'Pain relief',medicines:[
    {id:11,name:'Café Brand <tag>',brand_name:'Acme',generic_name:'Acetaminophen',generic_label:'Paracetamol',strength:'500mg',sku:'SKU-42',available_quantity:7,nearest_expiry:'2027-01-15',disabled:false,stock_status:'available',dosages:[{dosage:'500mg',available_quantity:7}]},
    {id:12,name:'Expired medicine',strength:'250mg',available_quantity:0,nearest_expiry:null,disabled:true,stock_status:'expired',dosages:[]},
    {id:13,name:'Empty medicine',strength:'100mg',available_quantity:0,nearest_expiry:null,disabled:true,stock_status:'out_of_stock',dosages:[]},
    {id:14,name:'Other medicine',sku:'SKU-88',strength:'500mg',available_quantity:2,nearest_expiry:null,disabled:false,stock_status:'available',dosages:[{dosage:'500mg',available_quantity:2}]}
]}];
(async()=>{
    const browser=await chromium.launch({channel:'chrome',headless:true});
    try {
        for(const editing of [false,true]) for(const doctor of [false,true]) {
            const page=await browser.newPage({viewport:{width:1100,height:900}});
            page.setDefaultTimeout(5000);
            const errors=[];
            page.on('pageerror',error=>errors.push(error.message));
            await page.setContent('<form><button type="button" id="add_plan_medicine_btn">Plan</button><button type="button" id="add_nursing_medicine_btn">Nursing</button><div id="plan_medicines_container"></div><div id="nursing_medicines_container"></div></form>');
            await page.addScriptTag({path:'resources/theme/js/vendor.js'});
            if(!await page.evaluate(()=>!!window.jQuery.fn.select2))await page.addScriptTag({path:'resources/theme/js/plugins.js'});
            await page.addStyleTag({path:'resources/theme/css/plugins.css'});
            await page.evaluate(({catalog,editing,doctor})=>{
                window.fetch=()=>new Promise(resolve=>{window.releaseMedicines=()=>resolve({ok:true,status:200,headers:{get:()=> 'application/json'},json:async()=>({success:true,data:catalog})});});
                const config=document.createElement('script');config.type='application/json';config.id='consultation_medicine_picker_config';
                const saved=[{id:81,used_for:'nursing',medicine_id:11,dosage:'500mg',quantity:3,medicine:{id:11,name:'Café Brand <tag>'}},
                    {id:82,used_for:'nursing',medicine_id:12,dosage:'250mg',quantity:4,medicine:{id:12,name:'Expired medicine'}}];
                config.textContent=JSON.stringify({url:'/synthetic',editing,canManagePlanMedicines:doctor,existingMedicines:editing?saved:[]});document.body.appendChild(config);
            },{catalog,editing,doctor});
            await page.addScriptTag({content:fs.readFileSync(process.env.PICKER_SCRIPT || 'resources/assets/js/custom/consultation-medicine-picker.js','utf8')});
            await page.evaluate(()=>document.dispatchEvent(new Event('DOMContentLoaded')));
            assert.equal(await page.getByRole('button',{name:'Nursing',exact:true}).isDisabled(),true,'Wait for catalog before adding rows.');
            await page.evaluate(()=>window.releaseMedicines());
            await page.getByRole('button',{name:'Nursing',exact:true}).click();
            // The last row is the new one added after the saved edit rows.
            const row=page.locator('#nursing_medicines_container .medicine-row').last();
            await row.waitFor();
            assert.deepEqual(errors,[], 'The picker must initialize without browser errors.');
            await row.getByRole('combobox',{name:'Medicine',exact:true}).click();
            const search=page.getByRole('searchbox');
            for(const term of ['acme','acetaminophen','paracetamol','500mg','pain relief','sku-42','cafe','paracetamol SKU-42']) {
                await search.fill('no-catalog-match');
                await page.getByRole('alert').filter({hasText:'No results found'}).waitFor();
                await search.fill(term);
                const choice=page.getByRole('option').filter({hasText:'Café Brand <tag>'});
                await choice.waitFor();assert.equal(await choice.count(),1,term);
                assert.equal(await page.locator('.select2-results').getByRole('option').count(),term==='500mg'?2:term==='pain relief'?4:1,term+' filters out unrelated medicines');
                assert((await choice.innerText()).includes('Stock: 7'));
                assert((await choice.innerText()).includes('2027-01-15'));
                assert.equal(await choice.locator('tag').count(),0,'Catalog text must be escaped.');
            }
            await search.fill('unmatched term');
            await page.getByRole('alert').filter({hasText:'No results found'}).waitFor();
            await search.fill('Expired medicine');
            assert.equal(await page.getByRole('option').filter({hasText:'Expired medicine'}).getAttribute('aria-disabled'),'true');
            await search.fill('Empty medicine');
            assert.equal(await page.getByRole('option').filter({hasText:'Empty medicine'}).getAttribute('aria-disabled'),'true');
            await search.fill('sku-42');
            await search.press('ArrowDown');await search.press('Enter');
            assert.equal(await row.locator('.medicine-select').inputValue(),'11');
            await row.locator('.dosage-select').selectOption('500mg');
            assert.equal(await row.getByRole('spinbutton').getAttribute('max'),'7');
            await row.getByRole('combobox',{name:'Medicine',exact:true}).click();
            await page.getByRole('searchbox').press('Escape');
            assert.equal(await row.locator('.medicine-select').inputValue(),'11','Escape keeps the selection.');
            await row.getByRole('combobox',{name:'Medicine',exact:true}).click();
            await page.getByRole('searchbox').pressSequentially('sku-42');
            await page.getByRole('option').filter({hasText:'Café Brand <tag>'}).waitFor();
            assert.equal(await page.locator('.select2-results').getByRole('option').count(),1,'Reopening accepts real keystrokes.');
            await page.getByRole('searchbox').press('Escape');
            if(editing) {
                const original=page.locator('.medicine-row').filter({has:page.locator('input[type="hidden"][value="81"]')});
                assert.equal(await original.getByRole('spinbutton').getAttribute('max'),'10');
                await original.getByRole('combobox',{name:'Medicine',exact:true}).click();
                await page.getByRole('searchbox').fill('SKU-88');
                await page.getByRole('option').filter({hasText:'Other medicine'}).click();
                await original.locator('.dosage-select').selectOption('500mg');
                assert.equal(await original.getByRole('spinbutton').getAttribute('max'),'2','Changing medicine must not borrow the old medicine stock credit.');
                const expired=page.locator('.medicine-row').filter({has:page.locator('input[type="hidden"][value="82"]')});
                assert.equal(await expired.locator('.medicine-select').inputValue(),'12');
                assert.equal(await expired.locator('.dosage-select').inputValue(),'250mg');
                assert.equal(await expired.getByRole('spinbutton').getAttribute('max'),'4','A saved expired line can be reduced or removed.');
            }
            if(doctor)await page.getByRole('button',{name:'Plan',exact:true}).click();
            else assert.equal(await page.getByRole('button',{name:'Plan',exact:true}).isDisabled(),true);
            assert.equal(await page.locator('#plan_medicines_container .medicine-row').count(),doctor?1:0);
            assert.deepEqual(errors,[]);
            await page.close();
            console.log('PASS '+(editing?'edit':'create')+' '+(doctor?'doctor':'staff')+' searchable fields, stock/expiry, disabled options, escaping and keyboard');
        }
        const failed=await browser.newPage();
        await failed.setContent('<button id="add_nursing_medicine_btn">Nursing</button><script type="application/json" id="consultation_medicine_picker_config">{"url":"/synthetic","editing":false,"canManagePlanMedicines":false}</script>');
        await failed.evaluate(()=>{window.fetch=async()=>{throw new Error('Synthetic connection failure');};});
        await failed.addScriptTag({content:fs.readFileSync(process.env.PICKER_SCRIPT || 'resources/assets/js/custom/consultation-medicine-picker.js','utf8')});
        await failed.evaluate(()=>document.dispatchEvent(new Event('DOMContentLoaded')));
        await failed.getByRole('alert').filter({hasText:'Medicine list could not be loaded'}).waitFor();
        assert.equal(await failed.getByRole('button',{name:'Nursing'}).isDisabled(),true);
        await failed.close();console.log('PASS catalog failure is visible and cannot create empty rows');
    } finally {await browser.close();}
})().catch(error=>{console.error(error.stack);process.exitCode=1;});
