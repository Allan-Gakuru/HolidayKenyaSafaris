# WordPress Content Model

## Architecture Principle

Content structure belongs in a site plugin. Presentation belongs in the custom block theme.

This protects the tour catalogue if the theme changes and makes structured content available to templates, patterns, SEO, analytics, and future integrations.

Use Secure Custom Fields for editor fields. Store field definitions in version control through Local JSON or code. Do not rely only on database-configured fields that cannot be reviewed or reproduced.

## Content Types

### Tour

The canonical package record. One Tour must feed:

- Tour detail page.
- Catalogue cards.
- Destination listings.
- Related-tour modules.
- Campaign landing pages.
- Structured data.
- Analytics parameters.

Do not duplicate a Tour merely to change the marketing angle.

### Campaign

A focused landing-page variant linked to one Tour.

It may override:

- Hero headline and supporting copy.
- Target avatar or occasion.
- Problem, desire, objective, or objection emphasis.
- Hero and proof imagery.
- Proof order.
- FAQ emphasis.
- CTA copy.
- Navigation mode.
- Campaign tracking label.

It must inherit canonical facts unless an explicitly approved campaign-specific package changes the product itself.

### Testimonial

Optional structured content type when real approved reviews are available.

Fields should include source, date, reviewer display name, customer type, related tour, review text, photograph permission, and verification status.

Do not seed invented testimonials.

### Team Member or Guide

Optional later content type for verified guide and staff profiles. It is not required for the first build unless the client supplies approved information.

## Taxonomies

### Destination

Examples:

- Maasai Mara.
- Amboseli.
- Nairobi.
- Naivasha.
- Nakuru.
- Ol Pejeta.
- Samburu.
- Tsavo.
- Mount Kenya.
- Mombasa and coast.

Destination terms may have SCF fields for overview, hero image, best-time guidance, travel time, map context, and SEO copy.

### Tour Type

- Road safari.
- Flying safari.
- Day excursion.
- Coast experience.
- Staycation.
- Trek or adventure.
- Group package.
- Corporate or MICE.
- Educational trip.

### Occasion or Audience

Use for discovery and internal campaign organization, not as a rigid identity system:

- Couples.
- Families.
- Friends.
- Birthdays and anniversaries.
- School holidays.
- Chamas, churches, and SACCOs.
- Schools and youth.
- Corporate teams.
- Premium private travel.
- Adventure and special interest.

### Travel Style

- Private.
- Group joining.
- Resident package.
- Luxury.
- Family friendly.
- Short break.
- Active.

Only expose filters that contain enough tours to be useful.

## Tour Field Groups

### Identity and Source

| Field | Type | Notes |
|---|---|---|
| Internal product ID | Text | Stable identifier independent of post ID |
| Source URL | URL | Ashford product page or client source |
| Source checked date | Date | Last factual verification |
| Source status | Select | imported, reviewed, client confirmed, archived |
| Public title | Text | Local-facing title |
| Original Ashford title | Text | Internal reference |
| Short summary | Textarea | Factual listing summary |
| Featured status | True/False | Curated, not automatically popularity data |

### Core Package

| Field | Type | Notes |
|---|---|---|
| Duration days | Number | Structured filtering |
| Duration nights | Number | Structured filtering |
| Duration label | Text | Display value such as `3 days / 2 nights` |
| Start location | Text or taxonomy | Usually Nairobi for initial products |
| End location | Text | May differ from start |
| Route summary | Text | Example: Nairobi -> Maasai Mara -> Nairobi |
| Transport type | Select/multiple | Safari van, Land Cruiser, flight, bus, other |
| Trip style | Taxonomy | Private, group joining, family, etc. |
| Minimum group size | Number | Must be confirmed |
| Maximum group size | Number | Must be confirmed where relevant |
| Resident basis | Select | Kenyan citizen, resident, non-resident, mixed, confirm |
| Accommodation basis | Text | Named or tiered options |
| Meals summary | Text | Avoid unexplained abbreviations publicly |

### Pricing

| Field | Type | Notes |
|---|---|---|
| Price display mode | Select | from price, request current rate, hidden |
| From price KSh | Number | Placeholder or confirmed value |
| Price status | Select | placeholder, converted estimate, operator reviewed, client confirmed, expired |
| Price checked date | Date | Required for confirmed rates |
| Price basis summary | Text | Example: per adult sharing, two travelers, resident rate |
| Seasonal rates | Repeater | season name, dates, residency, transport, room basis, adult, child, single supplement, status |
| Mandatory supplements | Repeater | event/season, amount, unit, dates |
| Price disclaimer | Textarea | Visible, plain-language assumptions |

Only `client confirmed` prices may be described as current confirmed rates.

### Itinerary

Repeater fields:

- Day number or range.
- Day title.
- Origin and destination.
- Description.
- Main activities.
- Accommodation.
- Meals.
- Approximate departure or drive time, when confirmed.
- Optional notes.

### Inclusions and Exclusions

Use repeaters or structured lists rather than one uneditable text blob.

Inclusion fields may cover:

- Transport.
- Guide/driver.
- Park fees and exact basis.
- Accommodation.
- Meals.
- Game drives or activities.
- Drinking water.
- Pickup/drop-off.
- Taxes.

Exclusion fields may cover:

- Personal expenses.
- Tips.
- Optional activities.
- Drinks.
- Insurance.
- Visa or flight items where relevant.
- Supplements.

### Suitability and Policies

- Best for.
- Physical difficulty.
- Child suitability and provisional child policy.
- Accessibility notes.
- Packing guidance.
- Weather or season notes.
- Cancellation summary.
- Deposit summary.
- Required documents.
- Safety notes.

Policy fields remain unpublished or flagged until client-confirmed.

### Media and Rights

- Hero image.
- Gallery.
- Vehicle image.
- Accommodation images.
- Route or map image.
- Image alt text.
- Source, owner, permission, and current usage notes per asset.
- Credit requirement.

Media metadata is informational and editorial. Do not implement automatic template rejection based on a status label; editors control which media is assigned and published.

### Conversion

- CTA label override.
- WhatsApp package label.
- Optional additional intake questions.
- Consultant routing label, if later required.
- Featured FAQ relationship.

## Campaign Fields

- Campaign name and internal label.
- Linked Tour relationship, required.
- Target audience or occasion.
- Primary desire.
- Primary problem.
- Primary objective.
- Primary objection.
- Hero headline.
- Supporting copy.
- Hero image override and rights status.
- Short proof-point repeater.
- Trust-module selection.
- FAQ selection or overrides.
- CTA label.
- Navigation: full, reduced, or campaign-minimal.
- Campaign status: draft, testing, active, paused, archived.
- Meta/analytics campaign label.
- Optional noindex toggle for temporary variants.

## Global Settings

Use an options page or equivalent for:

- Exact company name.
- Operator disclosure.
- WhatsApp number.
- Phone and email.
- Address and map.
- Business hours and response expectation.
- Social links.
- Default CTA wording.
- Global price disclaimer.
- Legal/policy page relationships.
- Meta and GA IDs or integration settings.
- Default logo assets.

Each setting needs a confirmation status where the source is not yet final.

## Editorial Safeguards

- Lock critical template structure but allow approved content regions.
- Use help text that explains price and rights statuses.
- Prevent publishing a Tour with a placeholder price that is labeled confirmed.
- Warn when a source checked date or confirmed price is stale.
- Keep original Ashford source values for audit, even when public copy is rewritten.
- Avoid free-form flexible content for canonical tour facts. Use it only for optional campaign storytelling modules.

## REST Readiness

Register public custom post types with `show_in_rest` so they work with the block editor and remain available to future integrations. This does not require a headless frontend.
