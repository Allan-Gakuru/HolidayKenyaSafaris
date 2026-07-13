# Holiday Kenya Safaris Website: Codex Execution Contract

This file is the entry point for any Codex task that designs or builds the Holiday Kenya Safaris website.

## Start Here

Before writing code, read these files in order:

1. `DECISIONS.md`
2. `BRAND-WAYFINDER.md`
3. `AUDIENCES-AND-MESSAGING.md`
4. `COPY-FRAMEWORK.md`
5. `FUNNEL-AND-ANALYTICS.md`
6. `CONTENT-MODEL.md`
7. `WEBSITE-STRUCTURE.md`
8. `ASHFORD-CONTENT-SOURCE.md`
9. `IMPLEMENTATION-PLAN.md`
10. `DEPLOYMENT-PIPELINE.md`
11. `CLIENT-CONFIRMATIONS.md`
12. `REFERENCES.md`

This documentation package overrides older workspace material when there is a conflict. In particular:

- The exact brand name is **Holiday Kenya Safaris**.
- The selected identity is **The Wayfinder**, not Open Horizon or Safari Window.
- Holiday Kenya Safaris serves the local Kenyan market and is operated by Ashford Tours & Travel.
- The website is not an app and should not look or speak like a technology product.

## Objective

Build an exquisite, fast, trustworthy WordPress website that turns Facebook-ad traffic and high-intent visitors into qualified WhatsApp inquiries for local Kenyan tours.

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

- Build for domestic safaris, excursions, coast trips, staycations, group packages, and relevant local special-interest travel.
- Exclude international holidays, visa services, airport transfers, and inbound-only products unless the client later includes them.
- Retain factual Ashford itinerary information, but rewrite marketing copy for local buyers.
- Reuse only photographs the client has confirmed it may use.
- Display prices primarily in KSh as provisional `From KSh...` values until confirmed.
- Every price must expose its status and assumptions: season, residency, group size, transport, accommodation, and inclusions.
- Never invent rates, reviews, memberships, policies, legal details, lodge availability, or operational claims.
- The temporary WhatsApp destination is `+254 722 742 799` (`254722742799` in `wa.me` URLs).
- Unknown information must be labeled `CLIENT CONFIRMATION REQUIRED` in source data and omitted or carefully marked on public pages.

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

Do not force one message across all visitors. The system must support additional landing-page variants without duplicating canonical itinerary and price data.

## Copy Rule

For conversion copy, read and apply the workspace skill:

`C:\Users\ALLAN MUGO\.codex\skills\outreach-message-writing\SKILL.md`

Adapt the process to page and ad copy:

1. Identify the buyer, occasion, current pressure, desired outcome, trust barrier, and next step.
2. Use the value equation to increase desired outcome and confidence while reducing delay and planning effort.
3. Use survival, identity, and progress tension without fearmongering.
4. Add concrete proof: itinerary, vehicle, accommodation, inclusions, price assumptions, policies, and operator relationship.
5. Run the human rewrite pass. Remove AI cadence, generic superlatives, repeated abstractions, and over-polished symmetry.

## Design Rule

- Use the Wayfinder palette, typography direction, and production logo described in `BRAND-WAYFINDER.md`.
- Preserve the information clarity and conversion path of the existing Maasai Mara prototype, but reskin and rebuild it for WordPress.
- Destination photography should reveal the actual place and experience.
- Keep interfaces quiet, premium, and highly legible. Avoid tourism-poster clutter, fake luxury, oversized decorative cards, and generic orange sunsets.
- Mobile is a primary conversion surface. Keep WhatsApp CTAs reachable without obscuring content.
- Meet WCAG AA contrast, keyboard access, reduced-motion preferences, and sensible focus states.

## Source Hierarchy

When package information conflicts, use this order:

1. Client-confirmed Holiday Kenya Safaris information.
2. Current Ashford product page and client-confirmed Ashford operational details.
3. The local catalogue files in this workspace.
4. Internal hypotheses and research documents, clearly labeled as hypotheses.

Never treat a hypothesis as sales data or a converted USD rate as an approved KSh price.

## Build Sequence

1. Audit the repository and read this documentation package.
2. Resolve or explicitly track blocking items in `CLIENT-CONFIRMATIONS.md`.
3. Redraw the Wayfinder identity into production SVG/PNG assets before implementing the header and favicon.
4. Scaffold the WordPress environment, custom block theme, and site plugin.
5. Implement content models and SCF field groups before hard-coding package pages.
6. Seed and validate the three priority packages.
7. Build the intake-to-WhatsApp component and analytics event contract.
8. Build reusable templates and patterns.
9. Add the remaining approved local catalogue in controlled batches.
10. Configure and test the local-to-GitHub-to-cPanel staging and production pipeline.
11. Test responsive behavior, accessibility, performance, SEO, tracking, and editorial usability.

## Definition of Done

The website is ready for launch only when:

- Editors can add a Tour once and publish it across catalogue, destination, and campaign templates.
- Campaign variants can change messaging without duplicating factual itinerary and price data.
- WhatsApp inquiries include enough context for a consultant to quote.
- Every published price, photograph, trust claim, and policy has an approved source.
- Wayfinder assets are crisp at favicon, header, social, print, and vehicle sizes.
- Meta and GA4 events have been tested with client IDs.
- Core Web Vitals, mobile layouts, forms, keyboard interaction, and major browsers have been tested.
- No placeholder marked `CLIENT CONFIRMATION REQUIRED` is exposed as a confirmed public fact.

## New Codex Task Prompt

Use this prompt in a new Codex task opened at the workspace root:

> Read `docs/Holiday Kenya Safaris Website/AGENTS.md` and every file it marks as required. Then inspect the referenced workspace assets. Build the Holiday Kenya Safaris WordPress website according to the documented architecture, working backward from qualified WhatsApp inquiries. Do not invent rates, policies, reviews, photographs, or company details. Start by reporting the repository state, blocking client confirmations, and the implementation plan; then proceed through the documented build sequence.
