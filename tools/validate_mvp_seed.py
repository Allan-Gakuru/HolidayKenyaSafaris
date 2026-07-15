#!/usr/bin/env python3
"""Validate the source-governed MVP seed without loading WordPress."""

from __future__ import annotations

import json
import sys
from pathlib import Path
from typing import Any, Dict, Iterable, List


ROOT = Path(__file__).resolve().parents[1]
SEED = ROOT / "wp-content" / "plugins" / "hks-core" / "data" / "mvp-seed.json"

EXPECTED_SOURCES = {
    "HKS-MVP-MARA-3D2N": "https://ashford-tours.com/product/3-days-2-nights-maasai-mara-road-safari/",
    "HKS-MVP-NNP-4H": "https://ashford-tours.com/product/nairobi-national-park-tours-4-hours/",
    "HKS-MVP-AMBOSELI-3D2N": "https://ashford-tours.com/product/3-days-2-night-amboseli-safari-package/",
}

REQUIRED_TOUR_FIELDS = {
    "hks_duration_label",
    "hks_start_location",
    "hks_end_location",
    "hks_route_summary",
    "hks_itinerary",
    "hks_best_for",
}

REQUIRED_CAMPAIGN_FIELDS = {
    "hks_hero_headline",
    "hks_supporting_copy",
    "hks_navigation_mode",
}

FORBIDDEN_TOUR_FIELDS = {
    "hks_from_price_ksh",
    "hks_duration_days",
    "hks_duration_nights",
    "hks_min_group_size",
    "hks_max_group_size",
    "hks_price_display_mode",
    "hks_price_status",
    "hks_seasonal_rates",
    "hks_mandatory_supplements",
    "hks_policies",
    "hks_gallery",
    "hks_card_image",
    "hks_cta_label",
    "hks_whatsapp_package_label",
    "hks_intake_questions",
}

FORBIDDEN_CAMPAIGN_FIELDS = {
    "hks_cta_label", "hks_target_audience", "hks_primary_desire",
    "hks_primary_problem", "hks_primary_objective", "hks_primary_objection",
    "hks_next_step", "hks_campaign_status", "hks_analytics_campaign_label",
    "hks_noindex",
}


def strings(value: Any) -> Iterable[str]:
    """Yield every string nested in a JSON-compatible value."""
    if isinstance(value, str):
        yield value
    elif isinstance(value, dict):
        for nested in value.values():
            yield from strings(nested)
    elif isinstance(value, list):
        for nested in value:
            yield from strings(nested)


def nonempty_text(value: Any) -> bool:
    return isinstance(value, str) and bool(value.strip())


def main() -> int:
    errors: List[str] = []

    try:
        data: Dict[str, Any] = json.loads(SEED.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as error:
        print(f"MVP seed validation failed: {error}")
        return 1

    if data.get("schema_version") != 1:
        errors.append("schema_version must be 1")
    if data.get("publication_policy") != "draft_only":
        errors.append("publication_policy must be draft_only")

    tours = data.get("tours")
    campaigns = data.get("campaigns")
    if not isinstance(tours, list) or len(tours) != 3:
        errors.append("seed must contain exactly three Tours")
        tours = []
    if not isinstance(campaigns, list) or len(campaigns) != 3:
        errors.append("seed must contain exactly three Campaigns")
        campaigns = []

    tour_ids: List[str] = []
    for index, tour in enumerate(tours):
        label = f"tour[{index}]"
        product_id = tour.get("product_id")
        tour_ids.append(product_id)

        if EXPECTED_SOURCES.get(product_id) != tour.get("source", {}).get("url"):
            errors.append(f"{label} does not use its approved Ashford source URL")
        if tour.get("source", {}).get("status") != "imported":
            errors.append(f"{label} source status must remain imported")
        if not all(nonempty_text(tour.get(name)) for name in ("slug", "title", "excerpt", "overview")):
            errors.append(f"{label} is missing native public copy")

        fields = tour.get("fields", {})
        if not isinstance(fields, dict):
            errors.append(f"{label} fields must be an object")
            continue
        missing = REQUIRED_TOUR_FIELDS - fields.keys()
        if missing:
            errors.append(f"{label} is missing fields: {', '.join(sorted(missing))}")
        forbidden = FORBIDDEN_TOUR_FIELDS & fields.keys()
        if forbidden:
            errors.append(f"{label} contains launch-gated fields: {', '.join(sorted(forbidden))}")
        if not isinstance(fields.get("hks_itinerary"), list) or not fields.get("hks_itinerary"):
            errors.append(f"{label} needs at least one source-backed itinerary row")

        for row_index, row in enumerate(fields.get("hks_itinerary", [])):
            extra = set(row) - {"day_number", "day_title", "description", "activities", "accommodation", "meals"}
            if extra:
                errors.append(f"{label} itinerary[{row_index}] contains removed fields: {', '.join(sorted(extra))}")

        public_surface = {
            "title": tour.get("title"),
            "excerpt": tour.get("excerpt"),
            "overview": tour.get("overview"),
            "fields": fields,
        }
        public_text = "\n".join(strings(public_surface)).lower()
        if "client confirmation required" in public_text:
            errors.append(f"{label} exposes the internal confirmation sentinel")
        if "usd" in public_text or "$" in public_text:
            errors.append(f"{label} exposes an old USD price on the public surface")

    if len(tour_ids) != len(set(tour_ids)) or set(tour_ids) != set(EXPECTED_SOURCES):
        errors.append("Tour product IDs must be the three unique MVP identities")

    campaign_labels: List[str] = []
    for index, campaign in enumerate(campaigns):
        label = f"campaign[{index}]"
        campaign_labels.append(campaign.get("internal_label"))
        if campaign.get("tour_product_id") not in EXPECTED_SOURCES:
            errors.append(f"{label} links to an unknown Tour")
        if not all(nonempty_text(campaign.get(name)) for name in ("internal_label", "slug", "title")):
            errors.append(f"{label} is missing identity copy")

        fields = campaign.get("fields", {})
        if not isinstance(fields, dict):
            errors.append(f"{label} fields must be an object")
            continue
        missing = REQUIRED_CAMPAIGN_FIELDS - fields.keys()
        if missing:
            errors.append(f"{label} is missing fields: {', '.join(sorted(missing))}")
        if any(not nonempty_text(fields.get(name)) for name in REQUIRED_CAMPAIGN_FIELDS):
            errors.append(f"{label} has an empty required copy field")
        forbidden = FORBIDDEN_CAMPAIGN_FIELDS & fields.keys()
        if forbidden:
            errors.append(f"{label} contains removed fields: {', '.join(sorted(forbidden))}")

        campaign_text = "\n".join(strings(fields)).lower()
        if "client confirmation required" in campaign_text:
            errors.append(f"{label} exposes the internal confirmation sentinel")
        if "usd" in campaign_text or "$" in campaign_text:
            errors.append(f"{label} exposes an old USD price")

    if len(campaign_labels) != len(set(campaign_labels)):
        errors.append("Campaign internal labels must be unique")
    if errors:
        print("MVP seed validation failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print("MVP seed validation passed (3 lean draft Tours, 3 linked lean draft Campaigns).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
