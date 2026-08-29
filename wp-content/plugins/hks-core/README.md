# Holiday Kenya Safaris Core

`hks-core` owns durable Holiday Kenya Safaris catalogue structure, editorial governance, and conversion behavior. Presentation belongs in `hks-wayfinder`; changing themes must not remove Tours, Campaigns, source records, or inquiry rules.

## Current scope

Version `0.11.0` provides:

- guarded WordPress 6.6+, PHP 8.3+, and Secure Custom Fields 6.9.1+ boot requirements;
- versioned, retry-safe upgrades and soft rewrite refreshes;
- canonical Tour, Campaign, and reusable FAQ post types, plus native WordPress Posts for Travel Guides;
- public Destination, Article Topic, Tour Scope, Tour Type, Occasion/Audience, and Travel Style taxonomies with canonical term archives;
- one Destination taxonomy shared by Tours and Posts while the theme keeps catalogue queries and counts Tour-only;
- code-owned SCF field groups with deterministic keys;
- hidden source-audit metadata and lean public Tour fields;
- controlled public-field REST exposure; and
- shared publication rules across SCF, REST, and programmatic saves;
- an idempotent administrator importer for the original MVP, seven standard site Page drafts, and 40 protected catalogue drafts in four controlled batches; and
- a private inquiry record, deduplicated email notification to the private repeatable recipient list, visitor review, and WhatsApp/email handoff.

Campaigns link to exactly one Tour and may change messaging, presentation, and their own optional selling price, never the linked Tour itinerary, logistics, inclusions, or policy facts. Drafts remain saveable while incomplete. Public or scheduled records must pass the publication rules.

Travel Guide Posts expose only public format, Primary Tour, Related Travel Guides, Destination, and Article Topic controls. Standard Guides may omit the Primary Tour. Advertorials require one published Primary Tour and reuse the existing intake-to-review-to-WhatsApp-or-email flow; they never add a direct launch shortcut. Featured images remain optional and author identity is not part of the public Travel Guides model.

## Structure

```text
hks-core.php          Plugin entry point, dependency declaration, and versions
src/Content/          Post types, taxonomies, and deferred rewrite handling
src/Fields/           SCF definitions, controlled choices, and publication rules
src/Conversion/       Private inquiry capture, configurable team notification, administration, analytics events, and WhatsApp/email handoff
src/Analytics/        Reserved for configured vendor integrations
acf-json/             Reserved; current field groups are registered in code
blocks/               Server-rendered quote CTA and future constrained blocks
assets/               Plugin-owned scripts and styles
languages/            Translation files
```

Modules implement `HolidayKenyaSafaris\Core\Contracts\Module`. The default module list is registered in `Plugin.php` and remains filterable through `hks_core_module_classes`.

## Editorial safety

- Source-audit metadata stays private and out of the client Tour form.
- Tours expose one optional positive whole `From price per person (KSh)` value; blank or zero omits the price cleanly.
- Campaigns keep a separate optional positive whole `From KSh` override and may inherit the linked Tour amount when blank.
- The client-authorized Ashford catalogue expansion may assign approved source media and reproducibly converted starting prices under the repository import rules; no import may invent policies, inclusions, exclusions, availability, or other claims.
- Every importer action creates or refreshes drafts only and protects records that an editor has moved beyond draft.
- `CLIENT CONFIRMATION REQUIRED` is rejected anywhere in public candidate copy.
- Campaigns cannot publish without one published Tour and the public landing-page fields consumed by the template.
- Advertorials cannot publish without one published Primary Tour. Standard Guides may publish without a Primary Tour; a selected relationship must reference a published Tour.
- Related Travel Guides are limited to three published native Posts and cannot include the current Post.
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
python -B tools\validate_mvp_seed.py
python -B tools\validate_phase_6_7_seed.py
python -B tools\validate_public_templates.py
python -B tools\validate_travel_guides.py
```

Runtime behavior is verified after GitHub-to-cPanel deployment in the WordPress dashboard and browser.
