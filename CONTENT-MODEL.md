# WordPress Content Model

## Architecture Principle

Content structure belongs in a site plugin. Presentation belongs in the custom block theme.

This protects the tour catalogue if the theme changes and makes structured content available to templates, patterns, SEO, analytics, and future integrations.

Use Secure Custom Fields for editor fields. Store field definitions in version control through Local JSON or code. Do not rely only on database-configured fields that cannot be reviewed or reproduced.

## Client-facing Editor Rule

The client editor is a publishing interface, not an audit database. Native WordPress post status is the approval signal:

- Draft content is not approved for public output.
- Publishing by an authorized Holiday Kenya Safaris editor approves the public copy and assigned media.
- Any user who already has the WordPress capability to publish the relevant record is treated as authorized. The simplification does not modify roles, capabilities, or administrative ownership.
- Blank optional fields are omitted with deliberate frontend fallbacks.
- A client-facing field must either render publicly or visibly control public discovery or placement.
- Necessary IDs, attribution values, migration markers, and other system metadata must be generated or hidden.
- Only Campaigns have start and end dates. These are the explicit operational exception to the visible-output rule. Campaign prices are optional, updated manually, and never expire automatically.

This rule does not permit invented facts. Imports remain drafts until an authorized editor reviews and publishes them.

## Content Types

### Tour

The canonical package record. One Tour must feed:

- Tour detail page.
- Catalogue cards.
- Destination listings.
- Related-tour modules.
- Gallery, facts, tabs, itinerary disclosures, and quote panel on the canonical Tour template.
- Campaign landing pages.
- Structured data.
- Analytics parameters.

Do not duplicate a Tour merely to change the marketing angle.

### Campaign

A focused landing-page variant linked to one Tour.

It may override:

- Hero headline and supporting copy.
- Featured hero image.
- Navigation mode.

Campaign start and end dates record the intended operating window. They do not auto-publish or unpublish content. Every Campaign inherits the linked Tour's canonical facts and may add one optional Campaign-specific starting price.

### Testimonial

Optional structured content type when real approved reviews are available.

Do not expose Testimonial fields until a public Testimonial component is implemented. When implemented, keep only values the component displays: reviewer display name, customer type, related Tour, review text, and optional photograph.

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

Destination terms may have SCF fields only for the short public summary, public overview, and hero image currently consumed by the templates.

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

Destination, Tour Type, Occasion/Audience, and Travel Style have canonical public term archives. Empty terms remain absent from public browse controls. Occasion/Audience archives list published Tours only even though Campaigns may also use the taxonomy internally.

## Tour Field Groups

### Native WordPress Tour Fields

| Field | Type | Notes |
|---|---|---|
| Title | Native title | Public Tour title and H1 |
| Short summary | Native excerpt | Catalogue and card summary |
| Overview | Native content | Public Overview section |
| Hero image | Featured image | Card image and first gallery image |
| Publication state | Native post status | Draft is private; Published is editor approval |

### Discovery and Core Package

| Field | Type | Public effect |
|---|---|---|
| Featured status | True/False | Curated, not automatically popularity data |
| Destination | Taxonomy | Breadcrumbs, cards, filters, archives, related Tours |
| Tour Type | Taxonomy | Road safari, flying safari, coast experience, staycation, and other product distinctions |
| Occasion or Audience | Taxonomy | Public browse routes and filters when populated |
| Travel Style | Taxonomy | Public facts and filters when populated |
| Duration display label | Text | Public value such as `3 days / 2 nights` |
| Start location | Text | Public Tour fact and card context |
| End location | Text | Public Tour fact |
| Route summary | Text | Public route, for example `Nairobi -> Maasai Mara -> Nairobi` |
| Transport types | Select/multiple | Public Tour fact; product type still belongs in Tour Type |
| Accommodation basis | Textarea | Public practical detail |
| Meals summary | Text | Public practical detail; avoid unexplained abbreviations |
| Best for | Text | Public practical detail |
| Child suitability | Text | Public practical detail |
| Accessibility notes | Text | Public practical detail |

### Pricing

Tours have no active price field and no public price output. Tour cards, catalogue and taxonomy archives, Destination pages, canonical Tour pages, related-Tour modules, and Tour quote panels do not show either a value or a request-rate fallback.

The legacy `hks_from_price_ksh` Tour meta key remains stored for backward compatibility but is hidden from editors and ignored by frontend templates, analytics, and structured data. Do not delete or migrate it automatically.

### Itinerary

Repeater fields:

- Day number or range.
- Day title.
- Description.
- Main activities.
- Accommodation.
- Meals.

These are the only itinerary row fields in the client editor because they are the values rendered by the canonical Tour and Campaign templates.

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

### Public Package Notes and FAQs

- Public package notes use a simple repeater containing one visible note per row.
- A Tour may select published FAQs. Each FAQ contains only its native title as the question and one public answer field.
- Publishing the Tour, package note, or FAQ is the approval signal. There are no per-row source, status, checked-date, or expiry fields.
- Unknown policy, safety, deposit, cancellation, or legal information stays blank; publishing is not permission to invent it.

### Media and Rights

- Use the native featured image as the Tour hero and card image.
- Use one ordered Tour gallery for the remaining public gallery images.
- Require useful native WordPress alt text for accessibility.
- Use the native public caption only when a visible photo credit is required.
- Do not expose owner, source, permission status, usage scope, license, evidence, granted date, checked date, expiry date, credit-required status, or internal rights-note fields.
- Uploading or assigning an image to published content is the authorized editor's approval for website use. Imported media must not be assigned to published content automatically.

### Conversion

- Featured FAQ relationship, because selected FAQs render publicly.

The Tour title supplies the WhatsApp package label. The canonical CTA, quote-panel heading, supporting copy, and intake behavior are template-controlled. Do not expose Tour-level overrides until a public template actually consumes them.

The Group Travel planner adds no Tour editor fields. It queries published Tours and their existing Destination assignments, uses the chosen Tour as the signed package context, and derives the private inquiry Destination from that Tour. Inquiry records may store `_hks_inquiry_route` and `_hks_inquiry_destination` for administrator triage; neither value is public or editor-maintained Tour data.

### Presentation and Relationships

- Related Tours are derived automatically from shared destinations, with a catalogue fallback.
- Catalogue cards use the native excerpt and featured image.
- Homepage priority uses the Featured Tour control and WordPress ordering.

The title band, gallery mosaic, desktop tabs, mobile disclosures, itinerary behavior, and sticky quote-panel structure are template-controlled. Do not expose unrestricted per-Tour layout builders for canonical facts.

## Campaign Fields

- Native Campaign title and publication state.
- Linked Tour relationship, required and visible through inherited Tour content.
- Hero headline.
- Supporting copy.
- Featured image as the Campaign hero override.
- From price per person (KSh), optional and stored as `hks_campaign_from_price_ksh`.
- Navigation: full, reduced, or campaign-minimal.
- Campaign start date and Campaign end date.

Campaigns inherit the linked Tour facts, itinerary, inclusions, and exclusions. A positive Campaign price renders as `From KSh X per person` on that Campaign only; blank or zero produces no price output. Campaign dates record the planned campaign window only; they do not auto-publish, unpublish, expire, or alter the Campaign price. Campaign attribution and default indexing behavior are generated from the Campaign ID, slug, and template rather than requested as client fields.

Use the Campaign price only when one figure can truthfully represent a per-person starting price and price is a useful selling point for that focused offer. Never auto-convert a USD amount, copy a legacy Tour price, or seed an unconfirmed public price.

## Destination and FAQ Editors

Destination terms expose only public values consumed by the current templates:

- Native term name.
- Short public summary.
- Public overview.
- Hero image.

FAQ records expose only the native title as the question, the public answer, and native publication state. Destination and FAQ source URLs, confirmation statuses, checked dates, expiry dates, and internal notes are removed from the client workflow. Published means approved; blank means omitted.

## Global Settings

Use an options page or equivalent for:

- Exact company name.
- Operator disclosure.
- WhatsApp number.
- Phone and email.
- Address and map.
- Business hours and response expectation.
- Social links.
- Default CTA wording when the template exposes a global override.
- Legal/policy page relationships.
- Meta and GA IDs or integration settings.
- Default logo assets.

Use native WordPress Navigation for the approved primary structure in `WEBSITE-STRUCTURE.md`. Populate dropdown routes only when corresponding taxonomy archives or pages contain approved public content. Do not hard-code an empty menu item merely because it appears in the design reference.

Do not pair public settings with confirmation-status, source, or checked-date fields. Blank settings remain omitted. Project-level decisions that still require approval belong in `CLIENT-CONFIRMATIONS.md`, not in duplicate WordPress controls.

## Backward Compatibility and Migration

The simplified editor must not rename custom post types, taxonomies, existing SCF field keys that remain in use, inquiry records, or Campaign relationships.

- Keep legacy Tour price metadata, including `hks_from_price_ksh`, in the database but do not register it in the client editor or render it publicly.
- Add `hks_campaign_from_price_ksh` as a separate Campaign field. Do not copy, inherit, or migrate a legacy Tour amount into it automatically.
- Stop reading legacy price status, checked date, valid-until date, assumptions, seasonal rates, supplements, and disclaimer values on the frontend.
- Keep legacy metadata in the database during the MVP migration; hide it from the editor and ignore it at render time. Do not delete it in a destructive upgrade.
- Remove source/status/date gates from published Tours, Destinations, FAQs, package notes, and assigned media. Native publication state and deliberate assignment become the gates.
- Existing published content may therefore reveal assigned media, FAQ answers, Destination copy, or package notes that older status gates suppressed. This is an intended consequence of the client's publish-as-approval decision.
- Tour cards, canonical Tour quote panels, Tour structured data, and Tour analytics omit price entirely. Campaign templates may render their own positive Campaign price; blank Campaign prices are omitted.
- Campaign analytics may derive `price_display` as `from_price` when the Campaign field is populated; it does not read or transmit legacy Tour price metadata or a price-status field.
- Campaign start and end dates remain intact. All Tour, price, source, media-rights, FAQ, and policy checked/validity dates become ignored legacy metadata.
- Clear relevant WordPress and host caches after deployment so old gated output is not served.

## Editorial Safeguards

- Lock critical template structure but allow approved content regions.
- Use short help text that explains exactly where each field appears publicly.
- Validate the optional Campaign price as a positive whole KSh amount when populated.
- Do not require source, confirmation, price-assumption, rights, or validity metadata to publish.
- Keep original Ashford source values in repository import files when useful; do not require the client to maintain them in WordPress.
- Avoid free-form flexible content for canonical tour facts. Use it only for optional campaign storytelling modules.
- Use the featured image plus gallery when available; otherwise render deliberate single-image or no-image fallbacks without empty tiles.
- Do not allow a permanent booking-form block in the canonical Tour quote-panel area.

## REST Readiness

Register public custom post types with `show_in_rest` so they work with the block editor and remain available to future integrations. This does not require a headless frontend.
