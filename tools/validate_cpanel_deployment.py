#!/usr/bin/env python3
"""Validate the checked-in cPanel deployment boundary."""

from __future__ import annotations

import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
MANIFEST = ROOT / ".cpanel.yml"

EXPECTED_LINES = [
    "---",
    "deployment:",
    "  tasks:",
    "    - export WP_ROOT=/home/holidayk/public_html",
    "    - /bin/mkdir -p $WP_ROOT/wp-content/themes",
    "    - /bin/mkdir -p $WP_ROOT/wp-content/plugins",
    "    - /bin/cp -R wp-content/themes/hks-wayfinder $WP_ROOT/wp-content/themes/",
    "    - /bin/cp -R wp-content/plugins/hks-core $WP_ROOT/wp-content/plugins/",
]

FORBIDDEN = [
    "wp-config.php",
    "wp-content/uploads",
    "public_html/.git",
    "cp -R .",
    "cp -R *",
    "rm -rf",
    "database",
]


def main() -> int:
    try:
        text = MANIFEST.read_text(encoding="utf-8")
    except OSError as error:
        print(f"cPanel deployment validation failed: {error}")
        return 1

    errors = []
    lines = text.splitlines()

    if lines != EXPECTED_LINES:
        errors.append(".cpanel.yml does not match the reviewed explicit deployment task list")

    for forbidden in FORBIDDEN:
        if forbidden in text:
            errors.append(f"forbidden deployment scope or command: {forbidden}")

    for relative in (
        "wp-content/themes/hks-wayfinder",
        "wp-content/plugins/hks-core",
    ):
        if not (ROOT / relative).is_dir():
            errors.append(f"deployment source directory is missing: {relative}")

    if errors:
        print("cPanel deployment validation failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print("cPanel deployment validation passed (theme and plugin only).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
