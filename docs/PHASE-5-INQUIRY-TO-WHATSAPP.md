# Phase 5: Saved inquiry to WhatsApp

The MVP conversion component is the dynamic `hks/quote-cta` block. On a published
Tour or Campaign it resolves the canonical Tour, selected intake questions, CTA
copy, Campaign label, and official Holiday Kenya Safaris WhatsApp destination.

## Visitor journey

1. The quote CTA opens an accessible modal rather than WhatsApp.
2. The visitor completes the required name, phone, package, preferred date/month,
   and traveler fields plus only the optional questions enabled for that Tour.
3. The form explains that WordPress will save the request for recovery and requires
   contact consent.
4. Selecting **Save & review WhatsApp message** validates the form and creates or
   refreshes an idempotent private inquiry.
5. The visitor reviews the exact message and request reference.
6. **Open WhatsApp to send** opens `wa.me/254712965131`; the visitor still sends the
   message inside WhatsApp.

The website records `WhatsApp opened`, never `message sent`.

## Data and security boundaries

- Inquiries use a non-public, non-queryable `hks_inquiry` post type.
- The post type is absent from REST, search, navigation, feeds, and export.
- Only users with `manage_options` can see inquiry screens.
- Names and phone numbers are protected metadata, never post titles or analytics.
- The public capture endpoint verifies a signed Tour/Campaign token, a UUID v4
  idempotency key, a time trap, a honeypot, strict fields, and a salted-IP transient
  rate limit. Raw IP addresses are not stored.
- Attribution is limited to UTMs, the landing path, referrer host, canonical Tour,
  and Campaign context. Click IDs remain excluded until privacy decisions permit
  them.
- A retry with the same request key refreshes the same record instead of creating a
  duplicate.

## Event contract

The component pushes only non-sensitive context to `window.dataLayer` and dispatches
the same payload as `hks:analytics`:

- `view_tour` or `view_campaign`
- `quote_cta_click`
- `quote_form_start`
- `quote_form_error` (field name and error type, never the value)
- `quote_inquiry_saved`
- `quote_form_complete` (traveler-count bucket only)
- `whatsapp_launch`

No Meta, GA4, or GTM ID is invented or loaded. Production trackers and consent
management remain gated on client configuration.

## Deployment verification

After cPanel deployment, confirm:

1. the modal works on a published Tour and Campaign;
2. invalid and expired requests fail without creating a record;
3. one valid submission appears under **Tours → Quote inquiries**;
4. editing and resaving the same browser request updates rather than duplicates it;
5. the review message contains the correct package and request reference;
6. WhatsApp opens with encoded text and the record changes only to `Opened`; and
7. no inquiry answer appears in REST responses or `dataLayer`.

Before production launch, approve the privacy notice, retention period, deletion
process, access roles, cookie/analytics consent, and tracking IDs.
