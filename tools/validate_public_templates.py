#!/usr/bin/env python3
"""Validate the catalogue-led public frontend contract without WordPress."""

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
    "tour type": "templates/taxonomy-hks_tour_type.html",
    "occasion": "templates/taxonomy-hks_occasion.html",
    "travel style": "templates/taxonomy-hks_travel_style.html",
    "tour scope": "templates/taxonomy-hks_tour_scope.html",
    "page": "templates/page.html",
    "group travel": "templates/page-group-travel.html",
    "travel guides": "templates/home.html",
    "article": "templates/single-post.html",
    "article topic": "templates/taxonomy-hks_article_topic.html",
}

BLOCKS = {
    "blocks/tour-hero/block.json": "hks-wayfinder/tour-hero",
    "blocks/tour-details/block.json": "hks-wayfinder/tour-details",
    "blocks/tour-card/block.json": "hks-wayfinder/tour-card",
    "blocks/destination-intro/block.json": "hks-wayfinder/destination-intro",
    "blocks/taxonomy-intro/block.json": "hks-wayfinder/taxonomy-intro",
    "blocks/home-experience/block.json": "hks-wayfinder/home-experience",
    "blocks/catalogue-controls/block.json": "hks-wayfinder/catalogue-controls",
    "blocks/footer-navigation/block.json": "hks-wayfinder/footer-navigation",
    "blocks/page-title/block.json": "hks-wayfinder/page-title",
    "blocks/group-travel-page/block.json": "hks-wayfinder/group-travel-page",
    "blocks/article-archive-intro/block.json": "hks-wayfinder/article-archive-intro",
    "blocks/article-card/block.json": "hks-wayfinder/article-card",
    "blocks/article-page/block.json": "hks-wayfinder/article-page",
    "blocks/destination-guides/block.json": "hks-wayfinder/destination-guides",
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
        "article_renderer": THEME / "inc" / "ArticleBlocks.php",
        "nav_menus": THEME / "inc" / "NavMenus.php",
        "functions": THEME / "functions.php",
        "style": THEME / "style.css",
        "header": THEME / "patterns" / "header.php",
        "footer": THEME / "parts" / "footer.html",
        "navigation": THEME / "assets" / "js" / "navigation.js",
        "home_gallery": THEME / "assets" / "js" / "home-gallery.js",
        "catalogue_filters": THEME / "assets" / "js" / "catalogue-filters.js",
        "tour_ui": THEME / "assets" / "js" / "tour-ui.js",
        "article_ui": THEME / "assets" / "js" / "article-ui.js",
        "theme_htaccess": THEME / ".htaccess",
        "quote": PLUGIN / "src" / "Conversion" / "QuoteBlock.php",
        "inquiry_repository": PLUGIN / "src" / "Conversion" / "InquiryRepository.php",
        "inquiry_admin": PLUGIN / "src" / "Conversion" / "InquiryAdmin.php",
        "inquiry_script": PLUGIN / "assets" / "js" / "inquiry.js",
        "inquiry_style": PLUGIN / "assets" / "css" / "inquiry.css",
        "plugin_htaccess": PLUGIN / ".htaccess",
    }
    for label, path in source_paths.items():
        try:
            sources[label] = path.read_text(encoding="utf-8")
        except OSError as error:
            errors.append(f"missing {path.relative_to(ROOT)}: {error}")
            sources[label] = ""

    require(errors, "theme metadata", sources["style"], ["Version: 0.7.1", "body.home .hks-site-header", "body.home .hks-site-header.is-scrolled", "position: fixed", ".hks-home-hero--featured", ".hks-home-gallery__viewport", ".hks-home-gallery__progress", "--hks-card-width", ".hks-home-gallery__transition-image", "clip-path", "aspect-ratio: 3 / 4", ".hks-tour-workspace", ".hks-tour-media", ".hks-tour-gallery__thumbnails", ".hks-tour-gallery__thumbnail--desktop-overflow", ".hks-tour-gallery__thumbnail-more", ".hks-tour-gallery__stage", ".hks-tour-gallery__nav--prev", ".hks-tour-gallery__nav--next", ".hks-mobile-menu", ".hks-mobile-menu__parent-link", ".hks-footer-menu", ".hks-catalogue-filter-sidebar", ".hks-catalogue-filter-toggle", ".hks-catalogue-filter-drawer", "grid-template-columns: minmax(14rem, 16rem) minmax(0, 1fr)", ".hks-editorial-page", ".hks-group-travel-planner", ".hks-group-travel-visuals", ".hks-article-mobile-quote.is-visible", ".hks-article-discovery__form", ":focus-visible", "prefers-reduced-motion", "prefers-reduced-transparency"])
    forbid(errors, "theme stylesheet", sources["style"], ["linear-gradient(", "radial-gradient(", ".hks-site-header--home-overlay"])

    require(
        errors,
        "theme registration",
        sources["functions"],
        [
            "inc/NavMenus.php",
            "inc/TourBlocks.php",
            "inc/ArticleBlocks.php",
            "NavMenus::register",
            "register_admin_page",
            "assets/js/navigation.js",
            "assets/js/home-gallery.js",
            "assets/js/catalogue-filters.js",
            "assets/js/tour-ui.js",
            "assets/js/article-ui.js",
            "hks_wayfinder_filter_tour_archive",
            "is_admin()",
            "hks_wayfinder_campaign_robots",
            "hks_wayfinder_taxonomy_archive_title",
            "hks_wayfinder_taxonomy_archive_description",
            "document_title_parts",
            "Tours in %s",
            "%s tours",
            "Tours for %s",
            "hks_noindex",
            "hks_wayfinder_enable_output_compression",
            "hks_wayfinder_preload_primary_image",
            "hks_wayfinder_catalogue_cache_version",
            "hks_wayfinder_move_meta_signal_to_footer",
            "hks_wayfinder_move_meta_pixel_to_footer",
            "FacebookWordpressPixelInjection",
            "remove_action( 'wp_head'",
            "hks_wayfinder_capture_deferred_meta_pixel",
            "hks_wayfinder_print_deferred_meta_pixel",
            "wp_add_inline_script( 'facebook-signal'",
            "connect.facebook.net/en_US/fbevents.js",
            "if(f.fbq)return",
            "if(!f._fbq)f._fbq=n",
            "n.loaded=!0",
            "n.version='2.0'",
            "n.queue=[]",
            "FacebookSignal.initPixel(",
            "fbq('track', 'PageView'",
            "3 !== count( $matches[1] )",
            "preg_replace( '#<script\\b[^>]*>.*?</script>#is'",
            "Meta Pixel Code|End Meta Pixel Code",
            "$loader_tail_count",
            "__hksDeferredMetaPixelOwnsLoader",
            "window.fbq&&window.fbq.callMethod",
            "data-hks-deferred-meta-pixel",
            "window.requestIdleCallback(run, { timeout: 1000 })",
            "window.setTimeout(run, 5000)",
            "'wp_footer'",
            "add_action( 'wp_head', 'hks_wayfinder_move_meta_pixel_to_footer', 2 )",
            "add_action( 'wp_footer', 'hks_wayfinder_print_deferred_meta_pixel', 21 )",
            "wp_scripts()->add_data( 'facebook-signal', 'group', 1 )",
            "$GLOBALS['hks_wayfinder_deferred_meta_pixel'][] = $state",
            "$removed_callbacks",
            "get_transient",
            "set_transient",
        ],
    )
    if not re.search(r"hks_wayfinder_capture_deferred_meta_pixel\( \$callback \);\s*\},\s*9", sources["functions"]):
        errors.append("Meta Pixel callback must be captured before the official footer-priority-10 CAPI flush")
    meta_capture = sources["functions"].find("function hks_wayfinder_capture_deferred_meta_pixel")
    meta_print = sources["functions"].find("function hks_wayfinder_print_deferred_meta_pixel")
    meta_relocation = sources["functions"].find("function hks_wayfinder_move_meta_pixel_to_footer")
    if min(meta_capture, meta_print, meta_relocation) < 0 or not (meta_capture < meta_print < meta_relocation):
        errors.append("Meta Pixel capture, post-helper output and atomic relocation must remain in dependency order")
    meta_loader = re.search(r"\$state\['javascript'\]\s*=\s*<<<'JS'\s*(.*?)\s*JS;", sources["functions"], re.DOTALL)
    if not meta_loader:
        errors.append("Meta Pixel optimization must isolate its deferred external-runtime loader")
    else:
        require(errors, "deferred Meta runtime loader", meta_loader.group(1), ["createElement('script')", "insertBefore(s,f)", "connect.facebook.net/en_US/fbevents.js"])
        forbid(errors, "deferred Meta runtime loader", meta_loader.group(1), ["FacebookSignal.init", "initPixel", "PageView"])
    forbid(errors, "Meta Pixel hook timing", sources["functions"], ["add_action( 'wp', 'hks_wayfinder_move_meta_pixel_to_footer'", "hks_wayfinder_meta_pixel_deferred"])
    require(errors, "theme asset delivery", sources["theme_htaccess"], ["AddOutputFilterByType DEFLATE", "ExpiresActive On", "Cache-Control", "immutable"])
    require(errors, "plugin asset delivery", sources["plugin_htaccess"], ["AddOutputFilterByType DEFLATE", "ExpiresActive On", "Cache-Control", "immutable"])
    require(
        errors,
        "header",
        sources["header"],
        [
            "hks-utility",
            "hks-site-header",
            "hks-primary-nav",
            "data-hks-nav-menu",
            "data-hks-mobile-menu",
            "<dialog",
            "holiday-kenya-safaris-logo.svg",
            "Home",
            "Safaris",
            "Coast & Stays",
            "Destinations",
            "Travel Guides",
            "Group Travel",
            "group-travel",
            "Request a quote",
            "data-hks-quote-proxy",
            "hks-mobile-menu__social",
            "NavMenus::has_primary_menu",
            "NavMenus::render_desktop",
            "NavMenus::render_mobile",
        ],
    )
    forbid(errors, "header", sources["header"], ["is_front_page()", "hks-site-header--home-overlay"])
    menu_check = sources["header"].find("$has_primary_menu")
    fallback_guard = sources["header"].find("if ( ! $has_primary_menu )")
    fallback_lookup = sources["header"].find("hks_wayfinder_populated_terms")
    if min(menu_check, fallback_guard, fallback_lookup) < 0 or not (menu_check < fallback_guard < fallback_lookup):
        errors.append("header fallback catalogue queries must run only when no managed primary menu is assigned")
    require(errors, "header image priorities", sources["header"], ['loading="eager" fetchpriority="low" decoding="async"', 'loading="lazy" fetchpriority="low" decoding="async"'])
    for label in ("renderer", "article_renderer"):
        if re.search(r"wp_kses_post\(\s*wp_get_attachment_image", sources[label]):
            errors.append(f"{label} must preserve responsive image and priority attributes generated by WordPress")
    header_actions = re.search(r'<div class="hks-header-actions">(.*?)</div>', sources["header"], re.DOTALL)
    if not header_actions or "hks-button--quote" in header_actions.group(1):
        errors.append("desktop primary header must not contain the large quote button")
    require(errors, "utility WhatsApp link", sources["header"], ["https://wa.me/", "254712965131", "$whatsapp_message", "get_permalink()", "target=\"_blank\""])
    utility_whatsapp = re.search(r'<a class="hks-utility__contact hks-utility__whatsapp"(.*?)</a>', sources["header"], re.DOTALL)
    if not utility_whatsapp or "data-hks-quote-proxy" in utility_whatsapp.group(1):
        errors.append("utility WhatsApp contact must be a direct link, not a quote-form proxy")
    require(errors, "footer", sources["footer"], ["operated by Ashford Tours &amp; Travel", "hks-wayfinder/footer-navigation"])
    require(errors, "managed navigation", sources["nav_menus"], ["register_nav_menus", "PRIMARY_LOCATION", "FOOTER_LOCATION", "wp_nav_menu", "Desktop_Menu_Walker", "Mobile_Menu_Walker", "hks-nav-menu__panel", "hks-mobile-menu__parent-link", "nav-menus.php", "href=\"%4$s\"", "home_url( '/tours/' )", "Destinations", "Travel Guides", "home_url( '/travel-guides/' )"])

    require(errors, "home template", files["home"], ["hks-wayfinder/home-experience"])
    require(errors, "catalogue template", files["catalogue"], ["hks-title-band", "hks-wayfinder/catalogue-controls", "hks-wayfinder/tour-card", "postType\":\"hks_tour", "inherit\":true"])
    forbid(errors, "catalogue template", files["catalogue"], ["KSh starting price", "Request current KSh rate"])
    require(errors, "Tour template", files["tour"], ["hks-wayfinder/tour-hero", "hks-wayfinder/tour-details", "data-hks-quote-proxy", "Request a quote", "choose WhatsApp or email"])
    forbid(errors, "Tour template", files["tour"], ["<!-- wp:hks/quote-cta"])
    require(errors, "Campaign template", files["campaign"], ["hks-wayfinder/tour-hero", "hks-wayfinder/tour-details", "data-hks-quote-proxy", "Request a quote", "choose WhatsApp or email"])
    forbid(errors, "Campaign template", files["campaign"], ["<!-- wp:hks/quote-cta", "campaign_hero", "hks-hero-cta"])
    require(errors, "Destination template", files["destination"], ["hks-wayfinder/destination-intro", "hks-wayfinder/tour-card", "inherit\":true", "hks-wayfinder/destination-guides", "hks-catalogue-prompt"])
    for label in ("tour type", "occasion", "travel style"):
        require(errors, f"{label.title()} template", files[label], ["hks-wayfinder/taxonomy-intro", "hks-wayfinder/tour-card", "inherit\":true", "hks-catalogue-prompt"])
    require(errors, "standard Page template", files["page"], ["hks-standard-page", "hks-wayfinder/page-title", "hks-editorial-page", "wp:post-content"])
    require(errors, "Group Travel template", files["group travel"], ["hks-standard-page", "hks-group-travel-page", "hks-wayfinder/page-title", "hks-wayfinder/group-travel-page", "hks-group-travel-page__support", "wp:post-content"])
    require(errors, "Travel Guides template", files["travel guides"], ["hks-wayfinder/article-archive-intro", "hks-wayfinder/article-card", "postType\":\"post", "inherit\":true"])
    forbid(errors, "Travel Guides template", files["travel guides"], ["wp:post-author", "wp:post-author-name"])
    require(errors, "article template", files["article"], ["hks-wayfinder/article-page"])
    forbid(errors, "article template", files["article"], ["wp:post-author", "wp:post-author-name"])
    require(errors, "article topic template", files["article topic"], ["hks-wayfinder/article-archive-intro", "hks-wayfinder/article-card", "postType\":\"post", "inherit\":true"])

    archive_labels = (
        "catalogue",
        "destination",
        "tour type",
        "occasion",
        "travel style",
        "tour scope",
        "travel guides",
        "article topic",
    )
    for label in archive_labels:
        require(errors, f"{label.title()} archive contract", files[label], ["hks-archive-page"])
    for label in ("home", "tour"):
        forbid(errors, f"{label.title()} archive isolation", files[label], ["hks-archive-page"])

    for label in ("page", "group travel"):
        require(errors, f"{label.title()} standard Page contract", files[label], ["hks-standard-page"])
    for label in ("home", "tour", "campaign"):
        forbid(errors, f"{label.title()} standard Page isolation", files[label], ["hks-standard-page"])

    require(
        errors,
        "shared archive and Page presentation",
        sources["style"],
        [
            ".wp-site-blocks > .hks-archive-page",
            ".hks-archive-page > *",
            "--hks-standard-page-title-size: clamp(1.6875rem, 3.75vw, 2.625rem);",
            "--hks-standard-subtitle-size: clamp(0.84375rem, calc(0.75rem + 0.3vw), 0.984375rem);",
            "--hks-standard-section-title-size: var(--wp--preset--font-size--card-title);",
            "--hks-standard-paragraph-size: 1rem;",
            "--hks-standard-card-copy-size: 0.88rem;",
            "--hks-standard-title-band-padding: 0.5rem 0.625rem;",
            "--hks-standard-content-padding: 0.75rem clamp(2.5rem, 6vw, 5rem);",
            ":is(.hks-archive-page, .hks-standard-page, body.page:not(.home) main) .hks-title-band {",
            "padding-block: var(--hks-standard-title-band-padding);",
            ":is(.hks-archive-page, .hks-standard-page, body.page:not(.home) main) .hks-title-band h1",
            "font-size: var(--hks-standard-page-title-size);",
            ":is(.hks-archive-page, .hks-standard-page, body.page:not(.home) main) .hks-title-band p:not(.hks-taxonomy-intro__label)",
            "font-size: var(--hks-standard-subtitle-size);",
            ":is(.hks-standard-page, body.page:not(.home) main) .hks-editorial-page :is(p:not(.hks-page-lead), li)",
            ":is(.hks-standard-page, body.page:not(.home) main) .hks-editorial-page .wp-block-button__link",
            ".hks-article-discovery__form select",
            "min-height: 44px;",
            ".hks-article-discovery__form button",
            "min-height: 46px;",
            ".hks-article-card__body :is(h2, h3)",
            "font-size: var(--wp--preset--font-size--card-title);",
        ],
    )
    forbid(
        errors,
        "legacy Travel Guides archive presentation",
        sources["style"],
        [
            ".hks-article-archive-intro__layout",
            ".hks-article-results__heading",
        ],
    )

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
            "footer-navigation",
            "hks-catalogue-filter-sidebar",
            "hks-catalogue-filter-toggle",
            "hks-catalogue-filter-drawer",
            "data-hks-filter-open",
            "data-hks-filter-close",
            "data-hks-filter-dialog",
            "render_canonical_hero",
            "render_campaign_hero",
            "hks-title-band",
            "render_gallery",
            "View gallery",
            "hks-tour-gallery--",
            "hks-tour-media",
            "data-hks-gallery-thumb",
            "data-hks-gallery-more-open",
            "data-hks-gallery-stage",
            "data-hks-gallery-view",
            "data-hks-gallery-stage-prev",
            "data-hks-gallery-stage-next",
            "data-hks-gallery-interval=\"5000\"",
            "Choose a gallery image",
            "hks-tour-workspace",
            "data-hks-tour-tabs",
            "data-hks-tour-section",
            "Important Information",
            "data-hks-itinerary-day",
            "hks-tour-quote__panel",
            "tour_sidebar",
            "Request a quote",
            "campaign_images",
            "campaign_sidebar",
            "hks_hero_headline",
            "hks_supporting_copy",
            "render_related_tours",
            "hks-mobile-quote-bar",
            "hks-tour-card__destination",
            "View trip",
            "private const SENTINEL",
            "campaign_price_summary",
            "tour_price_summary",
            "hks_from_price_ksh",
            "hks_campaign_from_price_ksh",
            "From KSh %s per person",
            "approved_policies",
            "approved_faqs",
            "media_allowed",
            "_wp_attachment_image_alt",
            "wp_get_attachment_caption",
            "Request a tailored quote",
            "hks-tour-quote__reassurances",
            "Inclusions and exclusions clarified",
            "No booking commitment required",
            "Fast Responses to all queries",
            "Your quote confirms the final package for your dates and group.",
            "Holiday Kenya Safaris is operated by Ashford Tours & Travel.",
            "data-hks-primary-quote",
            "data-hks-home-gallery",
            "data-hks-gallery-interval=\"5000\"",
            "data-hks-home-gallery-slide",
            "home_featured_tours",
            "hks_featured",
            "data-hks-home-gallery-active-image",
            "data-hks-home-gallery-title",
            "data-hks-home-gallery-eyebrow",
            "data-hks-home-gallery-link",
            "data-hks-home-gallery-pause",
            "data-hks-home-gallery-progress",
            "data-hks-home-gallery-details",
            "data-hks-home-gallery-price",
            "data-hks-home-gallery-route",
            "data-hks-home-gallery-included",
            "data-hks-tour-price",
            "data-hks-tour-route",
            "data-hks-tour-included",
            "hero_inclusions_summary",
            "hero_media_allowed",
            "1200 <= (int) $image[1]",
            "675 <= (int) $image[2]",
            "data-hks-tour-title",
            "data-hks-tour-eyebrow",
            "data-hks-tour-url",
            "hks-home-gallery__caption",
            "Click here to book tour",
            "hks-home-gallery__cta--destinations",
            "Click here to browse destinations",
            "href=\"<?php echo esc_url( $tours_url ); ?>\"",
            "render_group_travel_page",
            "hks-group-travel-planner",
            "group_travel_page",
            "current_gallery_image_id",
        ],
    )
    gallery_start = sources["renderer"].find("private static function render_gallery")
    gallery_end = sources["renderer"].find("private static function render_itinerary", gallery_start)
    gallery = sources["renderer"][gallery_start:gallery_end]
    require(
        errors,
        "Tour gallery critical path",
        gallery,
        [
            "'loading' => 'lazy', 'fetchpriority' => 'low', 'decoding' => 'async', 'sizes' => '112px'",
            "'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async'",
            "'loading' => 'lazy', 'fetchpriority' => 'low', 'decoding' => 'async'",
        ],
    )
    forbid(errors, "Tour gallery critical path", gallery, ["$index < 5 ? 'eager' : 'lazy'"])
    require(errors, "Campaign shared optimized renderer", sources["renderer"], ["render_canonical_details( $context['tour_id'], $context['campaign_id'] )"])
    forbid(
        errors,
        "public renderer",
        sources["renderer"],
        [
            "hks_price_status",
            "hks_tour_from_price_invalid",
            "Request current KSh rate",
            "request_rate_fallback",
            "hks-rate-information__lead",
            "hks_price_valid_until",
            "hks_price_season_assumption",
            "hks_source_status",
            "hks_permission_status",
            "hks_usage_scopes",
            "hks_rights_checked_date",
            "hks_credit_required",
            "hks_confirmation_status",
            "hero_destination_specs",
            "data-hks-gallery-interval=\"3000\"",
            "Explore destination",
            "Find a Kenya trip that fits you.",
        ],
    )
    require(
        errors,
        "Travel Guides renderer",
        sources["article_renderer"],
        [
            "hks_article_format",
            "hks_article_primary_tour",
            "hks_article_related_posts",
            "get_post_field( 'post_excerpt'",
            "data-hks-primary-tour-id",
            "data-hks-article-early-quote",
            "data-hks-cta-location=\"article_hero\"",
            "data-hks-cta-location=\"article_final\"",
            "data-hks-cta-location=\"article_mobile_sticky\"",
            "Request a quote",
            "View this trip",
            "hks-article-final-quote",
            "hks-article-conversion-panel",
            "hks-article-mobile-quote",
            "hks-article-discovery__form",
            "hks_guide_destination",
            "hks_guide_topic",
            "terms_for_posts",
            "render_related_posts",
            "post_type' => 'post'",
        ],
    )
    forbid(errors, "Travel Guides renderer", sources["article_renderer"], ["destination_url", "wp:post-author", "related tours", "Related tours"])
    require(errors, "Travel Guides query filtering", sources["functions"], ["$query->is_home()", "hks_guide_destination", "hks_guide_topic", "'hks_destination'", "'hks_article_topic'", "ignore_sticky_posts"])
    require(errors, "article UI script", sources["article_ui"], ["view_article", "article_primary_tour_click", "article_id", "article_format", "primary_tour_id", "cta_location", "IntersectionObserver", "data-hks-article-quote-stop", ".hks-site-footer", "is-visible", "aria-hidden"])
    forbid(errors, "article UI analytics", sources["article_ui"], ["destination_url", "window.location", "link.href"])
    public_copy = "\n".join(
        [
            sources["renderer"],
            sources["article_renderer"],
            sources["quote"],
            sources["footer"],
            files["catalogue"],
            files["destination"],
            files["tour type"],
            files["occasion"],
            files["travel style"],
        ]
    )
    forbid(
        errors,
        "traveller-facing copy",
        public_copy,
        [
            "published Tour",
            "published tours",
            "stored in WordPress",
            "package context",
            "sales conversation",
            "source itinerary",
            "assigned to this",
        ],
    )
    if sources["renderer"].count("wp:hks/quote-cta") != 2:
        errors.append("public renderer must create one canonical and one Group Travel shared quote block instance")

    require(errors, "navigation script", sources["navigation"], ["showModal", "aria-expanded", "Escape", "data-hks-quote-proxy", "data-hks-inquiry-open", "is-scrolled", "window.scrollY", "requestAnimationFrame", "{ passive: true }"])
    require(errors, "catalogue filter script", sources["catalogue_filters"], ["showModal", "aria-expanded", "data-hks-filter-dialog", "data-hks-filter-open", "data-hks-filter-close", "hks-filter-is-open", "cancel", "event.target === dialog", "returnFocus", "matchMedia('(min-width: 64rem)"])
    require(errors, "utility contact strip", sources["header"], ["info@holidaykenyasafaris.ke", "instagram.com/holidaykenyasafaris", "facebook.com/people/Holiday-Kenya-Safaris/61591508593846", "hks-utility__social", "hks-utility__whatsapp"])
    require(errors, "homepage gallery script", sources["home_gallery"], ["5000", "prefers-reduced-motion", "IntersectionObserver", "pointermove", "ArrowLeft", "ArrowRight", "dataset.hksPosition", "aria-hidden", "is-dragging", "drag.captured", "track.setPointerCapture", "data-hks-home-gallery-pause", "data-hks-home-gallery-progress", "data-hks-home-gallery-details", "data-hks-home-gallery-price", "data-hks-home-gallery-route", "data-hks-home-gallery-included", "dataset.hksTourPrice", "dataset.hksTourRoute", "dataset.hksTourIncluded", "activeIndex", "userPaused", "activeAnimation", "transitionSwapTimer", "animateStageSelection", "cloneImage.decode", "scale(1.025)", "is-copy-ready", "clone.animate", "preload.decode", "visibilitychange"])
    forbid(errors, "homepage gallery timing", sources["home_gallery"], ["2500", "3000"])
    pointer_capture = sources["home_gallery"].find("track.setPointerCapture")
    drag_threshold = sources["home_gallery"].find("drag.moved = true")
    if pointer_capture < drag_threshold:
        errors.append("homepage gallery must capture the pointer only after a real drag begins")
    require(errors, "Tour UI script", sources["tour_ui"], ["role', 'tablist", "ArrowRight", "matchMedia('(min-width: 769px)", "tour_gallery_open", "tour_section_open", "itinerary_toggle", "related_tour_select", "selectPreview", "data-hks-gallery-thumb", "data-hks-gallery-more-open", "openDialogAt", "data-hks-gallery-stage-prev", "data-hks-gallery-stage-next", "hksGalleryStageSrc", "hksGalleryInterval", "5000", "scheduleAutoplay", "IntersectionObserver", "visibilitychange", "prefers-reduced-motion", "aria-pressed", "stageImageReady", "stageImage?.addEventListener('load'", "if (!canAutoplay()) return", "if (stageImageReady) preloadPreview(previewIndex + 1)"])
    require(errors, "quote block", sources["quote"], ["$attributes['label']", "$attributes['mode']", "Request a quote", "InquiryRepository::REST_NAMESPACE", "data-hks-inquiry-form", "data-hks-whatsapp-launch", "data-hks-email-launch", "info@holidaykenyasafaris.ke", "'email', __( 'Email address', 'hks-core' ), 'email', 'email', true", "group_context", "group_fields", "data-hks-inquiry-inline", "destination_selection", "tour_selection", "data-form-token"])
    require(errors, "Group Travel inquiry script", sources["inquiry_script"], ["destination_selection", "tour_selection", "syncGroupTour", "filterGroupTours", "destination_id", "inquiry_route", "group_travel"])
    require(errors, "Group Travel inquiry storage", sources["inquiry_repository"], ["_hks_inquiry_email", "_hks_inquiry_destination", "_hks_inquiry_route", "destination_label", "group_travel"])
    require(errors, "Group Travel inquiry administration", sources["inquiry_admin"], ["Inquiry route", "Destination", "Group Travel page"])
    require(errors, "Group Travel inquiry styling", sources["inquiry_style"], [".hks-inquiry--inline", ".hks-inquiry__group-choice"])

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
        if relative in {
            "blocks/article-archive-intro/block.json",
            "blocks/article-card/block.json",
            "blocks/article-page/block.json",
            "blocks/destination-guides/block.json",
        } and "Holiday Kenya Safaris" not in str(block.get("title", "")):
            errors.append(f"{relative} must spell Holiday Kenya Safaris in full in its editor title")
        if block.get("supports", {}).get("html") is not False:
            errors.append(f"{relative} must disable unrestricted HTML")

    try:
        quote_block = json.loads((PLUGIN / "blocks" / "quote-cta" / "block.json").read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as error:
        errors.append(f"invalid HKS quote block metadata: {error}")
    else:
        if quote_block.get("attributes", {}).get("label", {}).get("type") != "string":
            errors.append("HKS quote block must expose the optional presentation-only label attribute")
        if quote_block.get("attributes", {}).get("mode", {}).get("type") != "string":
            errors.append("HKS quote block must expose the presentation mode attribute")

    if errors:
        print("Public-template validation failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print("Public-template validation passed (optional Tour and Campaign prices, shared quote conversion, responsive accessibility).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
