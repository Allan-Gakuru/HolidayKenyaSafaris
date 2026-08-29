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
    "lifecycle": "src/Lifecycle.php",
    "plugin": "src/Plugin.php",
    "content": "src/Content/Module.php",
    "post_type": "src/Content/PostTypes/Inquiry.php",
    "module": "src/Conversion/Module.php",
    "token": "src/Conversion/FormToken.php",
    "repository": "src/Conversion/InquiryRepository.php",
    "notification": "src/Conversion/InquiryNotification.php",
    "admin": "src/Conversion/InquiryAdmin.php",
    "fields": "src/Fields/FieldGroups.php",
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

    require(errors, "plugin bootstrap", content["bootstrap"], ["Version:           0.12.2", "define( 'HKS_CORE_VERSION', '0.12.2' )"])
    require(
        errors,
        "client-ready copy migration",
        content["lifecycle"],
        [
            "'0.9.0'",
            "make_public_copy_client_ready",
            "source duration and route outline",
            "is_repeater_row",
            "0.11.0",
            "seed_article_topic_terms",
            "seed_anchor_destination_terms",
        ],
    )
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
            "_hks_whatsapp_opened_at",
            "'website'",
            "InquiryNotification::send",
        ],
    )
    require(
        errors,
        "internal inquiry notification",
        content["notification"],
        [
            "hks_settings_inquiry_notification_recipients",
            "get_field( self::RECIPIENTS_FIELD, 'hks_settings' )",
            "sanitize_email",
            "array_unique",
            "wp_mail",
            "_hks_inquiry_notification_hash",
            "_hks_inquiry_notification_sent_at",
            "_hks_inquiry_notification_failed_at",
            "Review quote request",
        ],
    )
    require(
        errors,
        "private notification recipient settings",
        content["fields"],
        [
            "hks_settings_inquiry_notification_recipients",
            "Quote notification recipients",
            "Add notification email",
            "Leave all rows empty to disable notifications",
        ],
    )
    require(errors, "restricted admin", content["admin"], ["Quote request details", "administrator access", "Team email accepted", "No quote notification recipients are configured", "does not prove"])
    require(
        errors,
        "quote renderer",
        content["renderer"],
        [
            "254712965131",
            "info@holidaykenyasafaris.ke",
            "Review quote request",
            "Holiday Kenya Safaris is owned and operated by Ashford Tours and Travels. With over 20 years of international travelers experience, we made Holiday Kenya Safaris created to serve Kenyans.",
            "choose WhatsApp or email",
            "Open email to send",
            "data-hks-email-launch",
            "FormToken::issue",
            "group_context",
            "article_id",
            "article_format",
            "hks_article_primary_tour",
            "'article' === $page_type",
            "destination_selection",
            "tour_selection",
            "data-hks-inquiry-inline",
        ],
    )

    forbidden_consent = {
        "quote renderer": content["renderer"],
        "capture repository": content["repository"],
        "browser payload": content["script"],
    }
    for label, text in forbidden_consent.items():
        for snippet in ("contact_consent", "consent_version", "I agree that Holiday Kenya Safaris"):
            if snippet in text:
                errors.append(f"{label} still contains removed consent control: {snippet}")

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
        "email_launch",
    ]
    require(errors, "browser event contract", content["script"], [f"'{event}'" for event in events])
    require(
        errors,
        "browser privacy and handoff",
        content["script"],
        ["window.dataLayer.push(payload)", "sessionStorage", "encodeURIComponent(reviewedMessage)", "mailto:", "encodeURIComponent(emailSubject)", "keepalive: true", "sourceAttribution", "destination_id", "inquiry_route", "group_travel"],
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

    require(errors, "accessible modal styling", content["style"], ["::backdrop", ":focus-visible", "prefers-reduced-motion", "#25d366", "min-height: 48px", ".hks-inquiry__email-launch"])

    if errors:
        print("Conversion validation failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print("Conversion validation passed (private recovery, review, WhatsApp/email handoff, analytics, and privacy boundaries).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
