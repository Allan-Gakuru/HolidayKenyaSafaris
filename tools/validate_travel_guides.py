#!/usr/bin/env python3
"""Static regression checks for the Holiday Kenya Safaris Travel Guides system.

This validator deliberately checks the seams between the native WordPress Post
model, SCF fields, the existing quote journey, and the public block theme.  It
does not load WordPress or publish content; runtime layout and live routing
remain deployment/browser checks.
"""

from __future__ import annotations

import json
import re
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
PLUGIN = ROOT / "wp-content" / "plugins" / "hks-core"
THEME = ROOT / "wp-content" / "themes" / "hks-wayfinder"
PROTOTYPES = ROOT / "outputs" / "travel-guides-prototypes"

ERRORS: list[str] = []


def read(relative: str) -> str:
    path = ROOT / relative
    try:
        return path.read_text(encoding="utf-8-sig")
    except (OSError, UnicodeError) as exc:
        ERRORS.append(f"cannot read {relative}: {exc}")
        return ""


def require(label: str, text: str, *markers: str) -> None:
    for marker in markers:
        if marker not in text:
            ERRORS.append(f"{label}: missing `{marker}`")


def require_regex(label: str, text: str, pattern: str, explanation: str) -> None:
    if not re.search(pattern, text, re.MULTILINE | re.DOTALL):
        ERRORS.append(f"{label}: {explanation}")


def json_file(relative: str) -> dict:
    text = read(relative)
    try:
        value = json.loads(text)
    except json.JSONDecodeError as exc:
        ERRORS.append(f"{relative}: invalid JSON: {exc}")
        return {}
    if not isinstance(value, dict):
        ERRORS.append(f"{relative}: expected a JSON object")
        return {}
    return value


def check_taxonomies() -> None:
    destination = read("wp-content/plugins/hks-core/src/Content/Taxonomies/Destination.php")
    topic = read("wp-content/plugins/hks-core/src/Content/Taxonomies/ArticleTopic.php")
    module = read("wp-content/plugins/hks-core/src/Content/Module.php")

    require(
        "shared Destination taxonomy",
        destination,
        "register_taxonomy(",
        "array( 'hks_tour', 'post' )",
        "'public'             => true",
        "'publicly_queryable' => true",
        "'show_in_rest'       => true",
        "'slug'         => 'destinations'",
    )
    require(
        "Travel Guide Topic taxonomy",
        topic,
        "register_taxonomy(",
        "array( 'post' )",
        "'public'             => true",
        "'publicly_queryable' => true",
        "'hierarchical'       => true",
        "'show_in_rest'       => true",
        "'slug'         => 'travel-guides/topics'",
    )
    require("content module", module, "ArticleTopic::register();", "Destination::register();")

    seed_pairs = dict(re.findall(r"'([^']+)'\s*=>\s*'([^']+)'", topic))
    expected = {
        "Destination Guides": "destination-guides",
        "Planning & FAQs": "planning-faqs",
        "Travel Inspiration": "travel-inspiration",
        "Comparisons": "comparisons",
        "Holiday Kenya Safaris News": "holiday-kenya-safaris-news",
    }
    actual = {name: slug for name, slug in seed_pairs.items() if slug in set(expected.values()) or name in expected}
    if actual != expected:
        ERRORS.append(f"Travel Guide Topic seed terms: expected exactly {expected}, found {actual}")

    destination_seed_pairs = dict(re.findall(r"'([^']+)'\s*=>\s*'([^']+)'", destination))
    expected_destinations = {"Diani": "diani", "Dubai": "dubai", "Maasai Mara": "maasai-mara"}
    actual_destinations = {
        name: slug
        for name, slug in destination_seed_pairs.items()
        if slug in set(expected_destinations.values()) or name in expected_destinations
    }
    if actual_destinations != expected_destinations:
        ERRORS.append(
            f"anchor Destination seed terms: expected exactly {expected_destinations}, found {actual_destinations}"
        )


def check_scf() -> None:
    fields = read("wp-content/plugins/hks-core/src/Fields/FieldGroups.php")
    require(
        "SCF Travel Guide group",
        fields,
        "private static function article_public_group()",
        "'article_public'",
        "self::location( 'post_type', 'post' )",
        "'hks_article_format'",
        "'guide' =>",
        "'advertorial' =>",
        "'required' => 1",
        "'hks_article_primary_tour'",
        "self::post_object_args( 'hks_tour', false )",
        "'allow_null' => 1",
        "'hks_article_related_posts'",
        "'post_type' => array( 'post' )",
        "'post_status' => array( 'publish' )",
        "'multiple' => 1",
        "'max' => 3",
        "'hks_article_destination'",
        "'taxonomy' => Destination::TAXONOMY",
        "'save_terms' => 1",
        "'load_terms' => 1",
        "'hks_article_topic'",
        "'taxonomy' => ArticleTopic::TAXONOMY",
    )
    require(
        "optional featured image model",
        fields,
        "optional featured image",
        "Native title, editor, excerpt, featured image, URL and publication status",
    )
    if re.search(r"hks_(?:featured|hero_image).*required['\"]?\s*=>\s*1", fields, re.IGNORECASE):
        ERRORS.append("SCF Travel Guide group: featured image is accidentally required")


def check_publication_guards() -> None:
    rules = read("wp-content/plugins/hks-core/src/Fields/PublicationRules.php")
    guard = read("wp-content/plugins/hks-core/src/Fields/PublicationGuard.php")
    require(
        "Travel Guide publication rules",
        rules,
        "if ( self::ARTICLE_POST_TYPE === $post_type )",
        "public static function validate_article",
        "hks_article_format",
        "hks_article_primary_tour",
        "hks_article_related_posts",
        "hks_advertorial_primary_tour_required",
        "1 !== count( $primary_tours )",
        "is_published_tour",
        "hks_article_related_posts_limit",
        "hks_article_related_post_self",
        "'post' !== get_post_type( $related_id )",
        "'publish' !== get_post_status( $related_id )",
    )
    # A Guide may omit the relationship; only the advertorial branch makes it
    # mandatory, while a supplied relationship is still publication-checked.
    require_regex(
        "Guide optional Primary Tour",
        rules,
        r"if \( 'advertorial' === \$format && 1 !== count\( \$primary_tours \) \)",
        "the Primary Tour requirement is scoped to advertorials",
    )
    require(
        "native Post guards",
        guard,
        "add_filter( 'rest_pre_insert_post'",
        "add_filter( 'wp_insert_post_data'",
        "add_action( 'acf/validate_save_post'",
        "array( Tour::POST_TYPE, Campaign::POST_TYPE, 'post' )",
        "Travel Guide",
    )


def check_quote_context() -> None:
    quote = read("wp-content/plugins/hks-core/src/Conversion/QuoteBlock.php")
    require(
        "article quote context",
        quote,
        "elseif ( 'post' === $post_type )",
        "self::field( 'hks_article_primary_tour', $post_id )",
        "FormToken::issue",
        "Review quote request",
        "data-hks-email-launch",
        "data-launch-endpoint",
    )
    require_regex(
        "article quote context",
        quote,
        r"'article_id'\s*=>\s*'article' === \$page_type \? \$post_id : 0.*?'page_type'\s*=>\s*\$page_type",
        "article context must carry page type and anonymous article ID into the existing intake",
    )
    if re.search(r"context\(\).*?wa\.me|article.*?wa\.me", quote, re.IGNORECASE | re.DOTALL):
        ERRORS.append("article quote context: constructs a direct wa.me URL instead of using the existing intake")


def check_theme() -> None:
    article_blocks = read("wp-content/themes/hks-wayfinder/inc/ArticleBlocks.php")
    functions = read("wp-content/themes/hks-wayfinder/functions.php")
    style = read("wp-content/themes/hks-wayfinder/style.css")
    destination_template = read("wp-content/themes/hks-wayfinder/templates/taxonomy-hks_destination.html")
    home = read("wp-content/themes/hks-wayfinder/templates/home.html")
    topic_template = read("wp-content/themes/hks-wayfinder/templates/taxonomy-hks_article_topic.html")
    single_post = read("wp-content/themes/hks-wayfinder/templates/single-post.html")

    require(
        "article presentation blocks",
        article_blocks,
        "final class ArticleBlocks",
        "'article-archive-intro' => 'render_archive_intro'",
        "'article-card'         => 'render_article_card'",
        "'article-page'         => 'render_article_page'",
        "'destination-guides'   => 'render_destination_guides'",
        "public static function render_article_page",
        "public static function render_destination_guides",
    )
    for block, name in {
        "article-archive-intro": "hks-wayfinder/article-archive-intro",
        "article-card": "hks-wayfinder/article-card",
        "article-page": "hks-wayfinder/article-page",
        "destination-guides": "hks-wayfinder/destination-guides",
    }.items():
        metadata = json_file(f"wp-content/themes/hks-wayfinder/blocks/{block}/block.json")
        if metadata.get("name") != name:
            ERRORS.append(f"{block} block metadata: expected name {name!r}, found {metadata.get('name')!r}")

    require("Travel Guides hub", home, "postType\":\"post\"", "hks-wayfinder/article-archive-intro", "hks-wayfinder/article-card")
    require("Travel Guide topic archive", topic_template, "postType\":\"post\"", "hks-wayfinder/article-archive-intro", "hks-wayfinder/article-card")
    require("native Post template", single_post, "hks-wayfinder/article-page")
    require(
        "Destination Tour-first archive",
        destination_template,
        "postType\":\"hks_tour\"",
        "hks-wayfinder/destination-guides",
    )
    require(
        "article assets",
        functions,
        "is_singular( 'post' )",
        "assets/js/article-ui.js",
        "ArticleBlocks::class, 'register'",
    )

    require(
        "Advertorial article outline",
        article_blocks,
        "private static function prepare_article_outline",
        "private static function render_article_toc",
        "private static function render_article_toc_items",
        "WP_HTML_Processor::create_fragment",
        "'H2' === $tag || 'H3' === $tag",
        "set_attribute( 'id'",
        "get_last_error()",
        "What we’ll cover",
        "hks-article-toc__list",
        "hks-article-toc__columns",
        "hks-article-toc__list--mobile",
        'id="hks-article-toc"',
    )
    outline_call = article_blocks.find("$outline      = $is_ad ? self::prepare_article_outline")
    cover_start = article_blocks.find('hks-article-hero--advertorial-cover')
    cover_end = article_blocks.find("</header>", cover_start)
    toc_output = article_blocks.find('class="hks-article-outline"', cover_end)
    layout_output = article_blocks.find('class="hks-article-layout', toc_output)
    excerpt_output = article_blocks.find('class="hks-article-hero__promise"')
    quote_output = article_blocks.find("data-hks-article-early-quote")
    if min(outline_call, cover_start, cover_end, excerpt_output, quote_output, toc_output, layout_output) < 0:
        ERRORS.append("Advertorial article outline: static hero, opening actions, or post-hero TOC output is missing")
    elif not (cover_start < excerpt_output < quote_output < cover_end < toc_output < layout_output):
        ERRORS.append("Advertorial article outline: TOC must render immediately after the static hero and before article content")
    require(
        "Advertorial static hero",
        article_blocks,
        "$image_id = get_post_thumbnail_id( $tour_id );",
        "hks-article-hero__wash",
        "'sizes' => '100vw'",
        "Book this tour now",
        "hks-article-hero__cta--outline",
        "hks-article-hero__learn",
        "$learn_more_target",
    )
    require(
        "Advertorial article outline styling",
        style,
        ".hks-article-outline {",
        ".hks-article-toc {",
        ".hks-article-toc__columns {",
        ".hks-article-toc__column {",
        ".hks-article-toc__list--mobile { display: none; }",
        ".hks-article-toc__list ul {",
        ".hks-article-toc__columns { display: none; }",
        ".hks-article-toc__list--mobile { display: grid; gap: 1.25rem; }",
        ".hks-article-content :is(h2, h3)[id] { scroll-margin-top:",
    )
    toc_style_start = style.find(".hks-article-toc {")
    toc_style_end = style.find(".hks-article-layout {", toc_style_start)
    toc_style = style[toc_style_start:toc_style_end]
    if "position: sticky" in toc_style:
        ERRORS.append("Advertorial article outline styling: TOC must remain fully expanded in normal flow")
    if ".hks-article-toc__list > li:nth-child(" in toc_style:
        ERRORS.append("Advertorial article outline styling: interleaved floats can reintroduce cross-column gaps")
    require(
        "Advertorial static cover styling",
        style,
        ".hks-article--advertorial .hks-article-hero--advertorial-cover {",
        ".hks-article--advertorial .hks-article-hero__media img {",
        ".hks-article--advertorial .hks-article-hero__wash {",
        ".hks-article-hero__actions {",
        ".hks-article--advertorial .hks-article-hero__cta--outline {",
        ".hks-article--advertorial .hks-article-hero__learn {",
        "flex-direction: column;",
    )
    if re.search(r"\.hks-article--advertorial\s+\.hks-article-hero__media\s*\{[^}]*display:\s*none", style, re.DOTALL):
        ERRORS.append("Advertorial static cover styling: hero image must remain visible on mobile")
    require(
        "Advertorial desktop quote reassurances",
        article_blocks,
        "hks-article-conversion-panel__reassurances",
        "Inclusions and exclusions clarified",
        "No booking commitment required",
        "Fast Responses to all queries",
    )
    if "Share your dates and group details, review the prepared message, then choose WhatsApp or email." in article_blocks:
        ERRORS.append("Advertorial desktop quote reassurances: superseded instruction paragraph still renders")
    require(
        "Advertorial mobile quote-panel reduction",
        style,
        ".hks-article-conversion-panel__reassurances,",
    )

    # No author identity is allowed in the public article renderer, article
    # templates, or examples. Empty Query author attributes are not display.
    public_article_text = "\n".join((article_blocks, single_post, home, topic_template))
    if re.search(r"get_the_author|wp:post-author|post-author-name|\bBy\s+[A-Z][a-z]+", public_article_text):
        ERRORS.append("public article render/templates: visible author output detected")

    # Related content is strictly native Posts and must be selected in the
    # documented priority: manual, same Destination, then same Article Topic.
    related = article_blocks[article_blocks.find("private static function render_related_posts"):]
    require(
        "related Travel Guides",
        related,
        "hks_article_related_posts",
        "'post' === get_post_type( $id )",
        "'publish' === get_post_status( $id )",
        "post_type' => 'post'",
        "post__not_in",
        "post__in",
        "orderby' => 'post__in'",
        "array_unique( $ids )",
        "hks_destination",
        "hks_article_topic",
    )
    if re.search(r"tax_query.*?relation['\"]?\s*=>\s*['\"]OR['\"]", related, re.DOTALL | re.IGNORECASE):
        ERRORS.append("related Travel Guides: Destination and Article Topic are combined into one OR tax query; destination priority is not enforceable")
    destination_index = related.find("'hks_destination'")
    topic_index = related.find("'hks_article_topic'")
    if destination_index < 0 or topic_index < 0 or destination_index > topic_index:
        ERRORS.append("related Travel Guides: same-Destination selection must be evaluated before same-Topic selection")

    # Destination navigation and counts must stay based on published Tours,
    # even though Posts share the taxonomy.
    populated = functions[functions.find("function hks_wayfinder_populated_terms"):]
    require(
        "Destination Tour-only navigation/counts",
        populated,
        "'hks_destination' === $taxonomy",
        "'post_type'      => 'hks_tour'",
        "'post_status'    => 'publish'",
        "wp_get_object_terms( $tour_ids, $taxonomy",
    )
    require(
        "Destination Tour-only main query",
        functions,
        "if ( $query->is_tax( 'hks_article_topic' ) )",
        "$query->set( 'post_type', 'hks_tour' );",
        "$query->set( 'post_status', 'publish' );",
    )


def check_analytics() -> None:
    article_js = read("wp-content/themes/hks-wayfinder/assets/js/article-ui.js")
    inquiry_js = read("wp-content/plugins/hks-core/assets/js/inquiry.js")
    require("Travel Guide analytics", article_js, "track('view_article'", "track('article_primary_tour_click'", "window.dataLayer.push(payload)")
    require("existing quote analytics", inquiry_js, "quote_cta_click", "quote_form_complete", "whatsapp_launch", "email_launch", "article_id", "article_format")
    if re.search(r"\b(name|phone|email|preferred_date|travelers|budget_range)\b", article_js, re.IGNORECASE):
        ERRORS.append("Travel Guide analytics: article events contain inquiry/PII field names")
    if "window.location" in article_js and "search" in article_js:
        ERRORS.append("Travel Guide analytics: URL query parameters are being sent with article events")


def check_public_copy_and_prototypes() -> None:
    files = {
        "travel-guides-hub.html": read("outputs/travel-guides-prototypes/travel-guides-hub.html"),
        "standard-guide.html": read("outputs/travel-guides-prototypes/standard-guide.html"),
        "advertorial.html": read("outputs/travel-guides-prototypes/advertorial.html"),
    }
    article_renderer = read("wp-content/themes/hks-wayfinder/inc/ArticleBlocks.php")
    require("article public copy", article_renderer, "Holiday Kenya Safaris")
    if re.search(r"\bHKS\b", article_renderer):
        # Class names, PHP namespaces, and comments may use the internal HKS
        # prefix; public translation/HTML strings must use the full brand.
        for line_number, line in enumerate(article_renderer.splitlines(), 1):
            code_line = re.sub(r"//.*$", "", line)
            code_line = re.sub(r"/\*.*?\*/", "", code_line)
            if re.search(r"\bHKS\b", code_line) and not code_line.lstrip().startswith(("*", "//", "/*", "*/")):
                ERRORS.append(f"ArticleBlocks.php:{line_number}: public article source uses the HKS abbreviation")
    for name, html in files.items():
        if html.count("<h1") != 1:
            ERRORS.append(f"{name}: expected exactly one H1, found {html.count('<h1')}")
        if "Holiday Kenya Safaris" not in html:
            ERRORS.append(f"{name}: public copy must spell Holiday Kenya Safaris in full")
        if re.search(r"\bHKS\b", html):
            ERRORS.append(f"{name}: public copy uses the HKS abbreviation")
        if re.search(r"get_the_author|wp:post-author|\bBy\s+[A-Z][a-z]+", html):
            ERRORS.append(f"{name}: visible author output detected")
        if "wa.me/" in html:
            ERRORS.append(f"{name}: prototype bypasses the existing intake with a direct wa.me link")
        logo = "../../wp-content/themes/hks-wayfinder/assets/images/brand/holiday-kenya-safaris-logo.svg"
        if logo not in html:
            ERRORS.append(f"{name}: must reference the existing production logo at {logo}")

    require("standard guide CRO label", files["standard-guide.html"], "View this trip")
    require("advertorial tour label", files["advertorial.html"], "View this trip")
    require("advertorial quote label", files["advertorial.html"], "Request quote on WhatsApp")
    if "Request a quote on WhatsApp" in files["advertorial.html"]:
        ERRORS.append("advertorial.html: use the canonical `Request quote on WhatsApp` label without the extra article")

    css = read("outputs/travel-guides-prototypes/assets/prototype.css")
    js = read("outputs/travel-guides-prototypes/assets/prototype.js")
    require("prototype assets", css, ".article-grid", ".mobile-quote-bar", "@media")
    require("prototype interactions", js, "data-open-quote", "data-quote-modal")


def main() -> int:
    check_taxonomies()
    check_scf()
    check_publication_guards()
    check_quote_context()
    check_theme()
    check_analytics()
    check_public_copy_and_prototypes()

    if ERRORS:
        print("Travel Guides validation failed:", file=sys.stderr)
        for error in ERRORS:
            print(f"- {error}", file=sys.stderr)
        return 1

    print("Travel Guides validation passed (shared taxonomy, SCF fields, publication guards, intake CRO, Tour-only destination discovery, analytics privacy, and prototypes).")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
