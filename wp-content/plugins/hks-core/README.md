# HKS Core

`hks-core` owns durable Holiday Kenya Safaris site behavior. Theme presentation belongs in `hks-wayfinder`; catalogue structure and business rules stay here so changing themes does not remove the content model.

## Current scope

This initial scaffold provides:

- a guarded, versioned plugin bootstrap;
- a small namespace autoloader with no Composer/runtime dependency;
- activation checks for WordPress 6.6+ and PHP 8.3+;
- a WordPress plugin dependency declaration for Secure Custom Fields;
- translation loading from `languages/`;
- conservative activation/deactivation hooks; and
- an explicit module contract and directories for later content, field, conversion, and analytics modules.

It intentionally does **not** register post types, taxonomies, SCF field groups, blocks, analytics, or WhatsApp conversion logic yet.

## Structure

```text
hks-core.php          Plugin entry point and constants
src/                  Namespaced PHP source
  Contracts/          Shared module contracts
  Content/            Future content types and taxonomies
  Fields/             Future SCF integration and validation
  Conversion/         Future intake and WhatsApp handoff logic
  Analytics/          Future event-contract integration
acf-json/             Version-controlled SCF Local JSON
blocks/               Future server-rendered/custom blocks
assets/               Future plugin-owned scripts and styles
languages/            Translation files
```

New modules implement `HolidayKenyaSafaris\Core\Contracts\Module` and are added through the `hks_core_module_classes` filter. A module's `register()` method should attach WordPress hooks rather than execute request-time behavior immediately.

## Lifecycle policy

- Activation fails with a readable administrator message on an unsupported PHP or WordPress version.
- Compatible activation records only `hks_core_version`; no rewrite flush or catalogue mutation occurs in this scaffold.
- Deactivation never deletes settings or future catalogue content.
- Uninstall deletion is not implemented. Any future uninstall routine must require a separate, explicit data-retention decision.

The cPanel PHP and WordPress versions still require host confirmation. The baseline constants and plugin headers are kept together in `hks-core.php` so they can be revised deliberately after that confirmation.

## Local verification

No local WordPress runtime is required. Syntax-check every PHP file with the available PHP binary:

```powershell
Get-ChildItem -Path wp-content\plugins\hks-core -Filter *.php -Recurse |
  ForEach-Object { php -l $_.FullName }
```
