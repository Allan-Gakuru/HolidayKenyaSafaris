# Holiday Kenya Safaris official logo assets

The approved artwork is `masters/holiday-kenya-safaris-logo.svg`. It is the same logo used by the website header and mobile drawer. The earlier Wayfinder redraw and all of its production exports have been removed.

| Use | Asset |
|---|---|
| Header, navigation, documents | `masters/holiday-kenya-safaris-logo.svg` |
| Footer on a dark background | `masters/holiday-kenya-safaris-logo-reversed.svg` |
| Compact browser icon | `masters/holiday-kenya-safaris-icon.svg` |
| Browser PNG icon | `exports/holiday-kenya-safaris-favicon-32.png` |
| Apple touch icon | `exports/holiday-kenya-safaris-apple-touch-icon-180.png` |
| WordPress Site Icon | `exports/holiday-kenya-safaris-site-icon-512.png` |
| Shared-link preview | `exports/holiday-kenya-safaris-social-1200x630.png` |
| Raster document logo | `exports/holiday-kenya-safaris-logo-1200.png` |

The compact icon extracts the complete compass monogram from the approved SVG without redrawing, retyping, or moving its paths. Raster exports have white backgrounds. The sharing image is a 1200 × 630 PNG with the full logo centered and clear space around it.

Rebuild with `node tools/brand/build_logo.cjs` from the workspace root. Node.js and the `sharp` package are build-time dependencies only; set `NODE_PATH` if using the bundled package directory. The builder copies the approved masters and web exports into the theme and records file hashes in `brand/manifest.json`.

Run `python -B tools/brand/validate_assets.py` and `python -B tools/validate_scaffold.py` after rebuilding. Preview the assets in `brand/specimen.html`. Do not regenerate artwork from the old concept boards or the unused historical source font.

Keep the supplied `Holiday-Kenya-Safaris-Logo-Approved.png` as a client reference. Historical presentation material under `outputs/` is not a production asset source.

Use the full logo at 160px or wider where practical. Use the official monogram for small square placements. Preserve aspect ratio and generous clear space; never stretch, decorate, or retype the artwork. Montserrat remains the website font, independent of the outlined logo. Print and vehicle applications still need substrate, color, and scale proofing; see `PRINT-SPEC.md`.
