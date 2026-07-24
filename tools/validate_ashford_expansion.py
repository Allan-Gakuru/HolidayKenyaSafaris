#!/usr/bin/env python3
"""Validate the client-authorized Ashford price and international Tour manifest."""

from __future__ import annotations

import json
import math
import sys
from collections import Counter
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SEED = ROOT / "wp-content" / "plugins" / "hks-core" / "data" / "ashford-expansion-seed.json"


def main() -> int:
    errors: list[str] = []

    try:
        data = json.loads(SEED.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as error:
        print(f"Ashford expansion validation failed: {error}", file=sys.stderr)
        return 1

    if data.get("schema_version") != 1:
        errors.append("schema_version must be 1")
    if data.get("publication_policy") != "client_authorized_publish":
        errors.append("publication policy must require the recorded client authorization")
    if data.get("authorization_date") != "2026-07-24":
        errors.append("authorization date must be 2026-07-24")

    rate = data.get("exchange_rate", {}).get("rate")
    if not isinstance(rate, (int, float)) or rate <= 0:
        errors.append("a positive USD/KSh exchange rate is required")
        rate = 0

    updates = data.get("existing_tour_updates", [])
    tours = data.get("new_tours", [])
    if len(updates) != 43:
        errors.append(f"expected 43 existing Tour updates, found {len(updates)}")
    if len(tours) != 21:
        errors.append(f"expected 21 international Tours, found {len(tours)}")

    for update in updates:
        if update.get("scope") != "Kenya Tours":
            errors.append(f"existing Tour has wrong scope: {update.get('slug')}")
        validate_price(update, rate, errors)

    identities: list[str] = []
    for tour in tours:
        identity = tour.get("product_id", "")
        identities.append(identity)
        if tour.get("taxonomies", {}).get("hks_tour_scope") != ["International Tours"]:
            errors.append(f"international Tour has wrong scope: {identity}")
        if not tour.get("taxonomies", {}).get("hks_destination"):
            errors.append(f"international Tour has no Destination: {identity}")
        media = tour.get("media", [])
        if not media or not media[0].get("url"):
            errors.append(f"international Tour has no source image: {identity}")
        if not tour.get("fields", {}).get("hks_itinerary"):
            errors.append(f"international Tour has no itinerary: {identity}")
        validate_price(tour, rate, errors)
        has_price = bool(tour.get("fields", {}).get("hks_from_price_ksh"))
        expected_status = "publish" if has_price else "draft"
        if tour.get("publication_status") != expected_status:
            errors.append(f"{identity} must be {expected_status} based on price availability")

    if len(identities) != len(set(identities)):
        errors.append("international product identities must be unique")
    if Counter(tour.get("batch") for tour in tours) != Counter({1: 3, 2: 7, 3: 11}):
        errors.append("international batch counts must be 3, 7, and 11")
    if Counter(tour.get("publication_status") for tour in tours) != Counter({"publish": 14, "draft": 7}):
        errors.append("expected 14 publishable Tours and 7 price-blocked drafts")

    if errors:
        print("Ashford expansion validation failed:", file=sys.stderr)
        for error in errors:
            print(f"- {error}", file=sys.stderr)
        return 1

    print("Ashford expansion validation passed (43 existing updates; 21 international Tours; 14 publishable, 7 retained as drafts).")
    return 0


def validate_price(record: dict, rate: float, errors: list[str]) -> None:
    conversion = record.get("conversion")
    if not conversion:
        return
    usd = conversion.get("source_price_usd")
    rounded = conversion.get("rounded_ksh")
    if not isinstance(usd, (int, float)) or not isinstance(rounded, int):
        errors.append(f"invalid conversion values: {record.get('slug') or record.get('product_id')}")
        return
    expected = math.ceil((usd * rate) / 500) * 500
    if rounded != expected or rounded % 500:
        errors.append(f"incorrect upward KSh 500 rounding: {record.get('slug') or record.get('product_id')}")
    stored = record.get("hks_from_price_ksh", record.get("fields", {}).get("hks_from_price_ksh"))
    if stored != rounded:
        errors.append(f"stored KSh price does not match conversion: {record.get('slug') or record.get('product_id')}")


if __name__ == "__main__":
    raise SystemExit(main())
