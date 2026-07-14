# HKS Core

`hks-core` owns durable Holiday Kenya Safaris catalogue structure, editorial governance, and conversion behavior. Presentation belongs in `hks-wayfinder`; changing themes must not remove Tours, Campaigns, source records, or inquiry rules.

## Current scope

Version `0.4.0` provides:

- guarded WordPress 6.6+, PHP 8.3+, and Secure Custom Fields 6.9.1+ boot requirements;
- versioned, retry-safe upgrades and soft rewrite refreshes;
- canonical Tour, Campaign, and reusable FAQ post types;
- Destination, Tour Type, Occasion/Audience, and Travel Style taxonomies;
- code-owned SCF field groups with deterministic keys;
- private source, price, policy, proof, rights, analytics, and global-setting records;
- controlled public-field REST exposure; and
- shared publication rules across SCF, REST, and programmatic saves;
- an idempotent, administrator-triggered three-package MVP draft importer; and
- an explicitly consented private inquiry record, visitor review, and WhatsApp handoff.

Campaigns link to exactly one Tour and may change messaging or presentation, never itinerary, logistics, inclusions, policy, or price facts. Drafts remain saveable while incomplete. Public or scheduled records must pass the publication rules.

## Structure

```text
hks-core.php          Plugin entry point, dependency declaration, and versions
src/Content/          Post types, taxonomies, and deferred rewrite handling
src/Fields/           SCF definitions, controlled choices, and publication rules
src/Conversion/       Private inquiry capture, administration, analytics events, and WhatsApp handoff
src/Analytics/        Reserved for configured vendor integrations
acf-json/             Reserved; current field groups are registered in code
blocks/               Server-rendered quote CTA and future constrained blocks
assets/               Plugin-owned scripts and styles
languages/            Translation files
```

Modules implement `HolidayKenyaSafaris\Core\Contracts\Module`. The default module list is registered in `Plugin.php` and remains filterable through `hks_core_module_classes`.

## Editorial safety

- Source and confirmation fields stay private.
- Raw price records stay out of anonymous SCF REST responses.
- A provisional KSh `From` price may publish only with a positive amount, explicit placeholder status, and complete assumptions and disclaimer.
- Converted estimates and expired prices cannot be displayed as current `From` prices.
- `CLIENT CONFIRMATION REQUIRED` is rejected anywhere in public candidate copy.
- Campaigns cannot publish without one published Tour and a complete campaign brief.
- Hiding or deleting a Tour returns its linked public Campaigns to Draft.
- Media-rights metadata is an editorial/launch audit, not an automatic post rejection.

## Lifecycle policy

- Activation fails with a readable message when WordPress, PHP, or the official Secure Custom Fields dependency is unsupported.
- Network activation requires Secure Custom Fields to be network active first.
- Activation and version migrations schedule a soft rewrite refresh after content registration; no catalogue content is deleted.
- Deactivation preserves settings and catalogue content.
- Uninstall deletion is not implemented without a separate retention decision.

## Verification

No local WordPress runtime is required:

```powershell
& .\tools\lint-php.ps1
python -B tools\validate_scaffold.py
python -B tools\validate_content_model.py
```

Runtime behavior is verified after GitHub-to-cPanel deployment in the WordPress dashboard and browser.
