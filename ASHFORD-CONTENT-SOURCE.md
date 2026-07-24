# Ashford Content Source and Migration Rules

## Relationship

Holiday Kenya Safaris is a local-market brand operated by Ashford Tours & Travel. Ashford's Kenya and international product information is the starting source for the expanded catalogue.

This relationship provides the factual starting point. The client has explicitly authorized the current remaining-Ashford-catalogue migration to reuse Ashford images and itinerary copy, assign the converted Tour starting price, and publish directly. That authorization is limited to this migration; unclear, contradictory, or unpriceable records remain draft.

## Workspace Catalogue

The workspace contains a public Ashford crawl dated 2026-07-02:

- `work/ashford_crawl/catalog.json`
- `work/ashford_crawl/catalog.txt`
- `outputs/Ashford Tours Public Catalog Summary.md`
- `outputs/Ashford Tours Public Catalog Table.csv`

The crawl contains 65 records. There are 45 local-category assignments and 44 unique pages across:

- 14 Kenya Safaris.
- 5 Kenya Flying Safaris.
- 16 Nairobi Excursions.
- 5 Mombasa Excursions.
- 5 Mount Kenya products.
- One excursion appears in both Nairobi and Mombasa categories.

These records and the current live Ashford international catalogue are candidates for the authorized expansion. Re-check the live source before import.

## Included Product Policy

Candidate for Holiday Kenya Safaris:

- Kenya road safaris.
- Kenya flying safaris that can credibly serve premium local travelers.
- Nairobi and nearby day excursions.
- Mombasa and coast excursions.
- Mount Kenya and suitable adventure products.
- Coast packages and staycations supplied or published by the client.
- Group, family, couple, school, chama, church, SACCO, corporate, or special-interest variants based on real operational packages.
- International holidays currently published by Ashford that contain a usable itinerary, source images, and a low-season per-person price.

Exclude by default:

- Visa services.
- Standalone transfers.
- Hotel listings that are not packaged as a relevant local experience.
- Products with contradictory or unusable public pricing until reviewed.

## Initial Seed Priority

Start with three representative funnels:

### 1. Maasai Mara Classic Safari

- Product: 3 Days / 2 Nights Maasai Mara Road Safari.
- Source: `https://ashford-tours.com/product/3-days-2-nights-maasai-mara-road-safari/`
- Why first: iconic short safari, strong fit for couples, families, premium resident explorers, and Nairobi short-break buyers.
- Existing prototype: `outputs/holiday-kenya-safari-maasai-mara.html`.

### 2. Nairobi Quick Safari

- Product: Nairobi National Park Tours - 4 hours.
- Source: `https://ashford-tours.com/product/nairobi-national-park-tours-4-hours/`
- Why first: low time commitment and broad relevance to families, residents, schools, local groups, and visiting relatives.

### 3. Amboseli Scenic Safari

- Product: 3 Days / 2 Nights Amboseli Safari Package.
- Source: `https://ashford-tours.com/product/3-days-2-night-amboseli-safari-package/`
- Why first: recognizable elephants and Kilimanjaro promise, compact duration, and strong couple/family/premium appeal.

This ranking is a hypothesis, not confirmed sales data. See:

`outputs/Ashford Tours Top 3 Likely Best Selling Packages Hypothesis.docx`

## Migration Process

For every candidate Tour:

1. Open the current Ashford product page.
2. Compare it with the workspace crawl.
3. Record the source URL in the repository import manifest when an audit trail is useful; do not add it to the client Tour form.
4. Extract factual duration, route, itinerary, accommodation, meals, inclusions, exclusions, transport, and the source low-season per-person price.
5. Flag contradictions, malformed copy, missing days, mixed rates, and questionable values. Reject placeholder or deposit-like WooCommerce values that are not credible per-person Tour prices.
6. Decide whether the product is relevant to local Kenyan buyers.
7. Preserve the Ashford title and itinerary wording as literally as makes sense. Remove only source-site debris, malformed fragments, contradictions, or wording that would mislead an HKS visitor.
8. Convert abbreviations such as `BB`, `L`, `D`, and `LB` into plain language for public display.
9. Assign the Tour Scope and Destination terms. Reuse and assign the corresponding Ashford images with useful native alt text.
10. Convert the source low-season per-person amount—or a clearly published per-person starting amount where no seasonal table exists—using the live USD/KSh rate on the import date, round upward to the next KSh 500, store the result in `hks_from_price_ksh`, and record the conversion evidence in the repository import manifest.
11. Publish directly under the client's authorization when the source is coherent. Leave unclear or contradictory information blank; keep the affected Tour draft when the uncertainty changes the product or price materially.

If the build starts materially after the crawl date, re-crawl or manually verify the included source pages. Do not assume the 2026-07-02 extraction is still current.

## Factual Reuse vs Rewrite

Retain as factual source material:

- Duration.
- Route.
- Day sequence.
- Named accommodation options.
- Meal basis.
- Transport options.
- Included and excluded items.
- Operational contact and company information that remains current.

Rewrite only where required for an accurate HKS presentation:

- Product title when needed for clarity.
- Hero headline.
- Short summary.
- Destination story.
- Audience and occasion framing.
- Trust explanation.
- FAQ wording.
- CTA and WhatsApp copy.

Do not copy navigation debris, SEO boilerplate, unrelated category text, or malformed crawl fragments.

## Pricing Rules

- Show KSh primarily.
- Tours have one editable `From price per person (KSh)` field.
- For this authorized migration, use the source low-season per-person amount, or a clearly credible published starting amount where no seasonal table exists, and the live USD/KSh rate checked on the import date.
- Always round the converted amount upward to the next KSh 500: `ceil(converted / 500) * 500`.
- Record the source URL, source currency, source amount, low-season basis, exchange-rate source, rate, conversion date, unrounded result, and rounded KSh result in the repository manifest.
- A positive Tour value renders as `From KSh X per person` across Tour discovery and detail surfaces; blank or zero renders nothing.
- Do not create client-facing status, season, residency, group-size, transport, accommodation, inclusion, validity, supplement, single, adult, or child price fields.
- Campaigns retain a separate optional override. A blank Campaign value may inherit the Tour starting price.
- Prices do not expire automatically. The client changes or removes them manually. Campaign dates never alter either price field.

## Photograph Rules

Do not add rights-status or source-audit fields to the media editor. Media uploaded or deliberately assigned to published content by an authorized editor is treated as approved for website use. For the current client-authorized Ashford expansion, corresponding Ashford media may be downloaded, imported, assigned, and published directly. This exception does not authorize unrelated third-party media or future unattended imports. Require useful native alt text and use a native caption only when a public credit must display.

AI-generated imagery may be used for clearly conceptual presentations, but live product pages should use approved real destination and product photographs wherever buyers need to inspect the actual experience.

## Company and Trust Information

Information may be taken from the current Ashford website only when it is relevant, current, and verifiable. Examples include operator history, contacts, address, services, and published memberships.

Do not infer:

- Licenses or memberships.
- Fleet ownership.
- Number of years in business.
- Insurance coverage.
- Guide credentials.
- Response times.
- Refund terms.
- Customer counts.

Keep supporting sources in repository research or handoff notes when useful; do not require source and checked-date fields in WordPress. Publishing a trust claim is the editor's approval, but it remains prohibited to invent one.

## No-Hallucination Rule

When the source is unclear, keep the Tour in draft or omit the claim and note the question outside the public editor. Do not fill the gap with a plausible-sounding industry standard. Publishing simplifies approval workflow; it does not weaken the no-hallucination rule.
