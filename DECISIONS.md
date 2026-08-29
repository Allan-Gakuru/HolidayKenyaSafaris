# Confirmed Decisions

## Business

| Item | Decision |
|---|---|
| Exact brand name | Holiday Kenya Safaris |
| Parent/operator relationship | Disclose discreetly that it is operated by Ashford Tours & Travel |
| Primary market | Local Kenyan travelers and local organizations |
| Positioning | Attainable-premium: better planned and more trustworthy than bargain poster sellers, warmer and more reachable than formal luxury operators |
| Initial commercial goal | Qualified quote inquiries generated from Facebook ads and high-intent website visits, handed off through WhatsApp or email |
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
- Use native WordPress Posts for the public **Travel Guides** section. Posts are public editorial pages, not a private classification system.
- Give Posts two public discovery relationships through Secure Custom Fields: the shared Destination taxonomy and the Post-only Article Topic taxonomy.
- Seed Article Topics for Destination Guides, Planning & FAQs, Travel Inspiration, Comparisons, and Holiday Kenya Safaris News. Comparisons cover useful travel choices and trip formats.
- Support a reading-first Standard Guide and a conversion-focused Advertorial. A Standard Guide may link one Primary Tour; an Advertorial must link one published Primary Tour.
- Advertorial heroes place a fully expanded **What we’ll cover** outline immediately below the excerpt and before the opening quote action. It links every rendered body H2 and H3, nests H3 links beneath the preceding H2, assigns deterministic unique IDs where needed, and remains a simple non-sticky article aid with no active-section state.
- Show no author name on Travel Guides pages, cards, archives, or related-guide modules. Featured images remain optional and missing imagery must produce an intentional text-led design.
- Related reading contains blog posts only: up to three editor-selected Posts first, then Posts sharing the Destination, then Posts sharing the Article Topic.
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
- A global floating **Chat on WhatsApp** control opens the official number with a concise general reach-out message; on canonical Tour pages it adds the current Tour title and canonical link. It does not collect answers or create a WordPress inquiry record; structured quote actions continue to use the intake, recovery, review, and visitor-controlled launch flow.
- Every structured quote CTA opens an intake form before the visitor chooses WhatsApp or email.
- Required fields: name, phone, package, preferred travel date or month, and number of travelers.
- The form constructs one reviewed message that the visitor may open in WhatsApp or in an email addressed to `info@holidaykenyasafaris.ke`.
- Selecting `Review quote request` stores the validated inquiry privately in WordPress, creates its `HKS-######` reference, and queues the details for background delivery to the private repeatable notification-recipient list under **HKS Settings → Conversion**. The review step does not wait for SMTP. Identical retries are deduplicated; revised answers may trigger a new notification.
- A team-notification timestamp means WordPress handed the email to its configured mailer; Fluent SMTP logs remain the delivery record. WordPress records `WhatsApp opened` only after that launch click, analytics may record a non-sensitive `email_launch`, and neither visitor-controlled action may be described as proof that the reviewed message was sent.
- Campaign attribution and package context should be retained.
- The canonical public CTA label is **Request a quote**.
- Canonical Tour pages use a persistent quote panel, not a permanently visible long booking form.
- The published Group Travel page uses the same intake, private recovery record, message review, and visitor-controlled WhatsApp/email handoff. It adds linked Destination and Tour choices, then uses the standard date/month and traveler-count fields.
- Group Travel Destination and Tour choices come from published catalogue records. The selected Destination is derived from the chosen Tour when the inquiry is stored; no duplicate client-maintained Group Travel fields are added.

## Website Scope

- Homepage.
- Tour catalogue.
- Destination pages.
- Tour/package detail pages.
- Travel Guides hub, Article Topic archives, Standard Guides, and Advertorials.
- About and trust page.
- Contact page.
- Reusable focused landing-page template for advertising.
- Future ability to add more campaign variations around the same tour.

## UI and UX Structure

- The approved catalogue contract governs the global navigation, image-led homepage, catalogue grids, and canonical Tour pages.
- The homepage is the only header exception: it omits the utility bar and overlays the existing logo and primary navigation directly on the hero using an almost-clear, lightly blurred Pale Mist surface. The header stays fixed at the top and changes to solid white after the visitor begins scrolling. Internal pages retain the two-level header.
- The homepage hero uses up to five published Featured Tours whose featured images are at least 1200 × 675 pixels. Hero eligibility does not depend on WordPress alt text; the repeated hero and preview images are decorative because the Tour title, destination, and labeled controls carry the same context. The active Tour title is the single H1, its CTA reads `Click here to book tour` and links to the canonical Tour, and portrait cards cycle every five seconds with minimal arrows, direct selection, swipe/drag, keyboard support, pause/resume, a synchronized progress line, and reduced-motion safeguards.
- Browse by destination appears directly below the hero, followed by the Featured Tours grid.
- Catalogue-mode implementation must keep the Wayfinder identity, HKS copy, shared conversion service, compact media treatment, clear package context, and accessible interactions.
- Standard website pages use light, browseable Catalogue mode.
- Focused paid-ad pages use the canonical Tour title-band, gallery, facts, quote-panel, tabs/disclosures, itinerary, related-Tour, and responsive structure. Campaigns preserve ad congruency through editable headline, supporting copy, first gallery image, price override, and navigation mode.
- Canonical Tour pages use a compact title and breadcrumb band followed by a desktop three-column composition: a vertical gallery rail capped at six visible thumbnails, one active image and a sticky quote panel. When more images exist, the sixth thumbnail shows a dark `+N more` overlay and opens the full gallery, preventing the rail from increasing the workspace row height. The active image has previous/next chevrons and advances every five seconds unless the visitor is interacting, the gallery is off-screen, the document is hidden or reduced motion is requested. Tour facts, accessible tabs, mobile disclosures, itinerary timeline, related Tours and the final quote prompt remain part of the canonical flow.
- A conventional booking sidebar is replaced by an HKS quote panel whose **Request a quote** button opens the approved intake, private recovery, message-review, and visitor-controlled WhatsApp/email handoff.
- Desktop navigation uses a utility bar plus product-led primary header. The utility bar carries a direct WhatsApp link with a prefilled, page-aware reach-out message; the primary header does not repeat it as a large button. Page-level quote actions still open the approved intake and recovery flow. Mobile uses a full-height accessible navigation drawer.
- Approved top-level navigation is Home, Safaris, Coast & Stays, Destinations, Travel Guides, Group Travel, About, and Contact. The mobile drawer retains a clear Request a quote action.
- Travel Guides appears between Destinations and Group Travel in the primary navigation. Its canonical hub is `/travel-guides/`.
- Header/mobile and footer links use native WordPress menu locations exposed as **Appearance > Site Menus**. One assigned two-level Primary menu drives both desktop dropdowns and mobile accordions; Footer has its own location. The prior catalogue-aware markup remains the fallback until an editor assigns a menu, preventing an empty header during rollout.
- Kenya and international Tours are separated by the public Tour Scope taxonomy. Destination remains the geographic taxonomy used beneath either scope.
- Destination is shared by Tours and native Posts so one place has one canonical Destination archive and one taxonomy sitemap entry. The Destination archive remains Tour-first: its main query, pagination, browse counts, and Tour navigation count published Tours only, while relevant Travel Guides appear in a separate section below the Tour catalogue.
- Standard Guides use a contextual **View this trip** link when a Primary Tour is assigned and have no persistent quote action. Advertorials use early and repeated **Request a quote** actions that open the shared intake and review flow, plus a desktop sticky Tour panel and a mobile sticky quote action after the opening CTA leaves view.
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
- Quote CTA, inquiry, WhatsApp-launch, and email-launch events.
- UTM and campaign attribution.
- Consent and privacy controls appropriate to the final tracking setup.

Travel Guides use the minimal anonymous event set `view_article` and `article_primary_tour_click`, plus the existing `quote_cta_click`, `quote_form_complete`, and `whatsapp_launch` events when an Advertorial enters the quote flow. Article ID, format, Primary Tour ID, CTA location, public taxonomy slugs, and campaign attribution may be used; personal details and exact form answers may not.

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
