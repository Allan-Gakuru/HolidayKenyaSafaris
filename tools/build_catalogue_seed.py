#!/usr/bin/env python3
"""Build the Phase 7 draft catalogue from the reviewed Ashford crawl.

The output deliberately contains no prices, photographs, policies, reviews, or
availability claims. Records stay in draft until an editor completes and
publishes them in WordPress.
"""

from __future__ import annotations

import html
import json
import re
from pathlib import Path
from urllib.parse import urlparse


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "work" / "ashford_crawl" / "catalog.json"
OUTPUT = ROOT / "wp-content" / "plugins" / "hks-core" / "data" / "catalogue-seed.json"

ALLOWED_CATEGORIES = {
    "Kenya Safaris",
    "Kenya Flying Safaris",
    "Nairobi Excursions",
    "Mombasa Excursions",
    "Mount Kenya",
}

EXCLUDED_URLS = {
    # Already represented by the three MVP Tour records.
    "https://ashford-tours.com/product/3-days-2-night-amboseli-safari-package/",
    "https://ashford-tours.com/product/3-days-2-nights-maasai-mara-road-safari/",
    "https://ashford-tours.com/product/nairobi-national-park-tours-4-hours/",
    # A generic, mixed-destination marketing page rather than a quotable Tour.
    "https://ashford-tours.com/product/african-wildlife-safari-2/",
}

BATCHES = {
    1: "Road safaris",
    2: "Flying safaris and Mount Kenya",
    3: "Nairobi excursions",
    4: "Mombasa excursions",
}

DESTINATIONS = (
    ("Maasai Mara", (r"\bmaasai mara\b", r"\bmasai mara\b", r"\bwings over mara\b")),
    ("Amboseli National Park", (r"\bamboseli\b",)),
    ("Samburu National Reserve", (r"\bsamburu\b",)),
    ("Lake Nakuru National Park", (r"\blake nakuru\b", r"\bnakuru national park\b")),
    ("Ol Pejeta Conservancy", (r"\bol pejeta\b", r"\bolpajeta\b", r"\bsweetwaters\b")),
    ("Tsavo West National Park", (r"\btsavo west\b",)),
    ("Tsavo East National Park", (r"\btsavo east\b",)),
    ("Aberdare National Park", (r"\baberdare(?:s)?\b",)),
    ("Mount Kenya", (r"\bmount kenya\b", r"\bmt\.? kenya\b")),
    ("Nairobi National Park", (r"\bnairobi national park\b",)),
    ("Shimba Hills National Reserve", (r"\bshimba hills\b",)),
    ("Lake Naivasha", (r"\blake naivasha\b", r"\bnaivasha\b", r"\bcrescent island\b")),
    ("Hell's Gate National Park", (r"\bhell'?s gate\b",)),
    ("Mount Longonot", (r"\bmount longonot\b",)),
)


def clean_text(value: object) -> str:
    """Decode entities and collapse source whitespace."""

    return re.sub(r"\s+", " ", html.unescape(str(value or ""))).strip()


def slug_from_url(url: str) -> str:
    return urlparse(url).path.rstrip("/").split("/")[-1]


def category_for(record: dict) -> str:
    categories = [clean_text(value) for value in record.get("categories", [])]
    for category in categories:
        if category in ALLOWED_CATEGORIES:
            return category
    return ""


def batch_for(category: str) -> int:
    if category == "Kenya Safaris":
        return 1
    if category in {"Kenya Flying Safaris", "Mount Kenya"}:
        return 2
    if category == "Nairobi Excursions":
        return 3
    return 4


def tour_type_for(category: str) -> str:
    return {
        "Kenya Safaris": "Road Safari",
        "Kenya Flying Safaris": "Flying Safari",
        "Nairobi Excursions": "Day Excursion",
        "Mombasa Excursions": "Coast Experience",
        "Mount Kenya": "Trekking & Adventure",
    }[category]


def duration_for(record: dict) -> str:
    durations = [clean_text(value) for value in record.get("duration", []) if clean_text(value)]
    if durations:
        return durations[0]

    title = clean_text(record.get("title"))
    explicit = re.search(r"\b(\d+\s*hours?)\b", title, re.IGNORECASE)
    if explicit:
        return explicit.group(1).lower()
    if re.search(r"\bhalf[ -]day\b", title, re.IGNORECASE):
        return "Half day"
    if re.search(r"\bday (?:trip|tour)\b", title, re.IGNORECASE):
        return "1 day"
    return ""


def travel_styles(category: str, duration: str) -> list[str]:
    value = duration.lower()
    if category == "Kenya Flying Safaris":
        return ["Flying Safari"]
    if category == "Mount Kenya":
        return ["Trekking", "Multi-day Adventure"]
    if "hour" in value:
        return ["Half Day"] if any(token in value for token in ("2 hour", "3 hour", "4 hour", "5 hour")) else ["Day Trip"]
    if "day" in value:
        match = re.search(r"\b(\d+)\s*days?\b", value)
        days = int(match.group(1)) if match else 0
        if days == 1:
            return ["Day Trip"]
        if 2 <= days <= 4:
            return ["Short Break"]
        if days >= 5:
            return ["Multi-day Safari"]
    if category in {"Nairobi Excursions", "Mombasa Excursions"}:
        return ["Day Excursion"]
    return []


def clean_route_day(value: object) -> str:
    """Keep the route heading and discard the crawl's truncated body fragment."""

    text = clean_text(value)
    if not text:
        return ""
    split = re.split(
        r"\s+(?=(?:Morning|Early|After|Upon|Pick(?:up|\s+up)|Depart|Drive|Breakfast|Arrive|Proceed|Today|Following)\b)",
        text,
        maxsplit=1,
        flags=re.IGNORECASE,
    )[0]
    split = split.rstrip(" ,.;:-")
    if len(split) > 110:
        split = split[:110].rsplit(" ", 1)[0].rstrip(" ,.;:-")
    return split


def route_days_for(record: dict) -> list[str]:
    days = [clean_route_day(value) for value in record.get("route_days", [])]
    return [value for value in days if value]


def destinations_for(title: str, route_days: list[str], category: str) -> list[str]:
    haystack = " ".join([title, *route_days]).lower()
    matches: list[tuple[int, str]] = []
    for destination, patterns in DESTINATIONS:
        positions = [match.start() for pattern in patterns for match in re.finditer(pattern, haystack, re.IGNORECASE)]
        if positions:
            matches.append((min(positions), destination))
    destinations = [destination for _, destination in sorted(matches)]
    if not destinations and category == "Nairobi Excursions":
        destinations = ["Nairobi"]
    elif not destinations and category == "Mombasa Excursions":
        destinations = ["Mombasa"]
    return destinations


def route_summary_for(route_days: list[str], destinations: list[str], category: str) -> str:
    places = list(destinations)
    if route_days and route_days[0].lower().startswith("nairobi"):
        places.insert(0, "Nairobi")
    if route_days and re.search(r"nairobi\s*$", route_days[-1], re.IGNORECASE):
        places.append("Nairobi")
    compact_places = [place for index, place in enumerate(places) if index == 0 or place != places[index - 1]]
    if compact_places:
        return " → ".join(compact_places)
    if route_days:
        return route_days[0]
    if category == "Nairobi Excursions":
        return "Nairobi excursion"
    if category == "Mombasa Excursions":
        return "Mombasa excursion"
    return ""


def location_fields(category: str, route_summary: str) -> tuple[str, str]:
    lower = route_summary.lower()
    if category == "Nairobi Excursions":
        return "Nairobi", "Nairobi"
    if category == "Mombasa Excursions":
        return "Mombasa", "Mombasa"
    start = "Nairobi" if lower.startswith("nairobi") else ""
    end = "Nairobi" if re.search(r"nairobi\s*$", lower) else ""
    return start, end


def concise_subject(title: str) -> str:
    value = re.sub(r"^\s*\d+\s*days?(?:\s*\([^)]*\)|\s*\d+\s*nights?)?\s*[:\-/]*\s*", "", title, flags=re.IGNORECASE)
    value = re.sub(r"\s+package\s*$", "", value, flags=re.IGNORECASE)
    return value or title


def marketing_copy(title: str, duration: str, category: str, destinations: list[str], route_summary: str) -> tuple[str, str]:
    subject = concise_subject(title)
    duration_prefix = f"A {duration.lower()} " if duration else "A "
    kind = {
        "Kenya Safaris": "road safari",
        "Kenya Flying Safaris": "flying safari",
        "Nairobi Excursions": "local excursion",
        "Mombasa Excursions": "coast excursion",
        "Mount Kenya": "Mount Kenya trek",
    }[category]
    place = ", ".join(destinations[:3])
    excerpt = f"{duration_prefix}{kind}"
    if place:
        excerpt += f" covering {place}"
    excerpt += "."
    detail = f"Explore {subject} with the source duration and route outline kept together in one Tour."
    if route_summary:
        detail += f" The route is listed as {route_summary}."
    detail += " Your quote confirms the current KSh rate and final package details for your dates and group."
    return excerpt, detail


def itinerary_for(route_days: list[str]) -> list[dict[str, str]]:
    return [
        {
            "day_number": str(index),
            "day_title": route,
            "description": "",
            "activities": "",
            "accommodation": "",
            "meals": "",
        }
        for index, route in enumerate(route_days, start=1)
    ]


def build_tour(record: dict) -> dict:
    title = clean_text(record.get("title"))
    url = clean_text(record.get("url"))
    slug = slug_from_url(url)
    category = category_for(record)
    duration = duration_for(record)
    route_days = route_days_for(record)
    destinations = destinations_for(title, route_days, category)
    route_summary = route_summary_for(route_days, destinations, category)
    start, end = location_fields(category, route_summary)
    excerpt, overview = marketing_copy(title, duration, category, destinations, route_summary)

    fields: dict[str, object] = {}
    if duration:
        fields["hks_duration_label"] = duration
    if start:
        fields["hks_start_location"] = start
    if end:
        fields["hks_end_location"] = end
    if route_summary:
        fields["hks_route_summary"] = route_summary
    if route_days:
        fields["hks_itinerary"] = itinerary_for(route_days)

    return {
        "batch": batch_for(category),
        "product_id": f"HKS-ASHFORD-{slug.upper()}",
        "slug": slug,
        "title": title,
        "excerpt": excerpt,
        "overview": overview,
        "source": {
            "url": url,
            "reference": "Ashford public catalogue crawl",
            "checked_date": "2026-07-02",
            "category": category,
            "status": "draft_migration",
            "notes": "Imported as a protected draft. Add the current KSh from-price, approved media, and any missing public details before publishing.",
        },
        "taxonomies": {
            "hks_destination": destinations,
            "hks_tour_type": [tour_type_for(category)],
            "hks_occasion": [],
            "hks_travel_style": travel_styles(category, duration),
        },
        "fields": fields,
    }


def main() -> None:
    source_records = json.loads(SOURCE.read_text(encoding="utf-8"))
    candidates = []
    seen_urls: set[str] = set()

    for record in source_records:
        url = clean_text(record.get("url"))
        category = category_for(record)
        if not category or url in EXCLUDED_URLS or url in seen_urls:
            continue
        seen_urls.add(url)
        candidates.append(build_tour(record))

    candidates.sort(key=lambda tour: (tour["batch"], tour["title"].casefold()))
    payload = {
        "schema_version": 1,
        "publication_policy": "draft_only",
        "source_checked_date": "2026-07-02",
        "batch_labels": {str(number): label for number, label in BATCHES.items()},
        "tours": candidates,
        "campaigns": [],
    }

    if len(candidates) != 40:
        raise SystemExit(f"Expected 40 Phase 7 candidates, found {len(candidates)}")

    OUTPUT.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print(f"Wrote {len(candidates)} draft Tours to {OUTPUT}")


if __name__ == "__main__":
    main()
