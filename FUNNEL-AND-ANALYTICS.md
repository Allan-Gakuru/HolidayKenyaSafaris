# Funnel and Analytics Contract

## Conversion Funnel

`Facebook ad -> package or campaign page -> WhatsApp CTA -> intake form -> prefilled WhatsApp message -> consultant response -> quote -> follow-up -> booking`

Other channels may enter the same system:

- Organic Facebook and Instagram.
- Google Search.
- Referrals and shared links.
- TikTok or YouTube awareness.
- Email and retargeting.

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

## WhatsApp Behavior

Temporary destination:

`https://wa.me/254722742799`

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

- Encode message text safely.
- Validate fields before opening WhatsApp.
- Let the user see that WhatsApp will open.
- Never claim the inquiry has been sent until the user sends it in WhatsApp.
- Preserve UTMs and campaign label in hidden state or the generated message, subject to privacy decisions.
- Support WhatsApp app and web behavior on mobile and desktop.

## Event Contract

Use a stable event vocabulary for Meta and GA4.

| Event | Trigger | Suggested parameters |
|---|---|---|
| `view_tour` | Tour detail becomes viewable | tour ID, slug, destination, type, duration, price status |
| `view_campaign` | Focused landing page becomes viewable | campaign ID, linked tour, avatar/angle, source |
| `select_tour` | Visitor opens a tour from a listing | tour ID, list name, position |
| `quote_cta_click` | Visitor opens the intake form | tour ID, campaign ID, CTA location |
| `quote_form_start` | Visitor interacts with first field | tour ID, campaign ID |
| `quote_form_error` | Validation prevents completion | field name, error type; never send sensitive values |
| `quote_form_complete` | Valid form is used to construct message | tour ID, campaign ID, traveler-count bucket |
| `whatsapp_launch` | Website opens the `wa.me` URL | tour ID, campaign ID, CTA location, UTMs |
| `contact_click` | Visitor uses phone, email, or map contact | method, page type |

Do not treat `whatsapp_launch` as a confirmed lead or booking. Reconcile website events with WhatsApp conversations and sales records.

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

- Cost per qualified WhatsApp conversation.
- Quote-form completion rate.
- WhatsApp launch rate.
- Inquiry-to-quote rate.
- Quote-to-booking rate, once sales data is available.

Diagnostic:

- Ad CTR and CPC.
- Landing-page engagement.
- CTA click rate.
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

