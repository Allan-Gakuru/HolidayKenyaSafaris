# Confirmed Decisions

## Business

| Item | Decision |
|---|---|
| Exact brand name | Holiday Kenya Safaris |
| Parent/operator relationship | Disclose discreetly that it is operated by Ashford Tours & Travel |
| Primary market | Local Kenyan travelers and local organizations |
| Positioning | Attainable-premium: better planned and more trustworthy than bargain poster sellers, warmer and more reachable than formal luxury operators |
| Initial commercial goal | Qualified WhatsApp inquiries generated from Facebook ads and high-intent website visits |
| Checkout | Not part of the initial build |

## Product Scope

Include:

- Domestic road and flying safaris.
- Nairobi and regional excursions.
- Coast trips and relevant Mombasa experiences.
- Staycations.
- Couple, family, friend-group, chama, church, SACCO, school, youth, corporate, and other group packages.
- Mount Kenya, hiking, photography, conservation, and other suitable special-interest products.
- International holidays published by Ashford Tours & Travel that can be accurately offered to HKS customers.

Exclude by default:

- Visa services.
- Standalone airport or hotel transfers.
- Any product with implausible or unverified public information until reviewed.

## Content

- Retain factual itinerary, route, inclusion, exclusion, duration, and accommodation information from approved Ashford sources.
- Rewrite marketing copy around local identities, occasions, problems, desires, objections, and objectives.
- Do not restrict the whole website to Mercy. Support avatar-specific campaign pages and tour-page variants.
- Treat photographs uploaded or assigned by an authorized editor and used on published content as approved for the website.
- Use clear Kenyan English and KSh pricing.
- Any unavailable company information may be taken from Ashford's current website if relevant and verifiable; otherwise request it.

## Pricing

- Each Tour has one editable `From price per person (KSh)` field stored as `hks_from_price_ksh`.
- A positive Tour value displays as `From KSh X per person` on the Tour, its cards, catalogue and taxonomy archives, Destination pages, and related-Tour modules. Blank or zero produces no price output.
- For the client-authorized Ashford catalogue expansion, use the source low-season per-person amount, or a clearly credible published starting amount where no seasonal table exists, convert it with the live USD/KSh rate on the import date, and always round upward to the next KSh 500. Reject placeholder/deposit-like values.
- The repository import manifest records the original currency and amount, exchange-rate source, rate, conversion date, unrounded result, and published rounded KSh amount. These audit values do not become client editor fields.
- Campaigns retain their separate optional `From price per person (KSh)` field. A positive Campaign value overrides the linked Tour price on that Campaign; a blank Campaign value may inherit the linked Tour price.
- Prices do not have client-facing statuses, assumptions, checked dates, validity dates, seasonal rows, supplements, or automatic expiry. An authorized editor updates or removes the editable value manually.
- Campaign start and end dates do not automatically change the Campaign or Tour price or WordPress publication state.

## Editorial Approval and Field Economy

- Publishing is approval. An authorized editor's decision to publish public copy and assigned media is the client-confirmation signal.
- Any WordPress user who already has permission to publish the relevant content is treated as an authorized editor. This change does not alter roles, capabilities, or admin ownership.
- Draft content is not public. Blank optional fields are omitted with deliberate frontend fallbacks.
- Client-facing content forms contain only fields that display publicly or visibly control public discovery and placement.
- Tour date, source-audit, confirmation-status, rights-status, and price-assumption fields are removed from the client workflow.
- Campaigns are the only content records with start and end date fields. These dates are the explicit exception to the visible-output rule and serve campaign operations only. The preferred travel date or month in the inquiry form remains visitor input.
- Road safari, flying safari, coast experience, staycation, group package, and other product differences are expressed through Tour Type, Destination, Occasion, Travel Style, and the public itinerary—not pricing assumptions.
- Internal import notes may remain in repository source files. Stable IDs and legacy metadata may remain stored for backward compatibility but are not editable requirements and do not gate publication.
- Publishing does not permit invented prices, reviews, policies, availability, or operational claims. Imports normally remain drafts until an authorized editor reviews and publishes them; the dated Ashford expansion is the explicit client-authorized direct-publication exception, with unclear or unpriceable records retained as drafts.

## Conversion

- Official Holiday Kenya Safaris mobile and WhatsApp number: `+254 712 965 131`.
- Official public email: `info@holidaykenyasafaris.ke`.
- Official Instagram: `https://www.instagram.com/holidaykenyasafaris/`.
- Official Facebook: `https://www.facebook.com/people/Holiday-Kenya-Safaris/61591508593846/`.
- A global floating **Chat on WhatsApp** control opens the official number with one fixed general reach-out message. It does not collect answers, customize the message, or create a WordPress inquiry record; structured quote actions continue to use the intake, consent, recovery, review, and visitor-controlled launch flow.
- Every quote CTA opens an intake form before WhatsApp.
- Required fields: name, phone, package, preferred travel date or month, and number of travelers.
- The form constructs a prefilled WhatsApp message that the visitor chooses to send.
- Selecting `Review WhatsApp message` stores the validated inquiry privately in WordPress before the review step, so the team can recover a lead when WhatsApp does not open or the visitor does not complete the handoff.
- The form must disclose storage and require contact consent before saving. WordPress records `WhatsApp opened` only after the launch click and must never claim that the message was sent.
- Campaign attribution and package context should be retained.
- The canonical public CTA label is **Request quote on WhatsApp**.
- Canonical Tour pages use a persistent quote panel, not a permanently visible long booking form.
- The published Group Travel page uses the same intake, consent, private recovery record, message review, and visitor-controlled WhatsApp launch. It adds linked Destination and Tour choices, then uses the standard date/month and traveler-count fields.
- Group Travel Destination and Tour choices come from published catalogue records. The selected Destination is derived from the chosen Tour when the inquiry is stored; no duplicate client-maintained Group Travel fields are added.

## Website Scope

- Homepage.
- Tour catalogue.
- Destination pages.
- Tour/package detail pages.
- About and trust page.
- Contact page.
- Reusable focused landing-page template for advertising.
- Future ability to add more campaign variations around the same tour.

## UI and UX Structure

- The approved catalogue contract governs the global navigation, image-led homepage, catalogue grids, and canonical Tour pages.
- Catalogue-mode implementation must keep the Wayfinder identity, HKS copy, shared conversion service, compact media treatment, clear package context, and accessible interactions.
- Standard website pages use light, browseable Catalogue mode.
- Focused paid-ad pages use Campaign mode and may retain the immersive emotional structure of the existing Maasai Mara prototype.
- Canonical Tour pages use a compact title and breadcrumb band, three-image gallery, destination line, approximately 68/32 desktop workspace, accessible tabs, mobile disclosures, itinerary timeline, sticky quote panel, related Tours, and final quote prompt.
- A conventional booking sidebar is replaced by an HKS quote panel whose **Request quote on WhatsApp** button opens the approved intake, consent, private recovery, message-review, and WhatsApp-launch flow.
- Desktop navigation uses a utility bar plus product-led primary header. The utility bar carries a direct WhatsApp link with a prefilled, page-aware reach-out message; the primary header does not repeat it as a large button. Page-level quote actions still open the approved intake and recovery flow. Mobile uses a full-height accessible navigation drawer.
- Approved top-level navigation is Home, Safaris, Coast & Stays, Destinations, Group Travel, About, and Contact. The mobile drawer retains a clear Request quote on WhatsApp action.
- Kenya and international Tours are separated by the public Tour Scope taxonomy. Destination remains the geographic taxonomy used beneath either scope.
- Visa services, standalone transfers, and non-Tour service pages remain excluded.

## Brand

- Selected direction: The Wayfinder.
- Montserrat is the single website font for headings, body copy, navigation, forms, and controls; the outlined logo artwork remains unchanged.
- The website header and mobile navigation drawer use the same production lockup: `wp-content/themes/hks-wayfinder/assets/images/brand/holiday-kenya-safaris-logo.svg`.
- This lockup is approved for website header use. Complete the remaining stacked, icon-only, one-color, reversed, print, social, and favicon variants as a separate identity-production task.
- Use the classic raised-roof safari Defender mockup as an application reference, not as the main logo symbol.
- The brand does not need app-specific identity assets.

## Technology

- WordPress.
- Custom block theme with `theme.json`.
- Small site plugin for content models and business logic.
- Secure Custom Fields for structured fields, repeaters, relationships, and editor configuration.
- Version-controlled field definitions.
- No heavy page builder.
- No headless frontend in the initial release.

## Analytics

Prepare integrations for:

- Meta Pixel.
- Google Analytics 4.
- Optionally Google Tag Manager if selected during implementation.
- WhatsApp CTA and inquiry events.
- UTM and campaign attribution.
- Consent and privacy controls appropriate to the final tracking setup.

Client IDs will be supplied later. Do not hard-code invented IDs.

## Phase 6 and 7 Delivery Decisions

- HKS Core `0.9.0` includes an idempotent, exact-match copy cleanup for previously seeded public Pages, Tours, and Campaign fields. It replaces only known implementation-facing seed phrases and does not overwrite independently edited wording or change publication status.
- Standard WordPress Pages use the Wayfinder title band and editorial content system; no new page builder or duplicate content model is introduced.
- About, Contact, Group Travel, and four legal routes were created through the existing guarded importer. Group Travel is published with a catalogue-driven quote planner; Contact and legal records stay in Draft until their missing project-level information is supplied.
- The 44 reviewed local Ashford candidates resolve to 40 Phase 7 draft imports: three already exist as MVP Tours and the generic `African-wildlife-safari` marketing page is excluded as non-quotable.
- Phase 7 is split into four operator-triggered batches: Road Safaris, Flying Safaris and Mount Kenya, Nairobi Excursions, and Mombasa Excursions.
- Catalogue migration preserves Ashford titles and source URLs, maps the four existing Tour taxonomies, and carries over source duration and route headings when available.
- The completed local migration assigned no automatic price. The new client-authorized Ashford expansion may assign source images, literal itinerary content, a converted low-season starting price, Tour Scope, Destinations, and other supported public Tour fields, then publish directly.
- Direct publication is limited to this client-authorized expansion. Contradictory, incomplete, or unpriceable products remain draft and are reported.
- Seeded records can be refreshed only while they remain Draft. Any record moved beyond Draft is protected from importer updates.
- Tour Type, Occasion/Audience, and Travel Style are public catalogue taxonomies, alongside Destination. Their canonical term routes use `/tour-types/`, `/occasions/`, and `/travel-styles/` respectively.
- Public taxonomy queries are constrained to published Tours. This is especially important for Occasion/Audience because Campaigns may share those terms without appearing in catalogue archives.
- Inquiry submission and WhatsApp launch testing is deferred until the client controls the destination number.
