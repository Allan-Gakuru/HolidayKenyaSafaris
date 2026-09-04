# HKS Wayfinder block theme

This is the custom block-theme foundation for Holiday Kenya Safaris. WordPress content types, structured fields, validation, analytics, and the intake-to-review-to-WhatsApp-or-email flow belong in the separate `hks-core` site plugin.

## Runtime baseline

- WordPress 6.6 or later (`theme.json` version 3).
- PHP 8.3 or later.
- No Node, Composer, or asset build is required to activate the theme.

## Theme assets

The deployed theme carries vector-derived Wayfinder header and favicon files in `assets/images/brand/`. The desktop header and mobile drawer both use `holiday-kenya-safaris-logo.svg`; do not give the two navigation surfaces different logo assets.

The header pattern references the full horizontal SVG directly from the theme, so enabling SVG uploads is neither necessary nor recommended. If WordPress has no configured Site Icon, `functions.php` provides SVG, 32px PNG, 512px PNG, and Apple touch icon fallbacks. A Site Icon selected in the dashboard takes precedence automatically; `holiday-kenya-safaris-site-icon-512.png` is also the controlled square source for that dashboard setting.

Montserrat is the single website family declared in `theme.json`, with safe system fallbacks and self-hosted Google Fonts v31 Latin and Latin Extended variable WOFF2 subsets. The theme uses weights 400–800 across body, navigation, controls, and headings. Source URLs, upstream hashes, output hashes, and the SIL Open Font License path are recorded in `assets/fonts/SOURCES.json`. Compatibility aliases keep block content saved with the former `sora` or `inter` slugs on Montserrat.

Regenerate or verify the font package from the pinned Montserrat WOFF2 and OFL files with `tools/theme/build_fonts.py`. Do not replace the fonts from an unrecorded download or load them from a third-party CDN at runtime.

## First activation

1. Activate **HKS Wayfinder** under Appearance → Themes.
2. Open **Appearance > Site Menus**, create or select the Primary and Footer menus, and assign them to `Primary header and mobile menu` and `Footer menu`. Keep Primary to two levels; child items become desktop dropdown links and mobile accordion links. Until a menu is assigned, the existing populated catalogue navigation remains active.
3. Configure the WordPress Site Icon when the final identity has client sign-off; until then, the theme fallback is used.
4. Do not publish a photograph until its source and usage approval are recorded.

Version `0.7.0` provides the catalogue-led public experience plus the Travel Guides
system: native WordPress Posts can render as reading-first destination guides or
focused conversion stories. Standard Guides may have an optional Primary Tour;
Advertorials require one published Primary Tour and use the existing shared quote flow.
Campaigns use the canonical Tour presentation with controlled headline, supporting-copy,
first-image, price, and navigation overrides. Related content contains guide Posts only.
No author byline is rendered publicly.

Version `0.5.0` provides the catalogue-led public experience: a product-led header,
image-led homepage, filterable Tour archive, compact Destination pages, and the
canonical Tour gallery/workspace with desktop tabs, mobile disclosures, a sticky
quote panel, itinerary timeline, and related Tours. Campaigns now reuse that complete
Tour presentation while retaining their ad-congruency overrides and navigation mode.
The HKS Core quote block remains the single source of the saved inquiry, message
review, and visitor-controlled handoff.
The footer also supplies a global floating Chat on WhatsApp contact with one fixed
general message. It opens the official number directly and does not create a private
inquiry record or replace the structured quote actions.

Standard WordPress Pages now use the same compact title band and a responsive editorial
content system for About, Group Travel, Contact, and client-approved legal content. The
header automatically switches its Group Travel, About, and Contact routes on only when
the corresponding Page has been published.

## About page

The `/about/` route uses `templates/page-about.html`. Its opening story is the
native block pattern `patterns/about-story.php`: a single-column introduction
of at most two paragraphs focused on Holiday Kenya Safaris, based on the
client-supplied Brand Script and the ownership/experience approval recorded on
4 September 2026. Keep Ashford's ownership and 20+ years of experience within
those paragraphs, without a separate introductory headline or CTA.
Edit that story through the About template in Appearance → Editor, or update
the version-controlled pattern. This dedicated template replaces the original
seeded Page body; it does not append the old operator and process sections.

The only sections after the story are Visit Us and Contact Us, in two compact
columns that stack below 768px. On the left, Visit Us shows working hours,
address, then the smaller map and directions link. On the right, Contact Us
shows the public phone number with a Chat on WhatsApp button, “Keep up with
Holiday Kenya Safaris updates on social media”, and Facebook and Instagram
follow buttons. `AboutPage.php` reads
the public address, directions URL, optional map embed URL, business hours and
phone and WhatsApp from HKS Settings → Identity and contact, and the Facebook and Instagram
profiles from its existing Social links repeater. Blank settings are omitted.
The compact Contact Us section has no email row; global contact
components retain their existing behavior. Visit Us includes a responsive,
lazy-loaded Google Maps iframe with an accessible title, at 220px desktop and
200px mobile height, alongside the existing directions link. Keep the layout
compact: 24px/20px intro padding, 12px paragraph gaps and 1.6 line height, 24px
vertical padding for the lower area, 32px between columns, and 12px heading/detail
spacing. Below 768px, stack the lower columns with a 24px gap. Section headings
use the standard 20–26px token and remain smaller than the page H1.
The embed URL defaults to the verified Twiga Towers URL obtained through Google
Maps **Share → Embed a map**. To change the map, paste the iframe's `src` URL
into the separate embed URL setting; clear it to hide the embedded map. Only
HTTPS `www.google.com/maps/embed` URLs render, and no API key is required.
Keep the original Map URL as the destination for the directions link.

All three compact contact/social buttons retain descriptive labels and use the
client-requested platform styling: WhatsApp Green (`#25D366`) with its recognizable
logo and Midnight Navy text/icon for contrast, Facebook blue with a white `f`
logo, and an Instagram purple/pink gradient with a white camera logo. Preserve
accessible contrast and focus states.
The Instagram button alone is an approved exception to the site's general
no-gradient rule.

Code deployment does not overwrite WordPress settings. The confirmed address,
phone and email were saved in HKS Settings during this rebuild; the client's
map URL and business hours were retained. After deploying, check `/about/` and
confirm that no saved Site Editor template overrides the file template.

When adding or removing theme patterns, bump the theme version in `style.css`:
WordPress caches pattern discovery against that version, including after cPanel
file-copy deployments. The About settings reader uses registered SCF field keys
so new field defaults work before their first save; explicitly saved blank
values still hide optional details. Verify the story and map on the deployed
WordPress page, since the static preview does not exercise either behavior.

Public presentation is fail-closed:

- Tour cards, archives, canonical Tour pages, related Tours, and Tour quote panels never render price;
- a Campaign renders its own positive `From KSh… per person` override or inherits the linked Tour amount when blank;
- optional policies and FAQs render only when their public fields are populated;
- Destination guidance renders only when the editor has supplied it; and
- Tour photographs render only when the attachment has useful native alt text.

Shared-link previews use the official 1200 × 630 PNG through Open Graph and Twitter metadata in `inc/Branding.php`. The icons derive from the same approved SVG. Retired logo URLs redirect to their official replacements, and `.cpanel.yml` removes the obsolete files from the deployed theme.
