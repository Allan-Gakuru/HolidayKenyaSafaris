# Catalogue UI and UX Reference

## Status

This document records the approved layout and interaction system for the Holiday Kenya Safaris website, consolidating review decisions made on 2026-07-15 and the catalogue expansion approved on 2026-07-24. Holiday Kenya Safaris must use the Wayfinder identity, local-market focus, Kenya and international Tour scopes, approved content model, KSh pricing rules, and qualified quote funnel.

## Core Decision

Use this catalogue grammar for the main website:

- a two-level desktop header on internal pages and a homepage-only translucent logo-and-menu overlay;
- product-led dropdown navigation;
- a full mobile navigation drawer;
- an image-led homepage with prominent package discovery;
- three-column catalogue grids;
- a canonical Tour page with a title band, thumbnail-led active gallery, tabbed content, a three-column desktop composition, related Tours, and a persistent conversion panel.

Do not use a permanent long booking form. The right-hand conversion area must be a Holiday Kenya Safaris quote panel whose primary command is **Request a quote**. That command opens the approved intake form, saves a private recovery record only after consent, lets the visitor review the generated message, and then lets the visitor choose WhatsApp or an email addressed to `info@holidaykenyasafaris.ke`.

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

Campaign mode uses the canonical Tour title band, gallery, facts, sticky quote panel, tabs/disclosures, itinerary, related-Tour system, responsive behavior, and final quote prompt. It may use full, reduced, or minimal navigation plus a pressure-led headline, supporting copy, a featured image placed first in the gallery, and one optional Campaign-specific starting price. It inherits the remaining media and all factual itinerary, inclusions, exclusions, and logistics from the linked canonical Tour.

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

On the homepage only, omit the utility bar and place the same logo and primary navigation in an almost-clear, lightly blurred Pale Mist surface over the Featured Tour hero. The photograph should remain visually continuous through the header. Keep this header fixed at the top and transition its surface to solid white once the page begins scrolling. Preserve an opaque accessible treatment for reduced-transparency or increased-contrast preferences, solid-white dropdown panels, and the normal opaque mobile drawer. All internal pages keep the two-level header.

Approved primary structure:

1. Home.
2. Safaris.
3. Coast & Stays.
4. Destinations.
5. Group Travel.
6. About.
7. Contact.

Editors manage this hierarchy in WordPress through the registered `Primary header and mobile menu` location. The same assigned menu must render in both the desktop header and mobile drawer: top-level items with children become desktop dropdowns and mobile accordions. Keep the managed hierarchy to two levels. The separate `Footer menu` location controls footer links. If either location is unassigned, the theme renders its existing safe fallback rather than an unrelated WordPress page list.

The mobile drawer and page-level conversion surfaces retain the **Request a quote** action.

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
- A clear Request a quote action.

The drawer must trap focus while open, close with Escape, return focus to the menu button, and prevent background scrolling. The menu and close buttons require visible labels for assistive technology.

## Homepage Template

The homepage should create a broad, image-led catalogue impression without an excessive carousel or generic service clutter.

Default sequence:

1. Homepage logo-and-menu overlay.
2. Featured Tour hero whose active Tour title is the single H1.
3. Browse by destination.
4. Featured Tours grid.
5. Browse by trip type or occasion.
6. Why Holiday Kenya Safaris and the Ashford operator relationship.
7. How the quote request process works.
8. Group Travel route.
9. Verified proof, testimonials, affiliations, or people when available.
10. Final quote prompt and full footer.

Hero rules:

- Use up to five published Tours explicitly marked Featured and carrying featured images at least 1200 × 675 pixels. Do not use native alt text as an eligibility gate; the repeated hero and preview images are decorative beside equivalent textual labels and controls.
- The active Tour supplies the full-bleed image, destination or scope label, single H1, and `Click here to book tour` link to its canonical Tour.
- Use portrait queue cards and a clipped card-to-stage image reveal as the homepage's signature motion. Keep the previous scene and title visible during the opening phase, then swap the active content near the end without stretching the image.
- Cycle every five seconds on all normal viewports and show a synchronized slim Saffron progress line. Provide minimal previous/next controls, direct card selection, touch drag or swipe, keyboard commands, and an accessible pause/resume control.
- Pause while the hero is outside the viewport, the document is hidden, or the visitor is interacting. Hide progress and disable autoplay and large movement under reduced motion.
- One eligible Tour renders statically without controls. No eligible Tours render a compact catalogue fallback.
- The next Browse by destination section should remain discoverable on common desktop and mobile viewports.

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

The Tour catalogue uses a restrained, sticky vertical filter sidebar from 1024px upward. Below that breakpoint, a sticky `Filters` control opens the same GET-based fields in a native accessible dialog drawer with Escape, backdrop-close, focus return, and scroll lock. Do not expose empty filters or meaningless metadata such as a default `1 person` value.

## Canonical Tour Template

The canonical Tour page should follow the approved information architecture and visual rhythm while keeping HKS conversion and content requirements intact.

### Page order

1. Global utility bar and header.
2. Compact title and breadcrumb band containing the only page H1.
3. Desktop thumbnail rail, active image and sticky quote panel.
4. Destination or route line beneath the active media.
5. Tour facts and information workspace beneath the gallery.
6. Related Tours.
7. Final quote prompt.
8. Global footer.

### Gallery

Desktop:

- A vertical rail showing at most six of the Tour's assigned gallery images in editorial order. When additional images exist, the sixth thumbnail carries a dark `+N more` overlay and opens the full gallery at that image; later thumbnails stay available through the active-image controls and lightbox without extending the desktop row.
- One dominant active landscape image beside the thumbnail rail.
- A clear View gallery control over the active image.
- Selecting a thumbnail updates the active image without opening the lightbox; activating the main image or View gallery control opens the lightbox at that image.
- Previous and next chevrons sit inside the active image. Multi-image galleries advance every five seconds, pause while hovered or focused and while off-screen or hidden, and do not autoplay when reduced motion is requested.

Mobile:

- One dominant image with a stable aspect ratio.
- A horizontally scrollable thumbnail strip below the active image.
- No tiny image collage that makes the destination impossible to inspect.

The gallery must use media deliberately assigned by an authorized editor to the published Tour, in editorial order. It should open an accessible lightbox or gallery dialog with keyboard navigation, image count, close control, and useful alt text.

### Desktop Tour workspace

The desktop composition reads as three columns: thumbnail rail, active media/main content, and sticky quote panel. The gallery and main Tour information share the flexible content column; the quote panel uses the narrower right column and remains sticky while the visitor reads the Tour information.

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
- A short note explaining that the visitor shares dates and group details, reviews the message, and chooses WhatsApp or email.
- A compact availability statement that does not invent availability.
- The primary **Request a quote** button.
- A short explanation that the visitor will answer a few questions, review one prepared message, and choose how to send it.
- Confirmed response or operator details only when available.

Do not display first name, last name, email, country, date selectors, adult count, child count, notes, and a Submit Booking form permanently in the sidebar.

The quote button opens the shared HKS intake dialog or mobile sheet. The required fields remain name, phone, package, preferred date or month, and number of travelers. Package-specific optional questions appear only when they improve quote accuracy.

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

The visible command is **Request a quote**.

Flow:

`Quote button -> intake dialog or sheet -> validation and consent -> private recovery record -> message review -> visitor chooses WhatsApp or email -> visitor sends message in that app`

Desktop quote entry points:

- Sticky Tour quote panel.
- Final Tour prompt.
- Header action where appropriate.

Mobile quote entry points:

- In-flow quote panel after the initial facts.
- Sticky bottom action respecting safe areas.
- Final Tour prompt.

Never use `Book now`, `Submit Booking`, or language implying confirmed availability or payment when the action only requests a quote.

The separate global **Chat on WhatsApp** control uses a concise general message, adding the current Tour title and canonical link on Tour pages, and opens the official number directly. It does not open the intake, save an inquiry, or replace any **Request a quote** entry point. Keep it at the bottom right, above any mobile quote bar and outside form controls or footer content.

### Group Travel route

The direct Group Travel navigation route resolves to `/group-travel/`, not a homepage anchor. Its inline planner is the shared quote conversion in a Group Travel presentation mode:

`Destination -> matching published Tour -> dates/month -> traveler count -> contact and consent -> private recovery record -> message review -> visitor chooses WhatsApp or email`

Only published Tours with assigned Destination terms appear. Changing Destination filters the Tour choices without duplicating Tour data. Keep the selected Tour as the canonical package context used by validation, storage, analytics, and the generated message.

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

- An unbounded or editorially uncontrolled homepage carousel.
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
- Tour thumbnail rail, active image and lightbox.
- Tour tabs and mobile disclosures.
- Itinerary timeline.
- Sticky quote panel.
- Intake-to-review-to-WhatsApp-or-email dialog or sheet.
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
