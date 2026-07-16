# Phase 6 and 7 Delivery

Date: 2026-07-15

## Phase 6: Templates and Pages

The existing theme already contains the global header, footer, homepage, Tour catalogue,
Destination archive, canonical Tour, Campaign, search, blog, single-post, and 404 templates.

This phase adds the remaining standard-page system:

- a compact Wayfinder title band for WordPress Pages;
- responsive editorial layouts for About, Group Travel, and Contact content;
- a published Group Travel route that automatically replaces the homepage anchor in the
  header and homepage section;
- seven protected Page drafts: About, Group Travel, Contact, Privacy Policy, Website Terms,
  Booking Terms, and Cancellation and Refund Policy.

The About and Group Travel drafts use only confirmed product and operator context. Contact
contains no invented phone, email, address, hours, or response promise. Legal drafts are
empty by design and must remain drafts until approved legal text is supplied.

## Phase 7: Catalogue Migration

The reviewed Ashford crawl contains 44 unique local candidates. The migration excludes:

- the three products already represented by the MVP Tour records; and
- `African-wildlife-safari`, a generic multi-country marketing page rather than a quotable
  local Tour.

The 40 remaining records are available in four controlled batches:

| Batch | Family | Drafts |
|---|---|---:|
| 1 | Road safaris | 12 |
| 2 | Flying safaris and Mount Kenya | 10 |
| 3 | Nairobi excursions | 14 |
| 4 | Mombasa excursions | 4 |

Each record keeps the Ashford title and source URL, adds a concise source-derived catalogue
summary, maps existing Tour taxonomies, and carries over the duration and route outline when
the crawl contains them. The importer does not assign price, media, policies, inclusions,
exclusions, reviews, or availability.

## After Deployment

1. In WordPress, open **Tours → Import site drafts**.
2. Run **Create or refresh site page drafts**.
3. Review and publish About and Group Travel when their copy is satisfactory.
4. Keep Contact and all four legal pages in Draft until their missing client information is
   supplied.
5. Import one catalogue batch at a time.
6. Review each imported Tour, add approved photographs, complete any missing displayed fields,
   preview it, then publish it. Tours do not carry prices.
7. Add an optional `From KSh` per-person price only to a focused Campaign where price is a
   deliberate selling point.
8. Check catalogue filters and internal links after each published batch before importing the
   next one.

Re-running a batch is safe while its records remain drafts. As soon as an editor publishes,
privatizes, schedules, or otherwise moves a seeded record beyond Draft, the importer protects
that record and will not overwrite it.

## Deliberately Deferred

- Contact details and legal text still require the client.
- Imported catalogue media requires editor input. Campaign prices remain blank until deliberately entered.
- Inquiry submission and WhatsApp launch testing was explicitly skipped for this phase.
