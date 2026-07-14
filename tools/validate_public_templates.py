#!/usr/bin/env python3
"""Validate the fail-closed public MVP template contract without WordPress."""

from __future__ import annotations

import json
import sys
from pathlib import Path
from typing import Dict, List


ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "wp-content" / "themes" / "hks-wayfinder"

TEMPLATES = {
    "home": "templates/front-page.html",
    "catalogue": "templates/archive-hks_tour.html",
    "tour": "templates/single-hks_tour.html",
    "campaign": "templates/single-hks_campaign.html",
    "destination": "templates/taxonomy-hks_destination.html",
}

BLOCKS = {
    "blocks/tour-hero/block.json": "hks-wayfinder/tour-hero",
    "blocks/tour-details/block.json": "hks-wayfinder/tour-details",
    "blocks/tour-card/block.json": "hks-wayfinder/tour-card",
    "blocks/destination-intro/block.json": "hks-wayfinder/destination-intro",
}


def require(errors: List[str], label: str, text: str, snippets: List[str]) -> None:
    for snippet in snippets:
        if snippet not in text:
            errors.append(f"{label} is missing: {snippet}")


def main() -> int:
    errors: List[str] = []
    files: Dict[str, str] = {}

    for label, relative in TEMPLATES.items():
        path = THEME / relative
        try:
            files[label] = path.read_text(encoding="utf-8")
        except OSError as error:
            errors.append(f"missing {relative}: {error}")
            files[label] = ""

    try:
        renderer = (THEME / "inc" / "TourBlocks.php").read_text(encoding="utf-8")
        functions = (THEME / "functions.php").read_text(encoding="utf-8")
        style = (THEME / "style.css").read_text(encoding="utf-8")
        header = (THEME / "patterns" / "header.php").read_text(encoding="utf-8")
        footer = (THEME / "parts" / "footer.html").read_text(encoding="utf-8")
    except OSError as error:
        print(f"Public-template validation failed: {error}")
        return 1

    require(errors, "theme metadata", style, ["Version: 0.2.0", ".hks-tour-hero", ".hks-tour-card", ":focus-visible", "prefers-reduced-motion"])
    require(errors, "theme registration", functions, ["inc/TourBlocks.php", "TourBlocks::class", "hks_wayfinder_campaign_robots", "hks_noindex"])
    require(errors, "header", header, ["hks-skip-link", "#main-content", "navigation-link", "Tours"])
    require(errors, "footer", footer, ["operated by Ashford Tours &amp; Travel", "href=\"/tours/\""])

    require(errors, "home template", files["home"], ["hks-wayfinder/tour-card", "postType\":\"hks_tour", "operated by Ashford Tours &amp; Travel", "Save the request"])
    require(errors, "catalogue template", files["catalogue"], ["hks-wayfinder/tour-card", "postType\":\"hks_tour", "inherit\":true"])
    require(errors, "Tour template", files["tour"], ["hks-wayfinder/tour-hero", "hks-wayfinder/tour-details", "hks/quote-cta", "tour_hero", "tour_footer"])
    require(errors, "Campaign template", files["campaign"], ["hks-wayfinder/tour-hero", "hks-wayfinder/tour-details", "hks/quote-cta", "campaign_hero"])
    require(errors, "Destination template", files["destination"], ["hks-wayfinder/destination-intro", "hks-wayfinder/tour-card", "inherit\":true"])

    for label, template in files.items():
        if 'id="main-content"' not in template:
            errors.append(f"{label} template is not a valid skip-link target")
        if "CLIENT CONFIRMATION REQUIRED" in template:
            errors.append(f"{label} template exposes the internal confirmation sentinel")
        if "<img" in template.lower():
            errors.append(f"{label} template hard-codes an image outside the rights gate")

    require(
        errors,
        "public renderer",
        renderer,
        [
            "private const SENTINEL",
            "price_summary",
            "hks_price_valid_until",
            "request_rate_fallback",
            "hks-hero-price-context",
            "hks-tour-card__price",
            "$price['status']",
            "$price['basis']",
            "approved_policies",
            "approved_faqs",
            "media_allowed",
            "hks_permission_status",
            "hks_usage_scopes",
            "'website'",
            "hks_rights_checked_date",
            "_wp_attachment_image_alt",
            "hks_credit_required",
            "hks_source_status",
            "array( 'reviewed', 'client_confirmed' )",
            "hks_source_checked_date",
            "hks_source_reference",
            "operator_reviewed",
            "gmdate( 'Y-m-d' )",
        ],
    )

    for relative, expected_name in BLOCKS.items():
        try:
            block = json.loads((THEME / relative).read_text(encoding="utf-8"))
        except (OSError, json.JSONDecodeError) as error:
            errors.append(f"invalid {relative}: {error}")
            continue
        if block.get("name") != expected_name or block.get("apiVersion") != 3:
            errors.append(f"{relative} has the wrong name or API version")
        if block.get("supports", {}).get("html") is not False:
            errors.append(f"{relative} must disable unrestricted HTML")

    if errors:
        print("Public-template validation failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print("Public-template validation passed (MVP templates, source gates, media rights, pricing, SEO, and accessibility).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
