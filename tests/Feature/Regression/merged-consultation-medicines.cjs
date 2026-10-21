// Existing rows that converge after an inventory merge retain their identity; new duplicates remain refused.
const fs=require('fs'),assert=require('assert/strict');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE || 'playwright');
(async()=>{
 const browser=await chromium.launch({channel:'chrome',headless:true});
 try {
  for(const doctor of [false,true]) {
   const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));page.on('dialog',d=>d.accept());
   await page.setContent('<form><button type="button" id="add_nursing_medicine_btn">Nursing</button><div id="nursing_medicines_container"></div></form>');
   await page.addScriptTag({path:'resources/theme/js/vendor.js'});
   if(!await page.evaluate(()=>!!window.jQuery.fn.select2))await page.addScriptTag({path:'resources/theme/js/plugins.js'});
   await page.addStyleTag({path:'resources/theme/css/plugins.css'});
   await page.evaluate(doctor=>{
    const saved=[81,82].map(id=>({id,medicine_id:11,used_for:'nursing',dosage:'500mg',quantity:2,medicine:{id:11,name:'Merged medicine'}}));
    window.fetch=async()=>({ok:true,status:200,headers:{get:()=> 'application/json'},json:async()=>({success:true,data:[{name:'Category',medicines:[{id:11,name:'Merged medicine',available_quantity:10,dosages:[{dosage:'500mg',available_quantity:10}]}]}]})});
    const config=document.createElement('script');config.id='consultation_medicine_picker_config';config.type='application/json';
    config.textContent=JSON.stringify({url:'/synthetic',editing:true,canManagePlanMedicines:doctor,existingMedicines:saved});document.body.appendChild(config);
   },doctor);
   await page.addScriptTag({content:fs.readFileSync(process.env.PICKER_SCRIPT || 'resources/assets/js/custom/consultation-medicine-picker.js','utf8')});
   await page.evaluate(()=>document.dispatchEvent(new Event('DOMContentLoaded')));
   await page.locator('input[type="hidden"][value="82"]').waitFor({state:'attached'});
   const submit=()=>page.evaluate(()=>document.querySelector('form').dispatchEvent(new Event('submit',{cancelable:true})));
   assert.equal(await submit(),true,'Retained historical rows must remain editable.');
   await page.getByRole('button',{name:'Nursing',exact:true}).click();
   const added=page.locator('.medicine-row').last();await added.getByRole('combobox',{name:'Medicine',exact:true}).click();
   await page.getByRole('option').filter({hasText:'Merged medicine'}).click();await added.locator('.dosage-select').selectOption('500mg');
   assert.equal(await submit(),false,'A new duplicate must still be rejected.');
   await added.locator('.remove-medicine-btn').click();
   const rows=page.locator('.medicine-row');assert.equal(await rows.count(),2);
   await rows.last().evaluate(row=>{row.dataset.savedRowId='81';});
   assert.equal(await submit(),false,'Repeating a saved ID must not bypass the guard.');
   assert.deepEqual(errors,[]);await page.close();console.log('PASS '+(doctor?'doctor':'staff')+' retained merged rows and duplicate guards');
  }
 } finally {await browser.close();}
})().catch(error=>{console.error(error.stack);process.exitCode=1;});
