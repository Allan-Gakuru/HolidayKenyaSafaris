# Official Wayfinder logo assets

Updated: 2026-09-03

The client requested complete removal of the earlier logo from website assets and replacement of every active logo reference with the approved artwork. This supersedes the earlier Phase 1 redraw as the production baseline.

The source is `brand/masters/holiday-kenya-safaris-logo.svg`, copied without geometry changes from the approved header SVG. The existing official reversed logo remains the footer asset. The compact icon extracts the approved compass monogram; it does not recreate its lettering. The old production masters, raster exports, deployed icons, and redraw generator have been removed.

`node tools/brand/build_logo.cjs` exports the official favicon, touch icon, Site Icon, full raster logo and 1200 × 630 sharing image, then copies web assets to the theme. The build requires Node.js and sharp. `brand/manifest.json` records hashes and dimensions are checked by `python -B tools/brand/validate_assets.py`.

The theme emits Open Graph and Twitter sharing tags with the official raster logo, the current page title and canonical page link. Protected post excerpts are omitted. New image filenames avoid reusing old asset URLs. Exact redirects map retired theme asset URLs to the approved replacements. The cPanel deployment manifest removes only the five retired theme files after copying the new release.

After deployment, clear site/CDN page caches and inspect the page metadata. Sharing services may retain previews created before deployment until they fetch the page again. Historical concept presentations under `outputs/` are reference material only, never website assets.

For current asset selection and build instructions see `brand/README.md`; for artwork review see `brand/specimen.html`. Physical print and vehicle applications still require production proofing.
