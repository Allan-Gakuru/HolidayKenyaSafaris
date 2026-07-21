#!/usr/bin/env python3
"""Build the self-hosted Montserrat theme font package from verified sources."""

from __future__ import annotations

import argparse
import hashlib
import json
import shutil
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
FONT_DIR = (
    ROOT
    / "wp-content"
    / "themes"
    / "hks-wayfinder"
    / "assets"
    / "fonts"
)
LICENSE_DIR = FONT_DIR / "licenses"
SOURCE_MANIFEST = FONT_DIR / "SOURCES.json"

MONTSERRAT_CSS_URL = (
    "https://fonts.googleapis.com/css2?"
    "family=Montserrat:wght@100..900&display=swap"
)
MONTSERRAT_LATIN_URL = (
    "https://fonts.gstatic.com/s/montserrat/v31/"
    "JTUSjIg1_i6t8kCHKm459Wlhyw.woff2"
)
MONTSERRAT_LATIN_EXT_URL = (
    "https://fonts.gstatic.com/s/montserrat/v31/"
    "JTUSjIg1_i6t8kCHKm459Wdhyzbi.woff2"
)
MONTSERRAT_LICENSE_URL = (
    "https://raw.githubusercontent.com/google/fonts/main/ofl/montserrat/OFL.txt"
)
MONTSERRAT_LATIN_SHA256 = (
    "06b16db7a969135d48d38c49183be7fb88d4452e2a3011957c7851941f4e4879"
)
MONTSERRAT_LATIN_EXT_SHA256 = (
    "54d9a78b7ff60b689ad9f3017ffac8547b5d871afec733f6c1c3ae36577ee504"
)
MONTSERRAT_LICENSE_SHA256 = (
    "8b7141c03fa4f8d44e6345d5d4931709290f0f67875e452e95ac1fd3a027802e"
)


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def require_hash(path: Path, expected: str, label: str) -> None:
    actual = sha256(path)
    if actual != expected:
        raise SystemExit(
            f"{label} SHA-256 mismatch: expected {expected}, received {actual}"
        )


def inspect_woff2(path: Path) -> None:
    if path.stat().st_size < 1024 or path.read_bytes()[:4] != b"wOF2":
        raise SystemExit(f"Expected a valid WOFF2 container: {path}")


def build_montserrat(
    latin_source: Path,
    latin_ext_source: Path,
    license_source: Path,
) -> tuple[Path, Path, Path]:
    require_hash(
        latin_source,
        MONTSERRAT_LATIN_SHA256,
        "Montserrat Google Fonts v31 Latin WOFF2",
    )
    require_hash(
        latin_ext_source,
        MONTSERRAT_LATIN_EXT_SHA256,
        "Montserrat Google Fonts v31 Latin Extended WOFF2",
    )
    require_hash(
        license_source,
        MONTSERRAT_LICENSE_SHA256,
        "Montserrat OFL",
    )

    latin_output = FONT_DIR / "montserrat-latin-variable.woff2"
    latin_ext_output = FONT_DIR / "montserrat-latin-ext-variable.woff2"
    license_output = LICENSE_DIR / "Montserrat-OFL.txt"
    shutil.copyfile(latin_source, latin_output)
    shutil.copyfile(latin_ext_source, latin_ext_output)
    license_text = license_source.read_text(encoding="utf-8")
    normalized_license = "\n".join(
        line.rstrip() for line in license_text.splitlines()
    ) + "\n"
    license_output.write_text(normalized_license, encoding="utf-8", newline="\n")
    return latin_output, latin_ext_output, license_output


def write_manifest(
    latin_output: Path,
    latin_ext_output: Path,
    license_output: Path,
) -> None:
    payload = {
        "generated_by": "tools/theme/build_fonts.py",
        "families": {
            "Montserrat": {
                "version": "Google Fonts API v31",
                "weights": "100 900 (theme uses the 400-800 range)",
                "source_css": MONTSERRAT_CSS_URL,
                "latin_source": MONTSERRAT_LATIN_URL,
                "latin_source_sha256": MONTSERRAT_LATIN_SHA256,
                "latin_output": latin_output.relative_to(ROOT).as_posix(),
                "latin_output_sha256": sha256(latin_output),
                "latin_ext_source": MONTSERRAT_LATIN_EXT_URL,
                "latin_ext_source_sha256": MONTSERRAT_LATIN_EXT_SHA256,
                "latin_ext_output": latin_ext_output.relative_to(ROOT).as_posix(),
                "latin_ext_output_sha256": sha256(latin_ext_output),
                "license_source": MONTSERRAT_LICENSE_URL,
                "license_source_sha256": MONTSERRAT_LICENSE_SHA256,
                "license": license_output.relative_to(ROOT).as_posix(),
                "license_output_sha256": sha256(license_output),
            }
        },
    }
    SOURCE_MANIFEST.write_text(
        json.dumps(payload, indent=2) + "\n", encoding="utf-8", newline="\n"
    )


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--montserrat-latin",
        required=True,
        type=Path,
        help="Path to the official Google Fonts Montserrat v31 Latin WOFF2.",
    )
    parser.add_argument(
        "--montserrat-latin-ext",
        required=True,
        type=Path,
        help="Path to the official Google Fonts Montserrat v31 Latin Extended WOFF2.",
    )
    parser.add_argument(
        "--montserrat-license",
        required=True,
        type=Path,
        help="Path to the official Montserrat OFL text.",
    )
    arguments = parser.parse_args()

    sources = (
        ("Montserrat Latin WOFF2", arguments.montserrat_latin.resolve()),
        ("Montserrat Latin Extended WOFF2", arguments.montserrat_latin_ext.resolve()),
        ("Montserrat OFL", arguments.montserrat_license.resolve()),
    )
    for label, path in sources:
        if not path.is_file():
            raise SystemExit(f"{label} does not exist: {path}")

    FONT_DIR.mkdir(parents=True, exist_ok=True)
    LICENSE_DIR.mkdir(parents=True, exist_ok=True)

    latin_output, latin_ext_output, license_output = build_montserrat(
        sources[0][1], sources[1][1], sources[2][1]
    )
    for output in (latin_output, latin_ext_output):
        inspect_woff2(output)
    write_manifest(latin_output, latin_ext_output, license_output)

    print(f"Copied {latin_output.relative_to(ROOT)}")
    print(f"Copied {latin_ext_output.relative_to(ROOT)}")
    print(f"Copied {license_output.relative_to(ROOT)}")
    print(f"Recorded {SOURCE_MANIFEST.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
