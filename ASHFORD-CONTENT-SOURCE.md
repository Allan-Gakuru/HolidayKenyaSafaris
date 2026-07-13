# Ashford Content Source and Migration Rules

## Relationship

Holiday Kenya Safaris is a local-market brand operated by Ashford Tours & Travel. Ashford's approved Kenya product information is the starting source for the catalogue.

This relationship allows reuse only within the client's authorization. It does not remove the need to verify rates, availability, photographs, policies, and public wording.

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

These are candidates, not an automatic publish queue.

## Included Product Policy

Candidate for Holiday Kenya Safaris:

- Kenya road safaris.
- Kenya flying safaris that can credibly serve premium local travelers.
- Nairobi and nearby day excursions.
- Mombasa and coast excursions.
- Mount Kenya and suitable adventure products.
- Confirmed coast packages and staycations supplied by the client.
- Group, family, couple, school, chama, church, SACCO, corporate, or special-interest variants based on real operational packages.

Exclude by default:

- Tanzania-only products.
- Kenya and Tanzania combinations aimed primarily at inbound international visitors, unless the client approves a local-market version.
- International holidays.
- Visa services.
- Standalone transfers.
- Hotel listings that are not packaged as a relevant local experience.
- Products with clearly implausible or incomplete public pricing until reviewed.

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
3. Record source URL and checked date.
4. Extract factual duration, route, itinerary, accommodation, meals, inclusions, exclusions, transport, and published rates.
5. Flag contradictions, malformed copy, missing days, mixed rates, and questionable values.
6. Decide whether the product is relevant to local Kenyan buyers.
7. Rewrite the title, summary, and persuasive copy without changing facts.
8. Convert abbreviations such as `BB`, `L`, `D`, and `LB` into plain language for public display.
9. Assign price and photograph verification statuses.
10. Request client confirmation for unresolved operational information.
11. Publish only when the required facts and rights are approved.

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
- Published seasonal structure.
- Operational contact and company information that remains current.

Rewrite:

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
- Current values are placeholders until client-confirmed.
- Never silently convert USD and publish the result as a current operator rate.
- A planning conversion may exist internally with `converted estimate` status.
- Every displayed `From KSh...` value must expose its assumptions.
- Where public prices are mixed, implausible, or incomplete, display `Request current rate` or keep the Tour in draft.
- Store seasonal supplements and single/child rates separately.

## Photograph Rules

Record the provenance and current usage decision for each image:

- Client supplied and approved.
- Ashford-owned and client-confirmed for Holiday Kenya Safaris use.
- Properly licensed stock or commissioned work with recorded license.
- Other source or usage notes supplied by the client.

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

Record the exact source and checked date for every trust claim.

## No-Hallucination Rule

When the source is unclear:

- Mark the field `CLIENT CONFIRMATION REQUIRED`.
- Keep the Tour in draft or omit that claim.
- Do not fill the gap with a plausible-sounding industry standard.
