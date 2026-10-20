// Standalone browser regression: use an available Playwright module via PLAYWRIGHT_MODULE, and installed Chrome.
const fs = require('fs');
const assert = require('assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const snapshotPath = __dirname + '/consultation-medicine-picker.json';
const catalog = [{name:'Category <tag>', medicines:[{id:11,name:'Medicine <tag> & café',available_quantity:7,dosages:[{dosage:'500mg',available_quantity:7},{dosage:'250mg',available_quantity:20}]}]}];
const saved = [{id:81,used_for:'plan',medicine_id:11,dosage:'500mg',quantity:3,dosage_instructions:'Take <tag>',medicine:{id:11,name:'Medicine <tag> & café'}}, {id:82,used_for:'nursing',medicine_id:99,dosage:'20mg',quantity:4,dosage_instructions:'Before meals',medicine:{id:99,name:'Empty medicine <tag>'}}];
(async () => {
  const browser = await chromium.launch({channel:'chrome',headless:true});
  const snapshots = {};
  try {
    for (const editing of [false,true]) for (const doctor of [false,true]) {
      const key = (editing?'edit':'create')+'-'+(doctor?'doctor':'staff');
      const page = await browser.newPage();
      page.setDefaultTimeout(5000);
      const errors = [], dialogs = [];
      let acceptRemove = false;
      page.on('pageerror',e=>errors.push(e.message));
      page.on('dialog',async d=>{dialogs.push(d.message()); if(d.type()==='confirm' && !acceptRemove)await d.dismiss();else await d.accept();});
      await page.setContent('<form id="fixture"><button type="button" id="add_plan_medicine_btn">Plan</button><button type="button" id="add_nursing_medicine_btn">Nursing</button><div id="plan_medicines_container"></div><div id="nursing_medicines_container"></div></form>');
      await page.addScriptTag({path:'resources/theme/js/vendor.js'});
      if(!await page.evaluate(()=>!!window.jQuery.fn.select2))await page.addScriptTag({path:'resources/theme/js/plugins.js'});
      await page.addStyleTag({path:'resources/theme/css/plugins.css'});
      await page.evaluate(({catalog,editing,doctor,saved})=>{
        window.fetch = async ()=>({ok:true,status:200,headers:{get:()=> 'application/json'},json:async()=>({success:true,data:catalog})});
        const config=document.createElement('script');
        config.type='application/json';
        config.id='consultation_medicine_picker_config';
        config.textContent=JSON.stringify({url:'/synthetic-medicines',editing,canManagePlanMedicines:doctor,existingMedicines:editing?saved:[]});
        document.body.appendChild(config);
      },{catalog,editing,doctor,saved});
      await page.addScriptTag({content:fs.readFileSync(process.env.PICKER_SCRIPT || 'resources/assets/js/custom/consultation-medicine-picker.js','utf8')});
      await page.evaluate(()=>document.dispatchEvent(new Event('DOMContentLoaded')));
      if(editing) await page.locator('#nursing_medicines_container .medicine-row').waitFor();
      if(doctor)await page.locator('#add_plan_medicine_btn').click();
      else assert.equal(await page.locator('#add_plan_medicine_btn').isDisabled(),true);
      assert.equal(await page.locator('#plan_medicines_container .medicine-row').count(),doctor?(editing?2:1):0,key);
      await page.locator('#add_nursing_medicine_btn').click();
      const nursing = page.locator('#nursing_medicines_container .medicine-row').last();
      await nursing.getByRole('combobox',{name:'Medicine',exact:true}).click();
      await page.getByRole('option').filter({hasText:'Medicine <tag> & café'}).click();
      await nursing.locator('.dosage-select').selectOption('500mg');
      const quantity = nursing.locator('input[type=number]');
      assert.equal(await quantity.getAttribute('max'),'7');
      await quantity.fill('50');
      assert.equal(await quantity.inputValue(),'7');
      assert.equal(await page.evaluate(()=>document.querySelector('form').dispatchEvent(new Event('submit',{cancelable:true}))),true);
      snapshots[key] = await page.locator('.medicine-row').evaluateAll(rows=>rows.map(row=>({
        type:row.dataset.type,index:row.dataset.index,classes:row.className,
        controls:[...row.querySelectorAll('select,input')].map(e=>({tag:e.tagName,name:e.name,value:e.value,disabled:e.disabled,max:e.max||'',options:e.options?[...e.options].map(o=>[o.value,o.textContent,o.selected]):null})),
        warning:row.querySelector('.medicine-stock-info')?.textContent||'',badge:row.querySelector('.existing-medicine-badge')?.textContent.trim()||''
      })));
      // A duplicate is refused within a section, while the same medicine across sections remains allowed.
      await page.locator('#add_nursing_medicine_btn').click();
      const duplicate = page.locator('#nursing_medicines_container .medicine-row').last();
      await duplicate.getByRole('combobox',{name:'Medicine',exact:true}).click();
      await page.getByRole('option').filter({hasText:'Medicine <tag> & café'}).click();
      await duplicate.locator('.dosage-select').selectOption('500mg');
      assert.equal(await page.evaluate(()=>document.querySelector('form').dispatchEvent(new Event('submit',{cancelable:true}))),false);
      assert(dialogs.some(text=>text.includes('listed more than once')));
      const count = await page.locator('.medicine-row').count();
      await duplicate.locator('.remove-medicine-btn').click();
      if(editing) { assert.equal(await page.locator('.medicine-row').count(),count); acceptRemove=true; await duplicate.locator('.remove-medicine-btn').click(); }
      assert.equal(await page.locator('.medicine-row').count(),count-1);
      if(editing) {
        const empty = page.locator('#nursing_medicines_container .medicine-row').first();
        assert.equal(await empty.locator('input[type=hidden]').inputValue(),'82');
        await empty.locator('input[type=number]').fill('50');
        assert.equal(await empty.locator('input[type=number]').inputValue(),'4');
      }
      assert.deepEqual(errors,[]);
      await page.close();
      console.log('PASS '+key+' stock, saved ids, section permissions, duplicate guard and removal behavior');
    }
    assert.deepEqual(snapshots,JSON.parse(fs.readFileSync(snapshotPath,'utf8')));
    console.log('PASS all picker DOM states identical to the pre-refactor baseline.');
  } finally {await browser.close();}
})().catch(e=>{console.error(e.stack);process.exit(1);});
