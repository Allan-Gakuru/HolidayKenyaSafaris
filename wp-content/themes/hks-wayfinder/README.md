# HKS Wayfinder block theme

This is the custom block-theme foundation for Holiday Kenya Safaris. WordPress content types, structured fields, validation, analytics, and the intake-to-WhatsApp flow belong in the separate `hks-core` site plugin.

## Runtime baseline

- WordPress 6.6 or later (`theme.json` version 3).
- PHP 8.3 or later.
- No Node, Composer, or asset build is required to activate the theme.

## Theme assets

The deployed theme carries vector-derived Wayfinder header and favicon files in `assets/images/brand/`. They are copied from the production candidates in the repository-level `brand/` package; do not edit the copies independently.

The header pattern references the full horizontal SVG directly from the theme, so enabling SVG uploads is neither necessary nor recommended. If WordPress has no configured Site Icon, `functions.php` provides SVG, 32px PNG, 512px PNG, and Apple touch icon fallbacks. A Site Icon selected in the dashboard takes precedence automatically; `site-icon-512.png` is also the controlled square source for that dashboard setting.

The Sora and Inter family tokens are declared in `theme.json` with safe system fallbacks and self-hosted WOFF2 sources. Sora is delivered as official Google Fonts v17 Latin and Latin Extended variable files; Inter is the official 4.1 variable webfont. The theme uses Sora at 600/700 and Inter at 400/500/600. Source URLs, upstream hashes, output hashes, and license paths are recorded in `assets/fonts/SOURCES.json`.

Regenerate or verify the font package from the pinned upstream files with `tools/theme/build_fonts.py`. Do not replace the fonts from an unrecorded download or load them from a third-party CDN at runtime.

## First activation

1. Activate **HKS Wayfinder** under Appearance → Themes.
2. Open Appearance → Editor and configure the header and footer Navigation blocks.
3. Configure the WordPress Site Icon when the final identity has client sign-off; until then, the theme fallback is used.
4. Do not publish a photograph until its source and usage approval are recorded.

The generic posts, search, index, page, single, and 404 templates are intentionally restrained fallbacks. Tour, Campaign, catalogue, destination, trust, contact, and conversion templates are implemented in later phases against structured content.
