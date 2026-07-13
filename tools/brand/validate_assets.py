#!/usr/bin/env python3
"""Validate the production Wayfinder logo package.

This is intentionally independent from ``build_logo.py``.  A generator bug must
not be able to teach the validator that its own output is correct.
"""

from __future__ import annotations

import hashlib
import json
import re
import sys
from pathlib import Path, PurePosixPath
from typing import Any
from xml.etree import ElementTree

try:
    from PIL import Image, UnidentifiedImageError
except ImportError as exc:  # pragma: no cover - only exercised on an unprepared host
    raise SystemExit(
        "Pillow is required to decode and validate the PNG/ICO exports."
    ) from exc


ROOT = Path(__file__).resolve().parents[2]
BRAND_DIR = ROOT / "brand"
MASTER_DIR = BRAND_DIR / "masters"
EXPORT_DIR = BRAND_DIR / "exports"
MANIFEST_PATH = BRAND_DIR / "manifest.json"

SVG_NAMESPACE = "http://www.w3.org/2000/svg"
BRAND_NAME = "Holiday Kenya Safaris"
IDENTITY_NAME = "The Wayfinder"

NAVY = "#182B3A"
TEAL = "#2C7A78"
SAFFRON = "#E1A62B"
PALE_MIST = "#F3F1EA"
WHITE = "#FFFFFF"
LOGO_PALETTE = frozenset({NAVY, TEAL, SAFFRON, WHITE})


def svg_matrix() -> dict[str, tuple[str, frozenset[str]]]:
    """Return path -> (viewBox, exact rendered color set)."""

    matrix: dict[str, tuple[str, frozenset[str]]] = {
        "brand/masters/hks-wayfinder-favicon.svg": (
            "0 0 256 256",
            frozenset({NAVY, SAFFRON}),
        ),
        "brand/masters/hks-wayfinder-vehicle-door-navy.svg": (
            "0 0 780 320",
            frozenset({NAVY}),
        ),
        "brand/masters/hks-wayfinder-vehicle-door-white.svg": (
            "0 0 780 320",
            frozenset({WHITE}),
        ),
    }

    for layout, view_box in (
        ("horizontal", "0 0 720 256"),
        ("icon", "0 0 256 256"),
        ("stacked", "0 0 480 500"),
    ):
        matrix[f"brand/masters/hks-wayfinder-{layout}-primary.svg"] = (
            view_box,
            frozenset({NAVY, TEAL, SAFFRON}),
        )
        matrix[f"brand/masters/hks-wayfinder-{layout}-navy.svg"] = (
            view_box,
            frozenset({NAVY}),
        )
        matrix[f"brand/masters/hks-wayfinder-{layout}-white.svg"] = (
            view_box,
            frozenset({WHITE}),
        )
        matrix[f"brand/masters/hks-wayfinder-{layout}-reversed.svg"] = (
            view_box,
            frozenset({WHITE, SAFFRON}),
        )

    for variant, colors in (
        ("primary", frozenset({NAVY, TEAL})),
        ("navy", frozenset({NAVY})),
        ("white", frozenset({WHITE})),
    ):
        matrix[f"brand/masters/hks-wayfinder-wordmark-{variant}.svg"] = (
            "0 0 430 150",
            colors,
        )

    return matrix


SVG_ASSETS = svg_matrix()

# path -> (format, dimensions, alpha policy)
# ``transparent`` means the canvas must contain both transparent and visible
# pixels. ``opaque_mist`` means a fully opaque Pale Mist presentation canvas.
RASTER_ASSETS: dict[str, tuple[str, tuple[int, int], str]] = {
    "brand/exports/apple-touch-icon-180.png": ("PNG", (180, 180), "opaque_mist"),
    "brand/exports/favicon-16.png": ("PNG", (16, 16), "transparent"),
    "brand/exports/favicon-32.png": ("PNG", (32, 32), "transparent"),
    "brand/exports/favicon-48.png": ("PNG", (48, 48), "transparent"),
    "brand/exports/favicon.ico": ("ICO", (48, 48), "transparent"),
    "brand/exports/hks-wayfinder-horizontal-navy-1024.png": (
        "PNG",
        (1024, 364),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-horizontal-primary-1024.png": (
        "PNG",
        (1024, 364),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-horizontal-primary-512.png": (
        "PNG",
        (512, 182),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-horizontal-reversed-1024.png": (
        "PNG",
        (1024, 364),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-icon-primary-180.png": (
        "PNG",
        (180, 180),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-icon-navy-512.png": (
        "PNG",
        (512, 512),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-icon-primary-512.png": (
        "PNG",
        (512, 512),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-icon-reversed-512.png": (
        "PNG",
        (512, 512),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-stacked-navy-1024.png": (
        "PNG",
        (1024, 1067),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-stacked-primary-1024.png": (
        "PNG",
        (1024, 1067),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-stacked-reversed-1024.png": (
        "PNG",
        (1024, 1067),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-vehicle-door-navy-1600.png": (
        "PNG",
        (1600, 569),
        "transparent",
    ),
    "brand/exports/hks-wayfinder-vehicle-door-white-1600.png": (
        "PNG",
        (1600, 569),
        "transparent",
    ),
    "brand/exports/social-avatar-512.png": ("PNG", (512, 512), "opaque_mist"),
}

EXPECTED_ASSETS = frozenset(SVG_ASSETS) | frozenset(RASTER_ASSETS)
EXPECTED_ICO_SIZES = frozenset({(16, 16), (32, 32), (48, 48)})

ALLOWED_ELEMENTS = frozenset({"svg", "title", "desc", "g", "path", "polygon"})
ALLOWED_ATTRIBUTES: dict[str, frozenset[str]] = {
    "svg": frozenset({"viewBox", "role", "aria-labelledby", "focusable"}),
    "title": frozenset({"id"}),
    "desc": frozenset({"id"}),
    "g": frozenset({"transform", "aria-hidden", "role"}),
    "path": frozenset(
        {
            "d",
            "fill",
            "stroke",
            "stroke-width",
            "stroke-linecap",
            "stroke-linejoin",
            "fill-rule",
            "clip-rule",
            "transform",
            "aria-hidden",
        }
    ),
    "polygon": frozenset(
        {
            "points",
            "fill",
            "stroke",
            "stroke-width",
            "stroke-linejoin",
            "transform",
            "aria-hidden",
        }
    ),
}

HEX_COLOR = re.compile(r"#[0-9A-Fa-f]{6}\Z")
SHA256 = re.compile(r"[0-9a-f]{64}\Z")
NUMBER = r"[-+]?(?:\d+(?:\.\d*)?|\.\d+)(?:[Ee][-+]?\d+)?"
TRANSLATE = re.compile(rf"translate\(\s*{NUMBER}(?:[ ,]+{NUMBER})?\s*\)\Z")
FORBIDDEN_XML = re.compile(
    rb"<!\s*(?:DOCTYPE|ENTITY)|<\s*(?:script|style|foreignObject|iframe|image|use)\b",
    re.IGNORECASE,
)
FORBIDDEN_VALUE = re.compile(r"(?:url\s*\(|javascript\s*:|data\s*:)", re.IGNORECASE)


class Validator:
    """Collect all useful failures so one run gives a complete repair list."""

    def __init__(self) -> None:
        self.errors: list[str] = []

    def fail(self, scope: str, message: str) -> None:
        self.errors.append(f"{scope}: {message}")

    def require(self, condition: bool, scope: str, message: str) -> bool:
        if not condition:
            self.fail(scope, message)
            return False
        return True


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def local_name(name: str) -> str:
    return name.rsplit("}", 1)[-1]


def paint_color(value: str) -> str | None:
    if value.strip().lower() == "none":
        return None
    if not HEX_COLOR.fullmatch(value.strip()):
        return ""
    return value.strip().upper()


def validate_view_box(
    validator: Validator, scope: str, value: str | None, expected: str
) -> None:
    if value is None:
        validator.fail(scope, "missing viewBox")
        return
    parts = value.replace(",", " ").split()
    if len(parts) != 4:
        validator.fail(scope, f"invalid viewBox {value!r}")
        return
    try:
        numbers = tuple(float(part) for part in parts)
    except ValueError:
        validator.fail(scope, f"non-numeric viewBox {value!r}")
        return
    if not all(number == number and abs(number) != float("inf") for number in numbers):
        validator.fail(scope, f"non-finite viewBox {value!r}")
    if numbers[2] <= 0 or numbers[3] <= 0:
        validator.fail(scope, f"viewBox must have positive width and height: {value!r}")
    if " ".join(parts) != expected:
        validator.fail(scope, f"viewBox is {value!r}; expected {expected!r}")


def validate_svg(path: Path, relative: str, validator: Validator) -> None:
    scope = relative
    try:
        raw = path.read_bytes()
    except OSError as exc:
        validator.fail(scope, f"cannot read file ({exc})")
        return

    if not raw:
        validator.fail(scope, "file is empty")
        return
    if len(raw) > 2 * 1024 * 1024:
        validator.fail(scope, "SVG exceeds the 2 MiB safety ceiling")
        return
    if FORBIDDEN_XML.search(raw):
        validator.fail(scope, "contains a forbidden declaration or active/embedded element")
    if b"\x00" in raw:
        validator.fail(scope, "contains a NUL byte")

    try:
        root = ElementTree.fromstring(raw)
    except ElementTree.ParseError as exc:
        validator.fail(scope, f"XML does not parse ({exc})")
        return

    if root.tag != f"{{{SVG_NAMESPACE}}}svg":
        validator.fail(scope, "root must be an SVG element in the standard SVG namespace")

    expected_view_box, expected_colors = SVG_ASSETS[relative]
    validate_view_box(validator, scope, root.attrib.get("viewBox"), expected_view_box)

    if root.attrib.get("role") != "img":
        validator.fail(scope, "root must expose role=\"img\"")
    labelled_by = root.attrib.get("aria-labelledby", "").split()
    if not labelled_by:
        validator.fail(scope, "root must have a non-empty aria-labelledby reference")

    ids: set[str] = set()
    titles: list[ElementTree.Element] = []
    rendered_colors: set[str] = set()
    graphical_elements = 0

    for element in root.iter():
        if not isinstance(element.tag, str):
            validator.fail(scope, "comments or processing instructions are not allowed")
            continue
        if not element.tag.startswith(f"{{{SVG_NAMESPACE}}}"):
            validator.fail(scope, f"element uses a foreign namespace: {element.tag!r}")
            continue

        name = local_name(element.tag)
        if name not in ALLOWED_ELEMENTS:
            validator.fail(scope, f"forbidden or unsupported <{name}> element")
            continue
        if name == "text":
            validator.fail(scope, "contains live <text>; all lettering must be outlined")
        if name in {"linearGradient", "radialGradient", "meshgradient"}:
            validator.fail(scope, "contains a gradient; Wayfinder marks use solid colors")

        allowed = ALLOWED_ATTRIBUTES[name]
        for attribute, value in element.attrib.items():
            if attribute.startswith("{"):
                validator.fail(scope, f"namespaced attribute {attribute!r} is not allowed")
                continue
            if attribute not in allowed:
                validator.fail(scope, f"attribute {attribute!r} is not allowed on <{name}>")
            if attribute.lower().startswith("on"):
                validator.fail(scope, f"event handler {attribute!r} is not allowed")
            if FORBIDDEN_VALUE.search(value):
                validator.fail(scope, f"unsafe reference in {attribute!r}")

        identifier = element.attrib.get("id")
        if identifier:
            if identifier in ids:
                validator.fail(scope, f"duplicate id {identifier!r}")
            ids.add(identifier)

        if name == "title":
            titles.append(element)
        elif name not in {"desc"} and element.text and element.text.strip():
            validator.fail(scope, f"unexpected text content inside <{name}>")

        transform = element.attrib.get("transform")
        if transform and not TRANSLATE.fullmatch(transform):
            validator.fail(scope, f"unsupported transform {transform!r}; only translate() is allowed")

        if name in {"path", "polygon"}:
            graphical_elements += 1
            if "fill" not in element.attrib:
                validator.fail(scope, f"<{name}> has implicit black fill; declare fill explicitly")
            for attribute in ("fill", "stroke"):
                if attribute not in element.attrib:
                    continue
                color = paint_color(element.attrib[attribute])
                if color == "":
                    validator.fail(
                        scope,
                        f"{attribute}={element.attrib[attribute]!r} is not a solid six-digit HEX color or none",
                    )
                elif color is not None:
                    rendered_colors.add(color)
                    if color not in LOGO_PALETTE:
                        validator.fail(scope, f"off-palette {attribute} color {color}")

            if element.attrib.get("fill", "").lower() == "none" and "stroke" not in element.attrib:
                validator.fail(scope, f"<{name}> is invisible (fill=none and no stroke)")
            if name == "path" and not element.attrib.get("d", "").strip():
                validator.fail(scope, "<path> has empty path data")
            if name == "polygon":
                coordinates = re.findall(NUMBER, element.attrib.get("points", ""))
                if len(coordinates) < 6 or len(coordinates) % 2:
                    validator.fail(scope, "<polygon> must contain at least three coordinate pairs")

    if graphical_elements == 0:
        validator.fail(scope, "contains no graphical elements")
    if len(titles) != 1:
        validator.fail(scope, f"must contain exactly one <title>; found {len(titles)}")
    elif not (titles[0].text or "").strip():
        validator.fail(scope, "accessible <title> is empty")
    elif BRAND_NAME.lower() not in (titles[0].text or "").lower():
        validator.fail(scope, f"accessible <title> must name {BRAND_NAME}")

    missing_labels = [reference for reference in labelled_by if reference not in ids]
    if missing_labels:
        validator.fail(scope, f"aria-labelledby references missing ids: {missing_labels}")

    if rendered_colors != set(expected_colors):
        validator.fail(
            scope,
            f"rendered colors are {sorted(rendered_colors)}; expected {sorted(expected_colors)}",
        )


def validate_alpha(
    image: Image.Image, policy: str, scope: str, validator: Validator
) -> None:
    if image.mode != "RGBA":
        validator.fail(scope, f"image mode is {image.mode}; expected RGBA with explicit alpha")
        return
    extrema = image.getchannel("A").getextrema()
    if policy == "transparent":
        # The 16 px favicon peaks just below 255 after high-quality resampling,
        # so require a materially visible pixel rather than a brittle exact 255.
        if extrema[0] != 0 or extrema[1] < 200:
            validator.fail(
                scope,
                f"transparent export needs clear and visible pixels; alpha range is {extrema}",
            )
    elif policy == "opaque_mist":
        if extrema != (255, 255):
            validator.fail(scope, f"presentation canvas must be fully opaque; alpha range is {extrema}")
        expected_corner = (*tuple(bytes.fromhex(PALE_MIST[1:])), 255)
        corners = (
            image.getpixel((0, 0)),
            image.getpixel((image.width - 1, 0)),
            image.getpixel((0, image.height - 1)),
            image.getpixel((image.width - 1, image.height - 1)),
        )
        if any(pixel != expected_corner for pixel in corners):
            validator.fail(scope, f"opaque canvas corners must be Pale Mist {PALE_MIST}")
    else:  # Defensive: an invalid validator policy should never pass silently.
        validator.fail(scope, f"unknown alpha policy {policy!r}")


def validate_raster(path: Path, relative: str, validator: Validator) -> None:
    scope = relative
    expected_format, expected_size, alpha_policy = RASTER_ASSETS[relative]

    try:
        with Image.open(path) as probe:
            detected_format = probe.format
            probe.verify()
    except (OSError, SyntaxError, UnidentifiedImageError) as exc:
        validator.fail(scope, f"image decoder rejected file ({exc})")
        return

    if detected_format != expected_format:
        validator.fail(scope, f"decoded format is {detected_format}; expected {expected_format}")

    try:
        with Image.open(path) as image:
            image.load()
            if image.size != expected_size:
                validator.fail(scope, f"dimensions are {image.size}; expected {expected_size}")
            if getattr(image, "is_animated", False) or getattr(image, "n_frames", 1) != 1:
                validator.fail(scope, "animated or multi-frame raster is not allowed")
            validate_alpha(image, alpha_policy, scope, validator)

            if expected_format == "ICO":
                ico = getattr(image, "ico", None)
                if ico is None:
                    validator.fail(scope, "ICO decoder did not expose embedded icon sizes")
                    return
                sizes = frozenset(ico.sizes())
                if sizes != EXPECTED_ICO_SIZES:
                    validator.fail(
                        scope,
                        f"embedded ICO sizes are {sorted(sizes)}; expected {sorted(EXPECTED_ICO_SIZES)}",
                    )
                for size in sorted(EXPECTED_ICO_SIZES):
                    try:
                        frame = ico.getimage(size)
                        frame.load()
                    except (OSError, KeyError) as exc:
                        validator.fail(scope, f"cannot decode embedded {size[0]}px icon ({exc})")
                        continue
                    if frame.size != size:
                        validator.fail(scope, f"embedded {size[0]}px frame decoded as {frame.size}")
                    validate_alpha(frame, "transparent", f"{scope} [{size[0]}px]", validator)
    except (OSError, SyntaxError, UnidentifiedImageError) as exc:
        validator.fail(scope, f"image pixels could not be fully decoded ({exc})")


def safe_manifest_path(value: Any, validator: Validator, scope: str) -> str | None:
    if not isinstance(value, str) or not value:
        validator.fail(scope, "path must be a non-empty string")
        return None
    if "\\" in value:
        validator.fail(scope, "path must use forward slashes")
    pure = PurePosixPath(value)
    if pure.is_absolute() or ".." in pure.parts or "." in pure.parts:
        validator.fail(scope, f"unsafe path {value!r}")
        return None
    return pure.as_posix()


def validate_manifest(validator: Validator) -> None:
    scope = "brand/manifest.json"
    try:
        payload = json.loads(MANIFEST_PATH.read_text(encoding="utf-8"))
    except (OSError, UnicodeError, json.JSONDecodeError) as exc:
        validator.fail(scope, f"cannot read valid UTF-8 JSON ({exc})")
        return

    if not isinstance(payload, dict):
        validator.fail(scope, "top-level value must be an object")
        return
    expected_metadata = {
        "brand": BRAND_NAME,
        "identity": IDENTITY_NAME,
        "generated_by": "tools/brand/build_logo.py",
        "source_font": "Sora Version 2.000, SIL OFL 1.1",
    }
    for key, expected in expected_metadata.items():
        if payload.get(key) != expected:
            validator.fail(scope, f"{key} is {payload.get(key)!r}; expected {expected!r}")

    assets = payload.get("assets")
    if not isinstance(assets, list):
        validator.fail(scope, "assets must be an array")
        return

    seen: set[str] = set()
    listed_order: list[str] = []
    for index, entry in enumerate(assets):
        entry_scope = f"{scope} assets[{index}]"
        if not isinstance(entry, dict):
            validator.fail(entry_scope, "entry must be an object")
            continue
        relative = safe_manifest_path(entry.get("path"), validator, entry_scope)
        if relative is None:
            continue
        listed_order.append(relative)
        if relative in seen:
            validator.fail(entry_scope, f"duplicate asset path {relative!r}")
            continue
        seen.add(relative)

        if relative not in EXPECTED_ASSETS:
            validator.fail(entry_scope, f"unexpected asset {relative!r}")
            continue
        path = ROOT / Path(*PurePosixPath(relative).parts)
        if path.is_symlink():
            validator.fail(entry_scope, "manifest assets may not be symbolic links")
        if not path.is_file():
            validator.fail(entry_scope, "listed file does not exist")
            continue

        expected_bytes = path.stat().st_size
        if type(entry.get("bytes")) is not int or entry.get("bytes") != expected_bytes:
            validator.fail(
                entry_scope,
                f"bytes is {entry.get('bytes')!r}; actual file size is {expected_bytes}",
            )
        recorded_hash = entry.get("sha256")
        if not isinstance(recorded_hash, str) or not SHA256.fullmatch(recorded_hash):
            validator.fail(entry_scope, "sha256 must be 64 lowercase hexadecimal characters")
        else:
            actual_hash = sha256(path)
            if recorded_hash != actual_hash:
                validator.fail(entry_scope, f"sha256 mismatch; actual digest is {actual_hash}")

        if relative in SVG_ASSETS:
            expected_view_box = SVG_ASSETS[relative][0]
            if entry.get("format") != "SVG":
                validator.fail(entry_scope, f"format must be 'SVG', not {entry.get('format')!r}")
            if entry.get("viewBox") != expected_view_box:
                validator.fail(
                    entry_scope,
                    f"viewBox is {entry.get('viewBox')!r}; expected {expected_view_box!r}",
                )
            if "dimensions" in entry:
                validator.fail(entry_scope, "SVG manifest entry must not declare pixel dimensions")
        else:
            expected_format, expected_dimensions, _ = RASTER_ASSETS[relative]
            if entry.get("format") != expected_format:
                validator.fail(
                    entry_scope,
                    f"format is {entry.get('format')!r}; expected {expected_format!r}",
                )
            if entry.get("dimensions") != list(expected_dimensions):
                validator.fail(
                    entry_scope,
                    f"dimensions are {entry.get('dimensions')!r}; expected {list(expected_dimensions)!r}",
                )
            if "viewBox" in entry:
                validator.fail(entry_scope, "raster manifest entry must not declare a viewBox")

    missing = EXPECTED_ASSETS - seen
    if missing:
        validator.fail(scope, f"missing manifest assets: {sorted(missing)}")
    if listed_order != sorted(listed_order):
        validator.fail(scope, "asset entries must be sorted by path for deterministic builds")


def validate_asset_inventory(validator: Validator) -> None:
    actual: set[str] = set()
    if MASTER_DIR.is_dir():
        actual.update(
            path.relative_to(ROOT).as_posix()
            for path in MASTER_DIR.rglob("*")
            if path.is_file()
        )
    else:
        validator.fail("brand/masters", "directory does not exist")
    if EXPORT_DIR.is_dir():
        actual.update(
            path.relative_to(ROOT).as_posix()
            for path in EXPORT_DIR.rglob("*")
            if path.is_file()
        )
    else:
        validator.fail("brand/exports", "directory does not exist")

    missing = EXPECTED_ASSETS - actual
    unexpected = actual - EXPECTED_ASSETS
    if missing:
        validator.fail("brand", f"missing production assets: {sorted(missing)}")
    if unexpected:
        validator.fail("brand", f"unexpected production assets: {sorted(unexpected)}")


def main() -> int:
    validator = Validator()
    validate_asset_inventory(validator)
    validate_manifest(validator)

    for relative in sorted(SVG_ASSETS):
        path = ROOT / Path(*PurePosixPath(relative).parts)
        if path.is_file():
            validate_svg(path, relative, validator)
    for relative in sorted(RASTER_ASSETS):
        path = ROOT / Path(*PurePosixPath(relative).parts)
        if path.is_file():
            validate_raster(path, relative, validator)

    if validator.errors:
        print(f"Wayfinder asset validation FAILED ({len(validator.errors)} issue(s)):")
        for error in validator.errors:
            print(f"  - {error}")
        return 1

    print(
        "Wayfinder asset validation passed: "
        f"{len(SVG_ASSETS)} SVG masters, {len(RASTER_ASSETS)} raster exports, "
        "manifest hashes, palette, semantics, dimensions, decoding, and alpha."
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
