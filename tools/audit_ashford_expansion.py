"""Audit current Ashford product pages for the authorized HKS catalogue expansion.

This script reads the versioned Ashford crawl for product URLs, rechecks each live
page, extracts the primary image and visible product sections, identifies the
low-season per-person USD price (or the single published from-price where no
seasonal table exists), and records a reproducible KSh conversion.
"""

from __future__ import annotations

import argparse
import json
import math
import re
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path
from typing import Any

import requests
from bs4 import BeautifulSoup


USER_AGENT = "Holiday Kenya Safaris catalogue audit/1.0"
LOCAL_CATEGORIES = {
    "Kenya Flying Safaris",
    "Kenya Safaris",
    "Mombasa Excursions",
    "Mount Kenya",
    "Nairobi Excursions",
}


def clean_text(value: str) -> str:
    return re.sub(r"\s+", " ", value or "").strip()


def section_text(soup: BeautifulSoup, section_id: str) -> str:
    section = soup.find(id=section_id)
    return clean_text(section.get_text("\n", strip=True)) if section else ""


def section_items(soup: BeautifulSoup, section_id: str) -> list[str]:
    section = soup.find(id=section_id)
    if not section:
        return []
    items = [clean_text(item.get_text(" ", strip=True)) for item in section.select("li")]
    return [item for item in items if item]


def itinerary_rows(soup: BeautifulSoup) -> list[dict[str, str]]:
    section = soup.find(id="itinerary")
    if not section:
        return []

    rows: list[dict[str, str]] = []
    for paragraph in section.select("p"):
        text = clean_text(paragraph.get_text(" ", strip=True))
        match = re.match(r"Day\s+([0-9]+(?:\s*[-–—]\s*[0-9]+)?)\s*:\s*(.+)", text, re.I)
        if not match:
            continue
        body = match.group(2).strip()
        title, separator, description = body.partition(".")
        row = {
            "day_number": match.group(1).strip(),
            "day_title": title.strip(),
            "description": description.strip() if separator else body,
        }
        meal = re.search(r"Meal Plan\s*:\s*\(([^)]+)\)", body, re.I)
        if meal:
            row["meals"] = clean_text(meal.group(1))
        accommodation = re.search(
            r"(?:dinner and )?overnight at\s+(.+?)(?:\.|,\s*Meal Plan|Meal Plan)",
            body,
            re.I,
        )
        if accommodation:
            row["accommodation"] = clean_text(accommodation.group(1))
        rows.append(row)
    return rows


def primary_image(soup: BeautifulSoup) -> str:
    for script in soup.find_all("script", attrs={"type": "application/ld+json"}):
        try:
            data = json.loads(script.get_text(strip=True))
        except (TypeError, json.JSONDecodeError):
            continue

        nodes = data.get("@graph", []) if isinstance(data, dict) else []
        for node in nodes:
            if not isinstance(node, dict) or node.get("@type") != "Product":
                continue
            images = node.get("image", [])
            if isinstance(images, dict):
                images = [images]
            for image in images:
                if isinstance(image, str) and image.startswith("http"):
                    return image
                if isinstance(image, dict):
                    url = image.get("url") or image.get("contentUrl")
                    if isinstance(url, str) and url.startswith("http"):
                        return url
    return ""


def usd_amount(value: str) -> float | None:
    match = re.search(r"(?:US\s*\$|USD\s*)\s*([0-9][0-9,]*(?:\.[0-9]+)?)", value, re.I)
    return float(match.group(1).replace(",", "")) if match else None


def select_price(cost_text: str, fallback_prices: list[str]) -> tuple[float | None, str]:
    low_match = re.search(
        r"LOW\s*(?:[-–—]\s*)?SEASON(?P<body>.*?)(?:Payment Policy|Cancellation|$)",
        cost_text,
        re.I,
    )
    if low_match:
        body = low_match.group("body")
        per_person = re.search(
            r"Price per person sharing[^$]{0,260}(?:US\s*\$|USD\s*)\s*([0-9][0-9,]*(?:\.[0-9]+)?)",
            body,
            re.I,
        )
        if per_person:
            return float(per_person.group(1).replace(",", "")), "low_season_per_person"

    per_person = re.search(
        r"Price per person sharing[^$]{0,260}(?:US\s*\$|USD\s*)\s*([0-9][0-9,]*(?:\.[0-9]+)?)",
        cost_text,
        re.I,
    )
    if per_person:
        return float(per_person.group(1).replace(",", "")), "single_per_person_price"

    for candidate in fallback_prices:
        amount = usd_amount(candidate)
        if amount:
            return amount, "published_from_price"

    return None, "missing"


def audit_record(record: dict[str, Any], rate: float) -> dict[str, Any]:
    response = requests.get(
        record["url"],
        headers={"User-Agent": USER_AGENT},
        timeout=45,
    )
    response.raise_for_status()
    # Ashford's server can advertise a legacy charset for UTF-8 product copy.
    response.encoding = "utf-8"
    soup = BeautifulSoup(response.text, "html.parser")

    sections = {
        name: section_text(soup, name)
        for name in ("preview", "itinerary", "includes", "excludes", "cost")
    }
    live_title = clean_text(soup.find("h1").get_text(" ", strip=True)) if soup.find("h1") else ""
    source_amount, basis = select_price(sections["cost"], record.get("prices", []))
    duration_text = " ".join(record.get("duration", [])) + " " + (record.get("title", "") or live_title)
    day_match = re.search(r"\b([2-9]|[1-9][0-9]+)\s*days?\b", duration_text, re.I)

    if basis == "published_from_price" and (
        (source_amount is not None and source_amount < 20)
        or (day_match and source_amount is not None and source_amount < 300)
    ):
        source_amount = None
        basis = "rejected_placeholder_or_deposit"

    converted = source_amount * rate if source_amount else None
    rounded = int(math.ceil(converted / 500.0) * 500) if converted else None
    categories = list(record.get("categories", []))
    return {
        "title": record.get("title", "") or live_title,
        "slug": record["url"].rstrip("/").rsplit("/", 1)[-1],
        "url": record["url"],
        "categories": categories,
        "scope": "Kenya Tours" if set(categories) & LOCAL_CATEGORIES else "International Tours",
        "duration": list(record.get("duration", [])),
        "route_days": list(record.get("route_days", [])),
        "primary_image_url": primary_image(soup),
        "source_price_usd": source_amount,
        "price_basis": basis,
        "exchange_rate_usd_ksh": rate,
        "unrounded_ksh": round(converted, 2) if converted else None,
        "from_price_ksh": rounded,
        "sections": sections,
        "itinerary_rows": itinerary_rows(soup),
        "included_items": section_items(soup, "includes"),
        "excluded_items": section_items(soup, "excludes"),
        "http_status": response.status_code,
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--catalog", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    parser.add_argument("--rate", type=float, required=True)
    parser.add_argument("--rate-date", required=True)
    parser.add_argument("--rate-source", required=True)
    parser.add_argument("--workers", type=int, default=8)
    args = parser.parse_args()

    catalog = json.loads(args.catalog.read_text(encoding="utf-8"))
    records: list[dict[str, Any]] = []
    errors: list[dict[str, str]] = []

    with ThreadPoolExecutor(max_workers=max(1, args.workers)) as executor:
        futures = {executor.submit(audit_record, item, args.rate): item for item in catalog}
        for future in as_completed(futures):
            item = futures[future]
            try:
                records.append(future.result())
            except Exception as error:  # noqa: BLE001 - audit must report every failed URL.
                errors.append({"url": item.get("url", ""), "error": str(error)})

    records.sort(key=lambda item: item["slug"])
    output = {
        "schema_version": 1,
        "checked_date": args.rate_date,
        "exchange_rate": {
            "pair": "USD/KSh",
            "rate": args.rate,
            "source": args.rate_source,
            "rounding": "Always upward to the next KSh 500",
            "formula": "ceil(source_usd * rate / 500) * 500",
        },
        "records": records,
        "errors": errors,
    }
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(output, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"Audited {len(records)} records; {len(errors)} errors; wrote {args.output}")


if __name__ == "__main__":
    main()
