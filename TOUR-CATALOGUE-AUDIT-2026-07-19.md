# Tour Catalogue Audit — 19 July 2026

## Scope and method

This audit covers the 43 Tours published on `holidaykenyasafaris.ke` after the controlled Phase 7 import and editor publication. It compares public WordPress REST output with the reviewed `mvp-seed.json` and `catalogue-seed.json` manifests, checks current Ashford source availability, measures visible field and taxonomy coverage, and reviews the assigned local media manifest.

No missing facts were invented. Blank optional values remain blank and are omitted or handled by the documented frontend fallbacks.

## Catalogue integrity

- 43 of 43 retained Tours are published.
- 43 of 43 published slugs and titles map to the reviewed local manifests.
- All 43 current Ashford source URLs returned HTTP 200 during the audit.
- Source duration text remained present on every source page where a duration was recorded in the reviewed crawl.
- Live taxonomy assignments, field-row counts, and itinerary day titles match the reviewed manifests.
- Every Tour has a non-empty title, excerpt, overview, start location, route summary, Destination, Tour Type, Travel Style, and featured image.
- No numeric Tour price or Tour price field is rendered by the current frontend.

## Visible field coverage

| Public value | Tours populated | Tours blank | Effect |
|---|---:|---:|---|
| Title, excerpt and overview | 43 | 0 | Catalogue and Tour introductions are present. |
| Start location and route summary | 43 | 0 | Every card has useful departure and route context. |
| Destination | 43 | 0 | Destination discovery and related-Tour queries work. |
| Tour Type | 43 | 0 | Every Tour has a product-format archive route. |
| Travel Style | 43 | 0 | Every Tour has a travel-style archive route. |
| Featured image | 43 | 0 | No Tour relies on the no-image card fallback. |
| Duration display label | 35 | 8 | Eight cards and fact panels omit duration. |
| End location | 28 | 15 | The fact is omitted when unavailable. |
| Day-by-day itinerary | 25 | 18 | Eighteen Tours use the transparent no-public-itinerary fallback. |
| Inclusion/exclusion detail | 2 | 41 | Most Tours use the transparent current-package-breakdown fallback. |
| Occasion/Audience | 3 | 40 | Occasion discovery is valid but intentionally sparse. |
| Additional gallery media | 2 | 41 | Most Tours display the featured-image gallery fallback only. |

The eight Tours without a structured duration label are:

- Tamarind Evening Dhow — Mombasa
- Dolphin Safaris, Snorkeling, Seafood Lunch
- Kazuri Beads Factory and Pottery Centre, Karen Blixen and the Giraffe Centre Tour
- Hot Air Balloon Safari
- Hell’s Gate National Park Tour
- Farm Tours Kenya — Coffee, Tea and Agricultural Farm Experiences
- Crescent Island Game Conservancy Tour Naivasha
- Crocodile Farm Nairobi — Visit Nairobi Mamba Village Tour

## Taxonomy coverage

### Tour Type

| Term | Published Tours |
|---|---:|
| City Excursion | 1 |
| Coast Experience | 4 |
| Day Excursion | 14 |
| Flying Safari | 5 |
| Road Safari | 14 |
| Trekking & Adventure | 5 |

### Occasion or Audience

| Term | Published Tours |
|---|---:|
| Couples | 2 |
| Family Break | 2 |
| Family Outing | 1 |
| Friends | 2 |
| Short Notice | 1 |
| Visiting Friends and Relatives | 1 |

### Travel Style

| Term | Published Tours |
|---|---:|
| Day Excursion | 9 |
| Day Trip | 3 |
| Flying Safari | 5 |
| Half Day | 7 |
| Multi-day Adventure | 5 |
| Multi-day Safari | 5 |
| Private Quote | 3 |
| Short Break | 9 |
| Trekking | 5 |

The public archive implementation exposes only populated terms through existing browse controls. Thin Occasion/Audience terms remain valid public routes but should not be promoted as primary navigation until more Tours are deliberately classified.

## Copy findings

- No Tour has an empty excerpt or overview.
- The catalogue copy scan found no repeated generic-superlative or tourism-poster cliché pattern.
- The 40 controlled catalogue imports are one editorial sentence behind the current local seed: live overview copy says the quote confirms the “current KSh rate and final package details,” while the current seed removes the rate reference. This is not a numeric price leak, but it is stale copy under the price-free Tour policy and should be updated through an authorized content edit.
- The protected importer correctly does not overwrite those published Tours.

## Media findings

- The local manifest contains 45 files across 43 Tour folders; one Tour has three source images and the others have one.
- Every live Tour has assigned featured media with non-empty alt text, so the media fail-closed guard permits display.
- Current alt text generally repeats the Tour title. It satisfies the display guard but should be replaced over time with literal descriptions of what each photograph shows.
- Fourteen source images are below either 1200 pixels wide or 700 pixels high. They remain usable for cards, but should be checked visually before using them as full-width campaign heroes.
- Three exact source-image duplicates are assigned across six Tours:
  - Wings over Samburu and the eight-day Aberdares/Samburu/Ol Pejeta/Lake Nakuru/Maasai Mara package.
  - Mount Kenya six-day Land of Contrast and Mount Kenya five-day Sirimon–Naromoru.
  - Nairobi National Park Tour and Nairobi Safari Walk Day Tour.

These duplicates do not break rendering, but the Safari Walk/National Park pairing is the strongest candidate for a more specific replacement image.

## Archive implementation completed

- Tour Type is public at `/tour-types/{term}/`.
- Occasion/Audience is public at `/occasions/{term}/`.
- Travel Style is public at `/travel-styles/{term}/`.
- Each route has a compact breadcrumb/title band, one H1, a server-rendered introduction, responsive Tour cards, pagination, an empty state, and a return route to the full catalogue.
- Occasion archives are explicitly restricted to published Tours so Campaign records cannot leak into the catalogue.
- The plugin upgrade schedules one soft rewrite-rule refresh after deployment.

## Editorial follow-up

1. Add the eight missing structured durations from an approved source.
2. Prioritize itinerary and inclusion/exclusion detail for Tours used in ads or homepage promotion.
3. Replace the stale rate-reference sentence on the 40 protected published imports.
4. Add Occasion/Audience terms only where they meaningfully improve discovery.
5. Replace title-only alt text and the three duplicate hero assignments as more specific approved media becomes available.
