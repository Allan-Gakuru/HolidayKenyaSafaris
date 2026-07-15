#!/usr/bin/env python3
"""Validate the Phase 6 Page drafts and Phase 7 catalogue migration."""

from __future__ import annotations

import json
import sys
from pathlib import Path
from typing import Any


ROOT = Path(__file__).resolve().parents[1]
DATA = ROOT / "wp-content" / "plugins" / "hks-core" / "data"
CATALOGUE_SEED = DATA / "catalogue-seed.json"
PAGE_SEED = DATA / "site-pages-seed.json"
SOURCE_CATALOGUE = ROOT / "work" / "ashford_crawl" / "catalog.json"

EXPECTED_PAGES = {
    "about",
    "group-travel",
    "contact",
    "privacy-policy",
    "website-terms",
    "booking-terms",
    "cancellation-refund-policy",
}

EXPECTED_BATCH_COUNTS = {1: 12, 2: 10, 3: 14, 4: 4}
ALLOWED_TOUR_FIELDS = {
    "hks_duration_label",
    "hks_start_location",
    "hks_end_location",
    "hks_route_summary",
    "hks_itinerary",
}
FORBIDDEN_PUBLIC_TERMS = ("client confirmation required", "usd", "$")


def load_json(path: Path, errors: list[str]) -> Any:
    try:
        value = json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as error:
        errors.append(f"cannot read {path.relative_to(ROOT)}: {error}")
        return {}
    return value


def validate_contract(data: dict[str, Any], label: str, errors: list[str]) -> None:
    if not isinstance(data, dict):
        errors.append(f"{label} must contain a JSON object")
        return
    if data.get("schema_version") != 1:
        errors.append(f"{label} schema_version must be 1")
    if data.get("publication_policy") != "draft_only":
        errors.append(f"{label} publication_policy must be draft_only")


def main() -> int:
    errors: list[str] = []
    pages_data = load_json(PAGE_SEED, errors)
    catalogue_data = load_json(CATALOGUE_SEED, errors)
    source_data = load_json(SOURCE_CATALOGUE, errors)
    validate_contract(pages_data, "site page seed", errors)
    validate_contract(catalogue_data, "catalogue seed", errors)

    pages = pages_data.get("pages", []) if isinstance(pages_data, dict) else []
    if not isinstance(pages, list) or len(pages) != len(EXPECTED_PAGES):
        errors.append("site page seed must contain exactly seven Page drafts")
        pages = []

    slugs = {page.get("slug") for page in pages if isinstance(page, dict)}
    if slugs != EXPECTED_PAGES:
        errors.append("site page seed has missing or unexpected routes")
    if len({page.get("page_id") for page in pages if isinstance(page, dict)}) != len(pages):
        errors.append("site page IDs must be unique")

    for page in pages:
        text = "\n".join(str(page.get(key, "")) for key in ("title", "excerpt", "content")).lower()
        if any(term in text for term in FORBIDDEN_PUBLIC_TERMS):
            errors.append(f"page {page.get('slug')} contains forbidden public placeholder or currency copy")
        if page.get("slug") in {"privacy-policy", "website-terms", "booking-terms", "cancellation-refund-policy"} and page.get("content"):
            errors.append(f"legal page {page.get('slug')} must remain empty until approved text is supplied")

    tours = catalogue_data.get("tours", []) if isinstance(catalogue_data, dict) else []
    if not isinstance(tours, list) or len(tours) != 40:
        errors.append("catalogue seed must contain exactly 40 remaining local Tour drafts")
        tours = []

    source_titles = {
        record.get("url"): record.get("title")
        for record in source_data
        if isinstance(record, dict) and record.get("url")
    } if isinstance(source_data, list) else {}
    ids: set[str] = set()
    urls: set[str] = set()
    slugs_seen: set[str] = set()
    batch_counts = {number: 0 for number in EXPECTED_BATCH_COUNTS}

    for index, tour in enumerate(tours):
        label = f"catalogue tour[{index}]"
        product_id = tour.get("product_id")
        source = tour.get("source", {})
        url = source.get("url") if isinstance(source, dict) else None
        slug = tour.get("slug")
        batch = tour.get("batch")
        fields = tour.get("fields", {})
        taxonomies = tour.get("taxonomies", {})

        if not all(isinstance(value, str) and value.strip() for value in (product_id, slug, tour.get("title"), tour.get("excerpt"), tour.get("overview"), url)):
            errors.append(f"{label} is missing identity, source, or public draft copy")
        if product_id in ids or url in urls or slug in slugs_seen:
            errors.append(f"{label} duplicates an ID, URL, or slug")
        ids.add(product_id)
        urls.add(url)
        slugs_seen.add(slug)

        if url not in source_titles or source_titles.get(url) != tour.get("title"):
            errors.append(f"{label} title does not match its reviewed Ashford source record")
        if batch not in EXPECTED_BATCH_COUNTS:
            errors.append(f"{label} has an unsupported batch")
        else:
            batch_counts[batch] += 1
        if not isinstance(fields, dict) or set(fields) - ALLOWED_TOUR_FIELDS:
            errors.append(f"{label} contains unsupported or non-display Tour fields")
        if any(key in fields for key in ("hks_from_price_ksh", "hks_gallery", "hks_policies", "hks_inclusions", "hks_exclusions")):
            errors.append(f"{label} assigns price, media, policy, inclusion, or exclusion data automatically")
        if set(taxonomies) != {"hks_destination", "hks_tour_type", "hks_occasion", "hks_travel_style"}:
            errors.append(f"{label} does not use the four existing Tour taxonomies")

        public_text = "\n".join(str(tour.get(key, "")) for key in ("title", "excerpt", "overview", "fields")).lower()
        if any(term in public_text for term in FORBIDDEN_PUBLIC_TERMS):
            errors.append(f"{label} contains forbidden public placeholder or old currency copy")

    if batch_counts != EXPECTED_BATCH_COUNTS:
        errors.append(f"catalogue batch counts are {batch_counts}, expected {EXPECTED_BATCH_COUNTS}")

    if errors:
        print("Phase 6/7 seed validation failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print("Phase 6/7 seed validation passed (7 site Page drafts, 40 protected Tour drafts in 4 batches).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
