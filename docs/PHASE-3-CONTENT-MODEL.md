# Phase 3: Content Model and Editorial Safeguards

## Outcome

The site plugin now owns a canonical, reproducible Tour catalogue and campaign-variant model. Editors can create one Tour, attach multiple focused Campaigns, and keep source, price, policy, proof, and rights records separate from public marketing copy.

This phase contains source code only. Runtime checks will happen after deployment to cPanel; no local WordPress environment was created.

## Registered content

- `hks_tour`: public canonical product with `/tours/` archive and a constrained overview block.
- `hks_campaign`: directly accessible landing-page variant, excluded from search, navigation, archives, and automatic public post-type discovery.
- `hks_faq`: private reusable FAQ record; not anonymously enumerable through the REST API.
- `hks_destination`: public geographic discovery taxonomy.
- `hks_tour_type`, `hks_occasion`, and `hks_travel_style`: editor/filter taxonomies without thin public term archives.

Campaigns contain the audience angle, headline, supporting copy, proof order, FAQ emphasis, CTA override, navigation mode, and attribution labels. They do not duplicate Tour duration, route, itinerary, inclusions, logistics, policies, or prices.

## Secure Custom Fields model

The plugin registers deterministic field groups in code on `acf/include_fields`. This is the canonical version-controlled definition; the reserved `acf-json/` directory contains no duplicate group definitions.

Field groups cover:

- Tour provenance, package facts, pricing assumptions, seasonal rates, itinerary, inclusions, suitability, policies, media, and inquiry routing;
- Campaign public presentation, conversion brief, source-governed proof, and lifecycle/analytics controls;
- reusable FAQ answers and audit records;
- Destination public guidance and private source audit;
- attachment ownership, permission, usage scope, expiry, and credit records; and
- confirmation-wrapped global identity, contact, legal, analytics, social, conversion, and brand settings.

Only three global values have safe defaults: `Holiday Kenya Safaris`, the Ashford operator disclosure, and temporary WhatsApp destination `254722742799`. Other unknown settings remain blank.

## Publication rules

The same rules run through SCF validation, REST pre-insert checks, and a final `wp_insert_post_data` fail-safe. Incomplete drafts remain editable; unsafe public or scheduled saves are blocked or returned to Draft with an administrator notice.

Key gates include:

- unique Tour product ID, traceable source, checked date, and reviewed/client-confirmed source status;
- nonempty native Tour title, listing summary, and overview;
- internally consistent group/date ranges;
- explicit price display mode and complete assumptions for a KSh `From` price;
- provisional placeholder prices allowed only as visibly provisional, never as converted or expired rates;
- no `CLIENT CONFIRMATION REQUIRED` marker anywhere in visible public candidate text;
- exactly one published linked Tour plus a complete brief and analytics label for public Campaigns; and
- linked public Campaigns return to Draft when their Tour is hidden or deleted.

Raw price records and internal audit groups are excluded from anonymous SCF REST output. Media-rights metadata remains a launch audit rather than an automatic post rejection, as required by the content contract.

## Deferred MVP integration checks

The following are deliberately carried into the public-template step instead of extending this checkpoint:

- FAQ rendering must accept only published, source-approved, sentinel-free FAQ records.
- Destination templates must render custom guidance only through a fail-closed approval check; raw term meta must never be printed.
- Campaign and Tour preview behavior, template output, and editor usability require dashboard/browser verification after cPanel deployment.
- Public price templates must visibly render status and material assumptions rather than exposing raw field records.

These are launch gates, not permission to expose unreviewed content.

## Verification

Run before deployment:

```powershell
& .\tools\lint-php.ps1
python -B tools\validate_scaffold.py
python -B tools\validate_content_model.py
```

The content-model validator checks identifiers, visibility, taxonomy exposure, controlled choices, deterministic field keys, safe global defaults, module/version wiring, lifecycle behavior, REST boundaries, and publication-hook coverage.
