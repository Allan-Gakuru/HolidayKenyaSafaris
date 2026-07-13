# Phase 1: Wayfinder Production Assets

Date: 2026-07-14
Status: implementation baseline complete; final client sign-off remains required

## Outcome

The selected Wayfinder direction has been redrawn as deterministic production artwork for the website, favicon, social avatar, print handoff, and vehicle-door use.

The released working set contains:

- 18 outlined SVG masters;
- 19 PNG/ICO exports;
- full-colour, Navy one-colour, white one-colour, and reversed treatments where applicable;
- horizontal, stacked, icon, wordmark, compact favicon, and vehicle-door compositions;
- an official Sora Version 2.000 build-time source font and SIL OFL 1.1 notice;
- a generated manifest containing byte counts, dimensions or view boxes, and SHA-256 hashes;
- an internal responsive specimen for visual review.

The raster concept files in `outputs/brand-identity/` remain references only. The SVG masters in `brand/masters/` are the implementation baseline.

## Production decisions

- The HKS letters share a cap line and baseline and no longer use the concept mark's extended vertical bars or central dot.
- The compass uses four open arcs, three Lake Teal points, and a solid Saffron east point.
- The wordmark uses outlined Sora Bold paths, so the public logo has no runtime font dependency.
- The full HKS compass begins at 56px digital width.
- A dedicated compass/H drawing replaces the full icon between 16px and 48px.
- The horizontal lockup is the default website mark; compact, stacked, wordmark-only, and physical variants are controlled exceptions.
- One-colour artwork does not depend on the Saffron cue to remain identifiable.

## Build and verification

Rebuild the identity package with:

```powershell
python tools\brand\build_logo.py
```

Validate the package independently with:

```powershell
python tools\brand\validate_assets.py
```

The validation run checks:

- exact file inventory and deterministic manifest order;
- manifest byte counts and SHA-256 hashes;
- safe, semantic, well-formed SVG structure;
- outlined lettering, approved solid colours, and one-colour variants;
- absence of live text, scripts, embedded raster images, gradients, filters, and active references;
- positive view boxes, accessible titles, and resolved labels;
- exact PNG/ICO sizes, complete decoding, alpha behaviour, and favicon frames.

The final Phase 1 rebuild produced the same manifest hash before and after regeneration:

`6B1A9F683D0EDAD44D75F48769BAD77C1EB9D3D040F2A0D1F2EDA8D74875127A`

The internal `brand/specimen.html` was rendered in the Codex in-app browser at desktop and 390px mobile widths. All 16 specimen images loaded, no horizontal overflow was detected at 390px, and no browser warnings or errors were recorded. This verifies the specimen and SVG delivery; it does not claim full website browser coverage.

## Usage references

- `DESIGN.md`: website design system and component direction.
- `brand/README.md`: asset selection, sizes, clear space, colour, typography, and misuse rules.
- `brand/PRINT-SPEC.md`: provisional CMYK values and printer-proof workflow.
- `brand/specimen.html`: internal visual QA surface.
- `brand/manifest.json`: generated asset integrity record.

## Remaining approval gates

- The client must accept the production geometry before permanent signage, large print runs, or public launch.
- No public photograph is approved by this asset phase. The generated Mara image used in the specimen is clearly marked internal-only.
- CMYK values are provisional arithmetic conversions. The selected printer must provide the press/substrate ICC profile and a physical proof.
- Vehicle graphics require a 1:1 panel proof and viewing-distance check.
- Operator wording, legal details, policies, prices, analytics identifiers, and other launch confirmations remain governed by `CLIENT-CONFIRMATIONS.md`.
