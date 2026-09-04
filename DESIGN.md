# Holiday Kenya Safaris Design System

This file turns the approved **Wayfinder** direction into implementation rules for the WordPress theme. It supplements `BRAND-WAYFINDER.md`; it does not replace client approvals or source-of-truth content rules.

## Design intent

Holiday Kenya Safaris should feel like a calm, capable local travel planner: specific enough to trust, warm enough to contact, and premium without becoming distant. The interface is place-led and catalogue-clear, not an app dashboard and not a discount-tour poster.

The visual rhythm is:

1. reveal the destination and the emotional payoff;
2. make the route, timing, accommodation, transport, inclusions, and exclusions easy to scan;
3. place a clear, informed quote action beside that proof;
4. collect only the details needed to continue through WhatsApp or email.

## Interface modes

### Catalogue mode

The homepage, global navigation, catalogue, destination pages, and canonical Tour pages follow `UI-REFERENCE-CATALOGUE.md`. Catalogue mode is light, image-led, browseable, and internally connected. It should communicate the breadth and operational confidence of an established travel company.

### Campaign mode

Focused paid-ad pages use the canonical Tour title band, gallery, facts, sticky quote panel, tabs/disclosures, itinerary, related-Tour system, and responsive behavior. Campaign mode can change the headline, supporting copy, first gallery image, optional starting price, and navigation density while inheriting canonical Tour facts and remaining media.

Campaign differentiation comes from ad-matched content and optional focused navigation, not a second visual composition.

### Travel Guides modes

Travel Guides extends the Wayfinder system with two deliberate editorial compositions:

- **Standard Guide / Field Guide:** reading-first, quiet, and practical. Use a maximum 720px reading measure, clear heading rhythm, restrained public taxonomy context, and an optional **View this trip** link to the Primary Tour. It has no persistent conversion control.
- **Advertorial / Conversion Story:** emotionally focused but evidence-led. Open with one static, full-width destination image beneath the normal internal header, a restrained Midnight Navy wash, the public context, advertorial title, short excerpt, and two actions. **Book this tour now** is the transparent action and opens the shared quote intake; **Learn more** is the filled action and moves to the article outline. Immediately below the hero, show a fully expanded **What we’ll cover** outline of every body H2 and H3, with H3 links nested beneath their preceding H2. Keep it in normal flow as simple anchor links with no sticky or active-section behaviour. On desktop, its two visual columns stack independently so a topic with nested subheadings never creates an empty row on the opposite side; collapse to one column on mobile. Use a 680–720px reading column beside an approximately 320px sticky Primary Tour panel on desktop, and a mobile quote bar only after the opening action leaves view. Every quote action opens the shared intake and review flow.

Both formats omit author names and support an intentional text-led opening when no featured image is assigned. Neither format may invent urgency, availability, reviews, prices, or operational proof. Article cards and related modules link to Posts; the Primary Tour is a separate, explicit conversion relationship.

## Identity

- Exact name: **Holiday Kenya Safaris**.
- Identity: **The Wayfinder**.
- Primary mark: full-colour horizontal lockup.
- Compact mark: HKS compass icon, used only where the wordmark would become unreadable.
- Favicon: the official compass monogram export. Do not shrink the full lockup into a favicon.
- The saffron east point is a directional accent, never the sole carrier of meaning.
- Logo SVGs contain outlined lettering and require no runtime font.

Use a minimum clear space equal to 20% of the icon height on every side. The preferred digital minimums are 160px wide for the horizontal lockup and 56px for the full HKS icon. Between 16px and 48px, use the dedicated compact favicon asset.

Do not place the mark inside an app-style tile, add a containing circle, redraw it with gradients, add a phone number to it, stretch it, recolour individual parts, or use the older textured concept-board raster as production artwork.

## Colour

| Token | Value | Role |
| --- | --- | --- |
| Midnight Navy | `#182B3A` | Primary text, strong surfaces, one-colour logo |
| Lake Teal | `#2C7A78` | Secondary actions, route cues, selected states |
| Wayfinder Saffron | `#E1A62B` | Small directional accents and highlights |
| Pale Mist | `#F3F1EA` | Warm page background and quiet sections |
| White | `#FFFFFF` | Content surfaces and reversed text |

Contrast pairings approved for ordinary text:

- Midnight Navy on White: 14.53:1.
- Midnight Navy on Pale Mist: 12.85:1.
- White on Midnight Navy: 14.53:1.
- Lake Teal on White: 5.05:1.

Lake Teal on Pale Mist is approximately 4.46:1 and is therefore reserved for large text, non-text decoration, or controls whose complete treatment is verified. Saffron does not pass text contrast on White or Pale Mist; use it only as non-essential decoration there. On Midnight Navy, Saffron may be used for small high-visibility accents.

Never use colour alone to communicate an optional Campaign price, validation, selection, or errors.

## Typography

- **Montserrat** is the single website type family for headings, paragraphs, navigation, forms, tables, captions, itinerary details, utility copy, and numerical emphasis.
- Use 400 for body copy, 500 for compact metadata, 600 for controls and secondary headings, and 700–800 for primary headings where the hierarchy requires it.
- Self-host the Latin and Latin Extended variable WOFF2 subsets with the SIL Open Font License retained in the repository.
- The logo wordmark remains outlined; changing the website font must never alter or recreate the logo artwork.

Default type scale, adjusted at deliberate responsive breakpoints rather than continuously scaled with viewport width:

| Purpose | Target range | Weight |
| --- | --- | --- |
| Display | 44–72px | Montserrat 700–800 |
| Page title | 36–56px | Montserrat 700 |
| Section title | 28–40px | Montserrat 600–700 |
| Card/title | 20–26px | Montserrat 600 |
| Body lead | 18–21px | Montserrat 400 |
| Body | 16–18px | Montserrat 400 |
| Utility | 13–15px | Montserrat 500–600 |

Keep paragraph measure near 65 characters. Use sentence case. Avoid all-caps paragraphs and wide letter spacing in body copy.

## Layout and spacing

- Mobile is the primary buying surface.
- Default content maximum: 1240px.
- Reading column maximum: 720px.
- Use a 12-column desktop grid and a simple single-column mobile flow; editorial asymmetry is welcome when it preserves reading order.
- Core spacing scale: 4, 8, 12, 16, 24, 32, 48, 64, 96px.
- Section spacing should normally be 64–96px desktop and 48–64px mobile.
- Use modest 8–16px radii on interactive or content containers. Images may use smaller radii or bleed to an edge. Avoid a page made of floating rounded cards.
- Prefer whitespace, alignment, and tonal surfaces over decorative borders and shadows. When separation is needed, use a one-pixel translucent Navy border. Shadows are reserved for overlays and sticky controls.

## Photography

Destination photography must show the actual place, route, accommodation, vehicle, or experience being described. Media uploaded or assigned by an authorized editor and used on published content is treated as client-approved for the website. Generated Mara and Mercy images in this repository are internal presentation references and must never be published.

Avoid generic orange sunsets, wildlife collages, fake luxury staging, aggressive colour grading, and images an authorized editor has not deliberately selected for the public site. Meaningful public images need useful native WordPress alt text; add a public credit only when one must be displayed. Repeated hero and preview imagery may be deliberately decorative when adjacent text and labeled controls provide the same context.

## Components

### Header

Use a slim Midnight Navy utility bar and a white primary header on desktop. The utility bar contains only confirmed operator, contact, and social information, including one clean WhatsApp icon rather than a separate phone glyph. The WhatsApp icon and number open a direct chat with a concise prefilled message; on Tour and Campaign pages that message includes the current title and URL. The primary header and mobile navigation drawer both use `assets/images/brand/holiday-kenya-safaris-logo.svg`, with product-led dropdown navigation and without repeating the utility contact as a large quote button.

The primary header/mobile hierarchy and footer links are editor-managed through the native WordPress **Appearance > Site Menus** screen. Use the registered `Primary header and mobile menu` and `Footer menu` locations. A top-level Primary item with children becomes a desktop dropdown and mobile accordion; keep the hierarchy to two levels. When no menu is assigned, retain the existing catalogue-aware navigation as a safe fallback.

The homepage is the deliberate exception: omit the utility bar and place the existing logo and primary navigation in an almost-clear Pale Mist header over the Featured Tour hero. Use only a minimal tint with a restrained blur where supported so the photograph remains visually continuous through the header. Preserve the opaque Pale Mist treatment only for reduced-transparency or increased-contrast preferences. Keep the homepage header fixed at the top and change its surface to solid white after the visitor begins scrolling, while internal pages retain the complete utility bar and white primary header. Dropdown panels and the mobile drawer remain solid white in both contexts.

On mobile, use the Wayfinder mark and a familiar menu icon. The full-height navigation drawer uses accessible accordion groups, direct contact routes, and a quote action. It must trap focus, close with Escape, return focus to its trigger, and prevent background scrolling.

The header must not resemble a software toolbar. Search is optional and should appear only when the catalogue is large enough for it to help.

### Homepage and catalogue

The homepage hero uses up to five published Tours explicitly marked Featured and carrying a featured image at least 1200 × 675 pixels. Hero eligibility does not depend on native alt text; render the repeated stage and preview images with empty alt text because the adjacent Tour title, destination, and labeled controls provide equivalent context. The active Tour supplies the only H1, a destination or Tour-scope label, the full-bleed image, and a `Click here to book tour` link to the canonical Tour page. Portrait preview cards form the queue. Use a five-second cycle and synchronized slim Saffron progress line on every viewport, minimal previous/next controls, direct card selection, touch drag or swipe, keyboard commands, and a compact pause/resume control. Open a selected card into the full stage with a clipped-image reveal, retain the old scene and copy during the opening phase, and swap the image and copy near the end. Pause when the hero is not viewable or the visitor is interacting with it; hide progress and disable autoplay under reduced motion.

If only one eligible Featured Tour exists, render a static hero without controls. If none exists, render a compact generic fallback and catalogue action. Place Browse by destination immediately after the hero, followed by the existing Featured Tours grid. Tour grids use stable image ratios, consistent title space, and practical metadata rather than image-and-title-only cards.

Catalogue and taxonomy pages use a compact title and breadcrumb band, useful filters, a responsive Tour grid, and clear no-results behavior. On the Tour catalogue, filters form a sticky vertical left rail from 1024px upward; below that breakpoint, a sticky `Filters` button opens the same controls in an accessible drawer. Avoid abstract gradients, empty metadata, and oversized media inventory.

### Canonical Tour shell

Use the structure in `UI-REFERENCE-CATALOGUE.md`:

1. Compact title and breadcrumb band with the only H1.
2. Desktop three-column composition: vertical thumbnail rail, one active gallery image, and the sticky quote panel.
3. Destination or route line beneath the active media.
4. Main Tour facts and accessible tabs beneath the media while the quote panel remains sticky.
5. Accessible thumbnail selection, over-image previous/next chevrons, five-second automatic rotation with interaction and visibility pauses, and a full-gallery lightbox. Desktop shows no more than six rail thumbnails; when more images exist, the sixth uses a dark count overlay and opens the lightbox at that image.
6. Horizontal image previews and normal-flow quote content below desktop width.
7. Related Tours and final quote prompt.

On mobile, the gallery uses one stable active image followed by a horizontally scrollable thumbnail strip, tabs become stacked disclosures, the quote panel returns to normal document flow, and a safe-area-aware sticky action remains available.

### Package summaries

Show destination, duration, travel style, departure context, and route before decorative metadata. Tour summaries and cards show `From KSh X per person` only when the Tour has a positive starting price. Every card has a clear View trip action; the whole card may be linked only when keyboard and assistive-technology behavior remains correct.

### Itinerary

Use a readable day-by-day timeline with native headings and accessible disclosures. Support individual expansion and, for longer itineraries, Expand all and Collapse all. Open useful initial content by default. Do not hide the entire trip behind collapsed controls on mobile.

### Quote actions

The canonical primary label is **Request a quote**. Opening the action reveals the intake form first; it must never silently send visitor data. Tour and Campaign pages use the same sticky desktop quote panel containing the applicable starting price, the primary action, and a short explanation of the tailored quote and message-review step. A positive Campaign price overrides the linked Tour amount; a blank Campaign price inherits it when available. Neither page type uses a permanently visible long booking form. A mobile sticky action must respect safe areas and leave enough bottom padding that it cannot obscure content.

The global floating **Chat on WhatsApp** control is a separate lightweight contact route. It stays at the bottom right, uses a concise general prefilled message and adds the current Tour title and canonical link on Tour pages, opens the official number in a new tab, and does not create an inquiry record. It must rise above the Tour and Campaign mobile quote bar, respect device safe areas, and never replace or visually compete with the structured page-level quote action.

### Group Travel planner

The canonical `/group-travel/` Page uses Catalogue mode and keeps one H1 in the standard title band. Follow it with a concise image-led introduction using media already assigned to published Tours, an inline planner, a three-step explanation, and the Page's editable supporting content.

The planner reuses the shared HKS inquiry component rather than opening a second form. Destination and Tour are linked required selects; name, phone, email, preferred date or month, traveler count, private recovery, message review, and visitor-controlled WhatsApp/email handoff retain the global conversion behavior. The form must remain legible as one column on mobile and must not promise group capabilities that have not been confirmed.

### About page

The `/about/` template keeps the standard title band, followed only by the native `about-story` pattern, **Visit Us**, and **Contact Us**. The white narrative pairs its headline and lead with the story body in two desktop columns, collapsing to one below 1024px. Keep Ashford ownership and 20+ years of experience within that story. Use Montserrat throughout: a 40px story heading, reducing to 32px below 576px, and 32px Visit Us and Contact Us headings. Section padding is 64px vertically, reducing to 48px below 576px. Visit Us uses Pale Mist; Contact Us returns to white with simple contact rows. Render address, hours, an embedded Google Map, directions, phone, email, and WhatsApp links from public settings, omitting blank values and entirely empty sections. The responsive map uses a titled, lazy-loaded iframe with its own optional editable embed URL; retain the separate directions link. Directions and WhatsApp links identify that they open in a new tab; preserve visible focus and 44px link targets.

### Travel Guides

The `/travel-guides/` hub uses the internal-page shell, one H1, a concise planning promise, Destination and Article Topic discovery controls, and a responsive editorial grid. Article cards may show public destination/topic context and modified date, never an author. A missing featured image becomes a designed Midnight Navy or Pale Mist typographic surface; never render a broken image slot or generic stock placeholder.

Standard Guides privilege the reading flow. When a Primary Tour exists, place **View this trip** after the opening promise and link to the canonical Tour. Do not turn this into a sticky action or open the quote dialog from that label.

Advertorials require a published Primary Tour. Their outline is generated from the rendered article, preserves usable existing heading IDs, and assigns deterministic unique IDs to every body H2 and H3 that needs one. The hero uses the Post featured image as a single static full-bleed background; when that image is blank, use the linked Primary Tour featured image, and when both are blank retain a deliberate Midnight Navy text-led hero. Do not add homepage carousel controls, other-Tour choices, or homepage hero typography to this opening. Keep the small breadcrumb and public-context kicker opaque White over the washed image for contrast. Keep the image visible behind the content at every viewport, while stacking the two hero actions vertically on mobile. Place the generated outline immediately after the hero and before the reading layout. The desktop Tour panel may surface only verified canonical facts and a positive stored starting price when available. Repeated quote controls are proxies to one shared intake dialog; do not duplicate forms. On mobile, use `IntersectionObserver` or an equivalent visibility primitive to reveal the sticky quote bar after the opening quote action leaves view and hide it when it would collide with the footer. The global Chat on WhatsApp control remains above it.

The **What we’ll cover** outline automatically numbers top-level topics `1.`, `2.`, `3.` and nested H3 links `1.1`, `1.2`, `2.1`. Assign numbers in article order before splitting desktop columns, so each topic has the same number on mobile. Keep the numbers in the rendered link text, align wrapped labels separately, and omit existing explicit prefixes such as `1. ` or `2) ` from TOC labels only. Do not alter body heading text or anchor IDs. A leading H3 without a preceding H2 remains a standalone numbered topic.

Related reading appears after either format and contains up to three Posts in a stable grid or list. Use the same card component as the hub, including its no-image state. Destination pages remain Tour-first and place relevant Travel Guides below the Tour catalogue as a visually quieter secondary section.

### Forms and dialogs

Labels remain visible above their controls. Required fields are identified in text, not only by colour or an asterisk. Validation is inline, specific, focus-managed, and announced to assistive technology. The visitor reviews the generated message before choosing to open WhatsApp or an email addressed to `info@holidaykenyasafaris.ke`.

### Trust and proof

Prefer concrete facts—route, vehicle, accommodation, inclusions, exclusions, operator relationship, and deliberate public policy text—over badges, invented testimonials, or vague claims. Unknown trust details remain blank or in draft.

## Interaction and motion

- Visible focus rings use a high-contrast two-layer treatment and are never removed.
- Hover, active, selected, loading, success, and error states must be distinct without relying solely on colour.
- Default transitions are 120–220ms and limited to opacity, colour, border, and transform.
- Avoid scroll-jacking, parallax, bouncing CTAs, and ornamental motion. The approved Featured Tour hero is the sole autoplay exception and must retain its pause control, viewport/visibility pauses, and reduced-motion fallback.
- Under `prefers-reduced-motion: reduce`, remove non-essential movement and shorten necessary state changes.

## Editorial content state

The editor uses native WordPress publication state as the approval model:

- **Draft:** not approved for public output.
- **Published:** deliberately approved by an authorized editor, including assigned media.
- **Blank optional field:** unavailable and omitted with a deliberate fallback.

Do not expose separate confirmation, source-checked, rights-checked, price-status, or validity controls in the client-facing content forms. Internal import material and legacy metadata may remain stored, but they do not render and do not gate publication. No visual polish may make a draft, blank, or imported-only value appear public.

## Current approval boundary

The approved logo in `brand/masters/holiday-kenya-safaris-logo.svg` is the implementation baseline. Earlier redraws are retired; browser icons and shared-link previews derive from the approved artwork. Publishing is the per-record approval signal for public content and assigned media. Legal/operator wording, global policies, contact details, analytics identifiers, and other project-level launch decisions remain tracked in `CLIENT-CONFIRMATIONS.md`.
