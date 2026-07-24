# Catalogue UI and UX Reference

## Status

This document records the approved layout and interaction system for the Holiday Kenya Safaris website, consolidating review decisions made on 2026-07-15 and the catalogue expansion approved on 2026-07-24. Holiday Kenya Safaris must use the Wayfinder identity, local-market focus, Kenya and international Tour scopes, approved content model, KSh pricing rules, and qualified WhatsApp funnel.

## Core Decision

Use this catalogue grammar for the main website:

- a two-level desktop header;
- product-led dropdown navigation;
- a full mobile navigation drawer;
- an image-led homepage with prominent package discovery;
- three-column catalogue grids;
- a canonical Tour page with a title band, image gallery, tabbed content, two-column desktop layout, related Tours, and a persistent conversion panel.

Do not use a permanent long booking form. The right-hand conversion area must be a Holiday Kenya Safaris quote panel whose primary command is **Request quote on WhatsApp**. That command opens the approved intake form, saves a private recovery record only after consent, lets the visitor review the generated message, and then launches WhatsApp under the visitor's control.

## Two Page Modes

### Catalogue mode

Use for:

- Homepage.
- Tour catalogue and taxonomy archives.
- Destination pages.
- Canonical Tour pages.
- Group Travel, About, and Contact pages.

Catalogue mode is light, browseable, photography-led, and internally connected. It should make Holiday Kenya Safaris feel like an established local travel company with a useful range of real products.

### Campaign mode

Use for focused Facebook and other paid-ad landing pages.

Campaign mode may retain the immersive, emotionally focused structure of the existing Maasai Mara prototype. It may use reduced navigation, a pressure-led headline, supporting copy, a featured hero image, template-controlled repeated quote prompts, and one optional Campaign-specific starting price. It must still inherit the factual itinerary, inclusions, exclusions, and logistics from the linked canonical Tour.

The existing Maasai Mara prototype is a Campaign reference, not the default canonical Tour template.

## Global Header and Navigation

### Desktop utility bar

Use a slim Midnight Navy utility bar above the primary header. It may contain only confirmed information:

- Nairobi or confirmed operating location.
- Discreet `Operated by Ashford Tours & Travel` disclosure.
- Confirmed phone and email.
- Confirmed social links.

Keep it compact. It is a trust and contact surface, not a second full navigation menu.

The WhatsApp icon and number are one direct contact link to the confirmed HKS number. Prefill a concise general reach-out message, adding the current title and URL on Tour and Campaign pages. This lightweight utility contact is distinct from page-level quote actions, which continue to open the intake, consent, recovery, and message-review flow.

### Desktop primary header

Use a white header with the production `holiday-kenya-safaris-logo.svg` lockup and product-led navigation. Keep the compact WhatsApp contact in the utility bar; do not repeat it as a large primary-header button.

Approved primary structure:

1. Home.
2. Safaris.
3. Coast & Stays.
4. Destinations.
5. Group Travel.
6. About.
7. Contact.

The mobile drawer and page-level conversion surfaces retain the **Request quote on WhatsApp** action.

`Safaris` may expose only populated, approved routes such as:

- Road Safaris.
- Day Excursions.
- Weekend and Short Breaks.
- Flying Safaris, only when verified local products exist.
- Trekking and Special Interest, only when useful.

`Coast & Stays` may expose:

- Coast Trips.
- Staycations.
- Bush and Beach, only where a real package supports it.

`Destinations` should contain a concise set of populated priority terms, followed by a route to view all destinations. Do not place every taxonomy term in the header.

Expose populated Kenya Tours and International Tours routes. Do not include visa services, standalone transfers, or unrelated service categories.

### Mobile navigation

Use a compact white header with the Wayfinder mark and a familiar menu icon. Opening it reveals a full-height drawer with:

- The same `holiday-kenya-safaris-logo.svg` lockup used by the primary header, plus an accessible Close button.
- Search only when the catalogue is large enough for search to be useful.
- Accordion navigation for Safaris, Coast & Stays, and Destinations.
- Direct Group Travel, About, and Contact links.
- Confirmed contact and social links.
- A clear Request quote on WhatsApp action.

The drawer must trap focus while open, close with Escape, return focus to the menu button, and prevent background scrolling. The menu and close buttons require visible labels for assistive technology.

## Homepage Template

The homepage should create a broad, image-led catalogue impression without an excessive carousel or generic service clutter.

Default sequence:

1. Utility bar and primary header.
2. Image-led hero with one literal H1, supporting copy, and a package or catalogue action.
3. Featured Tours grid.
4. Browse by destination.
5. Browse by trip type or occasion.
6. Why Holiday Kenya Safaris and the Ashford operator relationship.
7. How the WhatsApp quote process works.
8. Group Travel route.
9. Verified proof, testimonials, affiliations, or people when available.
10. Final WhatsApp prompt and full footer.

Hero rules:

- Prefer one decisive destination image.
- A curated slider may contain no more than three verified slides.
- Each slide needs its own meaningful destination or package action.
- Do not auto-rotate rapidly, use 21 slides, or keep identical generic copy over unrelated images.
- The next section should remain visually discoverable on common desktop and mobile viewports.

Featured Tour rules:

- Use three columns on desktop, two when appropriate on tablet, and one on mobile.
- Begin with no more than six priority Tours on the homepage.
- Preserve a stable image ratio and card height.
- Show title, destination, duration, route or departure context, optional `From KSh X per person`, and a clear View trip action.
- Omit the price cleanly when the Tour has no positive starting amount.

## Catalogue and Taxonomy Templates

Use a compact title and breadcrumb band followed by:

1. A literal H1 and short useful introduction.
2. Filter and sort controls when the inventory justifies them.
3. A responsive Tour grid.
4. Clear empty, loading, and no-results states.
5. Pagination or a deliberate load-more pattern.
6. A final quote or discovery prompt.

Desktop filters may be horizontal or use a restrained sidebar. Mobile filters must use an accessible drawer or dialog. Do not expose empty filters or meaningless metadata such as a default `1 person` value.

## Canonical Tour Template

The canonical Tour page should follow the approved information architecture and visual rhythm while keeping HKS conversion and content requirements intact.

### Page order

1. Global utility bar and header.
2. Compact title and breadcrumb band containing the only page H1.
3. Three-image gallery mosaic with a View gallery control.
4. Destination or route line.
5. Two-column Tour workspace.
6. Related Tours.
7. Final WhatsApp quote prompt.
8. Global footer.

### Gallery

Desktop:

- One dominant landscape image occupying approximately two-thirds of the width.
- Two supporting images stacked in the remaining third.
- A clear View gallery control over or beside the final image.

Mobile:

- One dominant image with a stable aspect ratio.
- A small supporting preview or horizontal gallery affordance.
- No tiny image collage that makes the destination impossible to inspect.

The gallery must use media deliberately assigned by an authorized editor to the published Tour, in editorial order. It should open an accessible lightbox or gallery dialog with keyboard navigation, image count, close control, and useful alt text.

### Desktop Tour workspace

Use an approximately 68/32 main-content and quote-panel split.

Main content begins with:

- Tour title only when a shorter in-content label is useful; do not repeat the full H1 without purpose.
- Duration badge.
- Compact facts for nights, departure, route, travel style, accommodation basis, and transport.
- Accessible tabs for Overview, Itinerary, Included/Excluded, and Important Information.

The right column contains a sticky quote panel, not a long booking form.

### Quote panel

The panel should contain:

- A clear tailored-quote heading.
- The Tour's `From KSh X per person` starting price when populated, plus a concise reminder that the final quote depends on dates and group details.
- A short note explaining that the visitor shares dates and group details, reviews the message, and chooses whether to send it in WhatsApp.
- A compact availability statement that does not invent availability.
- The primary **Request quote on WhatsApp** button.
- A short explanation that the visitor will answer a few questions and review a prepared WhatsApp message.
- Confirmed response or operator details only when available.

Do not display first name, last name, email, country, date selectors, adult count, child count, notes, and a Submit Booking form permanently in the sidebar.

The WhatsApp button opens the shared HKS intake dialog or mobile sheet. The required fields remain name, phone, package, preferred date or month, and number of travelers. Package-specific optional questions appear only when they improve quote accuracy.

### Tour tabs and mobile disclosures

Desktop tabs:

- Overview.
- Itinerary.
- Included/Excluded.
- Important Information.

The content must remain present in the server-rendered document and crawlable. JavaScript enhances presentation rather than becoming the only source of essential Tour information.

On mobile, render the same sections as accessible stacked disclosures. Open Overview by default. Keep section labels and current state obvious. Do not hide practical facts behind several controls.

### Itinerary

Use the approved timeline interaction:

- Day number and day title.
- Origin and destination.
- Description.
- Activities, meals, and accommodation.
- Individual expand and collapse controls.
- Expand all and Collapse all controls when there are more than three days.

The first day may be open by default. The pattern must remain readable when all days are expanded and must not jump the sticky quote panel unexpectedly.

### Related Tours

Show up to three useful related Tours based on destination, duration, Tour type, or a curated editor override. Cards use the same visual system as the catalogue. Do not add a wishlist unless a real saved-trip workflow is approved.

## Quote Interaction

The visible command is **Request quote on WhatsApp**.

Flow:

`Quote button -> intake dialog or sheet -> validation and consent -> private recovery record -> message review -> visitor launches WhatsApp -> visitor sends message`

Desktop quote entry points:

- Sticky Tour quote panel.
- Final Tour prompt.
- Header action where appropriate.

Mobile quote entry points:

- In-flow quote panel after the initial facts.
- Sticky bottom action respecting safe areas.
- Final Tour prompt.

Never use `Book now`, `Submit Booking`, or language implying confirmed availability or payment when the action only requests a quote.

The separate global **Chat on WhatsApp** control uses one fixed general message and opens the official number directly. It does not open the intake, save an inquiry, or replace any **Request quote on WhatsApp** entry point. Keep it at the bottom right, above any mobile quote bar and outside form controls or footer content.

### Group Travel route

The direct Group Travel navigation route resolves to `/group-travel/`, not a homepage anchor. Its inline planner is the shared quote conversion in a Group Travel presentation mode:

`Destination -> matching published Tour -> dates/month -> traveler count -> contact and consent -> private recovery record -> message review -> visitor launches WhatsApp`

Only published Tours with assigned Destination terms appear. Changing Destination filters the Tour choices without duplicating Tour data. Keep the selected Tour as the canonical package context used by validation, storage, analytics, and the generated WhatsApp message.

## Visual Translation to Wayfinder

Implement the documented structure, density, and interaction within the Wayfinder brand.

- Use Midnight Navy for the utility bar, titles, and strong trust surfaces.
- Use white as the main catalogue surface.
- Use Pale Mist for section alternation and quiet supporting areas.
- Use Lake Teal for links, selected states, route cues, and secondary controls.
- Use Saffron sparingly for non-text directional accents and duration details.
- Reserve WhatsApp Green for WhatsApp conversion controls.
- Keep the Wayfinder typography and logo system; do not introduce mismatched fonts or logo treatment.
- Use 8-12px radii for Tour cards, galleries, and quote panels.
- Use borders or restrained short shadows, not both as decoration.
- Do not use an abstract blue-to-maroon title gradient. Use a solid Wayfinder band or a sufficiently legible approved destination image.

## Reference Behaviors to Avoid

Do not reproduce:

- The 21-slide homepage hero.
- Generic copy repeated over unrelated hero images.
- Tour cards without duration, route or departure context, or a useful action.
- Duplicate full page titles.
- Incorrect heading hierarchy.
- Long permanent booking forms.
- Date controls containing irrelevant historical years.
- `Submit Booking` when no booking occurs.
- Floating controls that overlap content, reCAPTCHA, or form fields.
- Empty links, incorrect phone or map targets, or unverified 24/7 claims.
- Large partner-logo carousels without verified relevance.
- Wishlist controls without a real saved-trip product.

## Implementation Boundary

Implement this experience in the approved custom block theme and `hks-core` plugin. Do not install Elementor or depend on WP Travel Engine merely because the reference site uses template-like travel components.

Build reusable theme parts and blocks for:

- Utility bar and main header.
- Desktop dropdown and mobile drawer navigation.
- Homepage hero.
- Tour and destination cards.
- Catalogue filters.
- Title and breadcrumb band.
- Tour gallery mosaic and lightbox.
- Tour tabs and mobile disclosures.
- Itinerary timeline.
- Sticky quote panel.
- Intake-to-WhatsApp dialog or sheet.
- Related Tours.

## Responsive Acceptance Criteria

- Test at 360, 390, 768, 1024, 1280, and 1440px widths.
- The navigation must remain usable with long labels and keyboard input.
- The gallery must not distort, collapse to zero height, or shift after load.
- The title, facts, starting price, and quote action must remain discoverable without overlap. A long KSh value or optional Campaign override must not shift or crowd those controls.
- Sticky elements must stop before the footer and never cover page content.
- Mobile disclosures must expose every canonical Tour fact available on desktop.
- The intake form must retain entered values when the keyboard opens, validation fails, or the dialog is temporarily dismissed.
- All essential interactions must work with a keyboard and reduced motion.
