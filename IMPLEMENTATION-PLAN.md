# Implementation Plan

## Principle

Build backward from a qualified WhatsApp inquiry. Do not start by producing a generic homepage and then bolt on conversion later.

The standard site must also feel like a complete travel catalogue. Follow `UI-REFERENCE-CATALOGUE.md` for the global shell, homepage, archives, and canonical Tour pages. Use the existing Maasai Mara prototype only for Campaign-mode structure.

## Phase 0: Repository and Environment Audit

Actions:

- Read this documentation package.
- Inspect the current workspace and determine whether a WordPress project already exists.
- Confirm local development, staging, production hosting, PHP, database, Node tooling, deployment, backups, and version control.
- Check `CLIENT-CONFIRMATIONS.md` and separate launch blockers from safe placeholders.
- Record the exact setup in the repository README.

Deliverable:

- Reproducible local environment and an implementation plan adjusted to the actual repository.

## Phase 1: Production Wayfinder Identity

Actions:

- Redraw the selected mark as clean SVG geometry.
- Correct HKS legibility, compass simplification, spacing, and small-size behavior.
- Export required variants.
- Test on Pale Mist, white, Midnight Navy, destination photography, and a classic safari Defender application.
- Confirm self-hosted Montserrat variable-font delivery and licensing.

Acceptance:

- Header and favicon use vector-derived assets, not a crop from the concept board.
- One-color marks work at small sizes.
- No app mockup or app-product language remains.

## Phase 2: WordPress Foundation

Recommended structure:

```text
wp-content/
  plugins/
    hks-core/
      hks-core.php
      src/
      acf-json/
      blocks/
      assets/
  themes/
    hks-wayfinder/
      style.css
      functions.php
      theme.json
      templates/
      parts/
      patterns/
      blocks/
      assets/
```

Actions:

- Create the custom block theme.
- Create the `hks-core` site plugin.
- Install and configure Secure Custom Fields.
- Register content types and taxonomies with REST/block-editor support.
- Add design tokens to `theme.json`.
- Establish coding standards, linting, formatting, and test commands.

Acceptance:

- Theme can change without deleting the content model.
- Field configuration is version controlled.
- No heavy page builder is required.

## Phase 3: Content Model and Editorial Experience

Actions:

- Implement Tour and Campaign models. Do not expose Testimonial fields until a public Testimonial component exists.
- Implement taxonomies and SCF groups from `CONTENT-MODEL.md`.
- Reduce the client editor to fields that render publicly or visibly control public discovery and placement.
- Use native WordPress publication state as approval; remove client confirmation, source-audit, rights-status, and validity fields from content workflows.
- Keep Tours price-free. Add one optional Campaign-only `From price per person (KSh)` field and retain Campaign-only start/end dates.
- Lock critical templates while preserving practical editing regions.
- Add preview behavior for Tours and Campaigns.
- Keep the ordered public gallery, Featured Tour placement, visible package facts, itinerary, inclusions/exclusions, package notes, and FAQ relationship required by the current templates.

Acceptance:

- An editor can create one Tour and link several Campaign variants.
- Tour cards, archives, canonical Tour pages, and Tour quote panels show no price or request-rate fallback.
- A Campaign shows `From KSh X per person` only when its own optional positive KSh value is populated; blank Campaign prices are omitted.
- Editors can assign public media without completing a separate rights envelope.
- Existing legacy Tour amounts do not become public or migrate into Campaigns accidentally.

## Phase 4: Conversion Component First

Actions:

- Build the reusable intake form and WhatsApp handoff.
- Implement field validation, accessible dialog/sheet behavior, focus management, error handling, and mobile keyboard behavior.
- Persist package and campaign attribution.
- Implement the event contract with placeholder-disabled analytics configuration.
- Test desktop WhatsApp Web and mobile WhatsApp behavior.
- Expose one reusable API and block/pattern contract so the desktop sticky quote panel, mobile sticky action, in-flow panels, header action, and Campaign pages all open the same intake flow.

Acceptance:

- A visitor can submit required details and receive a readable prefilled message.
- Nothing is described as sent before the visitor sends it.
- No sensitive form values enter analytics.

## Phase 5: Three Seed Tours

Implement and verify:

1. 3 Days / 2 Nights Maasai Mara Road Safari.
2. Nairobi National Park Tours - 4 hours.
3. 3 Days / 2 Nights Amboseli Safari Package.

Actions:

- Verify current Ashford facts.
- Keep useful source references in the repository import manifest, not the client Tour form.
- Import canonical facts.
- Rewrite local-market copy.
- Leave Tours price-free. Leave each Campaign price blank unless the client deliberately enters an honest KSh per-person starting value for that Campaign.
- Import remote imagery unassigned; the editor's deliberate assignment to published content is approval.
- Add representative Campaign variants for different audience angles.

Acceptance:

- Three end-to-end package funnels work before scaling the catalogue.

## Phase 6: Templates and Pages

Implementation status (2026-07-21): the global, Tour, Campaign, catalogue, all four Tour taxonomy archive families, Destination, homepage, and standard Page templates are implemented. Group Travel has a dedicated catalogue-driven planner on its published Page and reuses the shared inquiry recovery and WhatsApp flow. The global floating Chat on WhatsApp contact uses a fixed general message and remains separate from the saved-inquiry quote flow. The desktop header and mobile drawer share the production `holiday-kenya-safaris-logo.svg` lockup. About is available; Contact and four legal routes remain protected drafts until their missing project-level information is supplied.

Build in this order:

1. Utility bar, desktop header, dropdown navigation, mobile drawer, and footer.
2. Canonical Tour title band and three-image gallery.
3. Canonical Tour facts, accessible tabs, mobile disclosures, and itinerary timeline.
4. Sticky desktop quote panel and mobile in-flow/sticky quote actions.
5. Related-Tour cards and query/override behavior.
6. Complete canonical Tour detail template.
7. Campaign landing-page template based on the existing Maasai Mara conversion structure.
8. Tour catalogue and taxonomy archives.
9. Destination page.
10. Homepage.
11. About/trust.
12. Group Travel.
13. Contact.
14. Legal and policy templates.

Why this order:

- The conversion and product templates define the data and proof the homepage must surface.
- Building the homepage first encourages generic content and one-off components.

Acceptance:

- Every template uses the shared design system and structured content.
- Campaign pages inherit canonical Tour facts.
- The canonical Tour page implements the approved title band, gallery, two-column workspace, tabs/disclosures, itinerary, quote panel, and related-Tour flow within the Wayfinder system.
- The permanent reference-site booking form is absent; every Tour quote action opens the approved HKS intake and WhatsApp handoff.
- The current Maasai Mara prototype's strongest UX is preserved in Campaign mode without carrying over its old identity.
- Group Travel navigation resolves to one canonical Page whose published Destination and Tour choices feed the same private inquiry and WhatsApp review service as Tour and Campaign pages.

## Phase 7: Catalogue Migration

Implementation status (2026-07-19): all 44 local candidates were reviewed for migration. Forty eligible records were imported in four controlled batches, the three MVP Tours were retained without duplication, and the generic `African-wildlife-safari` marketing page was excluded. An authorized editor has published all 43 retained Tours. The catalogue audit is recorded in `TOUR-CATALOGUE-AUDIT-2026-07-19.md`; incomplete optional facts remain explicit editorial follow-up rather than invented data.

Actions:

- Review the 44 unique local candidate pages in controlled batches.
- Prioritize credible, complete, locally relevant products.
- Keep incomplete or questionable records in draft.
- Add missing coast/staycation products when supplied or deliberately published by an authorized editor.
- Check internal linking and filter usefulness after each batch.

Acceptance:

- No product is live merely because it existed in the crawl.
- Every live Tour was deliberately published by an authorized editor and contains only public values the current templates consume.
- Legacy source, price-status, assumption, validity, and rights metadata remains non-destructively stored but is hidden and ignored.

## Phase 8: Analytics, SEO, Security, and Performance

Actions:

- Request client analytics IDs.
- Configure and test Meta, GA4, and optional GTM.
- Implement consent according to the approved privacy approach.
- Add metadata, canonical behavior, sitemap, robots, breadcrumbs, and accurate structured data.
- Add security hardening, least-privilege roles, spam protection, update policy, backups, staging, and monitoring.
- Optimize media, caching, fonts, and JavaScript.

Acceptance:

- Events fire once with correct parameters.
- Campaign UTMs survive the journey to WhatsApp launch.
- No fake reviews or inaccurate offer schema is published.
- Backup and restore process is documented.

## Phase 9: Quality Assurance

Test:

- Mobile, tablet, laptop, and wide desktop layouts.
- Chrome, Edge, Firefox, and Safari where available.
- Keyboard navigation and screen-reader semantics.
- Long titles, large KSh values, missing optional fields, and empty states.
- Desktop dropdown navigation, mobile drawer focus behavior, gallery lightbox, tabs, disclosures, itinerary expand/collapse, and related-Tour navigation.
- Canonical Tour layouts at 360, 390, 768, 1024, 1280, and 1440px.
- Sticky quote-panel stopping behavior before the footer and mobile safe-area spacing.
- Form errors, WhatsApp cancellation, and back navigation.
- Slow connections and image failures.
- Template editing by a non-developer.
- Publish-as-approval behavior for Tours, Destinations, FAQs, public package notes, and assigned media.
- Legacy Tour price migration: every old Tour amount stays hidden; deliberate positive Campaign from-prices display only on their Campaigns.
- Core Web Vitals and layout shift.
- Analytics debug modes.

Capture desktop and mobile screenshots for the core templates before acceptance.

Compare the resulting header, homepage, catalogue, and canonical Tour page against `UI-REFERENCE-CATALOGUE.md` at the same viewport sizes. Verify the documented structure, responsive behavior, and HKS-specific improvements.

## Phase 10: Launch and Learning

Actions:

- Complete all launch-blocking confirmations.
- Freeze and back up staging.
- Deploy through a documented process.
- Verify SSL, forms, WhatsApp, analytics, indexing, redirects, and backups in production.
- Launch initial Facebook campaign pages.
- Review inquiry quality and consultant feedback weekly.

Use evidence to add new campaign variants, not assumptions alone.

Follow `DEPLOYMENT-PIPELINE.md` for the working-directory-to-GitHub-to-cPanel release process.
