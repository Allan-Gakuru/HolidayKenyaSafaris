#!/usr/bin/env python3
"""Build the self-hosted theme font package from verified upstream sources."""

from __future__ import annotations

import argparse
import hashlib
import json
import shutil
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[2]
SORA_LICENSE = ROOT / "brand" / "source-fonts" / "Sora-OFL.txt"
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

SORA_LATIN_SHA256 = "fa26406eeda9a3c6ec3d9ea8813c3045d6dc755e30c716d5c094e8ef43be5a7f"
SORA_LATIN_EXT_SHA256 = "c163c536f68befd99a83d4f17e8b88030e7f54229688a45c44f70c7149db7385"
INTER_ARCHIVE_SHA256 = "9883fdd4a49d4fb66bd8177ba6625ef9a64aa45899767dde3d36aa425756b11e"
INTER_FONT_MEMBER = "web/InterVariable.woff2"
INTER_LICENSE_MEMBER = "LICENSE.txt"


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


def build_sora(latin_source: Path, latin_ext_source: Path) -> tuple[Path, Path]:
    require_hash(latin_source, SORA_LATIN_SHA256, "Sora v17 Latin WOFF2")
    require_hash(
        latin_ext_source, SORA_LATIN_EXT_SHA256, "Sora v17 Latin Extended WOFF2"
    )
    latin_output = FONT_DIR / "sora-latin-variable.woff2"
    latin_ext_output = FONT_DIR / "sora-latin-ext-variable.woff2"
    shutil.copyfile(latin_source, latin_output)
    shutil.copyfile(latin_ext_source, latin_ext_output)
    shutil.copyfile(SORA_LICENSE, LICENSE_DIR / "Sora-OFL.txt")
    return latin_output, latin_ext_output


def extract_inter(archive_path: Path) -> tuple[Path, Path]:
    require_hash(archive_path, INTER_ARCHIVE_SHA256, "Inter 4.1 archive")
    output = FONT_DIR / "inter-variable.woff2"
    license_output = LICENSE_DIR / "Inter-OFL.txt"

    with ZipFile(archive_path) as archive:
        members = set(archive.namelist())
        required = {INTER_FONT_MEMBER, INTER_LICENSE_MEMBER}
        missing = required - members
        if missing:
            raise SystemExit(f"Inter archive is missing: {sorted(missing)}")
        output.write_bytes(archive.read(INTER_FONT_MEMBER))
        license_output.write_bytes(archive.read(INTER_LICENSE_MEMBER))

    return output, license_output


def inspect_woff2(path: Path) -> None:
    if path.stat().st_size < 1024 or path.read_bytes()[:4] != b"wOF2":
        raise SystemExit(f"Expected a valid WOFF2 container: {path}")


def write_manifest(
    inter_archive: Path,
    sora_latin_output: Path,
    sora_latin_ext_output: Path,
    inter_output: Path,
    inter_license: Path,
) -> None:
    payload = {
        "generated_by": "tools/theme/build_fonts.py",
        "families": {
            "Sora": {
                "version": "2.000",
                "weights": "100 800 (theme uses 600 and 700)",
                "source_css": "https://fonts.googleapis.com/css2?family=Sora:wght@100..800&display=swap",
                "latin_source": "https://fonts.gstatic.com/s/sora/v17/xMQbuFFYT72XzQUpDg.woff2",
                "latin_source_sha256": SORA_LATIN_SHA256,
                "latin_output": sora_latin_output.relative_to(ROOT).as_posix(),
                "latin_output_sha256": sha256(sora_latin_output),
                "latin_ext_source": "https://fonts.gstatic.com/s/sora/v17/xMQbuFFYT72XzQspDre2.woff2",
                "latin_ext_source_sha256": SORA_LATIN_EXT_SHA256,
                "latin_ext_output": sora_latin_ext_output.relative_to(ROOT).as_posix(),
                "latin_ext_output_sha256": sha256(sora_latin_ext_output),
                "license": (LICENSE_DIR / "Sora-OFL.txt").relative_to(ROOT).as_posix(),
            },
            "Inter": {
                "version": "4.1",
                "weights": "100 900 (theme uses 400, 500, and 600)",
                "source": "https://github.com/rsms/inter/releases/download/v4.1/Inter-4.1.zip",
                "archive_sha256": sha256(inter_archive),
                "archive_member": INTER_FONT_MEMBER,
                "output": inter_output.relative_to(ROOT).as_posix(),
                "output_sha256": sha256(inter_output),
                "license": inter_license.relative_to(ROOT).as_posix(),
            },
        },
    }
    SOURCE_MANIFEST.write_text(
        json.dumps(payload, indent=2) + "\n", encoding="utf-8", newline="\n"
    )


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--inter-archive",
        required=True,
        type=Path,
        help="Path to the official Inter-4.1.zip release archive.",
    )
    parser.add_argument(
        "--sora-latin",
        required=True,
        type=Path,
        help="Path to the official Google Fonts Sora v17 Latin WOFF2.",
    )
    parser.add_argument(
        "--sora-latin-ext",
        required=True,
        type=Path,
        help="Path to the official Google Fonts Sora v17 Latin Extended WOFF2.",
    )
    arguments = parser.parse_args()
    inter_archive = arguments.inter_archive.resolve()
    sora_latin = arguments.sora_latin.resolve()
    sora_latin_ext = arguments.sora_latin_ext.resolve()

    for label, path in (
        ("Inter archive", inter_archive),
        ("Sora Latin WOFF2", sora_latin),
        ("Sora Latin Extended WOFF2", sora_latin_ext),
    ):
        if not path.is_file():
            raise SystemExit(f"{label} does not exist: {path}")

    FONT_DIR.mkdir(parents=True, exist_ok=True)
    LICENSE_DIR.mkdir(parents=True, exist_ok=True)

    sora_latin_output, sora_latin_ext_output = build_sora(
        sora_latin, sora_latin_ext
    )
    inter_output, inter_license = extract_inter(inter_archive)
    for output in (sora_latin_output, sora_latin_ext_output, inter_output):
        inspect_woff2(output)
    write_manifest(
        inter_archive,
        sora_latin_output,
        sora_latin_ext_output,
        inter_output,
        inter_license,
    )

    print(f"Copied {sora_latin_output.relative_to(ROOT)}")
    print(f"Copied {sora_latin_ext_output.relative_to(ROOT)}")
    print(f"Extracted {inter_output.relative_to(ROOT)}")
    print(f"Recorded {SOURCE_MANIFEST.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
