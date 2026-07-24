"""Build the client-authorized HKS Ashford expansion seed from the live audit."""

from __future__ import annotations

import argparse
import json
import re
from pathlib import Path
from typing import Any


EXCLUDED_SLUGS = {"african-wildlife-safari"}


def clean(value: str) -> str:
    return re.sub(r"\s+", " ", value or "").strip()


def destination_terms(title: str, categories: list[str]) -> list[str]:
    haystack = " ".join([title, *categories]).lower()
    candidates = [
        ("Kenya", r"\bkenya\b|nairobi|amboseli|maasai mara|masai mara|lake nakuru"),
        ("Tanzania", r"\btanzania\b|arusha|serengeti|ngorongoro|tarangire|manyara|kilimanjaro"),
        ("Arusha", r"\barusha\b"),
        ("Serengeti National Park", r"\bserengeti\b"),
        ("Ngorongoro Conservation Area", r"\bngorongoro\b"),
        ("Lake Manyara National Park", r"\bmanyara\b"),
        ("Tarangire National Park", r"\btarangire\b"),
        ("Mount Kilimanjaro", r"\bkilimanjaro\b"),
        ("Amboseli National Park", r"\bamboseli\b"),
        ("Maasai Mara", r"\bmaasai mara\b|\bmasai mara\b"),
        ("Lake Nakuru National Park", r"\blake nakuru\b"),
    ]
    return [label for label, pattern in candidates if re.search(pattern, haystack)]


def start_end(record: dict[str, Any]) -> tuple[str, str]:
    category_text = " ".join(record.get("categories", []))
    for place in ("Nairobi", "Mombasa", "Malindi", "Arusha"):
        if re.search(rf"From/To {place}", category_text, re.I):
            return place, place
    if "Kenya & Tanzania" in category_text:
        return "Nairobi", "Nairobi"
    if "Kilimanjaro" in category_text:
        return "Tanzania", "Tanzania"
    return "", ""


def overview_and_excerpt(preview: str, title: str) -> tuple[str, str]:
    overview = clean(preview)
    overview = re.sub(r"^Safari Preview\s*", "", overview, flags=re.I)
    overview = overview.split("Safari Highlight", 1)[0].strip()
    if not overview:
        overview = f"See the day-by-day itinerary for {title} and request a quote for your dates and group."
    sentences = re.split(r"(?<=[.!?])\s+", overview)
    excerpt = " ".join(sentences[:2]).strip()
    return overview, excerpt[:400].rstrip()


def list_rows(items: list[str]) -> list[dict[str, str]]:
    return [{"category": "other", "item": clean(item), "detail": ""} for item in items if clean(item)]


def route_summary(record: dict[str, Any], start: str, end: str) -> str:
    destinations = destination_terms(record["title"], record.get("categories", []))
    route = [start, *destinations, end]
    compact: list[str] = []
    for item in route:
        if item and (not compact or compact[-1] != item):
            compact.append(item)
    return " → ".join(compact[:7])


def new_tour(record: dict[str, Any]) -> dict[str, Any]:
    title = clean(record["title"])
    overview, excerpt = overview_and_excerpt(record["sections"]["preview"], title)
    start, end = start_end(record)
    is_climb = any("Kilimanjaro" in category for category in record.get("categories", []))
    accommodations = []
    for day in record.get("itinerary_rows", []):
        accommodation = clean(day.get("accommodation", ""))
        if accommodation and accommodation not in accommodations:
            accommodations.append(accommodation)

    source = {
        "url": record["url"],
        "reference": "Current Ashford product page",
        "checked_date": "2026-07-24",
        "category": ", ".join(record.get("categories", [])),
        "status": "client_authorized_import",
        "notes": (
            f"Source price USD {record['source_price_usd']}; basis {record['price_basis']}; "
            f"USD/KSh {record['exchange_rate_usd_ksh']}; unrounded KSh {record['unrounded_ksh']}; "
            f"rounded upward to KSh {record['from_price_ksh']}."
            if record.get("source_price_usd")
            else "No public source price was found on the checked Ashford page; keep draft until priced."
        ),
    }
    fields: dict[str, Any] = {
        "hks_duration_label": (record.get("duration") or [""])[0],
        "hks_start_location": start,
        "hks_end_location": end,
        "hks_route_summary": route_summary(record, start, end),
        "hks_transport_types": ["other"] if is_climb else ["land_cruiser"],
        "hks_accommodation_basis": "; ".join(accommodations),
        "hks_meals_summary": "Meals as listed in the day-by-day itinerary.",
        "hks_itinerary": record.get("itinerary_rows", []),
        "hks_inclusions": list_rows(record.get("included_items", [])),
        "hks_exclusions": list_rows(record.get("excluded_items", [])),
    }
    if record.get("from_price_ksh"):
        fields["hks_from_price_ksh"] = record["from_price_ksh"]

    categories = record.get("categories", [])
    if any("Kilimanjaro" in category for category in categories):
        batch = 2
    elif "Kenya & Tanzania" in categories:
        batch = 1
    else:
        batch = 3

    return {
        "batch": batch,
        "product_id": "HKS-ASHFORD-" + record["slug"].upper(),
        "slug": record["slug"],
        "title": title,
        "excerpt": excerpt,
        "overview": overview,
        "publication_status": "publish" if record.get("from_price_ksh") else "draft",
        "source": source,
        "taxonomies": {
            "hks_tour_scope": ["International Tours"],
            "hks_destination": destination_terms(title, record.get("categories", [])),
            "hks_tour_type": ["Trekking & Adventure" if is_climb else "Road Safari"],
            "hks_travel_style": ["Multi-day Adventure" if is_climb else "Multi-day Safari"],
        },
        "fields": fields,
        "media": [
            {
                "url": record["primary_image_url"],
                "alt": title,
                "role": "featured",
            }
        ]
        if record.get("primary_image_url")
        else [],
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--audit", type=Path, required=True)
    parser.add_argument("--existing-seeds", type=Path, nargs="+", required=True)
    parser.add_argument("--output", type=Path, required=True)
    args = parser.parse_args()

    audit = json.loads(args.audit.read_text(encoding="utf-8"))
    existing_slugs: set[str] = set()
    existing_by_source: dict[str, str] = {}
    for seed_path in args.existing_seeds:
        seed = json.loads(seed_path.read_text(encoding="utf-8"))
        for tour in seed.get("tours", []):
            existing_slugs.add(tour["slug"])
            source_url = tour.get("source", {}).get("url", "")
            if source_url:
                existing_by_source[source_url.rstrip("/")] = tour["slug"]

    records = [record for record in audit["records"] if record["slug"] not in EXCLUDED_SLUGS]
    existing_updates = []
    expansion_tours = []
    for record in records:
        existing_slug = (
            record["slug"]
            if record["slug"] in existing_slugs
            else existing_by_source.get(record["url"].rstrip("/"), "")
        )
        if existing_slug:
            update: dict[str, Any] = {
                "slug": existing_slug,
                "source_url": record["url"],
                "scope": "Kenya Tours",
            }
            if record.get("from_price_ksh"):
                update["hks_from_price_ksh"] = record["from_price_ksh"]
                update["conversion"] = {
                    "source_price_usd": record["source_price_usd"],
                    "basis": record["price_basis"],
                    "rate": record["exchange_rate_usd_ksh"],
                    "unrounded_ksh": record["unrounded_ksh"],
                    "rounded_ksh": record["from_price_ksh"],
                }
            existing_updates.append(update)
        elif record["scope"] == "International Tours":
            expansion_tours.append(new_tour(record))

    output = {
        "schema_version": 1,
        "publication_policy": "client_authorized_publish",
        "authorization_date": "2026-07-24",
        "exchange_rate": audit["exchange_rate"],
        "batch_labels": {
            "1": "Kenya and Tanzania combinations",
            "2": "Mount Kilimanjaro",
            "3": "Tanzania safaris",
        },
        "existing_tour_updates": existing_updates,
        "new_tours": expansion_tours,
    }
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(output, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    published = sum(tour["publication_status"] == "publish" for tour in expansion_tours)
    print(
        f"Wrote {len(existing_updates)} existing updates and {len(expansion_tours)} new tours "
        f"({published} publish, {len(expansion_tours) - published} draft) to {args.output}"
    )


if __name__ == "__main__":
    main()
