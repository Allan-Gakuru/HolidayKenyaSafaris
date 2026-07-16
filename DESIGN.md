# Holiday Kenya Safaris Design System

This file turns the approved **Wayfinder** direction into implementation rules for the WordPress theme. It supplements `BRAND-WAYFINDER.md`; it does not replace client approvals or source-of-truth content rules.

## Design intent

Holiday Kenya Safaris should feel like a calm, capable local travel planner: specific enough to trust, warm enough to contact, and premium without becoming distant. The interface is place-led and catalogue-clear, not an app dashboard and not a discount-tour poster.

The visual rhythm is:

1. reveal the destination and the emotional payoff;
2. make the route, timing, accommodation, transport, inclusions, and exclusions easy to scan;
3. place a clear, informed quote action beside that proof;
4. collect only the details needed to continue in WhatsApp.

## Interface modes

### Catalogue mode

The homepage, global navigation, catalogue, destination pages, and canonical Tour pages follow `UI-REFERENCE-CATALOGUE.md`. Catalogue mode is light, image-led, browseable, and internally connected. It should communicate the breadth and operational confidence of an established travel company.

### Campaign mode

Focused paid-ad pages may use the immersive, emotionally concentrated structure of the existing Maasai Mara prototype. Campaign mode can change the opening headline, supporting copy, featured hero image, and navigation density while inheriting canonical Tour facts.

Do not blend both modes into an indecisive hybrid on every page. Canonical Tour pages use the catalogue shell. Campaign pages earn the more dramatic opening.

## Identity

- Exact name: **Holiday Kenya Safaris**.
- Identity: **The Wayfinder**.
- Primary mark: full-colour horizontal lockup.
- Compact mark: HKS compass icon, used only where the wordmark would become unreadable.
- Favicon: the dedicated simplified compass/H asset. Do not shrink the full lockup into a favicon.
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

- **Sora**: logo source, headings, important labels, and compact numerical emphasis. Use 600 and 700 in the theme.
- **Inter**: paragraphs, navigation, forms, tables, captions, itinerary details, and utility copy. Use 400, 500, and 600.
- Both families must be self-hosted as WOFF2 files with their SIL Open Font License notices retained in the repository.
- The logo wordmark remains outlined; never depend on a browser font to render the mark.

Default type scale, adjusted at deliberate responsive breakpoints rather than continuously scaled with viewport width:

| Purpose | Target range | Weight |
| --- | --- | --- |
| Display | 44–72px | Sora 700 |
| Page title | 36–56px | Sora 700 |
| Section title | 28–40px | Sora 600–700 |
| Card/title | 20–26px | Sora 600 |
| Body lead | 18–21px | Inter 400 |
| Body | 16–18px | Inter 400 |
| Utility | 13–15px | Inter 500–600 |

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

Avoid generic orange sunsets, wildlife collages, fake luxury staging, aggressive colour grading, and images an authorized editor has not deliberately selected for the public site. Every public image needs useful native WordPress alt text; add a public credit only when one must be displayed.

## Components

### Header

Use a slim Midnight Navy utility bar and a white primary header on desktop. The utility bar contains only confirmed location, operator, contact, and social information. The primary header uses the horizontal Wayfinder SVG, product-led dropdown navigation, and one dominant **Request quote on WhatsApp** action.

On mobile, use the Wayfinder mark and a familiar menu icon. The full-height navigation drawer uses accessible accordion groups, direct contact routes, and a quote action. It must trap focus, close with Escape, return focus to its trigger, and prevent background scrolling.

The header must not resemble a software toolbar. Search is optional and should appear only when the catalogue is large enough for it to help.

### Homepage and catalogue

Use one decisive hero image or no more than three curated slides. Keep the next section discoverable, and place verified featured Tours immediately after the hero. Tour grids use stable image ratios, consistent title space, and practical metadata rather than image-and-title-only cards.

Catalogue and taxonomy pages use a compact title and breadcrumb band, useful filters, a responsive Tour grid, and clear no-results behavior. Avoid abstract gradients, empty metadata, and oversized media inventory.

### Canonical Tour shell

Use the structure in `UI-REFERENCE-CATALOGUE.md`:

1. Compact title and breadcrumb band with the only H1.
2. Three-image gallery mosaic.
3. Destination or route line.
4. Approximately 68/32 desktop workspace.
5. Main Tour facts and accessible tabs.
6. Sticky quote panel.
7. Related Tours and final quote prompt.

On mobile, the gallery simplifies, tabs become stacked disclosures, the quote panel returns to normal document flow, and a safe-area-aware sticky action remains available.

### Package summaries

Show destination, duration, travel style, departure context, and route before decorative metadata. Tour summaries and cards never show price. Every card has a clear View trip action; the whole card may be linked only when keyboard and assistive-technology behavior remains correct.

### Itinerary

Use a readable day-by-day timeline with native headings and accessible disclosures. Support individual expansion and, for longer itineraries, Expand all and Collapse all. Open useful initial content by default. Do not hide the entire trip behind collapsed controls on mobile.

### Quote actions

The canonical primary label is **Request quote on WhatsApp**. Opening the action reveals the intake form first; it must never silently send visitor data. Canonical Tour pages use a price-free sticky desktop quote panel containing the primary action and a short explanation of the tailored quote and message-review step. Campaign pages may show their own optional starting price when one is deliberately entered. They do not use a permanently visible long booking form. A mobile sticky action must respect safe areas and leave enough bottom padding that it cannot obscure content.

### Forms and dialogs

Labels remain visible above their controls. Required fields are identified in text, not only by colour or an asterisk. Validation is inline, specific, focus-managed, and announced to assistive technology. The visitor reviews the generated message before choosing to launch WhatsApp.

### Trust and proof

Prefer concrete facts—route, vehicle, accommodation, inclusions, exclusions, operator relationship, and deliberate public policy text—over badges, invented testimonials, or vague claims. Unknown trust details remain blank or in draft.

## Interaction and motion

- Visible focus rings use a high-contrast two-layer treatment and are never removed.
- Hover, active, selected, loading, success, and error states must be distinct without relying solely on colour.
- Default transitions are 120–220ms and limited to opacity, colour, border, and transform.
- Avoid scroll-jacking, autoplay, parallax, bouncing CTAs, and ornamental motion.
- Under `prefers-reduced-motion: reduce`, remove non-essential movement and shorten necessary state changes.

## Editorial content state

The editor uses native WordPress publication state as the approval model:

- **Draft:** not approved for public output.
- **Published:** deliberately approved by an authorized editor, including assigned media.
- **Blank optional field:** unavailable and omitted with a deliberate fallback.

Do not expose separate confirmation, source-checked, rights-checked, price-status, or validity controls in the client-facing content forms. Internal import material and legacy metadata may remain stored, but they do not render and do not gate publication. No visual polish may make a draft, blank, or imported-only value appear public.

## Current approval boundary

The production geometry in `brand/masters/` is the implementation baseline, rebuilt from the approved Wayfinder direction. Publishing is the per-record approval signal for public content and assigned media. Legal/operator wording, global policies, contact details, analytics identifiers, and other project-level launch decisions remain tracked in `CLIENT-CONFIRMATIONS.md`.
