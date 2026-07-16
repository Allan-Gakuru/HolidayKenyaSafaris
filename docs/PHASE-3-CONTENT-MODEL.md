# Phase 3: Content Model and Editorial Safeguards

## Outcome

The site plugin now owns a canonical, reproducible Tour catalogue and Campaign-variant model. Editors can create one price-free Tour, attach multiple focused Campaigns, and optionally add a selling price to an individual Campaign.

This phase contains source code only. Runtime checks will happen after deployment to cPanel; no local WordPress environment was created.

## Registered content

- `hks_tour`: public canonical product with `/tours/` archive and a constrained overview block.
- `hks_campaign`: directly accessible landing-page variant, excluded from search, navigation, archives, and automatic public post-type discovery.
- `hks_faq`: private reusable FAQ record; not anonymously enumerable through the REST API.
- `hks_destination`: public geographic discovery taxonomy.
- `hks_tour_type`, `hks_occasion`, and `hks_travel_style`: editor/filter taxonomies without thin public term archives.

Campaigns contain the public headline, supporting copy, hero override, navigation mode, planning dates, and one optional Campaign-specific per-person starting price. They do not duplicate Tour duration, route, itinerary, inclusions, logistics, or policies.

## Secure Custom Fields model

The plugin registers deterministic field groups in code on `acf/include_fields`. This is the canonical version-controlled definition; the reserved `acf-json/` directory contains no duplicate group definitions.

Field groups cover:

- lean Tour package facts, itinerary, inclusions, suitability, public notes, gallery, and FAQ relationships;
- Campaign public presentation, optional price, navigation mode, and planning dates;
- reusable FAQ answers;
- Destination public guidance; and
- global identity, contact, legal, analytics, social, conversion, and brand settings.

Only three global values have safe defaults: `Holiday Kenya Safaris`, the Ashford operator disclosure, and temporary WhatsApp destination `254722742799`. Other unknown settings remain blank.

## Publication rules

The same rules run through SCF validation, REST pre-insert checks, and a final `wp_insert_post_data` fail-safe. Incomplete drafts remain editable; unsafe public or scheduled saves are blocked or returned to Draft with an administrator notice.

Key gates include:

- a nonempty native Tour title;
- a nonempty native Campaign title and exactly one published linked Tour;
- a positive whole KSh Campaign price when the optional field is populated;
- valid Campaign planning dates in chronological order;
- no `CLIENT CONFIRMATION REQUIRED` marker anywhere in visible public candidate text;
- linked public Campaigns return to Draft when their Tour is hidden or deleted.

Legacy Tour price and audit metadata remains stored for compatibility but is not registered in the client editor, rendered publicly, or used as a publication gate.

## Deferred MVP integration checks

The following are deliberately carried into the public-template step instead of extending this checkpoint:

- FAQ rendering must accept only published, source-approved, sentinel-free FAQ records.
- Destination templates must render custom guidance only through a fail-closed approval check; raw term meta must never be printed.
- Campaign and Tour preview behavior, template output, and editor usability require dashboard/browser verification after cPanel deployment.
- Campaign templates must omit price when the Campaign field is blank and must never read a legacy Tour price.

These are launch gates, not permission to expose unreviewed content.

## Verification

Run before deployment:

```powershell
& .\tools\lint-php.ps1
python -B tools\validate_scaffold.py
python -B tools\validate_content_model.py
```

The content-model validator checks identifiers, visibility, taxonomy exposure, controlled choices, deterministic field keys, safe global defaults, module/version wiring, lifecycle behavior, REST boundaries, and publication-hook coverage.
