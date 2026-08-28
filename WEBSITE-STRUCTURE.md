# Website Structure and Templates

## Structural Reference

The standard website uses the catalogue and canonical Tour structure documented in `UI-REFERENCE-CATALOGUE.md`. Holiday Kenya Safaris keeps the Wayfinder identity, a local-market focus with Kenya and international Tours, verified Ashford facts, editable KSh starting prices, and the qualified WhatsApp/email quote flow.

The existing Maasai Mara prototype is retained as a Campaign landing-page reference. It is not the default canonical Tour template.

## Information Architecture

### Desktop utility bar

Use a compact trust and contact strip containing only confirmed information:

- Operating location.
- `Operated by Ashford Tours & Travel` disclosure.
- Phone and email.
- Social links.

The homepage intentionally omits this strip. Its logo-and-menu header overlays the Featured Tour hero on an almost-clear, lightly blurred Pale Mist surface so the photograph remains continuous. It stays fixed at the top and changes to solid white after the visitor begins scrolling. Internal pages retain the utility bar and white primary header.

### Primary navigation

- Home.
- Safaris.
- Coast & Stays.
- Destinations.
- Travel Guides.
- Group Travel.
- About.
- Contact.

The utility bar carries a compact direct WhatsApp contact with a prefilled, page-aware reach-out message. Do not repeat it as a large button in the desktop primary header. Page-level quote actions retain the structured intake and recovery flow.

Travel Guides sits between Destinations and Group Travel and resolves to `/travel-guides/`. Public-facing navigation and page copy spell **Holiday Kenya Safaris** in full.

The desktop header and mobile drawer use the same production `holiday-kenya-safaris-logo.svg` lockup. A separate global floating **Chat on WhatsApp** control opens the official number with a concise general message, adding the current Tour title and canonical link on Tour pages; it does not create an inquiry record or replace the structured page-level quote actions.

Suggested dropdown structure:

| Menu | Approved routes when populated |
|---|---|
| Kenya Tours | Kenya destinations, Road Safaris, Day Excursions, Weekend and Short Breaks, Flying Safaris, Trekking and Special Interest |
| International Tours | Populated international destinations and useful trip types |
| Coast & Stays | Coast Trips, Staycations, Bush and Beach |
| Destinations | Priority populated destinations followed by View all destinations |

Do not expose empty categories. Do not include visa services, standalone transfers, or unrelated service pages.

The client manages header and footer links under **Appearance > Site Menus**. Assign one menu to `Primary header and mobile menu`; its top-level items and one child level render consistently as desktop links/dropdowns and mobile links/accordions. Assign a separate menu to `Footer menu`. Unassigned locations retain the documented theme fallback so navigation cannot disappear during editing or deployment.

### Mobile navigation

Use a full-height drawer with:

- Wayfinder mark and accessible Close control.
- Search only when the catalogue is large enough to justify it.
- Accordion groups for Safaris, Coast & Stays, and Destinations.
- Direct Travel Guides, Group Travel, About, and Contact links.
- Confirmed contacts and social links.
- Request a quote action.

The drawer must trap focus, close with Escape, return focus to the menu button, and prevent background scrolling.

## Sitemap

### Home

Purpose:

- Establish a broad, credible local-travel catalogue.
- Help visitors discover relevant verified Tours quickly.
- Make the Ashford operator relationship and quote process clear.
- Move visitors to a Tour, Campaign, destination, group-travel route, or qualified quote inquiry.

Default order:

1. Homepage logo-and-menu overlay.
2. Featured Tour hero whose active Tour title is the single H1.
3. Browse by destination.
4. Featured verified Tours.
5. Browse by trip type or occasion.
6. Why Holiday Kenya Safaris and the Ashford operator relationship.
7. How the quote process works.
8. Group Travel route.
9. Verified proof when available.
10. Final quote prompt and footer.

Hero rules:

- Query up to five published Tours explicitly marked Featured and require a featured image at least 1200 × 675 pixels. Hero eligibility does not depend on native alt text; its repeated images are decorative because the adjacent text and controls provide equivalent context.
- Use the active Tour's destination or scope, literal title, full-bleed image, and `Click here to book tour` canonical link.
- Use portrait preview cards with a five-second cycle, synchronized slim Saffron progress line, minimal previous/next controls, card selection, swipe or drag, keyboard commands, and a compact pause/resume control.
- Open the selected card into the stage through a clipped-image reveal, retaining the previous scene and title until the content swap near the end. Pause while hidden, outside the viewport, or under direct interaction. Reduced motion hides progress and disables autoplay and card-to-stage travel.
- One eligible Tour renders as a static hero; zero eligible Tours render a generic catalogue fallback.
- Keep Browse by destination visually discoverable after the hero.

Featured Tour rules:

- Show up to six priority Tours initially.
- Use a three-column desktop grid, responsive tablet layout, and one-column mobile flow.
- Cards must show image, title, destination, duration, route or departure context, an optional `From KSh X per person` value, and a clear View trip action.

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
6. Final discovery or quote prompt.

Initial filters may include:

- Destination.
- Tour scope.
- Tour type.
- Travel style.
- Occasion.

Tour cards show:

- Editor-selected destination image.
- Tour title.
- Destination.
- Tour scope when it helps distinguish the catalogue family.
- Duration.
- Route or departure point.
- One or two useful travel-style labels.
- `From KSh X per person` when a positive Tour price exists.
- View trip action.

Do not display meaningless default metadata such as `1 person`. On the Tour catalogue, filters are arranged vertically in a sticky left column from 1024px upward. Below 1024px, a sticky `Filters` button opens the same controls in an accessible drawer or dialog.

### Destination Pages

Purpose:

- Answer destination-level questions.
- Provide SEO entry points.
- Group relevant Tours.
- Explain suitable durations, travel styles, access context, and local considerations.

Use the catalogue visual system: compact title band, strong destination photography, practical introduction, relevant Tour grid, verified guidance, related Travel Guides, and final quote prompt.

Destination is one public taxonomy shared by Tours and native Posts. Its main archive query and pagination remain Tour-only. Render relevant Travel Guides as a separate secondary query below the Tour catalogue, without allowing Posts to alter Tour counts, Tour navigation, or Tour pagination. A Destination used only by Posts must not appear in Tour browse navigation.

Never invent weather, park-fee, or access information. Date and source any changeable facts.

### Travel Guides Hub

Canonical route: `/travel-guides/`.

Purpose:

- Help Kenyan travellers answer practical planning questions and move confidently toward the most relevant trip.
- Give Diani, Dubai, Maasai Mara, and later destinations useful search entry points without duplicating Destination archives.
- Offer two public editorial formats while keeping the Tour catalogue authoritative for package facts and conversion.

Default order:

1. Compact internal header and breadcrumb band.
2. One literal H1 and a concise planning promise.
3. Destination and Article Topic filters using server-rendered links or GET controls.
4. A responsive article grid with intentional image and no-image card states.
5. Pagination and a useful empty state.

Initial Article Topics are Destination Guides, Planning & FAQs, Travel Inspiration, Comparisons, and Holiday Kenya Safaris News. Article Topic archives live at `/travel-guides/topics/<topic>/`. Article cards may show the title, excerpt, modified date, and public Destination/Topic context, but never an author name.

### Standard Guide

Canonical route: `/travel-guides/<postname>/`.

The Standard Guide is reading-first: breadcrumbs, public Destination/Topic context, one H1, concise excerpt, optional featured image or deliberate text-led opening, and a highly legible article column. It shows no author name. When an editor assigns a Primary Tour, an early **View this trip** action links to that canonical Tour. It has no persistent quote action.

End with up to three related Posts, never related Tours. Resolve them in this order: editor-selected Posts, same-Destination Posts, then same-Article-Topic Posts. Exclude the current Post and duplicates.

### Advertorial

Canonical route: `/travel-guides/<postname>/` using the Advertorial article format.

The Advertorial is the conversion-focused editorial format. Publishing requires one published Primary Tour. Its opening connects the visitor's planning situation to concrete, verified itinerary and package facts without fabricated urgency or proof. Place an early **Request a quote** action, repeat it only at meaningful decision points, and send every instance into the existing intake, consent, private recovery, message-review, and visitor-controlled WhatsApp/email handoff.

Desktop uses a 680–720px reading column with an approximately 320px sticky Primary Tour panel. Mobile reveals a sticky quote action only after the opening CTA leaves view; it must respect safe areas and stack below the global Chat on WhatsApp control without obscuring content. The featured image remains optional. No permanent form or direct quote-to-WhatsApp jump is allowed.

### Canonical Tour Detail Page

Implement the approved canonical Tour structure, with a Holiday Kenya Safaris quote panel instead of a permanent booking form.

Default order:

1. Utility bar and primary header.
2. Compact title and breadcrumb band containing the only page H1.
3. Desktop vertical thumbnail rail, active gallery image and sticky quote panel.
4. Destination or route line beneath the active media.
5. Tour facts and information workspace beneath the gallery.
6. Related Tours.
7. Final quote prompt.
8. Footer.

#### Gallery

Desktop uses the Tour's assigned images as a vertical thumbnail rail beside one dominant active image. The rail is capped at six visible thumbnails; when more images exist, the sixth shows a dark `+N more` overlay that opens the keyboard-operable lightbox at that image, while all later images remain available through the active-image controls and lightbox. Selecting a regular thumbnail or the over-image previous/next chevrons updates the active image; activating the main image or View gallery control opens the lightbox at that image. Multi-image galleries advance every five seconds, pausing for hover, focus, hidden/off-screen state and reduced-motion preferences. Mobile keeps the full horizontally scrollable thumbnail strip beneath one dominant image with the same chevrons. Only media assigned by an authorized editor to the published Tour may appear.

#### Tour workspace

Desktop reads as three columns: the thumbnail rail, flexible active-media/main-content column and narrower sticky quote panel. Tour facts and information continue below the gallery while the quote panel remains persistent.

Main content includes:

- Duration badge.
- Fast facts for nights, departure, route, travel style, accommodation basis, and transport.
- Overview tab.
- Itinerary tab.
- Included/Excluded tab.
- Important Information tab.

The right column contains a sticky quote panel with:

- A clear tailored-quote heading without a Tour price or request-rate fallback.
- A short note explaining that the visitor shares dates and group details, reviews the message, and chooses WhatsApp or email.
- Primary **Request a quote** button.
- Short explanation of the intake and message-review step.
- Confirmed operator or response details when available.

The quote panel may show the Tour's positive `From KSh X per person` starting price, followed by a short reminder that the final quote depends on dates and group details. It must not contain a permanent long booking form. Clicking the button opens the shared intake dialog or mobile sheet.

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

`Request a quote -> intake form -> validation and consent -> private recovery record -> message review -> visitor chooses WhatsApp or email -> visitor sends message in that app`

Required intake fields:

- Name.
- Phone.
- Package.
- Preferred date or month.
- Number of travelers.

Optional fields are package-specific and appear only when needed to quote accurately.

Preserve from the existing Maasai Mara prototype:

- Clear practical detail.
- Clear quote context.
- Intake before either handoff.
- Mobile CTA clarity.

Do not use its dark campaign hero as the canonical Tour opening.

### Campaign Landing Page

Purpose:

- Maintain message match with a Facebook ad.
- Convert one audience, occasion, desire, problem, or objection efficiently.
- Reuse canonical Tour facts.

Campaign pages use the same title band, gallery, facts, sticky quote panel, tabs/disclosures, itinerary, important information, related Tours, responsive behavior, and final quote prompt as canonical Tour pages.

Features:

- Linked Tour required.
- Full, reduced, or minimal navigation.
- Campaign-specific headline, supporting copy, first gallery image, price override, and navigation treatment.
- Canonical itinerary, inclusions, exclusions, and logistics inherited from the linked Tour.
- One optional Campaign-specific `From KSh... per person` override. When blank, the linked Tour starting price may display.
- Attribution retained through inquiry save and WhatsApp/email launch.
- Start and end dates exist on Campaigns only; Campaign indexing defaults are template-controlled.

Campaign pages are not constrained to Mercy. Add variants as evidence supports them.

### Group Travel

The primary navigation, mobile drawer, homepage Group Travel section, and footer all link to the canonical `/group-travel/` Page.

The opening experience contains:

1. Standard Page title and breadcrumb band with the only H1.
2. Image-led introduction using media assigned to published Tours.
3. An inline shared inquiry planner.
4. A concise explanation of storage, message review, and visitor-controlled WhatsApp/email handoff.
5. Existing editable Page content for audience-specific supporting information.

The planner lets a visitor choose a populated Destination, choose a matching published Tour, enter a proposed date or month and traveler count, provide contact details and consent, then review one group quote request and choose WhatsApp or email. It reuses the existing private inquiry record and review flow. It must not build a parallel form or store a client-maintained duplicate of Destination or Tour facts.

Supporting content may provide routes for:

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
- Tour thumbnail rail, active image and lightbox.
- Duration and facts strip.
- Accessible Tour tabs and mobile disclosures.
- Canonical Tour quote panel with optional starting price and standard quote context.
- Optional Campaign price panel.
- Itinerary timeline.
- Inclusion/exclusion lists.
- Vehicle and accommodation evidence drawn from the public gallery and practical details, without duplicate media fields.
- Trust module.
- Testimonial module only after real public testimonial content and a rendered component are implemented.
- Intake-to-review-to-WhatsApp-or-email dialog or sheet.
- Sticky mobile quote action that does not obscure content.
- Global bottom-right Chat on WhatsApp contact that uses a fixed message, respects safe areas, and sits above Tour or Campaign mobile quote actions.
- Campaign attribution handler.
- Related-Tour query using shared destinations and a catalogue fallback.
- Travel Guide card with intentional featured-image and no-image states.
- Standard Guide reading composition and optional Primary Tour link.
- Advertorial Primary Tour quote panel and mobile sticky quote action using the shared intake.
- Related-guide resolver: manual Posts, then Destination, then Article Topic, maximum three.
- Global footer.

## Visual Requirements

- Follow `DESIGN.md`, `BRAND-WAYFINDER.md`, and `UI-REFERENCE-CATALOGUE.md`.
- Use the catalogue reference's structure and density, not its brand styling.
- Use destination imagery deliberately selected by an authorized editor as the primary visual proof.
- Keep the main catalogue surface white, with Pale Mist used for quiet section alternation.
- Use Wayfinder colors and typography; avoid mismatched logos, fonts, maroon palettes, and abstract title gradients.
- Cards use stable image ratios, restrained radii, and consistent content height.
- No cards nested inside cards.
- Do not let long Tour names, optional Campaign prices, or CTA labels overflow.
- Sticky controls must stop before the footer and never overlap content.

## Mobile Requirements

- Design and test mobile first because Facebook traffic and messaging conversion are mobile-heavy.
- The mobile drawer, gallery, disclosures, intake sheet, and sticky quote action must be tested at 360 and 390px.
- Mobile Tour pages expose the same factual information as desktop.
- The initial Tour facts and quote action must be discoverable without navigating a long permanent form.
- The intake form must support the on-screen keyboard and retain entered values.
- WhatsApp or email launch must not occur before validation, consent, inquiry save, and message review.
- Sticky actions respect safe areas and leave sufficient bottom padding.
- Standard Guides have no persistent mobile quote bar. Advertorial bars appear only after the opening quote action leaves view and must not cover the global Chat on WhatsApp control.

## SEO Requirements

- Human-readable URLs.
- One unique H1 per page.
- Unique titles, descriptions, and canonical behavior.
- Server-rendered canonical Tour facts even when tabs enhance presentation.
- Tour and destination internal linking.
- Travel Guides hub, native Post, Article Topic, and shared Destination sitemap entries without a second destination taxonomy.
- Primary Tour links from relevant guides and contextual guide links from Destination pages.
- Breadcrumbs where useful.
- Image alt text that describes the content rather than stuffing keywords.
- Structured data only where it accurately represents the offer and organization.
- No fake review ratings.
- Campaign indexing controlled according to duplication and campaign longevity.

## Performance Requirements

- Responsive images and modern formats.
- Set gallery and card dimensions to prevent layout shift.
- Lazy-load below-the-fold media, but not the primary hero or first gallery image.
- Avoid autoplay video and preload only the active and next Featured Tour images; remaining queue media loads responsively and lazily.
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
