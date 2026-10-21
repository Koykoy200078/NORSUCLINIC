// A merge retains separate saved instructions; only additional duplicate strengths need a warning.
const fs = require('fs');
const vm = require('vm');
const assert = require('assert/strict');
const source = fs.readFileSync(process.env.PRESCRIPTION_FORM || 'resources/views/prescriptions/form_v2.blade.php', 'utf8');
const script = source.match(/<script>\s*([\s\S]*?)<\/script>/)[1];
const context = vm.createContext({});
vm.runInContext(script, context);
const row = dosage => ({medicine_id: '11', dosage, instructions: 'Separate saved instruction'});
const form = context.prescriptionForm({initialRows: [row('500mg'), row(' 500 MG ')], savedStrengthCounts: {'11|500mg': 2}});
assert.equal(form.isDuplicate(0), false, 'The first saved merged line must not warn.');
assert.equal(form.isDuplicate(1), false, 'The second saved merged line must not warn.');
form.medicines.push(row('500mg'));
assert.equal(form.isDuplicate(2), true, 'A new third duplicate must warn.');
form.medicines[2].dosage = '250mg';
assert.equal(form.isDuplicate(2), false, 'Different strengths remain allowed.');
const create = context.prescriptionForm({initialRows: [row('500mg'), row('500mg')]});
assert.equal(create.isDuplicate(0), true, 'A new prescription retains its duplicate warning.');
console.log('PASS prescription retained multiplicity, additional duplicate and different-strength warnings');
