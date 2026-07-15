# Ashford Content Source and Migration Rules

## Relationship

Holiday Kenya Safaris is a local-market brand operated by Ashford Tours & Travel. Ashford's approved Kenya product information is the starting source for the catalogue.

This relationship provides the factual starting point. Imported material remains draft. An authorized Holiday Kenya Safaris editor approves public copy, media, and the one manually entered KSh price by publishing the record; no extra confirmation fields are required.

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
- Coast packages and staycations supplied or published by the client.
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
3. Record the source URL in the repository import manifest when an audit trail is useful; do not add it to the client Tour form.
4. Extract factual duration, route, itinerary, accommodation, meals, inclusions, exclusions, and transport. Do not import a public price automatically.
5. Flag contradictions, malformed copy, missing days, mixed rates, and questionable values.
6. Decide whether the product is relevant to local Kenyan buyers.
7. Rewrite the title, summary, and persuasive copy without changing facts.
8. Convert abbreviations such as `BB`, `L`, `D`, and `LB` into plain language for public display.
9. Import the Tour as a draft with remote media unassigned.
10. Leave unclear information blank and flag it in the import manifest or handoff notes.
11. Let an authorized editor upload or assign the intended media, enter the optional KSh per-person starting price, and publish. Publication is approval.

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
- Each Tour has one optional manually maintained `From price per person (KSh)` value.
- Never silently convert USD and publish the result as a current operator rate.
- Do not import Ashford USD or seasonal rates into the public price field. The client enters the KSh value directly.
- A positive value renders as `From KSh X per person`; a blank value renders as `Request current KSh rate`.
- Do not create status, season, residency, group-size, transport, accommodation, inclusion, validity, supplement, single, adult, or child price fields.
- If a package cannot honestly be summarized by one per-person starting price, leave it blank and request the current rate.
- Tour prices do not expire automatically. The client changes or removes them manually. Campaign dates never alter the linked Tour price.

## Photograph Rules

Do not add rights-status or source-audit fields to the media editor. Media uploaded or deliberately assigned to published content by an authorized editor is treated as approved for website use. Imported or scraped remote media must remain unassigned until the editor selects it. Require useful native alt text and use a native caption only when a public credit must display.

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
