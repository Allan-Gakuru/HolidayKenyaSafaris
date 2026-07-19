#!/usr/bin/env python3
"""Validate the MVP inquiry-to-WhatsApp contract without loading WordPress."""

from __future__ import annotations

import json
import sys
from pathlib import Path
from typing import List


ROOT = Path(__file__).resolve().parents[1]
PLUGIN = ROOT / "wp-content" / "plugins" / "hks-core"

FILES = {
    "bootstrap": "hks-core.php",
    "plugin": "src/Plugin.php",
    "content": "src/Content/Module.php",
    "post_type": "src/Content/PostTypes/Inquiry.php",
    "module": "src/Conversion/Module.php",
    "token": "src/Conversion/FormToken.php",
    "repository": "src/Conversion/InquiryRepository.php",
    "admin": "src/Conversion/InquiryAdmin.php",
    "renderer": "src/Conversion/QuoteBlock.php",
    "block": "blocks/quote-cta/block.json",
    "script": "assets/js/inquiry.js",
    "style": "assets/css/inquiry.css",
}


def require(errors: List[str], label: str, text: str, snippets: List[str]) -> None:
    for snippet in snippets:
        if snippet not in text:
            errors.append(f"{label} is missing: {snippet}")


def main() -> int:
    errors: List[str] = []
    content = {}

    for label, relative in FILES.items():
        path = PLUGIN / relative
        try:
            content[label] = path.read_text(encoding="utf-8")
        except OSError as error:
            errors.append(f"missing {relative}: {error}")
            content[label] = ""

    require(errors, "plugin bootstrap", content["bootstrap"], ["Version:           0.8.0", "define( 'HKS_CORE_VERSION', '0.8.0' )"])
    require(errors, "plugin coordinator", content["plugin"], ["Conversion\\Module as ConversionModule", "ConversionModule::class"])
    require(errors, "content model", content["content"], ["PostTypes\\Inquiry", "Inquiry::register();"])
    require(
        errors,
        "private inquiry post type",
        content["post_type"],
        [
            "public const POST_TYPE = 'hks_inquiry'",
            "'public'              => false",
            "'publicly_queryable'  => false",
            "'show_in_rest'        => false",
            "'can_export'          => false",
            "'create_posts'           => 'do_not_allow'",
            "'edit_posts'             => 'manage_options'",
        ],
    )
    require(errors, "conversion module", content["module"], ["register_block_type", "rest_api_init", "assets/css/inquiry.css", "assets/js/inquiry.js"])
    require(errors, "signed form token", content["token"], ["hash_hmac( 'sha256'", "hash_equals", "wp_salt( 'nonce' )", "DAY_IN_SECONDS"])
    require(
        errors,
        "capture repository",
        content["repository"],
        [
            "'/inquiries'",
            "'/inquiries/(?P<request_key>",
            "FormToken::verify",
            "within_rate_limit",
            "_hks_inquiry_request_key",
            "_hks_inquiry_name",
            "_hks_inquiry_phone",
            "_hks_inquiry_preferred_date",
            "_hks_inquiry_travelers",
            "_hks_inquiry_destination",
            "_hks_inquiry_route",
            "_hks_inquiry_consent_version",
            "_hks_whatsapp_opened_at",
            "'contact_consent'",
            "'website'",
        ],
    )
    require(errors, "restricted admin", content["admin"], ["Quote request details", "administrator access", "It does not prove"])
    require(
        errors,
        "quote renderer",
        content["renderer"],
        [
            "254712965131",
            "Save & review WhatsApp message",
            "we save these details privately in WordPress",
            "you still choose whether to send it",
            "InquiryRepository::CONSENT_VERSION",
            "FormToken::issue",
            "group_context",
            "destination_selection",
            "tour_selection",
            "data-hks-inquiry-inline",
        ],
    )

    try:
        block = json.loads(content["block"])
        if block.get("name") != "hks/quote-cta" or block.get("apiVersion") != 3:
            errors.append("quote block metadata has the wrong name or API version")
    except json.JSONDecodeError as error:
        errors.append(f"quote block metadata is invalid JSON: {error}")

    events = [
        "view_tour",
        "view_campaign",
        "quote_cta_click",
        "quote_form_start",
        "quote_form_error",
        "quote_inquiry_saved",
        "quote_form_complete",
        "whatsapp_launch",
    ]
    require(errors, "browser event contract", content["script"], [f"'{event}'" for event in events])
    require(
        errors,
        "browser privacy and handoff",
        content["script"],
        ["window.dataLayer.push(payload)", "sessionStorage", "encodeURIComponent(reviewedMessage)", "keepalive: true", "sourceAttribution", "destination_id", "inquiry_route", "group_travel"],
    )

    for line_number, line in enumerate(content["script"].splitlines(), 1):
        if "track(root" in line and any(
            sensitive in line
            for sensitive in (
                "form.elements.phone.value",
                "form.elements.preferred_date.value",
                "form.elements.budget_range.value",
                "form.elements.name.value",
                "data.get('phone')",
                "data.get('preferred_date')",
                "data.get('budget_range')",
                "data.get('name')",
            )
        ):
            errors.append(f"analytics call on JS line {line_number} appears to contain an inquiry answer")

    require(errors, "accessible modal styling", content["style"], ["::backdrop", ":focus-visible", "prefers-reduced-motion", "#25d366", "min-height: 48px"])

    if errors:
        print("Conversion validation failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print("Conversion validation passed (private recovery, review, WhatsApp, analytics, and privacy boundaries).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
