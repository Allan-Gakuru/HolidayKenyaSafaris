# Phase 2: WordPress Foundation

Date: 2026-07-14
Status: source scaffold complete; cPanel activation and integration remain pending

## Outcome

The repository now contains the two deployable WordPress code units required by the execution contract:

```text
wp-content/
  plugins/
    hks-core/
  themes/
    hks-wayfinder/
```

WordPress core, the database, uploads, environment configuration, and a local WordPress runtime are deliberately excluded. The operating loop remains local source work, `php -l`, GitHub, cPanel deployment, and browser/dashboard verification.

## Runtime baseline

The provisional source baseline is:

- WordPress 6.6 or later;
- PHP 8.3 or later;
- `theme.json` version 3;
- Secure Custom Fields installed and active before `hks-core` activation;
- HTTPS and the current WordPress database baseline on cPanel.

This baseline uses the current WordPress recommendation of PHP 8.3+, MariaDB 10.11+ or MySQL 8.0+, and HTTPS. `theme.json` version 3 works with WordPress 6.6+. Secure Custom Fields itself has a lower published floor of WordPress 6.2 and PHP 7.4, but the project uses the stricter theme/runtime baseline.

Official references:

- `https://wordpress.org/about/requirements/`
- `https://developer.wordpress.org/block-editor/reference-guides/theme-json-reference/theme-json-living/`
- `https://developer.wordpress.org/secure-custom-fields/`

Actual cPanel WordPress, PHP, database, and extension versions remain `CLIENT CONFIRMATION REQUIRED`. If the host cannot meet this baseline, change the declared requirement only after a deliberate compatibility review; do not silently rely on older runtime behavior.

## HKS Wayfinder theme

The custom block theme includes:

- valid block-theme metadata, `theme.json`, `functions.php`, and fallback templates;
- 720px reading and 1240px wide layout tokens;
- the approved Wayfinder palette, fluid type scale, spacing scale, modest radii, and accessible focus treatment;
- constrained editor colour, gradient, and spacing escape hatches;
- production horizontal-logo and favicon assets copied into the deployable theme;
- a PHP-registered header pattern so template HTML does not hard-code an installation path;
- Site Icon-aware SVG/PNG/Apple touch icon fallbacks;
- self-hosted Montserrat variable WOFF2 subsets with system fallbacks, license, upstream URLs, and SHA-256 records;
- restrained index, page, single, 404, header, and footer fallbacks.

The generic templates are a foundation, not the finished public site. Tour, Campaign, catalogue, destination, homepage, trust, contact, and conversion templates follow after the structured models and conversion component exist.

## HKS Core plugin

The site plugin includes:

- a guarded, versioned plugin bootstrap;
- a canonical `Update URI` so WordPress.org slug collisions cannot overwrite the private plugin;
- WordPress, PHP, and Secure Custom Fields dependency declarations;
- a dependency-free namespace autoloader;
- conservative activation, multisite, deactivation, and data-retention behavior;
- runtime requirement checks and administrator notices;
- an explicit module contract and folders for Content, Fields, Conversion, and Analytics;
- version-controlled `acf-json/`, blocks, assets, languages, and future-module boundaries.

The scaffold intentionally does not yet register post types, taxonomies, fields, analytics, or WhatsApp behavior. That keeps this release small and makes the next content-model change independently reviewable.

## Font delivery

The theme carries:

- Google Fonts Montserrat v31 variable WOFF2 subsets for Latin and Latin Extended, used site-wide at weights 400–800;
- the Montserrat SIL OFL 1.1 notice;
- `assets/fonts/SOURCES.json` with source URLs and hashes.

`tools/theme/build_fonts.py` verifies the pinned upstream inputs and reproduces the deployable font package. The public theme does not make runtime requests to Google Fonts or another font CDN.

## Verification

The complete PHP inventory is linted with:

```powershell
& .\tools\lint-php.ps1
```

The Phase 2 run passed for all 9 PHP files using PHP 8.3.32. This proves syntax only; it does not load WordPress or SCF.

Static scaffold validation is run with:

```powershell
python tools\validate_scaffold.py
```

Brand assets retain their repository-source hashes, font files retain their recorded hashes, JSON parses, block-template structure is checked, and no generated Mara or Mercy image is included in the deployable code.

The internal `tools/theme/font-smoke.html` surface now uses the self-hosted Montserrat variable face for both display and body roles. Repository validation checks its WOFF2 signature, declared local paths, provenance URLs, output hashes, and SIL Open Font License.

## First cPanel integration check

After the owner deploys this commit:

1. Record WordPress, PHP, database, and Secure Custom Fields versions.
2. Confirm required PHP extensions and HTTPS.
3. Install and activate Secure Custom Fields; do not install ACF/ACF Pro alongside it.
4. Activate `hks-core`, then `hks-wayfinder`.
5. Inspect Site Health and the PHP error log for warnings or fatals.
6. Open Appearance → Editor, confirm the header/footer and self-hosted fonts, and configure navigation.
7. Confirm favicon fallbacks and the Site Icon override.
8. Verify that no database content, uploads, or unrelated plugins were overwritten.

The authoritative integration result will come from that cPanel/browser/dashboard pass.

## Next phase

Implement the durable content model in `hks-core`: Tour and Campaign post types, Destination/Tour Type/Occasion/Travel Style taxonomies, a boot-time version/migration path for Git deployments, SCF API/version safeguards, SCF Local JSON, editor guidance, confirmation states, and publication safeguards. Presentation remains in the theme.
