# Phase 0 Baseline

Recorded: 2026-07-14

## Outcome

The repository is initialized and connected to `https://github.com/Allan-Gakuru/HolidayKenyaSafaris.git` on the local `main` branch. The remote has no branch history. No commit or push has been made yet.

The project began as a documentation-only handoff. The 12 required decision documents have been read in the order defined by `AGENTS.md`. The 17 research, catalogue, brand, prototype, and presentation assets referenced by `REFERENCES.md` were located in an older workspace and restored to their documented `outputs/` and `work/` paths with matching SHA-256 hashes.

There is no pre-existing WordPress theme, site plugin, content database, deployment workflow, approved photo library, or production configuration to preserve.

## Tooling Baseline

| Tool | Verified state |
|---|---|
| Git | 2.54.0.windows.1 |
| Node.js | 24.18.0 |
| npm | 11.18.0 |
| PHP CLI | 8.3.32 |
| Composer | Not installed/on PATH |
| WP-CLI | Not installed/on PATH |
| Docker | Not installed/on PATH |
| MySQL/MariaDB client | Not installed/on PATH |

## Development and Verification Workflow

No local WordPress runtime is required for this project. The repository will contain deployable custom theme and site-plugin code while WordPress core, the database, uploads, and environment configuration remain on cPanel.

The working loop is:

1. Implement source locally.
2. Run `php -l` against every changed PHP file, plus relevant static or unit checks that do not require WordPress.
3. Commit and push the reviewed change to GitHub.
4. The repository owner deploys the change to cPanel.
5. Verify the deployed result in a browser and, once credentials are supplied, in the WordPress dashboard.

The installed PHP 8.3 CLI is the local syntax checker. The cPanel PHP version, extensions, WordPress version, database, and Secure Custom Fields version must be recorded when server access is available. Browser and dashboard verification on cPanel is the authoritative integration test.

## Source and Asset Audit

### Reusable direction

- Wayfinder palette and the original Sora/Inter typography direction, superseded for the website by the site-wide Montserrat decision recorded in `BRAND-WAYFINDER.md` and `DESIGN.md`.
- The concept's planning, movement, and guidance idea.
- The prototype's sequence from emotional hero to practical facts, itinerary, inclusions/exclusions, quote explanation, intake form, and WhatsApp handoff.
- The 65-record Ashford crawl as dated migration input.
- The campaign deck's funnel logic and Mercy as one testable campaign avatar.

### Must be rebuilt or verified

- The Wayfinder logo is available only as raster concept artwork. It needs clean production vector geometry and all required variants.
- The prototype is a single inline HTML/CSS/JS file using the old singular name, temporary palette, generated imagery, and unapproved converted prices.
- The generated Mara and Mercy images are presentation assets, not public proof.
- The crawl is dated 2026-07-02. Every selected Tour requires current source verification, a checked date, pricing status, and media provenance.

### Must not be published as confirmed material

- Converted or placeholder KSh prices.
- Generated presentation imagery as evidence of a destination, vehicle, lodge, guide, or customer.
- Unapproved Ashford or third-party photography.
- Hypothesized bestseller rankings as sales facts.
- Unverified policies, memberships, licences, fleet, guide, safety, response-time, or legal claims.

## Blocker Register

### Launch blockers

- Approved production Wayfinder SVG/PNG/favicon set and confirmed font delivery.
- Production domain, DNS plan, hosting, staging, SSL, backups, PHP/database support, and cPanel deployment capability/access.
- WordPress administrator ownership and editorial roles.
- Approved operator/legal wording, privacy policy, cookie/consent approach, and commercial policies.
- Meta Pixel and GA4 IDs, plus a GTM decision and ID if used.
- Verified rights and consent for every public photograph.
- Verified current facts and approved media for every live Tour; Tours remain price-free.

### Feature-specific blockers

- Confirmed Campaign-specific KSh starting prices only when price will be a deliberate selling point.
- Deposit, payment, cancellation, refund, child-rate, supplement, group-size, insurance, and liability terms.
- Fleet, guide, safety, school/youth, corporate, group-payment, and accommodation proof.
- cPanel SSH/Git/SFTP capability before the deployment transport is chosen.

### Safe working defaults

- Use `254712965131` as the official Holiday Kenya Safaris mobile and WhatsApp destination.
- Omit prices from Tours and omit a Campaign price when it has not been deliberately entered.
- Keep analytics integrations disabled when IDs are empty; never use dummy IDs.
- Omit unverified contact details, response promises, policies, reviews, memberships, and operational claims.
- Keep Testimonial and Guide models optional and empty until approved evidence exists.
- Keep staging password-protected and `noindex`.

## Adjusted Implementation Order

1. Complete Phase 0 with an initial reviewed commit and push to the empty GitHub repository.
2. Redraw and test the production Wayfinder identity before implementing the final header or favicon.
3. Scaffold `hks-wayfinder` and `hks-core` as deployable cPanel code.
4. Implement content types, taxonomies, SCF fields, statuses, validation, and editorial safeguards.
5. Build the intake-to-WhatsApp component and analytics event adapter before page templates.
6. Re-verify and seed Maasai Mara, Nairobi National Park, and Amboseli without publishing unconfirmed rates or imagery.
7. Build Tour, Campaign, catalogue, destination, home, trust, group, contact, and legal templates in the documented order.
8. Migrate approved catalogue batches, then complete analytics, SEO, security, performance, accessibility, browser, editorial, staging, and launch QA.

## External Decisions Still Needed

No unresolved client decision blocks the local content model, conversion component, theme/plugin foundation, or identity redraw. Production publishing and integrations remain gated by the blocker register above.
