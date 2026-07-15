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
- Staycations when confirmed by the client.
- Couple, family, friend-group, chama, church, SACCO, school, youth, corporate, and other group packages.
- Mount Kenya, hiking, photography, conservation, and other suitable special-interest products.

Exclude by default:

- International holidays.
- Visa services.
- Standalone airport or hotel transfers.
- Tanzania-only and clearly inbound-only products.
- Any product with implausible or unverified public information until reviewed.

## Content

- Retain factual itinerary, route, inclusion, exclusion, duration, and accommodation information from approved Ashford sources.
- Rewrite marketing copy around local identities, occasions, problems, desires, objections, and objectives.
- Do not restrict the whole website to Mercy. Support avatar-specific campaign pages and tour-page variants.
- Reuse Ashford photographs only where the client has confirmed usage rights.
- Use clear Kenyan English and KSh pricing.
- Any unavailable company information may be taken from Ashford's current website if relevant and verifiable; otherwise request it.

## Pricing

- Public format: `From KSh X`.
- Current KSh values are placeholders, not approved live rates.
- Every price record must include pricing status and assumptions.
- Required assumptions: season, Kenyan resident or citizen status, group size, sharing basis, transport, accommodation tier, and notable inclusions.
- Converted USD values must never be presented as approved KSh prices.
- Unknown prices may display `Request current rate` rather than a fabricated figure.

## Conversion

- Temporary WhatsApp number: `+254 722 742 799`.
- Every quote CTA opens an intake form before WhatsApp.
- Required fields: name, phone, package, preferred travel date or month, and number of travelers.
- The form constructs a prefilled WhatsApp message that the visitor chooses to send.
- Selecting `Save & review WhatsApp message` stores the validated inquiry privately in WordPress before the review step, so the team can recover a lead when WhatsApp does not open or the visitor does not complete the handoff.
- The form must disclose storage and require contact consent before saving. WordPress records `WhatsApp opened` only after the launch click and must never claim that the message was sent.
- Campaign attribution and package context should be retained.
- The canonical public CTA label is **Request quote on WhatsApp**.
- Canonical Tour pages use a persistent quote panel, not a permanently visible long booking form.

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

- Attic Travel is the approved structural reference for the global navigation, image-led homepage, catalogue grids, and canonical Tour pages.
- This is not permission to copy Attic Travel's branding, copy, source code, plugins, long booking form, oversized hero carousel, missing price context, or UX defects.
- Standard website pages use light, browseable Catalogue mode.
- Focused paid-ad pages use Campaign mode and may retain the immersive emotional structure of the existing Maasai Mara prototype.
- Canonical Tour pages use a compact title and breadcrumb band, three-image gallery, destination line, approximately 68/32 desktop workspace, accessible tabs, mobile disclosures, itinerary timeline, sticky quote panel, related Tours, and final quote prompt.
- The Attic-style booking sidebar is replaced by an HKS quote panel whose **Request quote on WhatsApp** button opens the approved intake, consent, private recovery, message-review, and WhatsApp-launch flow.
- Desktop navigation uses a utility bar plus product-led primary header. Mobile uses a full-height accessible navigation drawer.
- Approved top-level navigation is Home, Safaris, Coast & Stays, Destinations, Group Travel, About, Contact, and Request quote on WhatsApp.
- International holidays, visa services, air ticketing, transfers, and inbound-only routes remain excluded.

## Brand

- Selected direction: The Wayfinder.
- Current Wayfinder concept is an approved direction, not a final production master.
- Create a clean production SVG, horizontal logo, stacked logo, icon-only mark, one-color marks, reversed marks, PNG exports, and favicon before building the final header.
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
