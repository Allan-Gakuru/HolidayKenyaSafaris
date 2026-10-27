// Exercise the actual inquiry message/payload builders with and without the date control.
import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const source = fs.readFileSync(new URL('../wp-content/plugins/hks-core/assets/js/inquiry.js', import.meta.url), 'utf8');
class FormDataFixture { constructor(form) { this.values = form.values; } get(key) { return this.values[key] ?? null; } }
const context = vm.createContext({ FormData: FormDataFixture, api: {}, document: { querySelectorAll: () => [] } });
vm.runInContext(source.replace("document.querySelectorAll('[data-hks-inquiry]').forEach(init);", 'Object.assign(api, { buildMessage, formPayload });'), context);
const form = { elements: {}, values: { name: 'Fixture Traveller', phone: '0712345678', email: 'fixture@example.test', travelers: '2', tour_id: '1', campaign_id: '2' } };
const root = { dataset: { pageType: 'campaign' } };
const message = context.api.buildMessage(form, 'Christmas Coast Holiday', 'HKS-000001', { pageType: 'campaign', christmasCoast: true });
assert(!message.includes('Preferred travel date'));
assert(message.includes('full holiday cost') && message.includes('advance booking payment'));
assert(!('preferred_date' in context.api.formPayload(form, root, {})));
form.elements.preferred_date = {};
form.values.preferred_date = '25-12-2026';
const normal = context.api.buildMessage(form, 'Normal Tour', 'HKS-000002', { pageType: 'tour' });
assert(normal.includes('Preferred travel date: 25-12-2026.'));
assert(!normal.includes('Christmas coast advance saver'));
assert.equal(context.api.formPayload(form, root, {}).preferred_date, '25-12-2026');
assert(!message.includes('utm_') && !message.includes('254712965131'));
console.log('Christmas Campaign: 7 message and payload checks passed.');
