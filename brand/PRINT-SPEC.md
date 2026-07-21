# Wayfinder Print Production Specification

This document prepares the Holiday Kenya Safaris Wayfinder artwork for print handoff. The RGB masters are production candidates; the CMYK values below are provisional mathematical conversions, not approved press recipes.

## Approval gate

The client has selected **The Wayfinder** as the identity direction. Selection of the direction is not the same as approval of every production file, color conversion, substrate, or supplier proof.

Do not release permanent vehicle graphics, signage, or a large print run until:

1. the client has accepted the final artwork variant;
2. the printer has named the press process, substrate, and ICC profile;
3. the supplied file has been converted and preflighted for that process;
4. a physical color and scale proof has been approved.

No Pantone or other spot-color match is assigned in the current brand contract.

## Master files

Use the outlined SVG files in `brand/masters/` as the artwork source. They scale without raster interpolation and do not require Sora to be installed at the printer.

| Job | Master |
|---|---|
| Full-color horizontal | `hks-wayfinder-horizontal-primary.svg` |
| Full-color stacked | `hks-wayfinder-stacked-primary.svg` |
| One-color on a light stock | `hks-wayfinder-horizontal-navy.svg` or `hks-wayfinder-stacked-navy.svg` |
| Knockout on a dark stock | `hks-wayfinder-horizontal-white.svg` or `hks-wayfinder-stacked-white.svg` |
| White with Saffron cue on a dark stock | `hks-wayfinder-horizontal-reversed.svg` or `hks-wayfinder-stacked-reversed.svg` |
| Light vehicle panel | `hks-wayfinder-vehicle-door-navy.svg` |

The PNG exports are suitable for office documents, mockups, and fixed-size raster delivery. They are not the preferred press master. If a printer cannot accept SVG, place the SVG in a color-managed layout application and supply the printer’s requested press-ready format, such as PDF/X-4, after confirming that format with the printer.

## Provisional process-color conversions

The source of truth remains the RGB/HEX palette in `BRAND-WAYFINDER.md`. The percentages below use a device-independent arithmetic RGB-to-CMYK conversion rounded to whole numbers. Paper, ink, laminate, vinyl, printer calibration, black generation, and ICC profile can all change the result.

| Color | HEX / RGB | Provisional CMYK |
|---|---|---|
| Midnight Navy | `#182B3A` / 24, 43, 58 | C 59 / M 26 / Y 0 / K 77 |
| Lake Teal | `#2C7A78` / 44, 122, 120 | C 64 / M 0 / Y 2 / K 52 |
| Saffron | `#E1A62B` / 225, 166, 43 | C 0 / M 26 / Y 81 / K 12 |
| Pale Mist | `#F3F1EA` / 243, 241, 234 | C 0 / M 1 / Y 4 / K 5 |
| WhatsApp Green | `#25D366` / 37, 211, 102 | C 82 / M 0 / Y 52 / K 17 |
| White | `#FFFFFF` / 255, 255, 255 | C 0 / M 0 / Y 0 / K 0 |
| Near Black | `#161B1F` / 22, 27, 31 | C 29 / M 13 / Y 0 / K 88 |

These values require printer and ICC proof. They are **not** a substitute for a contract proof and must not be entered into a brand manual as final measured ink recipes. Ask the printer to preserve the visual relationship among Navy, Teal, and Saffron rather than matching one patch while allowing the others to drift.

WhatsApp Green remains a conversion-action color, not a logo ink. It is listed only for print pieces that reproduce an actual WhatsApp call to action.

## Color workflow

1. Keep an untouched RGB SVG master in the job archive.
2. Obtain the printer’s ICC profile and total-ink requirements for the exact press and stock.
3. Convert a placed copy of the artwork in a color-managed application; do not overwrite the repository master.
4. Soft-proof, then request a physical proof on representative stock or vinyl.
5. Compare the proof in relevant viewing light and record any printer-specific correction separately from the brand master.

For a true single-ink job, the Navy SVG identifies the intended appearance, not a preselected spot ink. The printer must propose and proof the physical ink match. Do not separate the provisional four-process Navy recipe and call it a spot color.

## Scale and clear space

Let **X** equal one fifth of the compass icon’s outside diameter. Keep at least X between the artwork and trim, folds, seams, handles, panel gaps, fasteners, other logos, or surrounding copy.

| Variant | Minimum printed width |
|---|---:|
| Horizontal complete mark | 40 mm |
| Stacked complete mark | 25 mm |
| Wordmark only | 32 mm |
| Full HKS compass icon | 14 mm |
| Vehicle-door mark | 400 mm, followed by a 1:1 proof |

The simplified favicon is not print artwork. If the full HKS compass loses letter or point definition under the chosen process, increase the mark or use the approved one-color variant; do not thicken or redraw individual parts at the printer.

## Vehicle-door production

- Start with `hks-wayfinder-vehicle-door-navy.svg` on a light panel.
- Print or plot a 1:1 paper proof before cutting vinyl.
- Check HKS legibility and the complete name from the intended viewing distance.
- Keep the mark clear of door seams, handles, wheel arches, trim, and highly curved panel transitions.
- Proof the actual vinyl, laminate, adhesive, and vehicle color; a screen mockup is not a color approval.
- If the panel is dark, request a suitable white or reversed composition from the approved masters rather than inverting the Navy file informally.

The 400 mm minimum is a working lower bound, not a promise that every vehicle panel will be legible at that size. Panel geometry and viewing distance may require a larger application.

## Reproduction controls

- Use only solid fills. Gradients, textures, bevels, outlines, glows, and shadows are prohibited.
- Preserve aspect ratio and built-in compass-to-wordmark spacing.
- Do not add bleed to the logo itself. If the surrounding design bleeds, build that bleed in the print layout while retaining the logo clear space.
- Use the primary three-color mark only where registration, process, and scale can reproduce it cleanly.
- The Saffron eastward cue is optional at constrained sizes. Choose an existing one-color master instead of deleting parts manually.
- White artwork is a knockout or white-ink instruction, depending on substrate; confirm which with the printer.
- Do not place the mark inside an app-style rounded square or an unapproved badge shape.

## Fonts and editable companion text

The SVG logo artwork is outlined. No live font should be substituted for its wordmark.

If a printer receives editable companion copy set in Montserrat, package an official Montserrat source together with its SIL Open Font License, or outline short companion text when appropriate. The website's license record is `wp-content/themes/hks-wayfinder/assets/fonts/licenses/Montserrat-OFL.txt`. Never send an unlicensed font because its name appears in the brand direction.

## Photography in print

The identity approval does not grant reproduction rights to any photograph. Do not place concept-board, presentation, prototype, catalogue, generated, or other workspace imagery in a public print job unless the client has confirmed the right to use that specific image. Retain the approval source with the print job archive.

## Printer handoff checklist

- Correct approved SVG variant selected.
- Client approval of the final composition recorded.
- Final dimensions and X clear space confirmed.
- Target substrate, press, ICC profile, and total-ink limit recorded.
- CMYK conversion performed on a working copy, not the repository master.
- Fonts outlined or legally packaged with their license.
- Photograph rights confirmed for every placed image.
- Preflight passed with no unintended RGB objects in a CMYK-only job.
- Physical color proof approved.
- Vehicle/signage job checked at 1:1 scale and intended viewing distance.
- Final output and printer-specific color notes archived alongside `brand/manifest.json`.
