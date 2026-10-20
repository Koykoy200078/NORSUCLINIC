const fs = require('fs');
const vm = require('vm');
const assert = require('assert/strict');
const source = fs.readFileSync(process.env.NUMBER_FORMAT_SCRIPT || 'resources/assets/js/custom/number-format.js', 'utf8');
const context = {window: {}};
vm.runInNewContext(source, context);
for (const [value, expected] of [[0, '0'], [999, '999'], [1000, '1,000'], [1234567, '1,234,567'], ['1234.50', '1,234.50'], [-1234, '-1,234']]) {
    assert.equal(context.window.addCommas(value), expected);
}
console.log('Inventory quantity formatting is unchanged.');
