# Website Structure and Templates

## Structural Reference

The standard website uses the catalogue and canonical Tour structure documented in `UI-REFERENCE-ATTIC-TRAVEL.md`. Attic Travel is a UI and UX reference only. Holiday Kenya Safaris keeps the Wayfinder identity, local Kenyan scope, verified Ashford facts, KSh pricing rules, and qualified WhatsApp conversion flow.

The existing Maasai Mara prototype is retained as a Campaign landing-page reference. It is not the default canonical Tour template.

## Information Architecture

### Desktop utility bar

Use a compact trust and contact strip containing only confirmed information:

- Operating location.
- `Operated by Ashford Tours & Travel` disclosure.
- Phone and email.
- Social links.

### Primary navigation

- Home.
- Safaris.
- Coast & Stays.
- Destinations.
- Group Travel.
- About.
- Contact.
- Request quote on WhatsApp.

Suggested dropdown structure:

| Menu | Approved routes when populated |
|---|---|
| Safaris | Road Safaris, Day Excursions, Weekend and Short Breaks, Flying Safaris, Trekking and Special Interest |
| Coast & Stays | Coast Trips, Staycations, Bush and Beach |
| Destinations | Priority populated destinations followed by View all destinations |

Do not expose empty categories. Do not include international holidays, visa services, air ticketing, standalone transfers, or inbound-only products.

### Mobile navigation

Use a full-height drawer with:

- Wayfinder mark and accessible Close control.
- Search only when the catalogue is large enough to justify it.
- Accordion groups for Safaris, Coast & Stays, and Destinations.
- Direct Group Travel, About, and Contact links.
- Confirmed contacts and social links.
- Request quote on WhatsApp action.

The drawer must trap focus, close with Escape, return focus to the menu button, and prevent background scrolling.

## Sitemap

### Home

Purpose:

- Establish a broad, credible local-travel catalogue.
- Help visitors discover relevant verified Tours quickly.
- Make the Ashford operator relationship and quote process clear.
- Move visitors to a Tour, Campaign, destination, group-travel route, or qualified WhatsApp inquiry.

Default order:

1. Utility bar and primary header.
2. Image-led hero with one literal H1, supporting copy, and clear action.
3. Featured verified Tours.
4. Browse by destination.
5. Browse by trip type or occasion.
6. Why Holiday Kenya Safaris and the Ashford operator relationship.
7. How the WhatsApp quote process works.
8. Group Travel route.
9. Verified proof when available.
10. Final WhatsApp prompt and footer.

Hero rules:

- Prefer one decisive approved destination image.
- A slider may contain no more than three curated slides.
- Each slide needs its own useful package or destination action.
- Do not use rapid autoplay, generic repeated copy, or a large media carousel.
- Keep a hint of the next section visible on normal desktop and mobile viewports.

Featured Tour rules:

- Show up to six priority Tours initially.
- Use a three-column desktop grid, responsive tablet layout, and one-column mobile flow.
- Cards must show image, title, destination, duration, route or departure context, one price line, and a clear View trip action.

### Tour Catalogue and Taxonomy Archives

Purpose:

- Let visitors scan and compare approved products.
- Reproduce the clean, image-led catalogue rhythm of the reference site while exposing more useful local-buying information.

Default order:

1. Compact title and breadcrumb band.
2. Literal H1 and short introduction.
3. Filters and sort controls when inventory supports them.
4. Responsive Tour grid.
5. Pagination or deliberate load-more control.
6. Final discovery or WhatsApp prompt.

Initial filters may include:

- Destination.
- Tour type.
- Travel style.
- Occasion.

Tour cards show:

- Editor-selected destination image.
- Tour title.
- Destination.
- Duration.
- Route or departure point.
- `From KSh... per person`, or `Request current KSh rate` when the Tour price is blank.
- One or two useful travel-style labels.
- View trip action.

Do not display meaningless default metadata such as `1 person`. Mobile filters use an accessible drawer or dialog.

### Destination Pages

Purpose:

- Answer destination-level questions.
- Provide SEO entry points.
- Group relevant Tours.
- Explain suitable durations, travel styles, access context, and local considerations.

Use the catalogue visual system: compact title band, strong destination photography, practical introduction, relevant Tour grid, verified guidance, and final quote prompt.

Never invent weather, park-fee, or access information. Date and source any changeable facts.

### Canonical Tour Detail Page

Closely reproduce the reviewed Attic Travel Tour page structure, with the booking form replaced by the Holiday Kenya Safaris WhatsApp quote system.

Default order:

1. Utility bar and primary header.
2. Compact title and breadcrumb band containing the only page H1.
3. Three-image gallery mosaic with View gallery control.
4. Destination or route line.
5. Two-column Tour workspace.
6. Related Tours.
7. Final WhatsApp quote prompt.
8. Footer.

#### Gallery

Desktop uses one dominant image and two stacked supporting images. Mobile uses one dominant image with a useful supporting preview or gallery control. The lightbox must be keyboard operable and show only media assigned by an authorized editor to the published Tour.

#### Tour workspace

Desktop uses an approximately 68/32 main-content and quote-panel split.

Main content includes:

- Duration badge.
- Fast facts for nights, departure, route, travel style, accommodation basis, and transport.
- Overview tab.
- Itinerary tab.
- Included/Excluded tab.
- Rates & Important Information tab.

The right column contains a sticky quote panel with:

- `From KSh... per person` or `Request current KSh rate`.
- A standard note that the final quote depends on dates, group, availability, and the selected package.
- Primary **Request quote on WhatsApp** button.
- Short explanation of the intake and message-review step.
- Confirmed operator or response details when available.

The quote panel must not contain a permanent long booking form. Clicking the button opens the shared intake dialog or mobile sheet.

#### Itinerary

Use an expandable day-by-day timeline with:

- Day number and title.
- Description.
- Activities.
- Meals.
- Accommodation.
- Individual disclosure controls.
- Expand all and Collapse all controls when useful.

The first day may be open by default. On mobile, all Tour tabs become stacked accessible disclosures and expose the same information as desktop.

#### Related Tours

Show up to three relevant Tours from shared destinations, then use a catalogue fallback. Do not expose a curated-related-Tour field until the template consumes it, and do not add a wishlist without a real saved-trip workflow.

#### Required conversion behavior

Every Tour quote action follows:

`Request quote on WhatsApp -> intake form -> validation and consent -> private recovery record -> message review -> visitor launches WhatsApp -> visitor sends message`

Required intake fields:

- Name.
- Phone.
- Package.
- Preferred date or month.
- Number of travelers.

Optional fields are package-specific and appear only when needed to quote accurately.

Preserve from the existing Maasai Mara prototype:

- Clear practical detail.
- Visible price context.
- Intake before WhatsApp.
- Mobile CTA clarity.

Do not use its dark campaign hero as the canonical Tour opening.

### Campaign Landing Page

Purpose:

- Maintain message match with a Facebook ad.
- Convert one audience, occasion, desire, problem, or objection efficiently.
- Reuse canonical Tour facts.

Campaign pages may retain the immersive, emotionally focused structure of the existing Maasai Mara prototype.

Features:

- Linked Tour required.
- Full, reduced, or minimal navigation.
- Campaign-specific hero and navigation treatment.
- Canonical itinerary, inclusions, exclusions, logistics, and the single Tour price inherited.
- Attribution retained through inquiry save and WhatsApp launch.
- Start and end dates exist on Campaigns only; Campaign indexing defaults are template-controlled.

Campaign pages are not constrained to Mercy. Add variants as evidence supports them.

### Group Travel

Provide routes for:

- Corporate and MICE.
- Chamas, churches, and SACCOs.
- Schools and youth.
- Private friend or family groups.

These pages should emphasize organizer outcomes, quoting requirements, transport, documentation, safety, payment coordination, and one accountable contact.

Do not claim capabilities or documentation that Ashford has not confirmed.

### About and Trust

Purpose:

- Explain Holiday Kenya Safaris and its relationship to Ashford Tours & Travel.
- Show operational experience and people.
- Provide verifiable contacts and trust evidence.

Potential modules, only when verified:

- Operator story.
- Team and guide profiles.
- Physical address and contacts.
- Memberships and licenses.
- Vehicle or fleet information.
- Payment and booking process.
- Real testimonials.

### Contact

- WhatsApp.
- Phone.
- Email.
- Address and map when confirmed.
- Business hours and response expectation when confirmed.
- General inquiry form only if there is a real handling process.

## Reusable Components

- Utility bar.
- Wayfinder desktop header and mobile navigation drawer.
- Homepage hero.
- Title and breadcrumb band.
- Tour card.
- Destination card.
- Catalogue filters and mobile filter drawer.
- Tour gallery mosaic and lightbox.
- Duration and facts strip.
- Accessible Tour tabs and mobile disclosures.
- Single-price quote panel with standard quote context.
- Itinerary timeline.
- Inclusion/exclusion lists.
- Vehicle and accommodation evidence drawn from the public gallery and practical details, without duplicate media fields.
- Trust module.
- Testimonial module only after real public testimonial content and a rendered component are implemented.
- Intake-to-WhatsApp dialog or sheet.
- Sticky mobile WhatsApp action that does not obscure content.
- Campaign attribution handler.
- Related-Tour query using shared destinations and a catalogue fallback.
- Global footer.

## Visual Requirements

- Follow `DESIGN.md`, `BRAND-WAYFINDER.md`, and `UI-REFERENCE-ATTIC-TRAVEL.md`.
- Use the catalogue reference's structure and density, not its brand styling.
- Use destination imagery deliberately selected by an authorized editor as the primary visual proof.
- Keep the main catalogue surface white, with Pale Mist used for quiet section alternation.
- Use Wayfinder colors and typography; do not copy Attic Travel's logo, fonts, maroon palette, or abstract title gradient.
- Cards use stable image ratios, restrained radii, and consistent content height.
- No cards nested inside cards.
- Do not let long Tour names, KSh prices, or CTA labels overflow.
- Sticky controls must stop before the footer and never overlap content.

## Mobile Requirements

- Design and test mobile first because Facebook traffic and WhatsApp conversion are mobile-heavy.
- The mobile drawer, gallery, disclosures, intake sheet, and sticky quote action must be tested at 360 and 390px.
- Mobile Tour pages expose the same factual information as desktop.
- The initial Tour facts, price context, and quote action must be discoverable without navigating a long permanent form.
- The intake form must support the on-screen keyboard and retain entered values.
- WhatsApp launch must not occur before validation, consent, inquiry save, and message review.
- Sticky actions respect safe areas and leave sufficient bottom padding.

## SEO Requirements

- Human-readable URLs.
- One unique H1 per page.
- Unique titles, descriptions, and canonical behavior.
- Server-rendered canonical Tour facts even when tabs enhance presentation.
- Tour and destination internal linking.
- Breadcrumbs where useful.
- Image alt text that describes the content rather than stuffing keywords.
- Structured data only where it accurately represents the offer and organization.
- No fake review ratings.
- Campaign indexing controlled according to duplication and campaign longevity.

## Performance Requirements

- Responsive images and modern formats.
- Set gallery and card dimensions to prevent layout shift.
- Lazy-load below-the-fold media, but not the primary hero or first gallery image.
- Avoid autoplay video and large multi-slide hero payloads.
- Keep JavaScript limited to navigation, gallery, tabs/disclosures, filters, quote flow, analytics, and necessary blocks.
- Use caching, compression, CDN, backups, and staging at the hosting layer.

## Accessibility Requirements

- WCAG 2.2 AA contrast and interaction expectations.
- Keyboard-operable dropdowns, drawer navigation, filters, gallery, tabs, disclosures, forms, and dialogs.
- Clear focus indicators.
- Labels and errors tied to fields.
- Reduced-motion support.
- No information communicated only through color.
- Correct heading order and landmark structure.
