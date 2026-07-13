#!/usr/bin/env python3
"""Build the Holiday Kenya Safaris Wayfinder identity assets.

The script keeps the icon geometry and outlined wordmark deterministic. It uses
the official OFL-licensed Sora variable font only at build time; generated SVGs
contain paths rather than live text.
"""

from __future__ import annotations

import hashlib
import json
import math
import tempfile
from pathlib import Path
from typing import Iterable
from xml.etree import ElementTree

from PIL import Image, ImageDraw, ImageFont
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.ttLib import TTFont
from fontTools.varLib.instancer import instantiateVariableFont


ROOT = Path(__file__).resolve().parents[2]
FONT_PATH = ROOT / "brand" / "source-fonts" / "Sora-wght.ttf"
MASTER_DIR = ROOT / "brand" / "masters"
EXPORT_DIR = ROOT / "brand" / "exports"
MANIFEST_PATH = ROOT / "brand" / "manifest.json"

NAVY = "#182B3A"
TEAL = "#2C7A78"
SAFFRON = "#E1A62B"
PALE_MIST = "#F3F1EA"
WHITE = "#FFFFFF"

ICON_SIZE = 256
HORIZONTAL_WIDTH = 720
HORIZONTAL_HEIGHT = 256
STACKED_WIDTH = 480
STACKED_HEIGHT = 500
WORDMARK_SCALE = 0.056
MONOGRAM_SCALE_X = 0.055
MONOGRAM_SCALE_Y = 0.11
MONOGRAM_TRACKING = 100

PALETTES = {
    "primary": {
        "ring": NAVY,
        "monogram": NAVY,
        "cardinal": TEAL,
        "east": SAFFRON,
        "line_one": NAVY,
        "line_two": TEAL,
    },
    "navy": {
        "ring": NAVY,
        "monogram": NAVY,
        "cardinal": NAVY,
        "east": NAVY,
        "line_one": NAVY,
        "line_two": NAVY,
    },
    "white": {
        "ring": WHITE,
        "monogram": WHITE,
        "cardinal": WHITE,
        "east": WHITE,
        "line_one": WHITE,
        "line_two": WHITE,
    },
    "reversed": {
        "ring": WHITE,
        "monogram": WHITE,
        "cardinal": WHITE,
        "east": SAFFRON,
        "line_one": WHITE,
        "line_two": WHITE,
    },
}


def fmt(value: float) -> str:
    value = round(value, 3)
    if value == int(value):
        return str(int(value))
    return f"{value:.3f}".rstrip("0").rstrip(".")


def static_font(weight: int) -> TTFont:
    font = TTFont(str(FONT_PATH))
    return instantiateVariableFont(font, {"wght": weight}, inplace=False)


def pair_adjustment(font: TTFont, left: str, right: str) -> int:
    """Return the GPOS pair x-advance adjustment for a glyph pair."""

    if "GPOS" not in font:
        return 0

    total = 0
    lookups = font["GPOS"].table.LookupList.Lookup
    for lookup in lookups:
        subtables = []
        if lookup.LookupType == 2:
            subtables = lookup.SubTable
        elif lookup.LookupType == 9:
            subtables = [
                table.ExtSubTable
                for table in lookup.SubTable
                if table.ExtensionLookupType == 2
            ]

        for table in subtables:
            coverage = getattr(getattr(table, "Coverage", None), "glyphs", [])
            if left not in coverage:
                continue

            if table.Format == 1:
                pair_set = table.PairSet[coverage.index(left)]
                for record in pair_set.PairValueRecord:
                    if record.SecondGlyph == right and record.Value1:
                        total += record.Value1.XAdvance or 0
            elif table.Format == 2:
                left_class = table.ClassDef1.classDefs.get(left, 0)
                right_class = table.ClassDef2.classDefs.get(right, 0)
                record = table.Class1Record[left_class].Class2Record[right_class]
                if record.Value1:
                    total += record.Value1.XAdvance or 0

    return total


def text_width(text: str, font: TTFont, tracking: int = 0) -> int:
    cmap = font.getBestCmap()
    metrics = font["hmtx"].metrics
    width = 0
    for index, character in enumerate(text):
        glyph = cmap[ord(character)]
        width += metrics[glyph][0]
        if index < len(text) - 1:
            next_glyph = cmap[ord(text[index + 1])]
            width += pair_adjustment(font, glyph, next_glyph) + tracking
    return width


def outline_text(
    text: str,
    font: TTFont,
    x: float,
    baseline: float,
    scale_x: float,
    scale_y: float,
    fill: str,
    tracking: int = 0,
) -> list[str]:
    cmap = font.getBestCmap()
    metrics = font["hmtx"].metrics
    glyph_set = font.getGlyphSet()
    cursor = 0
    paths: list[str] = []

    for index, character in enumerate(text):
        glyph_name = cmap[ord(character)]
        if character != " ":
            pen = SVGPathPen(glyph_set)
            transform = TransformPen(
                pen,
                (
                    scale_x,
                    0,
                    0,
                    -scale_y,
                    x + cursor * scale_x,
                    baseline,
                ),
            )
            glyph_set[glyph_name].draw(transform)
            command = pen.getCommands()
            if command:
                paths.append(
                    f'<path d="{command}" fill="{fill}" fill-rule="nonzero"/>'
                )

        cursor += metrics[glyph_name][0]
        if index < len(text) - 1:
            next_glyph = cmap[ord(text[index + 1])]
            cursor += pair_adjustment(font, glyph_name, next_glyph) + tracking

    return paths


def polar(cx: float, cy: float, radius: float, degrees: float) -> tuple[float, float]:
    radians = math.radians(degrees)
    return cx + radius * math.cos(radians), cy + radius * math.sin(radians)


def arc_path(
    cx: float, cy: float, radius: float, start: float, end: float
) -> str:
    x1, y1 = polar(cx, cy, radius, start)
    x2, y2 = polar(cx, cy, radius, end)
    large_arc = 1 if end - start > 180 else 0
    return (
        f"M {fmt(x1)} {fmt(y1)} "
        f"A {fmt(radius)} {fmt(radius)} 0 {large_arc} 1 {fmt(x2)} {fmt(y2)}"
    )


def polygon(points: Iterable[tuple[float, float]], fill: str) -> str:
    encoded = " ".join(f"{fmt(x)},{fmt(y)}" for x, y in points)
    return f'<polygon points="{encoded}" fill="{fill}"/>'


def icon_elements(
    font: TTFont, palette: dict[str, str], simplified: bool = False
) -> list[str]:
    elements: list[str] = []

    if simplified:
        elements.append(
            f'<path d="{arc_path(128, 128, 89, 33, 327)}" fill="none" '
            f'stroke="{palette["ring"]}" stroke-width="15" stroke-linecap="butt"/>'
        )
        elements.append(
            polygon(((246, 128), (194, 111), (202, 128), (194, 145)), palette["east"])
        )
    else:
        for start, end in ((14, 76), (104, 166), (194, 256), (284, 346)):
            elements.append(
                f'<path d="{arc_path(128, 128, 89, start, end)}" fill="none" '
                f'stroke="{palette["ring"]}" stroke-width="15" stroke-linecap="butt"/>'
            )
        elements.extend(
            (
                polygon(((128, 10), (144, 65), (112, 65)), palette["cardinal"]),
                polygon(((246, 128), (198, 109), (198, 147)), palette["east"]),
                polygon(((128, 246), (112, 191), (144, 191)), palette["cardinal"]),
                polygon(((10, 128), (65, 144), (65, 112)), palette["cardinal"]),
            )
        )

    monogram_width = (
        text_width("HKS", font, tracking=MONOGRAM_TRACKING) * MONOGRAM_SCALE_X
    )
    monogram_x = (ICON_SIZE - monogram_width) / 2
    elements.extend(
        outline_text(
            "HKS",
            font,
            monogram_x,
            169,
            MONOGRAM_SCALE_X,
            MONOGRAM_SCALE_Y,
            palette["monogram"],
            tracking=MONOGRAM_TRACKING,
        )
    )
    return elements


def favicon_elements(palette: dict[str, str]) -> list[str]:
    """Return a deliberately simplified mark that remains legible at 16 px."""

    return [
        (
            f'<path d="{arc_path(128, 128, 89, 33, 327)}" fill="none" '
            f'stroke="{palette["ring"]}" stroke-width="17" stroke-linecap="butt"/>'
        ),
        polygon(
            ((246, 128), (194, 107), (204, 128), (194, 149)), palette["east"]
        ),
        (
            '<path d="M84 82H106V117H150V82H172V174H150V138H106V174H84Z" '
            f'fill="{palette["monogram"]}"/>'
        ),
    ]


def wordmark_elements(
    font: TTFont,
    palette: dict[str, str],
    x: float,
    first_baseline: float,
    second_baseline: float,
    centered_width: float | None = None,
) -> list[str]:
    first_width = text_width("Holiday Kenya", font) * WORDMARK_SCALE
    second_width = text_width("Safaris", font) * WORDMARK_SCALE
    first_x = x
    second_x = x
    if centered_width is not None:
        first_x = x + (centered_width - first_width) / 2
        second_x = x + (centered_width - second_width) / 2

    return [
        *outline_text(
            "Holiday Kenya",
            font,
            first_x,
            first_baseline,
            WORDMARK_SCALE,
            WORDMARK_SCALE,
            palette["line_one"],
        ),
        *outline_text(
            "Safaris",
            font,
            second_x,
            second_baseline,
            WORDMARK_SCALE,
            WORDMARK_SCALE,
            palette["line_two"],
        ),
    ]


def svg_document(
    width: int, height: int, title: str, elements: Iterable[str]
) -> str:
    body = "\n  ".join(elements)
    return (
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {width} {height}" '
        f'role="img" aria-labelledby="logo-title">\n'
        f'  <title id="logo-title">{title}</title>\n'
        f"  {body}\n"
        "</svg>\n"
    )


def write_svg(name: str, content: str) -> Path:
    path = MASTER_DIR / name
    path.write_text(content, encoding="utf-8", newline="\n")
    return path


def build_svgs(monogram_font: TTFont, wordmark_font: TTFont) -> list[Path]:
    paths: list[Path] = []

    for variant in ("primary", "navy", "white", "reversed"):
        palette = PALETTES[variant]
        paths.append(
            write_svg(
                f"hks-wayfinder-icon-{variant}.svg",
                svg_document(
                    ICON_SIZE,
                    ICON_SIZE,
                    "Holiday Kenya Safaris Wayfinder icon",
                    icon_elements(monogram_font, palette),
                ),
            )
        )

    paths.append(
        write_svg(
            "hks-wayfinder-favicon.svg",
            svg_document(
                ICON_SIZE,
                ICON_SIZE,
                "Holiday Kenya Safaris compact Wayfinder icon",
                favicon_elements(PALETTES["primary"]),
            ),
        )
    )

    for variant in ("primary", "navy", "white", "reversed"):
        palette = PALETTES[variant]
        horizontal = [
            '<g aria-hidden="true">',
            *[f"  {item}" for item in icon_elements(monogram_font, palette)],
            "</g>",
            *wordmark_elements(wordmark_font, palette, 282, 118, 188),
        ]
        paths.append(
            write_svg(
                f"hks-wayfinder-horizontal-{variant}.svg",
                svg_document(
                    HORIZONTAL_WIDTH,
                    HORIZONTAL_HEIGHT,
                    "Holiday Kenya Safaris",
                    horizontal,
                ),
            )
        )

        stacked_icon = [
            '<g transform="translate(112 20)" aria-hidden="true">',
            *[f"  {item}" for item in icon_elements(monogram_font, palette)],
            "</g>",
        ]
        stacked_wordmark = wordmark_elements(
            wordmark_font, palette, 30, 358, 428, centered_width=420
        )
        paths.append(
            write_svg(
                f"hks-wayfinder-stacked-{variant}.svg",
                svg_document(
                    STACKED_WIDTH,
                    STACKED_HEIGHT,
                    "Holiday Kenya Safaris",
                    [*stacked_icon, *stacked_wordmark],
                ),
            )
        )

    wordmark_width = 430
    for variant in ("primary", "navy", "white"):
        palette = PALETTES[variant]
        paths.append(
            write_svg(
                f"hks-wayfinder-wordmark-{variant}.svg",
                svg_document(
                    wordmark_width,
                    150,
                    "Holiday Kenya Safaris",
                    wordmark_elements(wordmark_font, palette, 6, 52, 122),
                ),
            )
        )

    for variant in ("navy", "white"):
        vehicle_elements = [
            '<g transform="translate(32 32)" aria-hidden="true">',
            *[
                f"  {item}"
                for item in icon_elements(monogram_font, PALETTES[variant])
            ],
            "</g>",
            *wordmark_elements(wordmark_font, PALETTES[variant], 314, 150, 220),
        ]
        paths.append(
            write_svg(
                f"hks-wayfinder-vehicle-door-{variant}.svg",
                svg_document(780, 320, "Holiday Kenya Safaris", vehicle_elements),
            )
        )

    return paths


def save_temp_font(font: TTFont, suffix: str) -> Path:
    handle = tempfile.NamedTemporaryFile(prefix=f"hks-{suffix}-", suffix=".ttf", delete=False)
    handle.close()
    path = Path(handle.name)
    font.save(path)
    return path


def rgb(hex_value: str) -> tuple[int, int, int, int]:
    hex_value = hex_value.lstrip("#")
    return (
        int(hex_value[0:2], 16),
        int(hex_value[2:4], 16),
        int(hex_value[4:6], 16),
        255,
    )


def scaled_points(
    points: Iterable[tuple[float, float]], scale: float, offset: tuple[float, float]
) -> list[tuple[float, float]]:
    ox, oy = offset
    return [(ox + x * scale, oy + y * scale) for x, y in points]


def draw_condensed_monogram(
    image: Image.Image,
    font_path: Path,
    fill: str,
    scale: float,
    offset: tuple[float, float],
) -> None:
    font = ImageFont.truetype(str(font_path), max(1, round(130 * scale)))
    tracking = max(1, round(13 * scale))
    advances = [font.getlength(character) for character in "HKS"]
    source_width = round(sum(advances) + tracking * 2 + 16 * scale)
    source_height = round(150 * scale)
    mask = Image.new("L", (source_width, source_height), 0)
    mask_draw = ImageDraw.Draw(mask)
    cursor = round(8 * scale)
    for character, advance in zip("HKS", advances):
        mask_draw.text(
            (cursor, round(8 * scale)),
            character,
            font=font,
            fill=255,
            anchor="lt",
        )
        cursor += round(advance + tracking)
    crop = mask.getbbox()
    if crop is None:
        raise ValueError("The HKS raster monogram rendered empty")
    mask = mask.crop(crop)
    target = (round(127 * scale), round(85 * scale))
    mask = mask.resize(target, Image.Resampling.LANCZOS)
    ox, oy = offset
    x = round(ox + 65 * scale)
    y = round(oy + 86 * scale)
    paste_mask(image, fill, mask, (x, y))


def draw_favicon_monogram(
    image: Image.Image,
    fill: str,
    scale: float,
    offset: tuple[float, float],
) -> None:
    ox, oy = offset
    points = (
        (84, 82),
        (106, 82),
        (106, 117),
        (150, 117),
        (150, 82),
        (172, 82),
        (172, 174),
        (150, 174),
        (150, 138),
        (106, 138),
        (106, 174),
        (84, 174),
    )
    ImageDraw.Draw(image).polygon(
        scaled_points(points, scale, (ox, oy)), fill=rgb(fill)
    )


def paste_mask(
    image: Image.Image,
    color: str,
    mask: Image.Image,
    position: tuple[int, int],
) -> None:
    layer = Image.new("RGBA", mask.size, rgb(color))
    image.paste(layer, position, mask)


def draw_icon_raster(
    image: Image.Image,
    font_path: Path,
    palette: dict[str, str],
    box: tuple[float, float, float],
    simplified: bool = False,
) -> None:
    ox, oy, size = box
    scale = size / ICON_SIZE
    draw = ImageDraw.Draw(image)
    stroke = max(1, round(15 * scale))
    center = (ox + 128 * scale, oy + 128 * scale)
    radius = 89 * scale
    bounds = (
        center[0] - radius,
        center[1] - radius,
        center[0] + radius,
        center[1] + radius,
    )

    if simplified:
        draw.arc(
            bounds,
            33,
            327,
            fill=rgb(palette["ring"]),
            width=max(1, round(17 * scale)),
        )
        draw.polygon(
            scaled_points(
                ((246, 128), (194, 107), (204, 128), (194, 149)), scale, (ox, oy)
            ),
            fill=rgb(palette["east"]),
        )
        draw_favicon_monogram(
            image, palette["monogram"], scale, (ox, oy)
        )
    else:
        for start, end in ((14, 76), (104, 166), (194, 256), (284, 346)):
            draw.arc(bounds, start, end, fill=rgb(palette["ring"]), width=stroke)
        draw.polygon(
            scaled_points(((128, 10), (144, 65), (112, 65)), scale, (ox, oy)),
            fill=rgb(palette["cardinal"]),
        )
        draw.polygon(
            scaled_points(
                ((246, 128), (198, 109), (198, 147)), scale, (ox, oy)
            ),
            fill=rgb(palette["east"]),
        )
        draw.polygon(
            scaled_points(((128, 246), (112, 191), (144, 191)), scale, (ox, oy)),
            fill=rgb(palette["cardinal"]),
        )
        draw.polygon(
            scaled_points(((10, 128), (65, 144), (65, 112)), scale, (ox, oy)),
            fill=rgb(palette["cardinal"]),
        )

    if not simplified:
        draw_condensed_monogram(image, font_path, palette["monogram"], scale, (ox, oy))


def draw_wordmark_raster(
    image: Image.Image,
    font_path: Path,
    palette: dict[str, str],
    scale: float,
    x: float,
    first_baseline: float,
    second_baseline: float,
    centered_width: float | None = None,
) -> None:
    font = ImageFont.truetype(str(font_path), max(1, round(56 * scale)))
    draw = ImageDraw.Draw(image)

    first = "Holiday Kenya"
    second = "Safaris"
    first_x = x
    second_x = x
    if centered_width is not None:
        first_width = draw.textlength(first, font=font)
        second_width = draw.textlength(second, font=font)
        first_x = x + (centered_width * scale - first_width) / 2 / scale
        second_x = x + (centered_width * scale - second_width) / 2 / scale

    draw.text(
        (first_x * scale, first_baseline * scale),
        first,
        font=font,
        fill=rgb(palette["line_one"]),
        anchor="ls",
    )
    draw.text(
        (second_x * scale, second_baseline * scale),
        second,
        font=font,
        fill=rgb(palette["line_two"]),
        anchor="ls",
    )


def render_reference(
    kind: str,
    variant: str,
    monogram_font_path: Path,
    wordmark_font_path: Path,
    scale: int = 4,
) -> Image.Image:
    palette = PALETTES[variant]
    if kind == "horizontal":
        image = Image.new(
            "RGBA", (HORIZONTAL_WIDTH * scale, HORIZONTAL_HEIGHT * scale), (0, 0, 0, 0)
        )
        draw_icon_raster(image, monogram_font_path, palette, (0, 0, ICON_SIZE * scale))
        draw_wordmark_raster(image, wordmark_font_path, palette, scale, 282, 118, 188)
        return image

    if kind == "stacked":
        image = Image.new(
            "RGBA", (STACKED_WIDTH * scale, STACKED_HEIGHT * scale), (0, 0, 0, 0)
        )
        draw_icon_raster(
            image,
            monogram_font_path,
            palette,
            (112 * scale, 20 * scale, ICON_SIZE * scale),
        )
        draw_wordmark_raster(
            image, wordmark_font_path, palette, scale, 30, 358, 428, centered_width=420
        )
        return image

    raise ValueError(f"Unsupported reference kind: {kind}")


def save_resized(image: Image.Image, path: Path, width: int) -> None:
    height = round(image.height * width / image.width)
    resized = image.resize((width, height), Image.Resampling.LANCZOS)
    resized.save(path, optimize=True)


def build_pngs(monogram_font: TTFont, wordmark_font: TTFont) -> list[Path]:
    paths: list[Path] = []
    monogram_path = save_temp_font(monogram_font, "extra-bold")
    wordmark_path = save_temp_font(wordmark_font, "bold")
    try:
        favicon_reference = Image.new("RGBA", (1024, 1024), (0, 0, 0, 0))
        draw_icon_raster(
            favicon_reference,
            monogram_path,
            PALETTES["primary"],
            (0, 0, 1024),
            simplified=True,
        )
        for size in (16, 32, 48):
            output = EXPORT_DIR / f"favicon-{size}.png"
            favicon_reference.resize((size, size), Image.Resampling.LANCZOS).save(
                output, optimize=True
            )
            paths.append(output)

        favicon_ico = EXPORT_DIR / "favicon.ico"
        favicon_reference.resize((256, 256), Image.Resampling.LANCZOS).save(
            favicon_ico, format="ICO", sizes=[(16, 16), (32, 32), (48, 48)]
        )
        paths.append(favicon_ico)

        for variant in ("primary", "navy", "reversed"):
            full_icon = Image.new("RGBA", (2048, 2048), (0, 0, 0, 0))
            draw_icon_raster(full_icon, monogram_path, PALETTES[variant], (0, 0, 2048))
            sizes = (180, 512) if variant == "primary" else (512,)
            for size in sizes:
                output = EXPORT_DIR / f"hks-wayfinder-icon-{variant}-{size}.png"
                full_icon.resize((size, size), Image.Resampling.LANCZOS).save(
                    output, optimize=True
                )
                paths.append(output)

        apple = Image.new("RGBA", (720, 720), rgb(PALE_MIST))
        draw_icon_raster(apple, monogram_path, PALETTES["primary"], (80, 80, 560))
        apple_path = EXPORT_DIR / "apple-touch-icon-180.png"
        apple.resize((180, 180), Image.Resampling.LANCZOS).save(apple_path, optimize=True)
        paths.append(apple_path)

        social = Image.new("RGBA", (1024, 1024), rgb(PALE_MIST))
        draw_icon_raster(
            social, monogram_path, PALETTES["primary"], (112, 112, 800)
        )
        social_path = EXPORT_DIR / "social-avatar-512.png"
        social.resize((512, 512), Image.Resampling.LANCZOS).save(
            social_path, optimize=True
        )
        paths.append(social_path)

        for variant in ("primary", "navy", "reversed"):
            horizontal = render_reference(
                "horizontal", variant, monogram_path, wordmark_path
            )
            for width in ((512, 1024) if variant == "primary" else (1024,)):
                output = EXPORT_DIR / f"hks-wayfinder-horizontal-{variant}-{width}.png"
                save_resized(horizontal, output, width)
                paths.append(output)

        for variant in ("primary", "navy", "reversed"):
            stacked = render_reference(
                "stacked", variant, monogram_path, wordmark_path
            )
            stacked_path = (
                EXPORT_DIR / f"hks-wayfinder-stacked-{variant}-1024.png"
            )
            save_resized(stacked, stacked_path, 1024)
            paths.append(stacked_path)

        for variant in ("navy", "white"):
            vehicle = render_reference(
                "horizontal", variant, monogram_path, wordmark_path
            )
            vehicle_path = (
                EXPORT_DIR / f"hks-wayfinder-vehicle-door-{variant}-1600.png"
            )
            save_resized(vehicle, vehicle_path, 1600)
            paths.append(vehicle_path)
    finally:
        # On Windows, Pillow can retain a FreeType handle while unwinding an
        # exception. Never hide the real build error with a cleanup failure.
        for temporary_font in (monogram_path, wordmark_path):
            try:
                temporary_font.unlink(missing_ok=True)
            except PermissionError:
                pass

    return paths


def validate_svg(path: Path) -> None:
    root = ElementTree.parse(path).getroot()
    if not root.tag.endswith("svg"):
        raise ValueError(f"{path} is not an SVG document")
    if "viewBox" not in root.attrib:
        raise ValueError(f"{path} has no viewBox")
    for element in root.iter():
        local_name = element.tag.rsplit("}", 1)[-1]
        if local_name == "text":
            raise ValueError(f"{path} contains live text")
        if local_name in {"linearGradient", "radialGradient"}:
            raise ValueError(f"{path} contains a gradient")


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def build_manifest(paths: Iterable[Path]) -> None:
    assets = []
    for path in sorted(paths):
        entry = {
            "path": path.relative_to(ROOT).as_posix(),
            "bytes": path.stat().st_size,
            "sha256": sha256(path),
        }
        if path.suffix.lower() in {".png", ".ico"}:
            with Image.open(path) as image:
                entry["dimensions"] = [image.width, image.height]
                entry["format"] = image.format
        elif path.suffix.lower() == ".svg":
            root = ElementTree.parse(path).getroot()
            entry["viewBox"] = root.attrib["viewBox"]
            entry["format"] = "SVG"
        assets.append(entry)

    payload = {
        "brand": "Holiday Kenya Safaris",
        "identity": "The Wayfinder",
        "generated_by": "tools/brand/build_logo.py",
        "source_font": "Sora Version 2.000, SIL OFL 1.1",
        "assets": assets,
    }
    MANIFEST_PATH.write_text(
        json.dumps(payload, indent=2) + "\n", encoding="utf-8", newline="\n"
    )


def main() -> None:
    if not FONT_PATH.exists():
        raise SystemExit(f"Missing build-time font: {FONT_PATH}")

    MASTER_DIR.mkdir(parents=True, exist_ok=True)
    EXPORT_DIR.mkdir(parents=True, exist_ok=True)

    for directory, patterns in (
        (MASTER_DIR, ("*.svg",)),
        (EXPORT_DIR, ("*.png", "*.ico")),
    ):
        for pattern in patterns:
            for generated_asset in directory.glob(pattern):
                generated_asset.unlink()

    monogram_font = static_font(800)
    wordmark_font = static_font(700)
    svg_paths = build_svgs(monogram_font, wordmark_font)
    png_paths = build_pngs(monogram_font, wordmark_font)

    for path in svg_paths:
        validate_svg(path)

    build_manifest([*svg_paths, *png_paths])
    print(f"Built {len(svg_paths)} SVG masters and {len(png_paths)} raster exports.")
    print(f"Manifest: {MANIFEST_PATH.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
