#!/usr/bin/env python3
"""Validate the Attic-inspired public frontend contract without WordPress."""

from __future__ import annotations

import json
import re
import sys
from pathlib import Path
from typing import Dict, List


ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "wp-content" / "themes" / "hks-wayfinder"
PLUGIN = ROOT / "wp-content" / "plugins" / "hks-core"

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
    "blocks/home-experience/block.json": "hks-wayfinder/home-experience",
    "blocks/catalogue-controls/block.json": "hks-wayfinder/catalogue-controls",
}


def require(errors: List[str], label: str, text: str, snippets: List[str]) -> None:
    for snippet in snippets:
        if snippet not in text:
            errors.append(f"{label} is missing: {snippet}")


def forbid(errors: List[str], label: str, text: str, snippets: List[str]) -> None:
    for snippet in snippets:
        if snippet in text:
            errors.append(f"{label} contains forbidden marker: {snippet}")


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

    sources: Dict[str, str] = {}
    source_paths = {
        "renderer": THEME / "inc" / "TourBlocks.php",
        "functions": THEME / "functions.php",
        "style": THEME / "style.css",
        "header": THEME / "patterns" / "header.php",
        "footer": THEME / "parts" / "footer.html",
        "navigation": THEME / "assets" / "js" / "navigation.js",
        "tour_ui": THEME / "assets" / "js" / "tour-ui.js",
        "quote": PLUGIN / "src" / "Conversion" / "QuoteBlock.php",
    }
    for label, path in source_paths.items():
        try:
            sources[label] = path.read_text(encoding="utf-8")
        except OSError as error:
            errors.append(f"missing {path.relative_to(ROOT)}: {error}")
            sources[label] = ""

    require(errors, "theme metadata", sources["style"], ["Version: 0.3.0", ".hks-tour-workspace", ".hks-tour-gallery", ".hks-mobile-menu", ":focus-visible", "prefers-reduced-motion"])
    forbid(errors, "theme stylesheet", sources["style"], ["linear-gradient(", "radial-gradient("])

    require(
        errors,
        "theme registration",
        sources["functions"],
        [
            "inc/TourBlocks.php",
            "assets/js/navigation.js",
            "assets/js/tour-ui.js",
            "hks_wayfinder_filter_tour_archive",
            "is_admin()",
            "hks_wayfinder_campaign_robots",
            "hks_noindex",
        ],
    )
    require(
        errors,
        "header",
        sources["header"],
        [
            "hks-utility",
            "hks-primary-nav",
            "data-hks-nav-menu",
            "data-hks-mobile-menu",
            "<dialog",
            "hks-wayfinder-horizontal-primary.svg",
            "Home",
            "Safaris",
            "Coast & Stays",
            "Destinations",
            "Group Travel",
            "Request quote on WhatsApp",
            "data-hks-quote-proxy",
        ],
    )
    forbid(errors, "header", sources["header"], ["wa.me/"])
    require(errors, "footer", sources["footer"], ["operated by Ashford Tours &amp; Travel", "href=\"/tours/\""])

    require(errors, "home template", files["home"], ["hks-wayfinder/home-experience"])
    require(errors, "catalogue template", files["catalogue"], ["hks-title-band", "hks-wayfinder/catalogue-controls", "hks-wayfinder/tour-card", "postType\":\"hks_tour", "inherit\":true"])
    require(errors, "Tour template", files["tour"], ["hks-wayfinder/tour-hero", "hks-wayfinder/tour-details", "data-hks-quote-proxy", "Request quote on WhatsApp"])
    forbid(errors, "Tour template", files["tour"], ["<!-- wp:hks/quote-cta"])
    require(errors, "Campaign template", files["campaign"], ["hks-wayfinder/tour-hero", "hks-wayfinder/tour-details", "hks/quote-cta", "campaign_hero"])
    require(errors, "Destination template", files["destination"], ["hks-wayfinder/destination-intro", "hks-wayfinder/tour-card", "inherit\":true", "hks-catalogue-prompt"])

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
        sources["renderer"],
        [
            "home-experience",
            "catalogue-controls",
            "render_canonical_hero",
            "render_campaign_hero",
            "hks-title-band",
            "render_gallery",
            "View gallery",
            "hks-tour-gallery--",
            "hks-tour-workspace",
            "data-hks-tour-tabs",
            "data-hks-tour-section",
            "Rates & Important Information",
            "data-hks-itinerary-day",
            "hks-tour-quote__panel",
            "tour_sidebar",
            "Request quote on WhatsApp",
            "render_related_tours",
            "hks-mobile-quote-bar",
            "hks-tour-card__destination",
            "hks-tour-card__price",
            "View trip",
            "private const SENTINEL",
            "price_summary",
            "hks_price_valid_until",
            "request_rate_fallback",
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
            "operator_reviewed",
            "gmdate( 'Y-m-d' )",
            "Holiday Kenya Safaris is operated by Ashford Tours & Travel.",
            "data-hks-primary-quote",
        ],
    )
    if sources["renderer"].count("do_blocks( '<!-- wp:hks/quote-cta") != 1:
        errors.append("canonical renderer must create exactly one shared quote block instance")

    require(errors, "navigation script", sources["navigation"], ["showModal", "aria-expanded", "Escape", "data-hks-quote-proxy", "data-hks-inquiry-open"])
    require(errors, "Tour UI script", sources["tour_ui"], ["role', 'tablist", "ArrowRight", "matchMedia('(min-width: 769px)", "tour_gallery_open", "tour_section_open", "itinerary_toggle", "related_tour_select"])
    require(errors, "quote block", sources["quote"], ["$attributes['label']", "Request quote on WhatsApp", "InquiryRepository::REST_NAMESPACE", "data-hks-inquiry-form", "data-hks-whatsapp-launch"])

    if re.search(r"border-left:\s*[2-9]", sources["style"]):
        errors.append("theme stylesheet contains a decorative side stripe wider than 1px")

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

    try:
        quote_block = json.loads((PLUGIN / "blocks" / "quote-cta" / "block.json").read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as error:
        errors.append(f"invalid HKS quote block metadata: {error}")
    else:
        if quote_block.get("attributes", {}).get("label", {}).get("type") != "string":
            errors.append("HKS quote block must expose the optional presentation-only label attribute")

    if errors:
        print("Public-template validation failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print("Public-template validation passed (Attic-inspired catalogue/Tour UI, governed content, shared quote conversion, responsive accessibility).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
