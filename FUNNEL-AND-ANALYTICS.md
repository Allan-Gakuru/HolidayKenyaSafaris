# Funnel and Analytics Contract

## Conversion Funnel

`Facebook ad -> package or campaign page -> Request a quote -> intake form -> private WordPress recovery record and team notification -> reviewed message -> WhatsApp or email handoff -> consultant response -> quote -> follow-up -> booking`

Other channels may enter the same system:

- Organic Facebook and Instagram.
- Google Search.
- Referrals and shared links.
- TikTok or YouTube awareness.
- Email and retargeting.

The Group Travel route enters the same system through an inline planner:

`Group Travel page -> Destination -> matching Tour -> dates and group size -> private recovery record and team notification -> reviewed message -> WhatsApp or email handoff -> consultant response`

Travel Guides add two intentional paths:

`Standard Guide -> View this trip -> canonical Tour -> Request a quote -> shared intake and review flow`

`Advertorial -> early or contextual Request a quote -> shared intake and review flow`

## Qualified Inquiry

At minimum, a qualified website inquiry contains:

- Name.
- Phone number.
- Package interest.
- Preferred date or travel month.
- Number of travelers.

Where relevant, also collect:

- Adults and children.
- Kenyan resident/citizen or non-resident status.
- Departure town.
- Preferred vehicle.
- Accommodation tier or room preference.
- Budget range.
- Group or organization type.

Do not require every optional field on every package. Form length should reflect the quoting complexity.

## Canonical Tour Conversion Placement

The canonical Tour page reproduces the reference site's two-column desktop workspace but replaces its permanent booking form with the HKS quote system.

Desktop entry points:

- Sticky right-column quote panel.
- Final Tour quote prompt.
- Header quote action when appropriate.

Mobile entry points:

- In-flow quote panel after the initial Tour facts.
- Safe-area-aware sticky **Request a quote** action.
- Final Tour quote prompt.

Every entry point opens the same intake dialog or mobile sheet with Tour and campaign context already attached. Campaigns use the same title-band, gallery, facts, sticky quote panel, tabs/disclosures, itinerary, related-Tour, and responsive structure as canonical Tours, with Campaign-controlled headline, supporting copy, first gallery image, price, and navigation mode. Do not show a separate long form in the sidebar, silently launch a handoff app, or describe the action as booking.

## Travel Guides Conversion Placement

A Standard Guide is reading-first. When it has a Primary Tour, show an early **View this trip** link to the canonical Tour. Do not give Standard Guides a sticky quote bar or bypass the Tour page with a quote action.

An Advertorial requires a published Primary Tour and may use:

- An opening **Request a quote** action after the article promise.
- A sticky desktop Primary Tour panel beside the reading column.
- Contextual quote actions after useful proof or planning sections.
- A final quote prompt.
- A mobile sticky quote action after the opening action has left the viewport.

Every Advertorial quote action opens the same existing intake. The visitor creates or refreshes the private recovery record, reviews the message, and chooses WhatsApp or email. Repetition must support a real decision point rather than manufacture urgency.

## Message Handoff Behavior

Official Holiday Kenya Safaris destination:

`https://wa.me/254712965131`

Official email destination:

`info@holidaykenyasafaris.ke`

The compact utility-bar WhatsApp contact and global floating **Chat on WhatsApp** control are lightweight direct-chat routes. The utility contact uses a concise page-aware message and includes the current title and URL on a Tour or Campaign. The floating control uses one fixed general message with no visitor customization. Neither route replaces the structured intake path used by page-level quote actions or creates an inquiry recovery record.

Default generated message:

```text
Hi Holiday Kenya Safaris, my name is {name}.

I am interested in {package}.
Preferred travel date/month: {date}.
Travelers: {traveler_summary}.
Departure town: {departure_or_not_provided}.

I came from: {campaign_or_page_label}.

Please confirm availability, the current KSh price, what is included, and the next booking step.
```

Requirements:

- Explain that selecting `Review quote request` stores the validated answers privately in WordPress and emails them to the configured internal recipients for follow-up.
- Store inquiry records outside public queries, search, REST responses, and analytics, with administrator-only access.
- Store internal recipient addresses only in the private HKS Settings options record. Never expose them in public markup, REST responses, analytics, or the public repository.
- Queue internal notification delivery outside the visitor-facing REST request. Deduplicate notifications for an unchanged idempotent request and record queued, mailer-accepted, or failed state privately without blocking the visitor's review or WhatsApp/email handoff.
- Encode message text safely.
- Validate fields before offering either handoff.
- Let the user choose **Open WhatsApp to send** or **Open email to send** after reviewing the same message.
- Build the email with `info@holidaykenyasafaris.ke` as recipient, the Tour name and request reference in the subject, and the exact reviewed message in the body.
- Never claim the inquiry has been sent merely because WhatsApp or the email app opened.
- Preserve UTMs and campaign label in hidden state or the generated message, subject to privacy decisions.
- Support WhatsApp app and web behavior on mobile and desktop.

## Event Contract

Use a stable event vocabulary for Meta and GA4.

| Event | Trigger | Suggested parameters |
|---|---|---|
| `view_tour` | Tour detail becomes viewable | tour ID, slug, Tour Scope, destination, type, duration, price-display state |
| `view_campaign` | Focused landing page becomes viewable | campaign ID, linked tour, avatar/angle, source, inherited or overridden price-display state |
| `select_tour` | Visitor opens a tour from a listing | tour ID, list name, position |
| `tour_gallery_open` | Visitor opens the Tour gallery | tour ID, image count, entry location |
| `tour_section_open` | Visitor opens a Tour tab or mobile disclosure | tour ID, section name, device layout |
| `itinerary_toggle` | Visitor opens a day or uses expand/collapse all | tour ID, day label or action; no personal values |
| `related_tour_select` | Visitor opens a related Tour | source tour ID, selected tour ID, position |
| `view_article` | A Standard Guide or Advertorial becomes viewable | article ID, article format, Primary Tour ID when present, public Destination and Article Topic slugs |
| `article_primary_tour_click` | Visitor uses **View this trip** or an article Primary Tour link | article ID, article format, Primary Tour ID, CTA location |
| `quote_cta_click` | Visitor opens the intake form | tour ID, campaign ID, CTA location |
| `quote_form_start` | Visitor interacts with first field | tour ID, campaign ID |
| `quote_form_error` | Validation prevents completion | field name, error type; never send sensitive values |
| `quote_form_complete` | Valid form is used to construct message | tour ID, campaign ID, traveler-count bucket |
| `quote_inquiry_saved` | A private recovery record is successfully created or refreshed | tour ID, campaign ID, non-sensitive request reference |
| `whatsapp_launch` | Website opens the `wa.me` URL | tour ID, campaign ID, CTA location, UTMs |
| `email_launch` | Website opens the visitor's email app with the reviewed request | tour ID, campaign ID, CTA location, UTMs |
| `contact_click` | Visitor uses phone, email, utility WhatsApp, floating WhatsApp, or map contact | method, page type, contact location |

The connected Meta integration maps `quote_form_complete` to Meta's standard `Lead` event. It fires only after the validated inquiry has been saved and sends only the non-sensitive Tour/Campaign context and traveler-count bucket already allowed by this contract. It does not fire on the initial quote-button click, validation failure, WhatsApp launch, or email launch.

Do not treat `whatsapp_launch` or `email_launch` as proof that a message was sent, a confirmed lead, or a booking. Reconcile website events with actual conversations and sales records.

For the Group Travel planner, use `page_type: group_travel` and `cta_location: group_travel_page`. The selected Tour ID and slug become the standard Tour context before completion events fire. Do not place the visitor's name, phone, dates, Destination label, or exact traveler count in analytics; the inquiry record may store the derived Destination privately for operational triage.

For an Advertorial quote, use `page_type: article` and include only article ID, article format, Primary Tour ID, CTA location, public taxonomy slugs, and existing attribution values. Reuse `quote_cta_click`, `quote_form_complete`, `whatsapp_launch`, and `email_launch`; do not create a second quote-event vocabulary. Standard Guides use `view_article` and `article_primary_tour_click` only unless the visitor later enters the canonical Tour flow.

These measurements are for Google Analytics 4 and Meta reporting after the client supplies and configures their IDs. The browser emits small anonymous event names and non-sensitive page context into the existing analytics adapter; the integrations use them to compare which guide formats and placements lead visitors toward a Tour or quote. Until IDs and consent configuration exist, the hooks remain disabled. Do not implement scroll depth, time-on-page, author tracking, names, phone numbers, dates, exact traveller answers, or generated WhatsApp message content.

## Required IDs

Mark these as `CLIENT CONFIRMATION REQUIRED` until supplied:

- Meta Pixel ID.
- GA4 Measurement ID.
- Google Tag Manager container ID, if GTM is chosen.
- Meta domain verification and ad-account details, if needed.
- Consent-management configuration.

Do not insert example IDs that can accidentally transmit production data.

## Campaign Attribution

Capture and retain:

- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`
- `utm_term`
- Meta click ID where legally and technically appropriate.
- Landing-page and campaign IDs.
- Original referrer where available.

Persist attribution long enough for the visitor to browse from a campaign page to a canonical tour page and still send a traceable inquiry. Respect the final consent and privacy policy.

## Measurement Priorities

Primary:

- Cost per qualified quote conversation.
- Quote-form completion rate.
- WhatsApp and email launch rates.
- Inquiry-to-quote rate.
- Quote-to-booking rate, once sales data is available.

Diagnostic:

- Ad CTR and CPC.
- Landing-page engagement.
- CTA click rate.
- Gallery, Tour-section, and itinerary engagement where useful for diagnosing missing proof or unanswered questions.
- Form abandonment and validation errors.
- Performance by package, avatar angle, device, and placement.
- Top objections recorded by consultants.

Do not scale campaigns based only on likes, cheap clicks, or raw WhatsApp opens.

## Facebook Landing Rule

An ad should normally land on:

- A focused campaign page when the ad speaks to a specific avatar, occasion, problem, desire, or objection.
- The canonical tour page when the ad is package-led and the page already matches its promise.

Do not send paid package traffic to the generic homepage unless the campaign is intentionally broad.

## Consultant Feedback Loop

Capture weekly:

- Number of qualified conversations by campaign and tour.
- Questions and objections heard repeatedly.
- Price, date, accommodation, and group-size patterns.
- Reasons quotes did not close.
- Photos or proof customers requested.

Use this feedback to revise ads, page order, FAQs, form questions, and follow-up scripts.

## Privacy and Security

- Collect only fields needed to quote.
- Do not put sensitive personal details in analytics parameters.
- Escape and sanitize all form inputs.
- Protect forms against spam and abusive automation.
- Publish a client-approved privacy policy and cookie/consent behavior before production tracking.
