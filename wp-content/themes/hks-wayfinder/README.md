# HKS Wayfinder block theme

This is the custom block-theme foundation for Holiday Kenya Safaris. WordPress content types, structured fields, validation, analytics, and the intake-to-WhatsApp flow belong in the separate `hks-core` site plugin.

## Runtime baseline

- WordPress 6.6 or later (`theme.json` version 3).
- PHP 8.3 or later.
- No Node, Composer, or asset build is required to activate the theme.

## Theme assets

The deployed theme carries vector-derived Wayfinder header and favicon files in `assets/images/brand/`. The desktop header and mobile drawer both use `holiday-kenya-safaris-logo.svg`; do not give the two navigation surfaces different logo assets.

The header pattern references the full horizontal SVG directly from the theme, so enabling SVG uploads is neither necessary nor recommended. If WordPress has no configured Site Icon, `functions.php` provides SVG, 32px PNG, 512px PNG, and Apple touch icon fallbacks. A Site Icon selected in the dashboard takes precedence automatically; `site-icon-512.png` is also the controlled square source for that dashboard setting.

Montserrat is the single website family declared in `theme.json`, with safe system fallbacks and self-hosted Google Fonts v31 Latin and Latin Extended variable WOFF2 subsets. The theme uses weights 400–800 across body, navigation, controls, and headings. Source URLs, upstream hashes, output hashes, and the SIL Open Font License path are recorded in `assets/fonts/SOURCES.json`. Compatibility aliases keep block content saved with the former `sora` or `inter` slugs on Montserrat.

Regenerate or verify the font package from the pinned Montserrat WOFF2 and OFL files with `tools/theme/build_fonts.py`. Do not replace the fonts from an unrecorded download or load them from a third-party CDN at runtime.

## First activation

1. Activate **HKS Wayfinder** under Appearance → Themes.
2. Confirm the product-led desktop navigation and mobile drawer. Only populated catalogue terms and published routes appear.
3. Configure the WordPress Site Icon when the final identity has client sign-off; until then, the theme fallback is used.
4. Do not publish a photograph until its source and usage approval are recorded.

Version `0.5.0` provides the catalogue-led public experience: a product-led header,
image-led homepage, filterable Tour archive, compact Destination pages, and the
canonical Tour gallery/workspace with desktop tabs, mobile disclosures, a sticky
quote panel, itinerary timeline, and related Tours. Focused Campaigns retain their
separate emotional conversion layout. The HKS Core quote block remains the single
source of the saved inquiry, message review, and visitor-controlled WhatsApp handoff.
The footer also supplies a global floating Chat on WhatsApp contact with one fixed
general message. It opens the official number directly and does not create a private
inquiry record or replace the structured quote actions.

Standard WordPress Pages now use the same compact title band and a responsive editorial
content system for About, Group Travel, Contact, and client-approved legal content. The
header automatically switches its Group Travel, About, and Contact routes on only when
the corresponding Page has been published.

Public presentation is fail-closed:

- Tour cards, archives, canonical Tour pages, related Tours, and Tour quote panels never render price;
- a Campaign renders `From KSh… per person` only when its own optional positive amount is populated;
- optional policies and FAQs render only when their public fields are populated;
- Destination guidance renders only when the editor has supplied it; and
- Tour photographs render only when the attachment has useful native alt text.
