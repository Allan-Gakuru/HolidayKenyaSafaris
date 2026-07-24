# Phase 6: Public MVP templates

HKS Wayfinder 0.5.0 now provides reusable public templates for:

- the homepage;
- the Tour catalogue at `/tours/`;
- canonical Tour pages;
- focused Campaign pages; and
- Destination taxonomy archives.

The homepage and catalogue use the same Tour-card block, including an optional
editable Tour starting price. A Campaign
changes its headline, supporting copy, hero, navigation treatment, and optional
Campaign price while inheriting the linked Tour's itinerary, logistics,
inclusions, policies, and FAQs.

## Public-data gates

The dynamic theme renderer fails closed:

- Tours, Tour cards, archives, Destination results, related Tours, and canonical
  Tour quote panels render `From KSh X per person` only when the Tour has a positive amount.
- A populated Campaign price overrides its linked Tour price; otherwise the linked
  Tour price may be inherited. When both are blank, price output is omitted.
- Policies, FAQs, and Destination guidance render only when their current public
  fields are populated on published content.
- Photographs render only when deliberately assigned and supplied with useful
  native alt text.

The first seeded records have no photographs or Campaign prices, so the initial
pages use deliberate no-image and no-price states.

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
4. publish one reviewed Campaign and confirm it inherits canonical facts, omits a
   blank price, renders its own populated price, and emits `noindex` when selected;
5. test all quote CTAs, keyboard order, dialog focus, browser back behavior, and
   safe-area spacing; and
6. inspect page source and REST output to confirm only the active Tour/Campaign
   price fields are public and private inquiry data remains absent.

Run `python tools/validate_public_templates.py` before every template push.
