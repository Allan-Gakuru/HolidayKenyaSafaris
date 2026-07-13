# Holiday Kenya Safaris Design System

This file turns the approved **Wayfinder** direction into implementation rules for the WordPress theme. It supplements `BRAND-WAYFINDER.md`; it does not replace client approvals or source-of-truth content rules.

## Design intent

Holiday Kenya Safaris should feel like a calm, capable local travel planner: specific enough to trust, warm enough to contact, and premium without becoming distant. The interface is editorial and place-led, not an app dashboard and not a discount-tour poster.

The visual rhythm is:

1. reveal the destination and the emotional payoff;
2. make the route, timing, accommodation, transport, inclusions, exclusions, and assumptions easy to scan;
3. place a clear, informed quote action beside that proof;
4. collect only the details needed to continue in WhatsApp.

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

Never use colour alone to communicate price status, availability, validation, selection, or errors.

## Typography

- **Sora**: logo source, headings, important labels, and compact numerical emphasis. Use 600 and 700 in the theme.
- **Inter**: paragraphs, navigation, forms, tables, captions, itinerary details, and utility copy. Use 400, 500, and 600.
- Both families must be self-hosted as WOFF2 files with their SIL Open Font License notices retained in the repository.
- The logo wordmark remains outlined; never depend on a browser font to render the mark.

Default type scale, adjusted responsively with `clamp()`:

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

Destination photography must show the actual place, route, accommodation, vehicle, or experience being described. Use client-approved photographs only. Generated Mara and Mercy images in this repository are internal presentation references and must never be published.

Avoid generic orange sunsets, wildlife collages, fake luxury staging, aggressive colour grading, and images whose origin or usage rights are unconfirmed. Every public image must have a source and approval record.

## Components

### Header

Use the horizontal Wayfinder SVG on wide layouts and the compact HKS icon only when space requires it. The header should be calm, with one dominant quote action. It must remain keyboard navigable and must not resemble a software toolbar.

### Package summaries

Show destination, duration, travel style, departure context, and price status before decorative metadata. A price is never presented without its assumptions. Unknown or unconfirmed numeric prices become **Request current rate** publicly.

### Itinerary

Use a readable day-by-day sequence with native headings or an accessible disclosure pattern. Do not hide the entire trip behind collapsed controls on mobile.

### Quote actions

Primary labels should describe the outcome, such as **Plan this trip** or **Request a WhatsApp quote**. Opening the action reveals the intake form first; it must never silently send visitor data. A mobile sticky action must respect safe areas and leave enough bottom padding that it cannot obscure content.

### Forms and dialogs

Labels remain visible above their controls. Required fields are identified in text, not only by colour or an asterisk. Validation is inline, specific, focus-managed, and announced to assistive technology. The visitor reviews the generated message before choosing to launch WhatsApp.

### Trust and proof

Prefer concrete facts—route, vehicle, accommodation, inclusions, exclusions, operator relationship, and sourced policy details—over badges, invented testimonials, or vague claims. Unknown trust details remain unpublished until confirmed.

## Interaction and motion

- Visible focus rings use a high-contrast two-layer treatment and are never removed.
- Hover, active, selected, loading, success, and error states must be distinct without relying solely on colour.
- Default transitions are 120–220ms and limited to opacity, colour, border, and transform.
- Avoid scroll-jacking, autoplay, parallax, bouncing CTAs, and ornamental motion.
- Under `prefers-reduced-motion: reduce`, remove non-essential movement and shorten necessary state changes.

## Content integrity states

The editor experience must make these distinctions explicit:

- confirmed and publishable;
- provisional with public assumptions;
- `CLIENT CONFIRMATION REQUIRED` and therefore omitted from public output;
- internal-only research or presentation material.

No visual polish may make an unconfirmed fact appear approved.

## Current approval boundary

The production geometry in `brand/masters/` is the implementation baseline, rebuilt from the approved Wayfinder direction. Final client sign-off, public photography, legal/operator wording, policies, analytics identifiers, and confirmed prices remain separate launch gates tracked in `CLIENT-CONFIRMATIONS.md`.
