// Deterministic boundary checks; no network requests or inquiry notifications.
import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const source = fs.readFileSync(new URL('../wp-content/plugins/hks-core/assets/js/inquiry.js', import.meta.url), 'utf8');
class FrozenDate extends Date {
  constructor(...args) { super(...(args.length ? args : ['2026-09-08T22:30:00Z'])); }
}
const context = vm.createContext({ Date: FrozenDate, Intl, validators: {}, document: { querySelectorAll: () => [] } });
vm.runInContext(source.replace("document.querySelectorAll('[data-hks-inquiry]').forEach(init);", 'Object.assign(validators, { phone: validPhone, email: validEmail, preferred_date: validTravelDate });'), context);
const cases = JSON.parse(fs.readFileSync(new URL('inquiry-validation-cases.json', import.meta.url), 'utf8'));
for (const [field, value, expected] of cases) assert.equal(context.validators[field](value), expected, `${field}: ${value}`);
console.log(`Browser validation: ${cases.length} boundary checks passed (Nairobi midnight included).`);
