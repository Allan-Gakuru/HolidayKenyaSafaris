# Holiday Kenya Safaris Wayfinder Identity

This directory contains the Phase 1 production redraw of **The Wayfinder**, the selected identity direction for **Holiday Kenya Safaris**.

## Status and authority

- **Approved direction:** The Wayfinder is the selected identity. Midnight Navy, Lake Teal, Saffron, Pale Mist, Sora, and Inter are the specified visual direction in `BRAND-WAYFINDER.md`.
- **Current website lockup:** `../wp-content/themes/hks-wayfinder/assets/images/brand/holiday-kenya-safaris-logo.svg` is confirmed for the desktop header and mobile navigation drawer. Its approval for website navigation does not automatically approve it for permanent signage or print.
- **Production artwork status:** the files in `brand/masters/` are the vector production candidates created from that direction. They are not evidence of final client approval by themselves. Record client acceptance before releasing them for permanent signage, a large print run, or a public launch.
- **Concept artwork:** the concept board and extracted concept logo under `outputs/brand-identity/` remain references only. Do not publish or trace those raster files as masters.
- **Asset integrity:** `brand/manifest.json` is the inventory of generated files, dimensions, and SHA-256 hashes. Treat it as the verification record for this asset set.

The exact public name is always **Holiday Kenya Safaris**. Do not shorten the wordmark to “Holiday Kenya Safari” or replace it with Ashford Tours & Travel. The approved operator relationship may appear separately once its final public wording is confirmed.

## Choose the right asset

Use an SVG master whenever the destination supports SVG. The PNG and ICO files in `brand/exports/` are delivery exports for fixed raster contexts.

| Context | Preferred file | Notes |
|---|---|---|
| Website header and mobile drawer | `../wp-content/themes/hks-wayfinder/assets/images/brand/holiday-kenya-safaris-logo.svg` | Current confirmed website lockup; use the same file in both navigation surfaces. |
| Website header on Midnight Navy or a suitably dark image | `masters/hks-wayfinder-horizontal-reversed.svg` | White artwork with the Saffron eastward cue. |
| Single-color mark on a light surface | `masters/hks-wayfinder-horizontal-navy.svg` | Use when color reproduction is restricted. |
| Single-color mark on a dark surface | `masters/hks-wayfinder-horizontal-white.svg` | Pure white; no accent color. |
| Centered or narrow composition | `masters/hks-wayfinder-stacked-primary.svg` | Stacked variants also exist in Navy, white, and reversed treatments. |
| Standalone brand icon at 56 px or larger | `masters/hks-wayfinder-icon-primary.svg` | Use the complete HKS compass icon, not the favicon drawing. |
| Browser favicon | `masters/hks-wayfinder-favicon.svg` | Simplified small-size drawing. PNG sizes 16, 32, and 48 plus `favicon.ico` are in `exports/`. |
| Apple touch icon | `exports/apple-touch-icon-180.png` | Fixed 180 × 180 export. |
| Social profile image | `exports/social-avatar-512.png` | Fixed 512 × 512 export. |
| Wordmark-only exception | `masters/hks-wayfinder-wordmark-primary.svg` | Use only where the compass is already present nearby or space cannot support the complete mark. Navy and white versions are available. |
| Vehicle door | `masters/hks-wayfinder-vehicle-door-navy.svg` | Vector master for a light vehicle panel; physically proof before production. |

The `primary` treatment uses Midnight Navy, Lake Teal, and the optional Saffron eastward cue. `navy` is one color. `white` is one color. `reversed` uses white plus Saffron and is intended for dark backgrounds.

## Minimum sizes

These are the working Phase 1 minimums. They protect wordmark and HKS legibility; they do not replace checking the actual browser, stock, print process, or viewing distance.

| Variant | Digital minimum | Print minimum |
|---|---:|---:|
| Horizontal complete mark | 160 px wide | 40 mm wide |
| Stacked complete mark | 96 px wide | 25 mm wide |
| Wordmark only | 120 px wide | 32 mm wide |
| Full HKS compass icon | 56 px wide | 14 mm wide |
| Simplified favicon | 16–48 px wide | Not for print |
| Vehicle-door mark | Not applicable | 400 mm wide, followed by a 1:1 proof |

Below 56 px, use only the simplified favicon artwork. Do not shrink the full compass icon and hope that its points or HKS monogram survive. If a complete mark must be smaller than the stated minimum, enlarge the available placement or use a different approved composition.

## Clear space

Let **X** equal one fifth of the compass icon’s outside diameter.

- Keep at least X of empty space beyond every edge of a complete horizontal or stacked mark.
- Keep at least X around a standalone icon.
- For the wordmark-only asset, use the cap height of the first “H” as X.
- Do not place copy, trim lines, borders, other logos, or prominent photographic detail inside the clear-space area.
- A square favicon or social-avatar canvas may crop the *canvas* tightly, but it must not crop or distort the artwork inside it.

Do not alter the built-in optical spacing between the compass and wordmark.

## Color

| Role | Name | HEX | Use |
|---|---|---|---|
| Primary | Midnight Navy | `#182B3A` | Wordmark, headings, footer, trust surfaces |
| Secondary | Lake Teal | `#2C7A78` | Links, active states, secondary actions, graphic details |
| Action accent | Saffron | `#E1A62B` | Highlights and the optional eastward route cue |
| Background | Pale Mist | `#F3F1EA` | Main warm-neutral background |
| Conversion only | WhatsApp Green | `#25D366` | WhatsApp action only |
| Utility | White | `#FFFFFF` | Reversed artwork and clean content surfaces |
| Utility | Near Black | `#161B1F` | Dense body copy where needed |

Use solid colors, never gradients. WhatsApp Green is not a substitute brand color and must not recolor the compass or wordmark. Maintain WCAG AA contrast in every interface placement.

Use the primary mark on White or Pale Mist. Use the reversed or white mark on Midnight Navy, Near Black, or a dark photographic area with dependable contrast. On a photograph, choose a calm area that keeps the entire mark legible; do not rescue a poor placement with a glow, outline, translucent app tile, or drop shadow.

## Typography and font licensing

- Display headings and the brand direction use **Sora SemiBold/Bold**.
- Body copy, interface labels, captions, and forms use **Inter Regular/Medium/SemiBold**.
- Use tabular numerals for prices, dates, durations, and comparisons.
- Keep normal letter spacing. Do not add wide tracking to simulate luxury.

The logo masters contain outlined paths, so displaying or printing the logo does not require a live font file. `source-fonts/Sora-wght.ttf` is the Sora Version 2.000 source used to build the outlined artwork. It is distributed under the SIL Open Font License 1.1 in `source-fonts/Sora-OFL.txt`; keep that license with any redistributed font software and do not sell the font by itself.

Inter is the confirmed interface direction but is not part of this Phase 1 brand asset package. Before self-hosting it in the WordPress theme, add the approved webfont files together with their own license and source record. Do not infer a font license from the logo exports.

## Photography restriction

This identity package does **not** approve any photograph for public use. Only publish destination, vehicle, accommodation, meal, activity, traveler, or guide photographs whose rights the client has confirmed. A photo appearing in a concept board, presentation, prototype, source catalogue, generated mockup, or internal specimen is not evidence of public-use permission.

When approved photography is available, it should reveal the actual destination and experience. Avoid dark generic stock, fake-luxury staging, wildlife collages, heavy sunset filters, and compositions that hide practical tour detail.

## Never do this

- Do not redraw, retype, rotate, stretch, skew, crop, or rearrange the mark.
- Do not change its approved colors or move, enlarge, or multiply the Saffron eastward cue.
- Do not use a gradient, texture, bevel, outline, glow, or decorative shadow.
- Do not put the icon in a rounded-square app tile or present Holiday Kenya Safaris as a navigation product.
- Do not use the full icon below 56 px; use the favicon drawing.
- Do not substitute the wordmark-only file as the normal website-header mark.
- Do not add an unapproved tagline, operator statement, or claim to the lockup.
- Do not recolor the mark WhatsApp Green.
- Do not recreate the outlined wordmark as editable browser text.
- Do not manually edit generated exports. Update the master/build source, regenerate the set, and verify `manifest.json` instead.

## Release checklist

Before an asset leaves the working repository:

1. Confirm that its filename matches the intended background and reproduction method.
2. Check the size and clear-space rules above at final scale.
3. Verify the file against `manifest.json` if asset integrity is in doubt.
4. Confirm that any accompanying photograph has explicit public-use approval.
5. For print, follow `PRINT-SPEC.md` and obtain a substrate-specific proof.
6. Record final client approval; do not describe a production candidate as client-approved until that confirmation exists.
