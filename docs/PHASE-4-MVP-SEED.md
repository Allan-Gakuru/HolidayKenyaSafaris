# Phase 4: MVP seed records

The first Holiday Kenya Safaris content batch is stored in
`wp-content/plugins/hks-core/data/mvp-seed.json` and contains:

- three canonical Tour drafts: Maasai Mara, Nairobi National Park, and Amboseli;
- one focused Campaign draft linked to each Tour;
- source URLs, audit notes, source-backed itinerary facts, and local-buyer copy;
- `Request current rate` pricing with no converted or invented KSh amount; and
- no photographs or policy claims.

## Import on the deployed WordPress site

1. Install and activate Secure Custom Fields 6.9.1 or later.
2. Activate HKS Core.
3. Open **Tours → Import MVP drafts**.
4. Select **Create or refresh MVP drafts**.
5. Review each Tour and Campaign in the editor before publishing.

The importer is explicit and idempotent. It creates only drafts, refreshes only
records that are still drafts, appends seed taxonomies without deleting editor
terms, and protects records moved to any other WordPress status. It never runs on
plugin activation.

Before a Tour may be published, an operator must review the current KSh rate,
residency and group assumptions, vehicle, accommodation, inclusions, and media
rights. Rates, photographs, and policy text remain absent until approved.

## Source validation

Run:

```powershell
python tools/validate_mvp_seed.py
```

The validator checks the exact three identities and sources, required fields,
draft/noindex campaign governance, relationship integrity, and the absence of
public USD prices, approval sentinels, media, policies, and invented KSh rates.
