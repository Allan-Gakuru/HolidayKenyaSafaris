#!/usr/bin/env python3
"""Validate the HKS Phase 3 content-model contract without loading WordPress.

The project deliberately has no local WordPress runtime. This standard-library
validator checks the source-owned registration, field, governance, and upgrade
contracts; PHP syntax remains the responsibility of ``tools/lint-php.ps1``.
"""

from __future__ import annotations

import re
import sys
from collections import Counter
from pathlib import Path
from typing import Dict, Iterable, List, Optional, Set


ROOT = Path(__file__).resolve().parents[1]
PLUGIN = ROOT / "wp-content" / "plugins" / "hks-core"
SRC = PLUGIN / "src"

REQUIRED_FILES = (
    "hks-core.php",
    "src/Content/Module.php",
    "src/Content/PostTypes/Tour.php",
    "src/Content/PostTypes/Campaign.php",
    "src/Content/PostTypes/Faq.php",
    "src/Content/Taxonomies/Destination.php",
    "src/Content/Taxonomies/TourType.php",
    "src/Content/Taxonomies/Occasion.php",
    "src/Content/Taxonomies/TravelStyle.php",
    "src/Fields/Choices.php",
    "src/Fields/FieldGroups.php",
    "src/Fields/FieldsModule.php",
    "src/Fields/PublicationRules.php",
    "src/Fields/PublicationGuard.php",
)

EXPECTED_GROUP_REST: Dict[str, bool] = {
    "tour_source": False,
    "tour_package": True,
    "tour_pricing": False,
    "tour_itinerary": True,
    "tour_inclusions": True,
    "tour_suitability": True,
    "tour_policies": False,
    "tour_media_conversion": True,
    "tour_operations": False,
    "campaign_public": True,
    "campaign_brief": False,
    "campaign_proof": False,
    "campaign_governance": False,
    "faq_public": True,
    "faq_audit": False,
    "destination_public": True,
    "destination_audit": False,
    "attachment_rights": False,
    "settings": False,
}

EXPECTED_CHOICES: Dict[str, Set[str]] = {
    "source_status": {"imported", "reviewed", "client_confirmed", "archived"},
    "confirmation_status": {
        "client_confirmation_required",
        "operator_reviewed",
        "client_confirmed",
        "expired",
        "not_applicable",
    },
    "price_status": {
        "placeholder",
        "converted_estimate",
        "operator_reviewed",
        "client_confirmed",
        "expired",
    },
    "price_display_mode": {"from_price", "request_current_rate", "hidden"},
    "campaign_status": {"draft", "testing", "active", "paused", "archived"},
    "navigation_mode": {"full", "reduced", "campaign_minimal"},
}

REQUIRED_FIELD_NAMES = {
    # Tour provenance and package facts.
    "hks_internal_product_id",
    "hks_original_ashford_title",
    "hks_source_url",
    "hks_source_reference",
    "hks_source_checked_date",
    "hks_source_status",
    "hks_duration_days",
    "hks_duration_nights",
    "hks_duration_label",
    "hks_start_location",
    "hks_end_location",
    "hks_route_summary",
    "hks_transport_types",
    "hks_min_group_size",
    "hks_max_group_size",
    "hks_residency_basis",
    "hks_accommodation_basis",
    "hks_meals_summary",
    # Price governance.
    "hks_price_display_mode",
    "hks_from_price_ksh",
    "hks_price_unit",
    "hks_price_status",
    "hks_price_checked_date",
    "hks_price_valid_until",
    "hks_price_season_assumption",
    "hks_price_residency_assumption",
    "hks_price_group_size_assumption",
    "hks_price_transport_assumption",
    "hks_price_accommodation_assumption",
    "hks_price_inclusions_assumption",
    "hks_price_basis_summary",
    "hks_price_disclaimer",
    "hks_seasonal_rates",
    "hks_mandatory_supplements",
    # Reusable Tour detail and conversion.
    "hks_itinerary",
    "hks_inclusions",
    "hks_exclusions",
    "hks_best_for",
    "hks_child_suitability",
    "hks_accessibility_notes",
    "hks_packing_guidance",
    "hks_safety_notes",
    "hks_policies",
    "hks_gallery",
    "hks_vehicle_image",
    "hks_accommodation_images",
    "hks_route_image",
    "hks_cta_label",
    "hks_whatsapp_package_label",
    "hks_intake_questions",
    "hks_featured_faqs",
    # Campaign variant data; no canonical product facts belong here.
    "hks_linked_tour",
    "hks_hero_headline",
    "hks_supporting_copy",
    "hks_trust_modules",
    "hks_navigation_mode",
    "hks_internal_label",
    "hks_target_audience",
    "hks_primary_desire",
    "hks_primary_problem",
    "hks_primary_objective",
    "hks_primary_objection",
    "hks_next_step",
    "hks_proof_points",
    "hks_campaign_status",
    "hks_analytics_campaign_label",
    "hks_noindex",
    # FAQ, destination, rights, and settings anchors.
    "hks_faq_answer",
    "hks_short_summary",
    "hks_overview",
    "hks_best_time_guidance",
    "hks_travel_time_guidance",
    "hks_permission_status",
    "hks_usage_scopes",
    "hks_license_basis",
    "hks_permission_evidence",
    "hks_rights_checked_date",
}

CAMPAIGN_FORBIDDEN_FACT_FIELDS = {
    "hks_duration_days",
    "hks_duration_nights",
    "hks_route_summary",
    "hks_transport_types",
    "hks_accommodation_basis",
    "hks_from_price_ksh",
    "hks_price_status",
    "hks_seasonal_rates",
    "hks_itinerary",
    "hks_inclusions",
    "hks_exclusions",
    "hks_policies",
}


class Validator:
    """Collect contract violations and report them together."""

    def __init__(self) -> None:
        self.errors: List[str] = []

    def error(self, message: str) -> None:
        self.errors.append(message)

    def read(self, relative: str) -> Optional[str]:
        path = PLUGIN / relative
        if not path.is_file():
            self.error(f"Missing required content-model file: {path.relative_to(ROOT)}")
            return None
        try:
            return path.read_text(encoding="utf-8-sig")
        except (OSError, UnicodeError) as exc:
            self.error(f"Cannot read {path.relative_to(ROOT)} as UTF-8: {exc}")
            return None

    def require_markers(self, label: str, text: str, markers: Iterable[str]) -> None:
        for marker in markers:
            if marker not in text:
                self.error(f"{label} is missing contract marker: {marker}")

    def require_assignment(self, label: str, text: str, key: str, value: str) -> None:
        pattern = re.compile(
            rf"['\"]{re.escape(key)}['\"]\s*=>\s*{value}", re.MULTILINE
        )
        if not pattern.search(text):
            self.error(f"{label} must set {key!r} to {value}")

    @staticmethod
    def method_body(text: str, method: str) -> Optional[str]:
        match = re.search(
            rf"\b(?:public|private)\s+static\s+function\s+{re.escape(method)}\s*\([^)]*\)\s*\{{"
            rf"(?P<body>.*?)(?=\n\s*(?:public|private)\s+static\s+function\s+|\n\}}\s*$)",
            text,
            re.DOTALL,
        )
        return match.group("body") if match else None

    def validate(self) -> None:
        for relative in REQUIRED_FILES:
            self.read(relative)

        self.validate_registrations()
        self.validate_choices()
        self.validate_field_groups()
        self.validate_boot_and_lifecycle()
        self.validate_publication_guard()
        self.validate_storage_boundaries()

    def validate_registrations(self) -> None:
        tour = self.read("src/Content/PostTypes/Tour.php")
        campaign = self.read("src/Content/PostTypes/Campaign.php")
        faq = self.read("src/Content/PostTypes/Faq.php")
        if tour:
            self.require_markers(
                "Tour post type",
                tour,
                ("'hks_tour'", "'rest_base'           => 'tours'", "'has_archive'         => 'tours'", "'core/paragraph'"),
            )
            self.require_assignment("Tour post type", tour, "public", "true")
            self.require_assignment("Tour post type", tour, "publicly_queryable", "true")
            self.require_assignment("Tour post type", tour, "show_in_rest", "true")
            self.require_assignment("Tour post type", tour, "template_lock", "['\"]all['\"]")
            for support in ("title", "editor", "excerpt", "thumbnail", "revisions"):
                if f"'{support}'" not in tour:
                    self.error(f"Tour post type must support {support}")

        if campaign:
            self.require_markers(
                "Campaign post type",
                campaign,
                ("'hks_campaign'", "'rest_base'           => 'campaigns'", "'has_archive'         => false"),
            )
            self.require_assignment("Campaign post type", campaign, "public", "false")
            self.require_assignment("Campaign post type", campaign, "publicly_queryable", "true")
            self.require_assignment("Campaign post type", campaign, "exclude_from_search", "true")
            self.require_assignment("Campaign post type", campaign, "show_in_rest", "true")
            supports_match = re.search(r"['\"]supports['\"]\s*=>\s*array\s*\((.*?)\)", campaign, re.DOTALL)
            if supports_match and "'editor'" in supports_match.group(1):
                self.error("Campaign must use structured SCF copy fields, not a duplicate free editor")

        if faq:
            self.require_markers("FAQ post type", faq, ("'hks_faq'",))
            self.require_assignment("FAQ post type", faq, "public", "false")
            self.require_assignment("FAQ post type", faq, "publicly_queryable", "false")
            self.require_assignment("FAQ post type", faq, "show_in_rest", "false")
            self.require_assignment("FAQ post type", faq, "rewrite", "false")

        taxonomy_specs = {
            "Destination.php": ("hks_destination", True, True, "array"),
            "TourType.php": ("hks_tour_type", False, False, "false"),
            "Occasion.php": ("hks_occasion", False, False, "false"),
            "TravelStyle.php": ("hks_travel_style", False, False, "false"),
        }
        for filename, (identifier, public, queryable, rewrite) in taxonomy_specs.items():
            text = self.read(f"src/Content/Taxonomies/{filename}")
            if not text:
                continue
            label = f"{identifier} taxonomy"
            self.require_markers(label, text, (f"'{identifier}'", "'show_in_rest'       => true"))
            self.require_assignment(label, text, "public", str(public).lower())
            self.require_assignment(label, text, "publicly_queryable", str(queryable).lower())
            self.require_assignment(label, text, "rewrite", rewrite)

        occasion = self.read("src/Content/Taxonomies/Occasion.php")
        if occasion:
            self.require_markers("Occasion taxonomy", occasion, ("Tour::POST_TYPE, Campaign::POST_TYPE",))

    def validate_choices(self) -> None:
        choices = self.read("src/Fields/Choices.php")
        if not choices:
            return
        for method, expected in EXPECTED_CHOICES.items():
            body = self.method_body(choices, method)
            if body is None:
                self.error(f"Choices::{method}() is missing")
                continue
            actual = set(re.findall(r"['\"]([a-z][a-z0-9_]*)['\"]\s*=>", body))
            if actual != expected:
                self.error(
                    f"Choices::{method}() keys must be {sorted(expected)}; found {sorted(actual)}"
                )

    def validate_field_groups(self) -> None:
        fields = self.read("src/Fields/FieldGroups.php")
        module = self.read("src/Fields/FieldsModule.php")
        if not fields or not module:
            return

        self.require_markers(
            "Fields module",
            module,
            ("acf/include_fields", "acf/init", "acf_add_local_field_group", "acf_add_options_page", "hks-settings"),
        )

        group_pattern = re.compile(
            r"self::group\(\s*'(?P<slug>[a-z0-9_]+)'.*?"
            r"self::location\([^;]+?\)\s*,\s*(?P<rest>true|false)\s*,",
            re.DOTALL,
        )
        actual_groups: Dict[str, bool] = {}
        for match in group_pattern.finditer(fields):
            slug = match.group("slug")
            if slug in actual_groups:
                self.error(f"Duplicate SCF group slug: {slug}")
            actual_groups[slug] = match.group("rest") == "true"
        if actual_groups != EXPECTED_GROUP_REST:
            missing = sorted(set(EXPECTED_GROUP_REST) - set(actual_groups))
            extra = sorted(set(actual_groups) - set(EXPECTED_GROUP_REST))
            wrong = sorted(
                slug
                for slug in set(actual_groups) & set(EXPECTED_GROUP_REST)
                if actual_groups[slug] != EXPECTED_GROUP_REST[slug]
            )
            self.error(f"SCF group/REST contract mismatch; missing={missing}, extra={extra}, wrong_rest={wrong}")

        literal_slugs = re.findall(
            r"self::(?:field|message|tab)\(\s*'([^']+)'\s*,", fields
        )
        duplicates = sorted(slug for slug, count in Counter(literal_slugs).items() if count > 1)
        if duplicates:
            self.error(f"Duplicate deterministic literal field-key slugs: {duplicates}")

        confirmed_slugs = re.findall(r"self::confirmed_setting\(\s*'([^']+)'\s*,", fields)
        duplicates = sorted(slug for slug, count in Counter(confirmed_slugs).items() if count > 1)
        if duplicates:
            self.error(f"Duplicate confirmation-wrapped settings: {duplicates}")

        missing_fields = sorted(
            name for name in REQUIRED_FIELD_NAMES if f"'{name}'" not in fields
        )
        if missing_fields:
            self.error(f"Missing required SCF stored field names: {missing_fields}")

        campaign_sections = "\n".join(
            self.method_body(fields, method) or ""
            for method in (
                "campaign_public_group",
                "campaign_brief_group",
                "campaign_proof_group",
                "campaign_governance_group",
            )
        )
        leaked = sorted(name for name in CAMPAIGN_FORBIDDEN_FACT_FIELDS if f"'{name}'" in campaign_sections)
        if leaked:
            self.error(f"Campaign groups duplicate canonical Tour facts: {leaked}")

        settings = self.method_body(fields, "settings_group") or ""
        defaults = set(re.findall(r"['\"]default_value['\"]\s*=>\s*['\"]([^'\"]+)['\"]", settings))
        expected_defaults = {
            "Holiday Kenya Safaris",
            "Holiday Kenya Safaris is operated by Ashford Tours & Travel.",
            "254722742799",
        }
        if defaults != expected_defaults:
            self.error(
                f"Global string defaults must be exactly the three approved values; found {sorted(defaults)}"
            )

        if "CLIENT CONFIRMATION REQUIRED" in fields:
            self.error("Field definitions must not seed the client-confirmation sentinel as public/default data")

    def validate_boot_and_lifecycle(self) -> None:
        bootstrap = self.read("hks-core.php")
        plugin = self.read("src/Plugin.php")
        lifecycle = self.read("src/Lifecycle.php")
        content = self.read("src/Content/Module.php")
        requirements = self.read("src/Requirements.php")
        if bootstrap:
            self.require_markers(
                "Plugin bootstrap",
                bootstrap,
                (
                    "Version:           0.2.0",
                    "define( 'HKS_CORE_VERSION', '0.2.0' )",
                    "define( 'HKS_CORE_MINIMUM_SCF_VERSION', '6.9.1' )",
                    "define( 'HKS_CORE_SCF_BASENAME', 'secure-custom-fields/secure-custom-fields.php' )",
                ),
            )
        if plugin:
            self.require_markers(
                "Plugin coordinator",
                plugin,
                ("ContentModule::class", "FieldsModule::class", "PublicationGuard::class"),
            )
        if lifecycle:
            self.require_markers(
                "Lifecycle",
                lifecycle,
                (
                    "'0.2.0' => array(",
                    "hks_core_flush_rewrite_rules",
                    "hks_core_rewrite_generation",
                    "HKS_CORE_SCF_BASENAME",
                    "active_sitewide_plugins",
                ),
            )
        if content:
            self.require_markers(
                "Content module",
                content,
                (
                    "add_action( 'init'",
                    "add_action( 'wp_loaded'",
                    "flush_rewrite_rules( false )",
                    "hks_core_rewrite_generation_applied",
                ),
            )
        if requirements:
            self.require_markers(
                "Requirements",
                requirements,
                ("HKS_CORE_MINIMUM_SCF_VERSION", "HKS_CORE_SCF_BASENAME", "active_sitewide_plugins"),
            )

    def validate_publication_guard(self) -> None:
        rules = self.read("src/Fields/PublicationRules.php")
        guard = self.read("src/Fields/PublicationGuard.php")
        if not rules or not guard:
            return
        self.require_markers(
            "Publication guard",
            guard,
            (
                "acf/validate_save_post",
                "rest_pre_insert_hks_tour",
                "rest_pre_insert_hks_campaign",
                "wp_insert_post_data",
                "transition_post_status",
                "before_delete_post",
                "post_status' => 'draft'",
            ),
        )
        self.require_markers(
            "Publication rules",
            rules,
            (
                "CLIENT CONFIRMATION REQUIRED",
                "stripos(",
                "array( 'placeholder', 'operator_reviewed', 'client_confirmed' )",
                "Converted estimates and expired rates cannot be published",
                "hks_internal_product_id",
                "hks_linked_tour",
                "hks_analytics_campaign_label",
                "hks_tour_title_required",
                "hks_campaign_title_required",
            ),
        )
        for media_gate in ("'hks_permission_status'", "'hks_rights_checked_date'"):
            if media_gate in rules:
                self.error(f"Media rights must not be an automatic publication gate: {media_gate}")

    def validate_storage_boundaries(self) -> None:
        php_text = "\n".join(
            path.read_text(encoding="utf-8-sig")
            for path in sorted(SRC.rglob("*.php"), key=str)
        )
        if "register_post_meta" in php_text:
            self.error("SCF-owned values must not also be registered through native register_post_meta")

        json_files = list((PLUGIN / "acf-json").glob("*.json"))
        if json_files:
            self.error(
                "Field groups are code-owned; remove duplicate SCF Local JSON definitions: "
                + ", ".join(path.name for path in json_files)
            )


def main() -> int:
    validator = Validator()
    validator.validate()
    if validator.errors:
        print("Content-model validation failed:", file=sys.stderr)
        for error in validator.errors:
            print(f"- {error}", file=sys.stderr)
        return 1

    print("Content-model validation passed.")
    print("Checked registrations, SCF schema/REST boundaries, lifecycle wiring, and publication safeguards.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
