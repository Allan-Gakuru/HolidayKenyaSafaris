#!/usr/bin/env python3
"""Validate the lean HKS editor/content-model contract without WordPress."""

from __future__ import annotations

import re
import sys
from collections import Counter
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
PLUGIN = ROOT / "wp-content" / "plugins" / "hks-core"

EXPECTED_GROUPS = {
    "tour_package": True,
    "tour_itinerary": True,
    "tour_inclusions": True,
    "tour_suitability": True,
    "tour_policies": True,
    "tour_media": True,
    "campaign_public": True,
    "faq_public": True,
    "destination_public": True,
    "settings": False,
}

REQUIRED_FIELDS = {
    "hks_featured", "hks_duration_label", "hks_start_location",
    "hks_end_location", "hks_route_summary", "hks_transport_types",
    "hks_accommodation_basis", "hks_meals_summary",
    "hks_itinerary", "hks_inclusions", "hks_exclusions", "hks_best_for",
    "hks_child_suitability", "hks_accessibility_notes", "hks_policies",
    "hks_gallery", "hks_featured_faqs", "hks_linked_tour",
    "hks_hero_headline", "hks_supporting_copy", "hks_campaign_from_price_ksh",
    "hks_navigation_mode",
    "hks_campaign_start_date", "hks_campaign_end_date", "hks_faq_answer",
    "hks_short_summary", "hks_overview", "hks_hero_image",
}

FORBIDDEN_EDITOR_FIELDS = {
    "hks_internal_product_id", "hks_original_ashford_title", "hks_source_url",
    "hks_source_reference", "hks_source_checked_date", "hks_source_status",
    "hks_duration_days", "hks_duration_nights", "hks_min_group_size",
    "hks_max_group_size", "hks_residency_basis", "hks_from_price_ksh",
    "hks_price_display_mode",
    "hks_price_unit", "hks_price_status", "hks_price_checked_date",
    "hks_price_valid_until", "hks_price_season_assumption",
    "hks_price_residency_assumption", "hks_price_group_size_assumption",
    "hks_price_transport_assumption", "hks_price_accommodation_assumption",
    "hks_price_inclusions_assumption", "hks_price_basis_summary",
    "hks_price_disclaimer", "hks_seasonal_rates", "hks_mandatory_supplements",
    "hks_physical_difficulty", "hks_packing_guidance", "hks_weather_season_notes",
    "hks_safety_notes", "hks_vehicle_image", "hks_accommodation_images",
    "hks_route_image", "hks_cta_label", "hks_whatsapp_package_label",
    "hks_intake_questions", "hks_target_audience", "hks_primary_desire",
    "hks_primary_problem", "hks_primary_objective", "hks_primary_objection",
    "hks_next_step", "hks_proof_points", "hks_campaign_status",
    "hks_analytics_campaign_label", "hks_noindex", "hks_confirmation_status",
    "hks_permission_status", "hks_usage_scopes", "hks_license_basis",
    "hks_permission_evidence", "hks_rights_checked_date",
}

REQUIRED_FILES = (
    "hks-core.php", "src/Plugin.php", "src/Content/Module.php",
    "src/Content/PostTypes/Tour.php", "src/Content/PostTypes/Campaign.php",
    "src/Content/PostTypes/Faq.php", "src/Fields/FieldGroups.php",
    "src/Content/Taxonomies/TourType.php",
    "src/Content/Taxonomies/Occasion.php",
    "src/Content/Taxonomies/TravelStyle.php",
    "src/Fields/FieldsModule.php", "src/Fields/PublicationRules.php",
    "src/Fields/PublicationGuard.php",
)


def read(relative: str, errors: list[str]) -> str:
    path = PLUGIN / relative
    try:
        return path.read_text(encoding="utf-8-sig")
    except (OSError, UnicodeError) as exc:
        errors.append(f"cannot read {path.relative_to(ROOT)}: {exc}")
        return ""


def require(errors: list[str], label: str, text: str, markers: tuple[str, ...]) -> None:
    for marker in markers:
        if marker not in text:
            errors.append(f"{label} is missing: {marker}")


def main() -> int:
    errors: list[str] = []
    files = {relative: read(relative, errors) for relative in REQUIRED_FILES}
    fields = files["src/Fields/FieldGroups.php"]
    rules = files["src/Fields/PublicationRules.php"]
    guard = files["src/Fields/PublicationGuard.php"]

    group_pattern = re.compile(
        r"self::group\(\s*'(?P<slug>[a-z0-9_]+)'.*?"
        r"self::location\([^;]+?\)\s*,\s*(?P<rest>true|false)\s*,",
        re.DOTALL,
    )
    actual_groups = {
        match.group("slug"): match.group("rest") == "true"
        for match in group_pattern.finditer(fields)
    }
    if actual_groups != EXPECTED_GROUPS:
        errors.append(f"lean SCF groups mismatch: expected {EXPECTED_GROUPS}, found {actual_groups}")

    missing = sorted(name for name in REQUIRED_FIELDS if f"'{name}'" not in fields)
    if missing:
        errors.append(f"missing lean editor fields: {missing}")

    leaked = sorted(name for name in FORBIDDEN_EDITOR_FIELDS if f"'{name}'" in fields)
    if leaked:
        errors.append(f"legacy/internal fields are still registered in the editor: {leaked}")

    literal_slugs = re.findall(r"self::(?:field|message|tab)\(\s*'([^']+)'\s*,", fields)
    duplicates = sorted(slug for slug, count in Counter(literal_slugs).items() if count > 1)
    if duplicates:
        errors.append(f"duplicate deterministic field slugs: {duplicates}")

    require(errors, "Campaign price field", fields, ("hks_campaign_from_price_ksh", "From price per person (KSh)", "'min'          => 1", "'step'         => 1", "Leave blank to omit price"))
    require(errors, "campaign dates", fields, ("hks_campaign_start_date", "hks_campaign_end_date", "do not publish, unpublish, expire, or change this Campaign"))
    require(errors, "settings compatibility", fields, ("public_setting", "'hks_settings_' . $slug", "Holiday Kenya Safaris", "254722742799"))
    require(errors, "fields module", files["src/Fields/FieldsModule.php"], ("acf_add_local_field_group", "acf_add_options_page", "hks-settings"))

    require(
        errors,
        "publication rules",
        rules,
        (
            "hks_tour_title_required", "hks_campaign_from_price_invalid",
            "is_positive_whole_number", "hks_linked_tour",
            "hks_campaign_start_date", "hks_campaign_end_date",
            "CLIENT CONFIRMATION REQUIRED",
        ),
    )
    if "hks_tour_from_price_invalid" in rules or "'hks_from_price_ksh'" in rules:
        errors.append("publication rules still load or validate the retired Tour price")
    for old_gate in ("hks_price_status", "hks_source_status", "hks_analytics_campaign_label"):
        if old_gate in rules:
            errors.append(f"publication rules still gate on removed field: {old_gate}")

    require(
        errors,
        "publication guard",
        guard,
        (
            "acf/validate_save_post", "rest_pre_insert_hks_tour",
            "rest_pre_insert_hks_campaign", "wp_insert_post_data",
            "transition_post_status", "before_delete_post",
        ),
    )

    require(errors, "Tour type", files["src/Content/PostTypes/Tour.php"], ("'hks_tour'", "'show_in_rest'        => true"))
    require(errors, "Campaign type", files["src/Content/PostTypes/Campaign.php"], ("'hks_campaign'", "'publicly_queryable'  => true"))
    require(errors, "Tour Type archive", files["src/Content/Taxonomies/TourType.php"], ("'public'             => true", "'publicly_queryable' => true", "'slug'         => 'tour-types'"))
    require(errors, "Occasion archive", files["src/Content/Taxonomies/Occasion.php"], ("'public'             => true", "'publicly_queryable' => true", "'slug'         => 'occasions'"))
    require(errors, "Travel Style archive", files["src/Content/Taxonomies/TravelStyle.php"], ("'public'             => true", "'publicly_queryable' => true", "'slug'         => 'travel-styles'"))

    source_text = "\n".join(
        path.read_text(encoding="utf-8-sig")
        for path in sorted((PLUGIN / "src").rglob("*.php"), key=str)
    )
    if "register_post_meta" in source_text:
        errors.append("SCF-owned values must not also use register_post_meta")
    if list((PLUGIN / "acf-json").glob("*.json")):
        errors.append("field groups are code-owned; duplicate Local JSON exists")

    if errors:
        print("Content-model validation failed:", file=sys.stderr)
        for error in errors:
            print(f"- {error}", file=sys.stderr)
        return 1

    print("Content-model validation passed (price-free Tours, optional Campaign price, Campaign-only dates, compatibility guards).")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
