# Holiday Kenya Safaris Website: Codex Execution Contract

This file is the entry point for any Codex task that designs or builds the Holiday Kenya Safaris website.

## Start Here

Before writing code, read these files in order:

1. `DECISIONS.md`
2. `PRODUCT.md`
3. `BRAND-WAYFINDER.md`
4. `DESIGN.md`
5. `UI-REFERENCE-CATALOGUE.md`
6. `AUDIENCES-AND-MESSAGING.md`
7. `COPY-FRAMEWORK.md`
8. `FUNNEL-AND-ANALYTICS.md`
9. `CONTENT-MODEL.md`
10. `WEBSITE-STRUCTURE.md`
11. `ASHFORD-CONTENT-SOURCE.md`
12. `IMPLEMENTATION-PLAN.md`
13. `DEPLOYMENT-PIPELINE.md`
14. `CLIENT-CONFIRMATIONS.md`
15. `REFERENCES.md`

This documentation package overrides older workspace material when there is a conflict. In particular:

- The exact brand name is **Holiday Kenya Safaris**.
- The selected identity is **The Wayfinder**, not Open Horizon or Safari Window.
- Holiday Kenya Safaris primarily serves the local Kenyan market, offers both Kenya and international trips, and is operated by Ashford Tours & Travel.
- The website is not an app and should not look or speak like a technology product.

## Objective

Build an exquisite, fast, trustworthy WordPress website that turns Facebook-ad traffic and high-intent visitors into qualified WhatsApp inquiries for Kenya and international tours.

The main website must feel like a complete, browseable travel catalogue. Its homepage, navigation, archives, and canonical Tour pages use the catalogue-led structure in `UI-REFERENCE-CATALOGUE.md`. Focused paid-ad Campaign pages retain a more emotionally concentrated conversion format.

Work backward from the conversion:

`Facebook ad -> relevant package or campaign page -> trust and package detail -> short intake form -> prefilled WhatsApp message -> human quote conversation`

The initial commercial endpoint is a qualified WhatsApp conversation, not online checkout.

## Approved Architecture

- WordPress CMS.
- Custom block theme. Do not use Elementor, Divi, or an unrestricted page-builder theme.
- A small site plugin owns custom post types, taxonomies, field definitions, validation, and conversion logic.
- Secure Custom Fields owns structured editor fields. Store field groups as version-controlled Local JSON or register them in code.
- Use native blocks, carefully constrained custom blocks, locked templates, and reusable patterns.
- Keep canonical tour facts separate from avatar-specific campaign copy.
- Avoid a headless frontend unless the client later approves the additional operational complexity.

## Non-Negotiable Product Rules

- Build for domestic safaris, excursions, coast trips, staycations, group packages, relevant local special-interest travel, and approved international holidays.
- Exclude visa services, standalone airport or hotel transfers, and products that cannot be represented accurately as a Tour.
- Retain Ashford itinerary copy and factual product information as literally as makes sense, removing only navigation debris, malformed fragments, contradictions, and wording that would mislead an HKS visitor.
- Treat an authorized editor's decision to upload or assign media and publish public content as client approval. The client has authorized the current Ashford catalogue expansion to be imported and published directly; later unattended imports require a new explicit authorization.
- Give Tours one editable `From price per person (KSh)` field stored as `hks_from_price_ksh`. A positive value renders as `From KSh X per person` on Tour cards, catalogue and taxonomy archives, Destination pages, related-Tour modules, canonical Tour pages, and Tour quote panels. Blank or zero omits the price cleanly.
- For the authorized Ashford catalogue expansion, convert the source low-season per-person price, or a clearly credible published starting price where no seasonal table exists, using the live USD/KSh rate checked on the import date and round upward to the next KSh 500. Reject placeholder/deposit-like values. Record the source amount, exchange-rate source, rate, and conversion date in the repository import manifest, not as extra client editor fields.
- Keep the separate optional Campaign `From price per person (KSh)` field. A populated Campaign price overrides the linked Tour price on that Campaign; when blank, the Campaign may inherit the linked Tour starting price.
- Do not add price status, validity-date, season, residency, group-size, transport, accommodation, inclusion, or other price-assumption fields. Editors update or remove a Campaign price manually.
- Never invent rates, reviews, memberships, policies, legal details, lodge availability, or operational claims.
- The official Holiday Kenya Safaris mobile and WhatsApp number is `+254 712 965 131` (`254712965131` in `wa.me` URLs).
- The official public email is `info@holidaykenyasafaris.ke`; the official Instagram and Facebook profiles are the URLs recorded in `CLIENT-CONFIRMATIONS.md`.
- Unknown public information stays blank or in draft and is omitted from public pages. `CLIENT-CONFIRMATIONS.md` remains the project-level register for unresolved legal, contact, analytics, and operational launch decisions; it is not a source of per-record approval fields.

## Conversion Rules

- Every package and campaign page has a prominent WhatsApp quote CTA.
- Opening the CTA first shows a short intake form.
- Required fields: name, phone, package, preferred date or month, and number of travelers.
- Optional package-specific fields may include departure town, adults and children, residency, vehicle preference, accommodation preference, and budget range.
- After validation, construct a readable WhatsApp message from the answers and preserve campaign attribution.
- Do not send data silently. The visitor reviews and sends the message in WhatsApp.
- Instrument page view, package view, CTA click, form open, form completion, and WhatsApp launch.

## Audience Rule

Mercy is a useful first avatar, not the entire market. The canonical tour pages should speak clearly to interested local travelers. Campaign-page variants may target one avatar, occasion, desire, problem, or objection at a time.

Do not force one message across all visitors. The system must support additional landing-page variants without duplicating canonical itinerary data. A Campaign may own its optional selling price without changing the linked Tour.

## Copy Rule

For conversion copy, read and apply the workspace skill:

`C:\Users\ALLAN MUGO\.codex\skills\outreach-message-writing\SKILL.md`

Adapt the process to page and ad copy:

1. Identify the buyer, occasion, current pressure, desired outcome, trust barrier, and next step.
2. Use the value equation to increase desired outcome and confidence while reducing delay and planning effort.
3. Use survival, identity, and progress tension without fearmongering.
4. Add concrete proof: itinerary, vehicle, accommodation, inclusions, exclusions, policies, and operator relationship.
5. Run the human rewrite pass. Remove AI cadence, generic superlatives, repeated abstractions, and over-polished symmetry.

## Design Rule

- Use the Wayfinder palette, typography direction, and production logo described in `BRAND-WAYFINDER.md`.
- Follow `DESIGN.md` and `UI-REFERENCE-CATALOGUE.md` for the global header, homepage, catalogue, canonical Tour template, responsive behavior, and the distinction between Catalogue and Campaign modes.
- Use the approved canonical Tour information architecture: title band, three-image gallery, destination line, two-column desktop workspace, tabs, itinerary disclosures, related Tours, and persistent quote panel.
- Replace the reference site's permanent booking form with the HKS **Request quote on WhatsApp** button and approved intake-to-WhatsApp flow.
- Preserve the information clarity and conversion path of the existing Maasai Mara prototype for Campaign pages, not as the default canonical Tour layout.
- Destination photography should reveal the actual place and experience.
- Keep interfaces quiet, premium, and highly legible. Avoid tourism-poster clutter, fake luxury, oversized decorative cards, and generic orange sunsets.
- Mobile is a primary conversion surface. Keep WhatsApp CTAs reachable without obscuring content.
- Meet WCAG AA contrast, keyboard access, reduced-motion preferences, and sensible focus states.

## Source Hierarchy

When package information conflicts, use this order:

1. Client-confirmed Holiday Kenya Safaris information.
2. Current Ashford product page and client-confirmed Ashford operational details.
3. The approved exchange-rate source recorded in the import manifest for a client-authorized conversion.
4. The local catalogue files in this workspace.
5. Internal hypotheses and research documents, clearly labeled as hypotheses.

Never treat a hypothesis as sales data. A converted USD rate becomes an approved HKS starting price only under the explicit conversion rule above; editors may change it later.

## Editorial Approval Rule

- Publishing a Tour, Campaign, Destination, FAQ, policy note, or assigned media is the authorized editor's approval for public use.
- Draft means not public. Blank means unavailable and must be omitted gracefully.
- The client-facing editor exposes only fields that produce visible public output or visibly control discovery, such as taxonomy assignment or Featured Tour placement.
- Required system identifiers and legacy audit metadata may remain stored for compatibility, but must be generated or hidden rather than requested from the client.
- Start and end date fields belong only to Campaign planning and do not auto-publish or alter the Campaign price. Preferred travel date or month remains an inquiry answer, not Tour metadata.
- Road safari, flying safari, coast experience, staycation, and similar product distinctions belong in Tour Type and related taxonomies, not in pricing assumptions.
- Publishing does not authorize invented facts. The current Ashford catalogue expansion is explicitly authorized for direct publication; unclear or contradictory products remain draft until resolved.

## Build Sequence

1. Audit the repository and read this documentation package.
2. Resolve or explicitly track blocking items in `CLIENT-CONFIRMATIONS.md`.
3. Redraw the Wayfinder identity into production SVG/PNG assets before implementing the header and favicon.
4. Scaffold the WordPress environment, custom block theme, and site plugin.
5. Implement content models and SCF field groups before hard-coding package pages.
6. Seed and validate the three priority packages.
7. Build the intake-to-WhatsApp component and analytics event contract.
8. Build and approve the catalogue-led global header, Tour gallery, canonical Tour workspace, sticky quote panel, tabs/disclosures, and related-Tour pattern.
9. Build the catalogue, destination, homepage, and Campaign templates from the approved components.
10. Add the remaining approved Kenya and international Ashford catalogue in controlled batches.
11. Configure and test the local-to-GitHub-to-cPanel staging and production pipeline.
12. Test responsive behavior, accessibility, performance, SEO, tracking, and editorial usability.

## Definition of Done

The website is ready for launch only when:

- Editors can add a Tour once and publish it across catalogue, destination, and campaign templates.
- Campaign variants can change messaging and optionally present their own selling price without duplicating factual Tour itinerary data.
- Canonical Tour pages use the approved gallery, two-column workspace, tabs/disclosures, related Tours, and sticky quote panel at desktop and mobile breakpoints.
- No canonical Tour page contains a permanent long booking form; every quote command opens the shared HKS intake and WhatsApp handoff.
- WhatsApp inquiries include enough context for a consultant to quote.
- Every published price, photograph, trust claim, and policy was deliberately entered, assigned, imported under the current client authorization, or published by an authorized editor.
- Wayfinder assets are crisp at favicon, header, social, print, and vehicle sizes.
- Meta and GA4 events have been tested with client IDs.
- Core Web Vitals, mobile layouts, forms, keyboard interaction, and major browsers have been tested.
- No draft, blank, legacy-hidden, or imported-only value is exposed as a public fact.

## New Codex Task Prompt

Use this prompt in a new Codex task opened at the workspace root:

> Read `AGENTS.md` and every file it marks as required. Then inspect the repository, referenced source material, and existing implementation. Build or revise the Holiday Kenya Safaris WordPress website according to the documented architecture, including the catalogue-led canonical Tour UI and the HKS intake-to-WhatsApp conversion flow. Do not invent rates, policies, reviews, photographs, or company details. Start by reporting the repository state, blocking client confirmations, and the implementation plan; then proceed through the documented build sequence.
