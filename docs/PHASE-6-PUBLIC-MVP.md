# Phase 6: Public MVP templates

HKS Wayfinder 0.2.0 now provides reusable public templates for:

- the homepage;
- the Tour catalogue at `/tours/`;
- canonical Tour pages;
- focused Campaign pages; and
- Destination taxonomy archives.

The homepage and catalogue use the same Tour-card block. A Campaign changes its
headline, supporting copy, CTA, navigation treatment, and attribution label while
inheriting the linked Tour's itinerary, price state, logistics, inclusions,
policies, and FAQs.

## Public-data gates

The dynamic theme renderer fails closed:

- `From KSh` appears only with a positive amount, acceptable status, checked date,
  live validity, full assumptions, public basis, and disclaimer. Otherwise the
  page says `Request current KSh rate`.
- Policies and FAQs require an acceptable confirmation state, source reference,
  checked date, live validity, and sentinel-free public copy.
- Destination guidance requires a reviewed or client-confirmed source, checked
  date, and source URL or reference. An unaudited term shows only its name and
  published Tour results.
- Photographs render only with acceptable permission, `website` usage scope,
  rights checked date, live permission, descriptive alt text, and any required
  credit line.

The first seeded records have no photographs or approved KSh amounts, so the
initial pages intentionally use a quiet typographic hero and request-current-rate
copy.

## Template behavior

- Tour and Campaign pages place the saved inquiry CTA above the detail and again
  at the end.
- Campaign `noindex` governance is emitted through `wp_robots` without inventing
  an SEO plugin dependency.
- `campaign_minimal` hides main navigation while retaining the brand and skip link.
- All primary templates expose `#main-content`, keyboard focus styles, reduced
  motion handling, responsive grids, and non-obscuring CTAs.
- The header ships only Home and Tours until supporting pages are approved.

## cPanel/browser verification

After deployment and activation:

1. run the MVP importer and review/publish the three Tours;
2. confirm the homepage shows the three published cards and no broken image area;
3. confirm `/tours/`, each Tour, and each Destination term at mobile and desktop;
4. publish one reviewed Campaign and confirm it inherits canonical facts and emits
   `noindex` when selected;
5. test all quote CTAs, keyboard order, dialog focus, browser back behavior, and
   safe-area spacing; and
6. inspect page source and REST output for private source, policy, price, rights,
   and inquiry data.

Run `python tools/validate_public_templates.py` before every template push.
