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
- Expose one editable Tour `From price per person (KSh)` field. Retain the separate optional Campaign override and Campaign-only start/end dates.
- Add the public Tour Scope taxonomy with `Kenya Tours` and `International Tours`; keep Destination as the geographic taxonomy.
- Extend the existing Destination taxonomy to native Posts and Tours while keeping every catalogue query and Tour count explicitly Tour-only.
- Add the Post-only Article Topic taxonomy and code-registered Secure Custom Fields for Article Format, Primary Tour, Related Reading, Destination, and Article Topic.
- Use native Posts for Travel Guides. Standard Guides have an optional Primary Tour; Advertorials cannot publish without one published Primary Tour. Featured images remain optional and no public author name is rendered.
- Lock critical templates while preserving practical editing regions.
- Add preview behavior for Tours and Campaigns.
- Keep the ordered public gallery, Featured Tour placement, visible package facts, itinerary, inclusions/exclusions, package notes, and FAQ relationship required by the current templates.

Acceptance:

- An editor can create one Tour and link several Campaign variants.
- Tour cards, archives, canonical Tour pages, and Tour quote panels show `From KSh X per person` when the Tour has a positive amount; blank prices are omitted.
- A Campaign shows its own positive override or inherits the linked Tour starting price when its field is blank.
- Editors can assign public media without completing a separate rights envelope.
- Existing Tour amounts are validated before display and never copied into Campaign metadata merely to support inheritance.

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
- Populate the editable Tour starting price when a source low-season amount or clearly credible published starting amount exists and the authorized conversion rule can be applied. Reject placeholder/deposit-like values. Leave Campaign overrides blank unless a focused offer needs a different honest amount.
- For the dated client-authorized Ashford expansion, import the corresponding source image as the featured image before direct publication. Future migrations revert to the normal authorization rule.
- Add representative Campaign variants for different audience angles.

Acceptance:

- Three end-to-end package funnels work before scaling the catalogue.

## Phase 6: Templates and Pages

Implementation status (2026-07-27): the global, Tour, Campaign, catalogue, all four Tour taxonomy archive families, Destination, homepage, and standard Page templates are implemented. The homepage uses a Featured Tour hero with a homepage-only translucent logo-and-menu header that stays fixed and becomes solid white after scrolling, one changing Tour-title H1, up to five portrait queue cards backed by featured images at least 1200 × 675 pixels, an accessible five-second cycle with a synchronized progress line, and Browse by destination before the Featured Tours grid. Hero eligibility does not depend on alt text; its repeated stage and preview images are decorative. The Tour catalogue presents its filters as a sticky vertical left rail from 1024px upward and as a sticky `Filters` control opening an accessible native-dialog drawer below that breakpoint; both presentations retain the same server-rendered GET filter contract. Group Travel has a dedicated catalogue-driven planner on its published Page and reuses the shared inquiry recovery and WhatsApp flow. The global floating Chat on WhatsApp contact uses a concise general message, adding the current Tour title and canonical link on Tour pages, and remains separate from the saved-inquiry quote flow. Internal pages retain the complete utility bar and white primary header. The desktop header and mobile drawer share the production `holiday-kenya-safaris-logo.svg` lockup. About is available; Contact and four legal routes remain protected drafts until their missing project-level information is supplied.

Menu management status (2026-08-03): the primary header/mobile hierarchy and footer links use native WordPress menu locations exposed under **Appearance > Site Menus**. One two-level Primary menu drives both responsive header surfaces, while a separate Footer menu controls footer links. The existing catalogue-aware output remains the safe fallback until an editor assigns a menu.

Travel Guides implementation target (2026-08-17): extend native WordPress Posts into a public `/travel-guides/` hub with Standard Guide and Advertorial formats. Destination remains one shared public taxonomy and one sitemap route, but its main archive stays Tour-first and Tour-paginated; relevant guides appear in a secondary section. Add public Article Topic archives, Post-only related reading, no-author output, optional-image fallbacks, and the approved Primary Tour conversion behavior. The first design examples use Diani, Dubai, and Maasai Mara; editorial research and production article writing follow design approval.

Production setup after the code release:

1. Create and publish a WordPress Page named **Travel Guides** with slug `travel-guides`, then select it as the Posts page under **Settings > Reading**.
2. Confirm whether any existing native Post URLs require redirects. If none do, set the Post permalink structure to `/travel-guides/%postname%/` under **Settings > Permalinks** and save once to refresh rewrite rules. The plugin deliberately does not change this global setting automatically.
3. Add Travel Guides between Destinations and Group Travel in the assigned Primary menu. The theme fallback already preserves that order when no menu is assigned.
4. Verify native Post, Article Topic, and shared Destination entries in the generated XML sitemap and verify canonical URLs before indexing.
5. Keep the existing default Post unpublished or remove it from the public editorial plan before launch.

Build in this order:

1. Utility bar, desktop header, dropdown navigation, mobile drawer, and footer.
2. Canonical Tour title band and thumbnail-led active gallery.
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
15. Travel Guides hub and Article Topic archive.
16. Standard Guide and Advertorial templates.
17. Destination-page Travel Guides section and related-guide resolver.

Why this order:

- The conversion and product templates define the data and proof the homepage must surface.
- Building the homepage first encourages generic content and one-off components.

Acceptance:

- Every template uses the shared design system and structured content.
- Campaign pages inherit canonical Tour facts.
- The canonical Tour page implements the approved title band, desktop thumbnail-rail/active-image/sticky-quote composition, six-thumbnail desktop rail cap with a `+N more` lightbox trigger, responsive full horizontal thumbnail strip, five-second visibility-aware gallery rotation, previous/next controls, tabs/disclosures, itinerary, and related-Tour flow within the Wayfinder system.
- The permanent reference-site booking form is absent; every Tour quote action opens the approved HKS intake and WhatsApp handoff.
- The current Maasai Mara prototype's strongest UX is preserved in Campaign mode without carrying over its old identity.
- Group Travel navigation resolves to one canonical Page whose published Destination and Tour choices feed the same private inquiry and WhatsApp review service as Tour and Campaign pages.
- Travel Guides navigation appears between Destinations and Group Travel, public page copy spells Holiday Kenya Safaris in full, and no author name appears in article cards or pages.
- A Standard Guide may publish without a Primary Tour and uses **View this trip** when one is assigned. An Advertorial requires one published Primary Tour and sends all quote actions into the shared intake and review flow.
- Sharing Destination never mixes Posts into Tour archive pagination, Tour navigation, or Tour counts. Related reading contains up to three Posts in manual, Destination, then Article Topic priority.

## Phase 7: Catalogue Migration

Implementation status (2026-07-19): all 44 local candidates were reviewed for migration. Forty eligible records were imported in four controlled batches, the three MVP Tours were retained without duplication, and the generic `African-wildlife-safari` marketing page was excluded. An authorized editor published all 43 retained Tours. On 2026-07-24 the client expanded this phase to the remaining Ashford Kenya and international catalogue, authorized direct publication, and approved live USD/KSh low-season conversion rounded upward to KSh 500.

Actions:

- Audit the current live Ashford catalogue against existing HKS Tours and import only missing products.
- Classify every Tour under Kenya Tours or International Tours and assign supported Destination terms.
- Reuse corresponding Ashford images and itinerary copy as literally as makes sense.
- Convert the source low-season per-person price, or a clearly credible published starting amount where no seasonal table exists, using the recorded live rate and upward KSh 500 rounding rule.
- Publish coherent records directly under the client's authorization; keep incomplete, contradictory, or unpriceable records draft.
- Add missing coast/staycation products when supplied or deliberately published by an authorized editor.
- Check internal linking and filter usefulness after each batch.

Acceptance:

- No duplicate product is created merely because it appears in more than one Ashford category.
- Every live imported Tour is covered by the client's direct-publication authorization and contains only public values the current templates consume.
- The import manifest makes every converted starting price reproducible.

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
- Tour price rendering: positive Tour amounts display consistently across cards, archives, details, related Tours, and quote panels; blank amounts disappear cleanly; Campaign overrides and inheritance behave correctly.
- Core Web Vitals and layout shift.
- Featured Tour hero image eligibility, five-second autoplay and progress synchronization, clipped card-to-stage reveal, pause/resume, pointer drag, keyboard commands, reduced-motion behavior, and homepage-header contrast at 360, 390, 768, 1024, 1280, and 1440px.
- Analytics debug modes.
- Travel Guides at 360, 390, 768, 1024, and 1440px: hub filters, image/no-image cards, long titles, Standard Guide with and without Primary Tour, Advertorial desktop panel and mobile sticky action, and related-Post ordering.
- Publication guard: block an Advertorial whose Primary Tour is blank or unpublished while allowing a Standard Guide without a Primary Tour.
- Shared Destination regression: a Destination with Tours and Posts remains Tour-first, and a Post-only Destination is absent from Tour navigation and Tour counts.
- Travel Guides keyboard behavior, quote-dialog focus return, safe-area spacing, reduced motion, sitemap routes, canonical URLs, and one anonymous analytics event per action.

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
