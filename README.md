# Holiday Kenya Safaris

Custom WordPress website for Holiday Kenya Safaris, a local-market travel brand operated by Ashford Tours & Travel. The site is designed around qualified WhatsApp quote inquiries rather than online checkout.

## Start Here

Read [AGENTS.md](AGENTS.md) and its 12 required documents before changing product, content, design, analytics, or deployment behavior. [PRODUCT.md](PRODUCT.md) captures the confirmed strategic design register. The numbered phase documents in [docs](docs) record the baseline, identity, foundation, content model, three-package seed, saved inquiry handoff, and public MVP templates.

## Architecture

- Custom block theme: `wp-content/themes/hks-wayfinder/`.
- Site plugin: `wp-content/plugins/hks-core/`.
- Secure Custom Fields with version-controlled field definitions.
- Primary conversion: explicitly saved private inquiry, visitor-reviewed WhatsApp message, and visitor-controlled send.

## Development and Verification

This repository contains the deployable custom theme and site plugin, not WordPress core or a local WordPress runtime.

Before pushing PHP changes, syntax-check the complete theme and plugin PHP inventory with the installed PHP CLI:

```powershell
& .\tools\lint-php.ps1
python -B tools\validate_scaffold.py
python -B tools\validate_content_model.py
python -B tools\validate_mvp_seed.py
python -B tools\validate_conversion.py
python -B tools\validate_public_templates.py
python -B tools\validate_cpanel_deployment.py
```

The working delivery loop is:

1. Change source locally.
2. Run PHP syntax checks and relevant static or unit checks.
3. Commit and push to GitHub.
4. The repository owner deploys to cPanel.
5. Verify the deployed site in the browser and, when access is supplied, in the WordPress dashboard.

Node.js may be added later for theme asset tooling or automated browser tests, but it is not a prerequisite for a local WordPress instance.

The production Wayfinder masters, exports, usage rules, and build tools live under `brand/` and `tools/brand/`.

## Repository Boundaries

Track custom theme/plugin code, field definitions, source assets, reviewed seed/import material, tests, workflows, and documentation. Do not commit `wp-config.php`, secrets, uploads, production database dumps, caches, or local runtime state.

## Source Warnings

- Exact brand name: **Holiday Kenya Safaris**.
- Selected identity: **The Wayfinder**.
- Raster identity concepts are redraw references, not production masters.
- Generated presentation visuals are not evidence of real trips or customers.
- Provisional KSh `From` prices must be explicitly labeled by status and include season, residency, group, transport, accommodation, inclusion, basis, and disclaimer assumptions; converted estimates and expired rates stay non-public.
- Never publish invented or unverified rates, photographs, reviews, policies, memberships, licences, availability, or company claims.
