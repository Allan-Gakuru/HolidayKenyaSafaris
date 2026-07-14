#!/usr/bin/env python3
"""Validate the Phase 2 WordPress scaffold without loading WordPress.

This script deliberately uses only the Python standard library.  It validates
the repository and deployable-code contract; PHP syntax remains the job of
``tools/lint-php.ps1`` and ``php -l``.
"""

from __future__ import annotations

import hashlib
import json
import re
import subprocess
import sys
from pathlib import Path
from typing import Any, Dict, Iterable, List, Optional, Sequence, Set, Tuple
from xml.etree import ElementTree


ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "wp-content" / "themes" / "hks-wayfinder"
PLUGIN = ROOT / "wp-content" / "plugins" / "hks-core"

WORDPRESS_VERSION = "6.6"
PHP_VERSION = "8.3"

EXPECTED_LAYOUT = {
    "contentSize": "720px",
    "wideSize": "1240px",
}

EXPECTED_PALETTE = [
    ("midnight-navy", "Midnight Navy", "#182B3A"),
    ("lake-teal", "Lake Teal", "#2C7A78"),
    ("saffron", "Wayfinder Saffron", "#E1A62B"),
    ("pale-mist", "Pale Mist", "#F3F1EA"),
    ("white", "White", "#FFFFFF"),
    ("near-black", "Near Black", "#161B1F"),
    ("whatsapp-green", "WhatsApp Green \u2014 conversion only", "#25D366"),
]

REQUIRED_THEME_FILES = [
    "style.css",
    "functions.php",
    "theme.json",
    "README.md",
    "parts/header.html",
    "parts/footer.html",
    "patterns/header.php",
    "templates/404.html",
    "templates/archive-hks_tour.html",
    "templates/front-page.html",
    "templates/home.html",
    "templates/index.html",
    "templates/page.html",
    "templates/search.html",
    "templates/single.html",
    "templates/single-hks_campaign.html",
    "templates/single-hks_tour.html",
    "templates/taxonomy-hks_destination.html",
    "inc/TourBlocks.php",
    "blocks/destination-intro/block.json",
    "blocks/tour-card/block.json",
    "blocks/tour-details/block.json",
    "blocks/tour-hero/block.json",
    "assets/fonts/SOURCES.json",
    "assets/fonts/inter-variable.woff2",
    "assets/fonts/sora-latin-ext-variable.woff2",
    "assets/fonts/sora-latin-variable.woff2",
    "assets/fonts/licenses/Inter-OFL.txt",
    "assets/fonts/licenses/Sora-OFL.txt",
]

REQUIRED_PLUGIN_FILES = [
    "hks-core.php",
    "index.php",
    "README.md",
    "acf-json/.gitkeep",
    "assets/.gitkeep",
    "blocks/.gitkeep",
    "languages/.gitkeep",
    "src/Autoloader.php",
    "src/Lifecycle.php",
    "src/Plugin.php",
    "src/Requirements.php",
    "src/Contracts/Module.php",
    "src/Analytics/.gitkeep",
    "src/Content/Module.php",
    "src/Content/PostTypes/Campaign.php",
    "src/Content/PostTypes/Faq.php",
    "src/Content/PostTypes/Inquiry.php",
    "src/Content/PostTypes/Tour.php",
    "src/Content/Taxonomies/Destination.php",
    "src/Content/Taxonomies/Occasion.php",
    "src/Content/Taxonomies/TourType.php",
    "src/Content/Taxonomies/TravelStyle.php",
    "src/Conversion/.gitkeep",
    "src/Conversion/FormToken.php",
    "src/Conversion/InquiryAdmin.php",
    "src/Conversion/InquiryRepository.php",
    "src/Conversion/Module.php",
    "src/Conversion/QuoteBlock.php",
    "src/Fields/Choices.php",
    "src/Fields/FieldGroups.php",
    "src/Fields/FieldsModule.php",
    "src/Fields/PublicationGuard.php",
    "src/Fields/PublicationRules.php",
    "data/mvp-seed.json",
    "src/Seed/Module.php",
    "src/Seed/MvpSeeder.php",
    "assets/css/inquiry.css",
    "assets/js/inquiry.js",
    "blocks/quote-cta/block.json",
]

# Each deployable copy must remain byte-for-byte identical to its production
# source.  The WordPress Site Icon uses the square social-avatar export.
BRAND_COPIES = {
    "assets/images/brand/apple-touch-icon-180.png": "brand/exports/apple-touch-icon-180.png",
    "assets/images/brand/favicon-32.png": "brand/exports/favicon-32.png",
    "assets/images/brand/hks-wayfinder-favicon.svg": "brand/masters/hks-wayfinder-favicon.svg",
    "assets/images/brand/hks-wayfinder-horizontal-primary.svg": "brand/masters/hks-wayfinder-horizontal-primary.svg",
    "assets/images/brand/site-icon-512.png": "brand/exports/social-avatar-512.png",
}

EXPECTED_PLUGIN_DIRECTORIES = [
    "acf-json",
    "assets",
    "blocks",
    "languages",
    "src",
    "src/Analytics",
    "src/Content",
    "src/Contracts",
    "src/Conversion",
    "src/Fields",
    "src/Seed",
]

REQUIRED_FONT_PROVENANCE = {
    "Sora": {
        "urls": ("source_css", "latin_source", "latin_ext_source"),
        "hashes": ("latin_source_sha256", "latin_ext_source_sha256"),
        "text": (),
    },
    "Inter": {
        "urls": ("source",),
        "hashes": ("archive_sha256",),
        "text": ("archive_member",),
    },
}

TEXT_SUFFIXES = {
    ".css",
    ".html",
    ".js",
    ".json",
    ".md",
    ".php",
    ".svg",
    ".txt",
    ".xml",
}

FORBIDDEN_SVG_ELEMENTS = {
    "animate",
    "animatemotion",
    "animatetransform",
    "embed",
    "foreignobject",
    "iframe",
    "image",
    "lineargradient",
    "meshgradient",
    "object",
    "radialgradient",
    "script",
    "set",
    "style",
}

LIVE_TEXT_SVG_ELEMENTS = {"text", "textpath", "tspan"}


class ScaffoldValidator:
    """Collect every scaffold error and report them together."""

    def __init__(self) -> None:
        self.errors: List[str] = []

    def error(self, message: str) -> None:
        self.errors.append(message)

    @staticmethod
    def relative(path: Path) -> str:
        try:
            return path.resolve().relative_to(ROOT.resolve()).as_posix()
        except (OSError, ValueError):
            return str(path)

    def require_file(self, path: Path, label: Optional[str] = None) -> bool:
        if not path.is_file():
            self.error(f"Missing required file: {label or self.relative(path)}")
            return False
        return True

    def require_directory(self, path: Path, label: Optional[str] = None) -> bool:
        if not path.is_dir():
            self.error(f"Missing required directory: {label or self.relative(path)}")
            return False
        return True

    def read_text(self, path: Path) -> Optional[str]:
        if not self.require_file(path):
            return None
        try:
            return path.read_text(encoding="utf-8-sig")
        except (OSError, UnicodeError) as exc:
            self.error(f"Cannot read {self.relative(path)} as UTF-8 text: {exc}")
            return None

    def read_json(self, path: Path) -> Optional[Any]:
        text = self.read_text(path)
        if text is None:
            return None
        try:
            return json.loads(text)
        except json.JSONDecodeError as exc:
            self.error(
                f"Invalid JSON in {self.relative(path)} at line {exc.lineno}, "
                f"column {exc.colno}: {exc.msg}"
            )
            return None

    @staticmethod
    def sha256(path: Path) -> str:
        digest = hashlib.sha256()
        with path.open("rb") as handle:
            for chunk in iter(lambda: handle.read(1024 * 1024), b""):
                digest.update(chunk)
        return digest.hexdigest()

    def validate(self) -> None:
        checks = (
            self.validate_required_structure,
            self.validate_deploy_boundaries,
            self.validate_metadata,
            self.validate_theme_json,
            self.validate_fonts,
            self.validate_brand_copies,
            self.validate_generated_asset_exclusion,
            self.validate_svg_safety,
            self.validate_block_markup,
            self.validate_plugin_scaffold,
            self.validate_acf_json_tracking,
        )

        for check in checks:
            try:
                check()
            except Exception as exc:  # Defensive: a checker must not hide later errors.
                self.error(f"Validator exception in {check.__name__}: {exc}")

    def validate_required_structure(self) -> None:
        self.require_directory(THEME)
        self.require_directory(PLUGIN)

        for relative_path in REQUIRED_THEME_FILES:
            self.require_file(THEME / relative_path)

        for relative_path in REQUIRED_PLUGIN_FILES:
            self.require_file(PLUGIN / relative_path)

        for relative_path in EXPECTED_PLUGIN_DIRECTORIES:
            self.require_directory(PLUGIN / relative_path)

    def validate_deploy_boundaries(self) -> None:
        """Keep WordPress core/runtime data outside the source repository."""

        forbidden_root_paths = [
            ROOT / "wp-admin",
            ROOT / "wp-includes",
            ROOT / "wp-config.php",
        ]
        forbidden_runtime_paths = [
            ROOT / "wp-content" / "cache",
            ROOT / "wp-content" / "debug.log",
            ROOT / "wp-content" / "uploads",
            ROOT / "wp-content" / "upgrade",
        ]

        for path in forbidden_root_paths + forbidden_runtime_paths:
            if path.exists():
                self.error(f"Runtime/core path must not be committed: {self.relative(path)}")

        wp_content = ROOT / "wp-content"
        if wp_content.is_dir():
            actual_children = {path.name for path in wp_content.iterdir()}
            allowed_children = {"plugins", "themes"}
            unexpected = sorted(actual_children - allowed_children)
            for child in unexpected:
                self.error(f"Unexpected deploy boundary under wp-content/: {child}")

        deploy_roots = {
            ROOT / "wp-content" / "themes": {"hks-wayfinder"},
            ROOT / "wp-content" / "plugins": {"hks-core"},
        }
        for parent, allowed in deploy_roots.items():
            if not parent.is_dir():
                self.error(f"Missing deploy boundary: {self.relative(parent)}")
                continue
            actual = {path.name for path in parent.iterdir()}
            for unexpected in sorted(actual - allowed):
                self.error(
                    f"Only {', '.join(sorted(allowed))} may be deployed from "
                    f"{self.relative(parent)}; found {unexpected}"
                )

        for deploy_root in (THEME, PLUGIN):
            if not deploy_root.exists():
                continue
            for path in deploy_root.rglob("*"):
                if path.is_symlink():
                    self.error(f"Symlinks are not allowed in deployable code: {self.relative(path)}")
                if path.name.lower() in {".env", "wp-config.php"}:
                    self.error(f"Secret/runtime file in deployable code: {self.relative(path)}")
                if path.suffix.lower() in {".db", ".sqlite", ".sql"}:
                    self.error(f"Database artifact in deployable code: {self.relative(path)}")

        if self.require_file(ROOT / ".gitignore"):
            ignore_probes = (
                "wp-config.php",
                "wp-content/cache/hks-ignore-probe.tmp",
                "wp-content/debug.log",
                "wp-content/uploads/hks-ignore-probe.tmp",
                "wp-content/upgrade/hks-ignore-probe.tmp",
                ".env",
                ".env.production",
            )
            for probe in ignore_probes:
                ignored = self.git_ignored(probe)
                if ignored is False:
                    self.error(f"Repository runtime path is not effectively ignored: {probe}")
                elif ignored is None:
                    self.error(f"Could not verify Git ignore behavior for: {probe}")

    @staticmethod
    def extract_headers(text: str) -> Dict[str, str]:
        end = text.find("*/")
        header_block = text[: end + 2] if end >= 0 else text[:8192]
        headers: Dict[str, str] = {}
        pattern = re.compile(r"^\s*(?:\*\s*)?([A-Za-z][A-Za-z ]+):\s*(.*?)\s*$", re.MULTILINE)
        for match in pattern.finditer(header_block):
            headers[match.group(1).strip().casefold()] = match.group(2).strip()
        return headers

    @staticmethod
    def strip_php_comments(text: str) -> str:
        """Remove PHP comments while preserving strings and line boundaries."""

        output: List[str] = []
        index = 0
        state = "code"
        quote = ""

        while index < len(text):
            character = text[index]
            following = text[index + 1] if index + 1 < len(text) else ""

            if state == "code":
                if character in {"'", '"'}:
                    quote = character
                    state = "string"
                    output.append(character)
                elif character == "/" and following == "/":
                    state = "line-comment"
                    index += 1
                elif character == "#":
                    state = "line-comment"
                elif character == "/" and following == "*":
                    state = "block-comment"
                    index += 1
                else:
                    output.append(character)
            elif state == "string":
                output.append(character)
                if character == "\\" and following:
                    output.append(following)
                    index += 1
                elif character == quote:
                    state = "code"
            elif state == "line-comment":
                if character in {"\r", "\n"}:
                    output.append(character)
                    state = "code"
            elif state == "block-comment":
                if character == "*" and following == "/":
                    state = "code"
                    index += 1
                elif character in {"\r", "\n"}:
                    output.append(character)

            index += 1

        return "".join(output)

    def validate_metadata(self) -> None:
        theme_css = self.read_text(THEME / "style.css")
        if theme_css is not None:
            headers = self.extract_headers(theme_css)
            expected = {
                "theme name": "HKS Wayfinder",
                "update uri": "https://github.com/Allan-Gakuru/HolidayKenyaSafaris",
                "requires at least": WORDPRESS_VERSION,
                "requires php": PHP_VERSION,
                "text domain": "hks-wayfinder",
            }
            for key, value in expected.items():
                if headers.get(key) != value:
                    self.error(
                        f"Theme metadata {key!r} must be {value!r}; "
                        f"found {headers.get(key)!r}"
                    )
            if not re.fullmatch(r"\d+\.\d+\.\d+", headers.get("version", "")):
                self.error("Theme Version must be a three-part numeric semantic version")

        bootstrap = self.read_text(PLUGIN / "hks-core.php")
        if bootstrap is not None:
            headers = self.extract_headers(bootstrap)
            bootstrap_code = self.strip_php_comments(bootstrap)
            expected = {
                "plugin name": "HKS Core",
                "update uri": "https://github.com/Allan-Gakuru/HolidayKenyaSafaris",
                "requires at least": WORDPRESS_VERSION,
                "requires php": PHP_VERSION,
                "requires plugins": "secure-custom-fields",
                "text domain": "hks-core",
            }
            for key, value in expected.items():
                if headers.get(key) != value:
                    self.error(
                        f"Plugin metadata {key!r} must be {value!r}; "
                        f"found {headers.get(key)!r}"
                    )
            if not re.fullmatch(r"\d+\.\d+\.\d+", headers.get("version", "")):
                self.error("Plugin Version must be a three-part numeric semantic version")

            constants = {
                "HKS_CORE_MINIMUM_PHP_VERSION": PHP_VERSION,
                "HKS_CORE_MINIMUM_WP_VERSION": WORDPRESS_VERSION,
                "HKS_CORE_MINIMUM_SCF_VERSION": "6.9.1",
                "HKS_CORE_SCF_BASENAME": "secure-custom-fields/secure-custom-fields.php",
            }
            for constant, expected_value in constants.items():
                pattern = re.compile(
                    r"define\s*\(\s*['\"]"
                    + re.escape(constant)
                    + r"['\"]\s*,\s*['\"]"
                    + re.escape(expected_value)
                    + r"['\"]\s*\)"
                )
                if not pattern.search(bootstrap_code):
                    self.error(
                        f"Plugin constant {constant} must declare baseline {expected_value}"
                    )

            version = headers.get("version")
            if version:
                pattern = re.compile(
                    r"define\s*\(\s*['\"]HKS_CORE_VERSION['\"]\s*,\s*['\"]"
                    + re.escape(version)
                    + r"['\"]\s*\)"
                )
                if not pattern.search(bootstrap_code):
                    self.error("Plugin header Version and HKS_CORE_VERSION must match")

    def validate_theme_json(self) -> None:
        document = self.read_json(THEME / "theme.json")
        if not isinstance(document, dict):
            return

        if document.get("version") != 3:
            self.error("theme.json version must be the integer 3")
        if document.get("$schema") != "https://schemas.wp.org/wp/6.6/theme.json":
            self.error("theme.json $schema must target WordPress 6.6")

        settings = document.get("settings")
        if not isinstance(settings, dict):
            self.error("theme.json settings must be an object")
            return

        if settings.get("appearanceTools") is not False:
            self.error("theme.json settings.appearanceTools must be false")

        if settings.get("layout") != EXPECTED_LAYOUT:
            self.error(
                "theme.json layout must be exactly "
                + json.dumps(EXPECTED_LAYOUT, sort_keys=True)
            )

        color = settings.get("color")
        if not isinstance(color, dict):
            self.error("theme.json settings.color must be an object")
            return

        required_false_flags = (
            "custom",
            "customDuotone",
            "customGradient",
            "defaultDuotone",
            "defaultGradients",
            "defaultPalette",
        )
        for flag in required_false_flags:
            if color.get(flag) is not False:
                self.error(f"theme.json settings.color.{flag} must be false")

        if color.get("gradients") not in (None, []):
            self.error("theme.json must not define gradient presets")

        palette = color.get("palette")
        actual_palette: List[Tuple[Any, Any, Any]] = []
        if isinstance(palette, list):
            for item in palette:
                if isinstance(item, dict):
                    actual_palette.append(
                        (
                            item.get("slug"),
                            item.get("name"),
                            item.get("color"),
                        )
                    )
                else:
                    actual_palette.append((None, None, None))
        else:
            self.error("theme.json settings.color.palette must be an array")

        if actual_palette != EXPECTED_PALETTE:
            self.error(
                "theme.json palette slugs, order, and values must exactly match "
                "the seven approved Wayfinder tokens"
            )

        def scan_for_gradients(value: Any, trail: str = "theme.json") -> None:
            if isinstance(value, dict):
                for key, child in value.items():
                    scan_for_gradients(child, f"{trail}.{key}")
            elif isinstance(value, list):
                for index, child in enumerate(value):
                    scan_for_gradients(child, f"{trail}[{index}]")
            elif isinstance(value, str) and re.search(
                r"(?:linear|radial|conic)-gradient\s*\(", value, re.IGNORECASE
            ):
                self.error(f"Gradient CSS is not allowed at {trail}")

        scan_for_gradients(document)

    def resolve_within(self, base: Path, relative_path: str, label: str) -> Optional[Path]:
        candidate = (base / relative_path).resolve()
        try:
            candidate.relative_to(base.resolve())
        except ValueError:
            self.error(f"{label} escapes its allowed directory: {relative_path}")
            return None
        return candidate

    @staticmethod
    def valid_sha256(value: Any) -> bool:
        return isinstance(value, str) and re.fullmatch(r"[0-9a-fA-F]{64}", value) is not None

    def validate_fonts(self) -> None:
        theme_json = self.read_json(THEME / "theme.json")
        manifest = self.read_json(THEME / "assets" / "fonts" / "SOURCES.json")
        referenced_fonts: Set[Path] = set()

        if isinstance(theme_json, dict):
            settings = theme_json.get("settings", {})
            typography = settings.get("typography", {}) if isinstance(settings, dict) else {}
            families = typography.get("fontFamilies", []) if isinstance(typography, dict) else []
            if not isinstance(families, list) or not families:
                self.error("theme.json must declare self-hosted fontFamilies")
                families = []

            for family_index, family in enumerate(families):
                if not isinstance(family, dict):
                    self.error(f"theme.json fontFamilies[{family_index}] must be an object")
                    continue
                faces = family.get("fontFace")
                if not isinstance(faces, list) or not faces:
                    self.error(
                        f"theme.json font family {family.get('slug', family_index)!r} "
                        "must declare at least one fontFace"
                    )
                    continue
                for face_index, face in enumerate(faces):
                    if not isinstance(face, dict):
                        self.error(
                            f"theme.json fontFace {family_index}:{face_index} must be an object"
                        )
                        continue
                    sources = face.get("src")
                    if not isinstance(sources, list) or not sources:
                        self.error(
                            f"theme.json fontFace {family_index}:{face_index} has no sources"
                        )
                        continue
                    for source in sources:
                        if not isinstance(source, str) or not source.startswith("file:./"):
                            self.error(
                                "Every theme.json fontFace source must be a local file:./ URL; "
                                f"found {source!r}"
                            )
                            continue
                        relative_path = source[len("file:./") :]
                        path = self.resolve_within(THEME, relative_path, "Font source")
                        if path is None:
                            continue
                        referenced_fonts.add(path)
                        if path.suffix.lower() != ".woff2":
                            self.error(f"Font source must use WOFF2: {self.relative(path)}")
                        if self.require_file(path):
                            try:
                                signature = path.read_bytes()[:4]
                            except OSError as exc:
                                self.error(f"Cannot inspect font {self.relative(path)}: {exc}")
                            else:
                                if signature != b"wOF2":
                                    self.error(
                                        f"Invalid WOFF2 signature in {self.relative(path)}"
                                    )

        manifest_fonts: Set[Path] = set()
        if isinstance(manifest, dict):
            families = manifest.get("families")
            if not isinstance(families, dict) or not families:
                self.error("Font SOURCES.json must contain a non-empty families object")
                families = {}

            for family_name, specification in REQUIRED_FONT_PROVENANCE.items():
                record = families.get(family_name)
                if not isinstance(record, dict):
                    self.error(
                        f"Font SOURCES.json must declare provenance for {family_name}"
                    )
                    continue
                for key in specification["urls"]:
                    value = record.get(key)
                    if not isinstance(value, str) or not re.fullmatch(
                        r"https://[^\s/]+/.+", value
                    ):
                        self.error(
                            f"Font manifest {family_name}.{key} must be a declared HTTPS source URL"
                        )
                for key in specification["hashes"]:
                    if not self.valid_sha256(record.get(key)):
                        self.error(
                            f"Font manifest {family_name}.{key} must be a source SHA-256"
                        )
                for key in specification["text"]:
                    value = record.get(key)
                    if not isinstance(value, str) or not value.strip():
                        self.error(
                            f"Font manifest {family_name}.{key} must be declared"
                        )

            sora_record = families.get("Sora")
            if isinstance(sora_record, dict):
                for prefix in ("latin", "latin_ext"):
                    source_hash = sora_record.get(f"{prefix}_source_sha256")
                    output_hash = sora_record.get(f"{prefix}_output_sha256")
                    if (
                        self.valid_sha256(source_hash)
                        and self.valid_sha256(output_hash)
                        and str(source_hash).lower() != str(output_hash).lower()
                    ):
                        self.error(
                            f"Direct Sora source/output hashes differ for {prefix}"
                        )

            for family_name, record in families.items():
                if not isinstance(record, dict):
                    self.error(f"Font manifest record {family_name!r} must be an object")
                    continue
                if not record.get("version"):
                    self.error(f"Font manifest record {family_name!r} is missing version")
                license_value = record.get("license")
                if not isinstance(license_value, str):
                    self.error(f"Font manifest record {family_name!r} is missing license")
                else:
                    license_path = self.resolve_within(ROOT, license_value, "Font license")
                    if license_path is not None:
                        self.require_file(license_path)

                for key, value in record.items():
                    if key.endswith("sha256") and not self.valid_sha256(value):
                        self.error(
                            f"Font manifest hash {family_name}.{key} must be 64 hexadecimal characters"
                        )

                output_keys = [
                    key for key in record if key == "output" or key.endswith("_output")
                ]
                if not output_keys:
                    self.error(f"Font manifest record {family_name!r} declares no outputs")
                for output_key in output_keys:
                    output_value = record.get(output_key)
                    hash_key = f"{output_key}_sha256"
                    expected_hash = record.get(hash_key)
                    if not isinstance(output_value, str):
                        self.error(
                            f"Font manifest output {family_name}.{output_key} must be a path"
                        )
                        continue
                    output_path = self.resolve_within(ROOT, output_value, "Font output")
                    if output_path is None:
                        continue
                    try:
                        output_path.relative_to((THEME / "assets" / "fonts").resolve())
                    except ValueError:
                        self.error(
                            f"Font output is outside the theme font directory: {output_value}"
                        )
                    manifest_fonts.add(output_path)
                    if not self.require_file(output_path):
                        continue
                    try:
                        actual_hash = self.sha256(output_path)
                    except OSError as exc:
                        self.error(f"Cannot hash {self.relative(output_path)}: {exc}")
                        continue
                    if not self.valid_sha256(expected_hash):
                        self.error(
                            f"Font output {family_name}.{output_key} lacks a valid {hash_key}"
                        )
                    elif actual_hash.lower() != str(expected_hash).lower():
                        self.error(
                            f"Font hash mismatch for {self.relative(output_path)}: "
                            f"expected {expected_hash}, got {actual_hash}"
                        )

        missing_from_manifest = referenced_fonts - manifest_fonts
        unreferenced_outputs = manifest_fonts - referenced_fonts
        for path in sorted(missing_from_manifest, key=str):
            self.error(
                f"theme.json font is not covered by SOURCES.json: {self.relative(path)}"
            )
        for path in sorted(unreferenced_outputs, key=str):
            self.error(
                f"SOURCES.json font output is not referenced by theme.json: {self.relative(path)}"
            )

    def validate_brand_copies(self) -> None:
        theme_brand_dir = THEME / "assets" / "images" / "brand"
        self.require_directory(theme_brand_dir)

        expected_deploy_paths = {THEME / relative for relative in BRAND_COPIES}
        if theme_brand_dir.is_dir():
            actual_deploy_paths = {path for path in theme_brand_dir.iterdir() if path.is_file()}
            for unexpected in sorted(actual_deploy_paths - expected_deploy_paths, key=str):
                self.error(
                    "Theme brand directory contains an unregistered copy: "
                    f"{self.relative(unexpected)}"
                )

        for deploy_relative, source_relative in BRAND_COPIES.items():
            deploy_path = THEME / deploy_relative
            source_path = ROOT / source_relative
            deploy_exists = self.require_file(deploy_path)
            source_exists = self.require_file(source_path)
            if not (deploy_exists and source_exists):
                continue
            try:
                deploy_hash = self.sha256(deploy_path)
                source_hash = self.sha256(source_path)
            except OSError as exc:
                self.error(f"Cannot hash brand copy {self.relative(deploy_path)}: {exc}")
                continue
            if deploy_hash != source_hash:
                self.error(
                    f"Brand copy differs from its production source: {self.relative(deploy_path)} "
                    f"!= {self.relative(source_path)}"
                )

            if deploy_path.suffix.lower() == ".png":
                try:
                    signature = deploy_path.read_bytes()[:8]
                except OSError as exc:
                    self.error(f"Cannot inspect PNG {self.relative(deploy_path)}: {exc}")
                else:
                    if signature != b"\x89PNG\r\n\x1a\n":
                        self.error(f"Invalid PNG signature in {self.relative(deploy_path)}")

    def validate_generated_asset_exclusion(self) -> None:
        generated_sources = [
            ROOT / "outputs" / "assets" / "maasai-mara-escape.png",
            ROOT / "outputs" / "assets" / "mercy-avatar.png",
        ]
        generated_hashes: Set[str] = set()
        for source in generated_sources:
            if source.is_file():
                try:
                    generated_hashes.add(self.sha256(source))
                except OSError as exc:
                    self.error(f"Cannot hash internal generated asset {self.relative(source)}: {exc}")

        forbidden_reference_fragments = (
            "maasai-mara-escape",
            "maasai_mara_escape",
            "mercy-avatar",
            "mercy_avatar",
        )

        for deploy_root in (THEME, PLUGIN):
            if not deploy_root.is_dir():
                continue
            for path in deploy_root.rglob("*"):
                if not path.is_file():
                    continue
                lower_name = path.name.casefold()
                if any(fragment in lower_name for fragment in forbidden_reference_fragments):
                    self.error(
                        f"Internal generated Mara/Mercy asset is not deployable: {self.relative(path)}"
                    )

                if generated_hashes:
                    try:
                        digest = self.sha256(path)
                    except OSError as exc:
                        self.error(f"Cannot inspect deployed file {self.relative(path)}: {exc}")
                    else:
                        if digest in generated_hashes:
                            self.error(
                                "Deployable file is a byte copy of an internal generated "
                                f"Mara/Mercy asset: {self.relative(path)}"
                            )

                if path.suffix.lower() in TEXT_SUFFIXES:
                    try:
                        text = path.read_text(encoding="utf-8-sig").casefold()
                    except (OSError, UnicodeError):
                        continue
                    for fragment in forbidden_reference_fragments:
                        if fragment in text:
                            self.error(
                                f"Deployable source references internal generated asset "
                                f"{fragment!r}: {self.relative(path)}"
                            )

    @staticmethod
    def xml_local_name(name: str) -> str:
        return name.rsplit("}", 1)[-1].casefold()

    def validate_svg_safety(self) -> None:
        svg_files: List[Path] = []
        for deploy_root in (THEME, PLUGIN):
            if deploy_root.is_dir():
                svg_files.extend(deploy_root.rglob("*.svg"))

        for path in sorted(svg_files, key=str):
            try:
                raw = path.read_bytes()
            except OSError as exc:
                self.error(f"Cannot read SVG {self.relative(path)}: {exc}")
                continue

            lower_raw = raw.lower()
            if b"<!doctype" in lower_raw or b"<!entity" in lower_raw:
                self.error(f"DOCTYPE/entities are not allowed in SVG: {self.relative(path)}")
                continue
            if re.search(br"<\?xml-stylesheet(?:\s|\?>)", lower_raw):
                self.error(f"XML stylesheets are not allowed in SVG: {self.relative(path)}")
                continue

            try:
                root = ElementTree.fromstring(raw)
            except ElementTree.ParseError as exc:
                self.error(f"Invalid SVG XML in {self.relative(path)}: {exc}")
                continue

            if self.xml_local_name(root.tag) != "svg":
                self.error(f"SVG root element is not <svg>: {self.relative(path)}")
            if not root.get("viewBox"):
                self.error(f"SVG is missing viewBox: {self.relative(path)}")

            for element in root.iter():
                local_name = self.xml_local_name(element.tag)
                if local_name in LIVE_TEXT_SVG_ELEMENTS:
                    self.error(
                        f"SVG contains live graphical text <{local_name}>: {self.relative(path)}"
                    )
                if local_name in FORBIDDEN_SVG_ELEMENTS:
                    self.error(
                        f"Unsafe or disallowed SVG element <{local_name}>: {self.relative(path)}"
                    )

                for raw_attribute, raw_value in element.attrib.items():
                    attribute = self.xml_local_name(raw_attribute)
                    value = str(raw_value).strip()
                    lower_value = value.casefold()
                    if attribute.startswith("on"):
                        self.error(
                            f"SVG event handler {attribute!r} is not allowed: {self.relative(path)}"
                        )
                    if attribute == "href" and value and not value.startswith("#"):
                        self.error(
                            f"External SVG href is not allowed ({value!r}): {self.relative(path)}"
                        )
                    if re.search(r"(?:javascript|data):|https?:|^//", lower_value):
                        self.error(
                            f"External/active SVG attribute is not allowed ({value!r}): "
                            f"{self.relative(path)}"
                        )
                    for url_match in re.finditer(r"url\((.*?)\)", value, re.IGNORECASE):
                        target = url_match.group(1).strip().strip("'\"")
                        if not target.startswith("#"):
                            self.error(
                                f"External SVG url() is not allowed ({target!r}): "
                                f"{self.relative(path)}"
                            )

    def validate_block_comments(self, path: Path, text: str) -> None:
        comment_pattern = re.compile(
            r"<!--\s*(/?)wp:([a-z0-9-]+(?:/[a-z0-9-]+)?)(.*?)-->",
            re.IGNORECASE | re.DOTALL,
        )
        stack: List[str] = []
        for match in comment_pattern.finditer(text):
            is_closing = bool(match.group(1))
            block_name = match.group(2).casefold()
            tail = match.group(3).rstrip()
            is_self_closing = not is_closing and tail.endswith("/")

            if is_closing:
                if not stack:
                    self.error(
                        f"Unexpected closing WordPress block {block_name!r} in "
                        f"{self.relative(path)}"
                    )
                elif stack[-1] != block_name:
                    expected = stack[-1]
                    self.error(
                        f"Mismatched WordPress block in {self.relative(path)}: "
                        f"expected closing {expected!r}, found {block_name!r}"
                    )
                    stack.pop()
                else:
                    stack.pop()
            elif not is_self_closing:
                stack.append(block_name)

        for block_name in reversed(stack):
            self.error(
                f"Unclosed WordPress block {block_name!r} in {self.relative(path)}"
            )

    def validate_block_markup(self) -> None:
        html_files = sorted(THEME.rglob("*.html"), key=str) if THEME.is_dir() else []
        pattern_files = (
            sorted((THEME / "patterns").rglob("*.php"), key=str)
            if (THEME / "patterns").is_dir()
            else []
        )

        for path in html_files:
            text = self.read_text(path)
            if text is None:
                continue
            if "<?" in text or "?>" in text:
                self.error(f"PHP is not allowed in block-template HTML: {self.relative(path)}")
            self.validate_block_comments(path, text)

        for path in pattern_files:
            text = self.read_text(path)
            if text is not None:
                self.validate_block_comments(path, text)

    @staticmethod
    def has_direct_access_guard(text: str) -> bool:
        text = ScaffoldValidator.strip_php_comments(text)
        defines_abspath = re.search(
            r"defined\s*\(\s*(['\"])ABSPATH\1\s*\)", text
        )
        exits = re.search(r"\bexit\b", text)
        return defines_abspath is not None and exits is not None

    def validate_plugin_scaffold(self) -> None:
        bootstrap_path = PLUGIN / "hks-core.php"
        bootstrap = self.read_text(bootstrap_path)

        if bootstrap is not None:
            required_markers = (
                "src/Autoloader.php",
                "Autoloader::register",
                "register_activation_hook",
                "register_deactivation_hook",
                "Requirements::register",
                "Requirements::is_satisfied",
                "plugins_loaded",
                "Plugin::instance()->boot",
            )
            for marker in required_markers:
                if marker not in self.strip_php_comments(bootstrap):
                    self.error(f"Plugin bootstrap is missing required marker: {marker}")

        if PLUGIN.is_dir():
            for php_file in sorted(PLUGIN.rglob("*.php"), key=str):
                text = self.read_text(php_file)
                if text is None:
                    continue
                if not self.has_direct_access_guard(text):
                    self.error(
                        f"Plugin PHP file lacks an ABSPATH direct-access guard: "
                        f"{self.relative(php_file)}"
                    )

                if (PLUGIN / "src") in php_file.parents:
                    if not re.search(
                        r"\bnamespace\s+HolidayKenyaSafaris\\Core(?:\\[A-Za-z][A-Za-z0-9_]*)*\s*;",
                        text,
                    ):
                        self.error(
                            f"Plugin source file lacks the project namespace: "
                            f"{self.relative(php_file)}"
                        )

        functions_php = self.read_text(THEME / "functions.php")
        if functions_php is not None and not self.has_direct_access_guard(functions_php):
            self.error("Theme functions.php lacks an ABSPATH direct-access guard")

        patterns_directory = THEME / "patterns"
        if patterns_directory.is_dir():
            for pattern_file in sorted(patterns_directory.rglob("*.php"), key=str):
                pattern_text = self.read_text(pattern_file)
                if pattern_text is not None and not self.has_direct_access_guard(pattern_text):
                    self.error(
                        f"Theme pattern PHP lacks an ABSPATH direct-access guard: "
                        f"{self.relative(pattern_file)}"
                    )

        module_contract = self.read_text(PLUGIN / "src" / "Contracts" / "Module.php")
        if module_contract is not None:
            if not re.search(r"\binterface\s+Module\b", module_contract):
                self.error("src/Contracts/Module.php must declare the Module interface")
            if not re.search(r"\bfunction\s+register\s*\(", module_contract):
                self.error("The Module interface must declare register()")

    def git_ignored(self, relative_path: str) -> Optional[bool]:
        try:
            result = subprocess.run(
                ["git", "check-ignore", "--quiet", "--no-index", "--", relative_path],
                cwd=ROOT,
                check=False,
                capture_output=True,
                text=True,
                encoding="utf-8",
                errors="replace",
            )
        except OSError:
            return None
        if result.returncode == 0:
            return True
        if result.returncode == 1:
            return False
        return None

    def git_visible(self, relative_path: str) -> Optional[bool]:
        try:
            result = subprocess.run(
                [
                    "git",
                    "ls-files",
                    "--cached",
                    "--others",
                    "--exclude-standard",
                    "--",
                    relative_path,
                ],
                cwd=ROOT,
                check=False,
                capture_output=True,
                text=True,
                encoding="utf-8",
                errors="replace",
            )
        except OSError:
            return None
        if result.returncode != 0:
            return None
        paths = {line.strip().replace("\\", "/") for line in result.stdout.splitlines()}
        return relative_path.replace("\\", "/") in paths

    def git_tracked(self, relative_path: str) -> Optional[bool]:
        try:
            result = subprocess.run(
                ["git", "ls-files", "--cached", "--", relative_path],
                cwd=ROOT,
                check=False,
                capture_output=True,
                text=True,
                encoding="utf-8",
                errors="replace",
            )
        except OSError:
            return None
        if result.returncode != 0:
            return None
        paths = {line.strip().replace("\\", "/") for line in result.stdout.splitlines()}
        return relative_path.replace("\\", "/") in paths

    def validate_acf_json_tracking(self) -> None:
        acf_json = PLUGIN / "acf-json"
        placeholder = acf_json / ".gitkeep"
        if not self.require_directory(acf_json) or not self.require_file(placeholder):
            return

        # Accept both index-tracked and currently untracked/non-ignored files so
        # the validator can run before `git add`; either state guarantees the
        # otherwise-empty Local JSON directory will enter the next commit.
        relative = self.relative(placeholder)
        visible = self.git_visible(relative)
        if visible is False:
            self.error(
                f"SCF Local JSON placeholder is ignored and cannot be tracked: {relative}"
            )

        tracked = self.git_tracked(relative)
        scaffold_tracking = [
            self.git_tracked("wp-content/plugins/hks-core/hks-core.php"),
            self.git_tracked("wp-content/themes/hks-wayfinder/style.css"),
        ]
        if tracked is False and any(item is True for item in scaffold_tracking):
            self.error(
                f"SCF Local JSON placeholder must be tracked with the scaffold: {relative}"
            )

        for json_file in sorted(acf_json.glob("*.json"), key=str):
            self.read_json(json_file)


def main() -> int:
    validator = ScaffoldValidator()
    validator.validate()

    if validator.errors:
        print(f"Scaffold validation failed with {len(validator.errors)} error(s):")
        for index, message in enumerate(validator.errors, start=1):
            print(f"  {index}. {message}")
        return 1

    print("Scaffold validation passed.")
    print("Checked theme/plugin boundaries, metadata, theme.json, local fonts, brand copies,")
    print("SVG safety, block markup, plugin guards, and the SCF Local JSON path.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
