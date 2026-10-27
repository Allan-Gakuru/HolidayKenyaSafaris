# Christmas coast advance saver campaign

Approved scope: 27 October 2026. The client requested a Christmas coast Campaign using the existing Tour layout, showing an advance saver booking payment rather than a full holiday price. Only this Campaign omits the travel-date question. Existing Tour and Campaign structure is retained.

## After code deployment

The next administrator visit creates two **drafts** once:

- Tour: **Christmas Coast Holiday**, slug `christmas-coast-holiday`.
- Campaign: **Christmas at the coast**, slug `christmas-coast-advance-saver`, linked to that Tour.

The installation stores their generated IDs privately in `hks_christmas_coast_drafts`. The exception checks the Campaign ID, post type and private creation marker. Renaming the Campaign does not remove the exception. Copying it does not give another Campaign the exception. Setup preserves existing independent records on either route, never overwrites editorial changes, and does not recreate deleted drafts after successful setup.

Complete the Tour with confirmed destination/resort, duration, route and transport, itinerary, inclusions, exclusions, and deliberately assigned photographs. The normal Tour starting-price field remains a **full per-person starting price**; leave it blank if unknown. No source package, price, resort, photograph or itinerary has been assumed for this draft.

The new Campaign alone receives **Christmas coast booking payment** editor fields:

- Advance booking payment in whole KSh.
- Whether that payment is per person, per family or per booking.
- Optional confirmed payment details, such as credit toward the final total.

The previously discussed KSh 60,000 Diani family payment has **not** been copied into this new offer. Neither its amount nor the hotel has been confirmed for this campaign.

Publish the completed Tour first, then the Campaign. The Campaign publication guard requires a published linked Tour and a positive payment amount with its basis. Draft preview is available to editors even while the linked Tour remains draft; submitting a draft preview cannot create an inquiry. The normal Campaign From-price field is ignored on this Campaign, including when the advance amount is blank. All other Campaigns preserve their existing override/inheritance behaviour.

## Visitor behaviour

The existing title band, gallery, facts, tabs, itinerary, related Tours, responsive layout and quote buttons are reused. The payment panel says **Advance saver booking payment**, identifies its basis, and explicitly states that this is not the full holiday price. No checkout, confirmed availability, payment commitment, savings amount or refund promise is introduced.

The quote form retains name, phone, email, package and traveler count, plus existing Tour-specific optional questions. This Campaign has no date input or calendar. Both browser payload and reviewed WhatsApp message omit the date. The server derives the exemption from the signed Campaign identity, discards unexpected date values, and continues to require dates everywhere else.

Private inquiry storage, reference generation, idempotent retries, background team notification and the visitor-controlled WhatsApp launch remain the shared flow. Team email identifies the advance saver offer and omits the blank date line. Stale forms cannot save new inquiries after the payment is cleared. No personal answers enter analytics.

## Verification

Run `node tools/test_christmas_campaign.mjs`, the existing PHP/JS inquiry validation suites, and `tools/test_christmas_campaign_wp.php` against the isolated local WordPress fixture with SCF and HKS active. The integration test requires `HKS_WEBINAR_TESTING=true` and environment `local`, intercepts mail, and restores the two drafts with no test price. It covers ID isolation, publication, malformed amounts, normal price inheritance/overrides, absent dates, malicious client flags, signed-context tampering, saved inquiries and notification deduplication.

Pushing code to GitHub does not deploy cPanel or publish WordPress content. Use the existing code deployment procedure, then complete the drafts in WordPress.

Review on 27 October 2026: 41 isolated WordPress integration checks, 7 message/payload checks, 61 server and 58 browser validation checks passed, together with PHP lint and content/conversion/template checks. Desktop and mobile draft/form layouts were inspected. The independent review agent could not run because of the account usage limit; a direct code and visual review was substituted. Its disposition is **ship the draft-capable code**, not approve the incomplete Campaign for public launch. Gallery, itinerary and payment amount remain intentionally unpopulated pending confirmed content. The design detector ran only in its degraded regex mode; it did not measure computed contrast.
