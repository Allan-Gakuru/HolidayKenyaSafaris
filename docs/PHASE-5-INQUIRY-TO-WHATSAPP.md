# Phase 5: Saved inquiry to WhatsApp or email

The MVP conversion component is the dynamic `hks/quote-cta` block. On a published
Tour or Campaign it resolves the canonical Tour, selected intake questions, CTA
copy, Campaign label, and official Holiday Kenya Safaris WhatsApp and email destinations.

## Visitor journey

1. The quote CTA opens an accessible modal rather than a messaging app.
2. The visitor completes the required name, phone, email, package, preferred date/month,
   and traveler fields plus only the optional questions enabled for that Tour.
3. The visitor completes the form and selects **Review quote request**.
4. Selecting **Review quote request** validates the form and creates or
   refreshes an idempotent private inquiry, creates its reference, and queues the
   validated details for background delivery to every address configured under **HKS Settings → Conversion →
   Quote notification recipients**. An identical retry is not emailed twice; revised
   answers may generate a new notification. The visitor does not wait for SMTP.
5. The visitor reviews the exact message and request reference. It contains the
   visitor's contact and trip answers, but not the HKS destination number,
   Campaign label, or Facebook/UTM source information.
6. **Open WhatsApp to send** opens `wa.me/254712965131`; **Open email to send** opens
   the visitor's configured email app with `info@holidaykenyasafaris.ke` as recipient,
   the Tour name and request reference in the subject, and the exact reviewed message
   in the body. The visitor still sends the message inside the chosen app.

The website may record `WhatsApp opened` and the anonymous `email_launch` event,
never `message sent`.

## Data and security boundaries

- Inquiries use a non-public, non-queryable `hks_inquiry` post type.
- The post type is absent from REST, search, navigation, feeds, and export.
- Only users with `manage_options` can see inquiry screens.
- Names, phone numbers, and email addresses are protected metadata, never post titles or analytics.
- The public capture endpoint verifies a signed Tour/Campaign token, a UUID v4
  idempotency key, a time trap, a honeypot, strict fields, and a salted-IP transient
  rate limit. Raw IP addresses are not stored.
- Attribution is limited to UTMs, the landing path, referrer host, canonical Tour,
  and Campaign context. Click IDs remain excluded until privacy decisions permit
  them. This attribution remains private with the inquiry and in non-sensitive
  launch analytics; it is not copied into the visitor-reviewed message.
- A retry with the same request key refreshes the same record instead of creating a
  duplicate.
- Notification recipients are repeatable private HKS Settings values. They never
  appear in public markup, REST responses, analytics, or source-controlled defaults.
- WordPress records whether the team notification is queued and whether its configured
  mailer later accepts or rejects it. Fluent SMTP's Email Logs remain the delivery-troubleshooting record;
  notification failure does not remove the visitor's WhatsApp/email handoff choices.

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
- `email_launch`

No Meta, GA4, or GTM ID is invented or loaded. Production trackers and consent
management remain gated on client configuration.

## Deployment verification

After cPanel deployment, confirm:

1. the modal works on a published Tour and Campaign;
2. invalid and expired requests fail without creating a record;
3. add the approved internal recipients under **HKS Settings → Conversion**, then
   confirm one valid submission appears under **Tours → Quote inquiries** and one
   notification appears in Fluent SMTP's Email Logs for every configured recipient;
4. replaying the same unchanged request does not create another notification, while
   editing an answer and reviewing again does;
5. the review message contains the correct visitor contact details, package, and
   request reference without the HKS destination number or Campaign/Facebook/UTM
   attribution;
6. WhatsApp opens with encoded text and the record changes only to `Opened`;
7. the visitor's email app opens with the confirmed public recipient, subject, and
   exact reviewed body; and
8. no inquiry answer or internal recipient appears in REST responses or `dataLayer`.

Before production launch, approve the privacy notice, retention period, deletion
process, access roles, cookie/analytics consent, and tracking IDs.
